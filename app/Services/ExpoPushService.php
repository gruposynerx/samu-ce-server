<?php

namespace App\Services;

use App\Enums\NotificationTypeEnum;
use App\Models\Notification;
use App\Models\NotificationType;
use App\Models\PushToken;
use App\Models\User;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ExpoPushService
{
    protected Client $http;

    protected string $sendEndpoint = 'https://exp.host/--/api/v2/push/send';

    public function __construct()
    {
        $this->http = new Client();
    }

    public function send(string $userId, string $title, string $body, array $data = []): Notification
    {

        $notification = null;
        $typeName = $data['notification_type'];
        $isReminder = isset($data['retry_count']) && $data['retry_count'] > 0;

        if ($isReminder && $typeName === 'fleet_assignment') {
            $typeName = NotificationTypeEnum::FLEET_ASSIGNMENT_REMINDER->value;
        }

        if (!$isReminder && $this->isDuplicateNotification($userId, $typeName, $title, $body, $data)) {
            return $this->getRecentNotification($userId, $typeName, $title, $body);
        }

        $notificationType = NotificationType::where('name', $typeName)->first();
        if (!$notificationType) {
            return new Notification();
        }

        $notificationTypeId = $notificationType->id;
        $userExists = User::where('id', $userId)->exists();

        if (!$userExists) {
            $notification = $this->createNotification(
                null,
                $notificationTypeId,
                $title,
                $body,
                array_merge($data, ['original_user_id' => $userId])
            );
        } else {
            try {
                $notification = $this->createNotification($userId, $notificationTypeId, $title, $body, $data);
                $this->sendPushNotification($notification, $userId);
            } catch (\Exception $e) {
                $notification = $this->createFallbackNotification($userId, $notificationTypeId, $title, $body, $data);
            }
        }

        return $notification;
    }

    private function createNotification(?string $userId, string $notificationTypeId, string $title, string $body, array $data): Notification
    {
        $notificationData = [
            'id' => (string) Str::orderedUuid(),
            'user_id' => $userId,
            'notification_type_id' => $notificationTypeId,
            'title' => $title,
            'body' => $body,
            'data' => $data,
            'sent_at' => now(),
        ];

        $notification = new Notification($notificationData);
        $notification->save();

        return $notification;
    }

    private function sendPushNotification(Notification $notification, string $userId): void
    {
        $pushTokens = $notification->user->pushTokens()
            ->whereNotNull('token')
            ->where('token', '!=', '')
            ->orderBy('created_at', 'desc')
            ->get()
            ->unique('token')
            ->groupBy('device_id')
            ->map(function ($tokens) {
                return $tokens->first();
            })
            ->values();

   
        if ($pushTokens->isEmpty()) {
            return;
        }

        $basePayload = [
            'title' => $notification->title,
            'body' => $notification->body,
            'data' => $notification->data,
            'sound' => 'granules_notification.wav',
            'channelId' => 'default',
            'ios' => [
                'sound' => 'granules_notification.wav',
                'badge' => 1,
                'priority' => 'high',
                'contentAvailable' => true,
            ],
            'android' => [
                'sound' => 'granules_notification.wav',
                'channelId' => 'default',
                'priority' => 'high',
                'vibrate' => [0, 250, 250, 250],
            ],
        ];

        $this->sendBatchPushNotifications($pushTokens, $basePayload, $userId, $notification->id);
    }

    private function sendBatchPushNotifications($pushTokens, array $basePayload, string $userId, string $notificationId): void
    {
        $chunks = $pushTokens->chunk(100);

        foreach ($chunks as $chunk) {
            $messages = [];
            $tokenMap = [];

            foreach ($chunk as $pushToken) {
                $message = array_merge($basePayload, ['to' => $pushToken->token]);
                $messages[] = $message;
                $tokenMap[] = [
                    'token' => $pushToken->token,
                    'device_id' => $pushToken->device_id,
                    'platform' => $pushToken->platform,
                ];
            }

            try {
                $response = $this->http->post($this->sendEndpoint, [
                    'json' => $messages,
                ]);

                $responseBody = json_decode($response->getBody()->getContents(), true);

                $this->processBatchResponse($responseBody, $tokenMap, $userId);

            } catch (\Exception $e) {
               throw new \Exception('Erro ao enviar notificação para frota', 0, $e);
            }
        }
    }

    private function processBatchResponse(array $responseData, array $tokenMap, string $userId): void
    {
        if (!isset($responseData['data']) || !is_array($responseData['data'])) {
            return;
        }

        foreach ($responseData['data'] as $index => $result) {
            if (isset($result['status']) && $result['status'] === 'error') {
                $tokenInfo = $tokenMap[$index] ?? null;

                if ($tokenInfo && isset($result['details']['error'])) {
                    $error = $result['details']['error'];

                    if (in_array($error, ['DeviceNotRegistered', 'InvalidCredentials', 'MessageTooBig'])) {
                        PushToken::where('user_id', $userId)
                            ->where('device_id', $tokenInfo['device_id'])
                            ->delete();
                    }
                }
            }
        }
    }

    private function createFallbackNotification(string $userId, string $notificationTypeId, string $title, string $body, array $data): Notification
    {
        try {
            $fallbackData = array_merge($data, ['original_user_id' => $userId]);

            $notification = $this->createNotification(null, $notificationTypeId, $title, $body, $fallbackData);

            return $notification;
        } catch (\Exception $innerE) {
            Log::critical('ExpoPushService::send - Erro ao criar notificação de fallback', [
                'exception' => get_class($innerE),
                'message' => $innerE->getMessage(),
            ]);

            return new Notification([
                'id' => (string) Str::orderedUuid(),
                'notification_type_id' => $notificationTypeId,
                'title' => $title,
                'body' => $body,
                'data' => $data,
            ]);
        }
    }

    /**
     * Verifica se a notificação é uma duplicata baseada nos últimos 5 minutos
     */
    private function isDuplicateNotification(string $userId, string $typeName, string $title, string $body, array $data): bool
    {
        $notificationType = NotificationType::where('name', $typeName)->first();
        if (!$notificationType) {
            return false;
        }

        $fiveMinutesAgo = now()->subMinutes(1);

        $existingNotification = Notification::where('user_id', $userId)
            ->where('notification_type_id', $notificationType->id)
            ->where('title', $title)
            ->where('body', $body)
            ->where('sent_at', '>=', $fiveMinutesAgo)
            ->first();

        if ($existingNotification) {
            return true;
        }

        return false;
    }

    /**
     * Busca a notificação mais recente do mesmo tipo
     */
    private function getRecentNotification(string $userId, string $typeName, string $title, string $body): Notification
    {
        $notificationType = NotificationType::where('name', $typeName)->first();
        if (!$notificationType) {
            return new Notification();
        }

        $notification = Notification::where('user_id', $userId)
            ->where('notification_type_id', $notificationType->id)
            ->where('title', $title)
            ->where('body', $body)
            ->orderBy('sent_at', 'desc')
            ->first();

        return $notification ?: new Notification();
    }
}
