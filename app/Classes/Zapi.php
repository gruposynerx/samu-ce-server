<?php

namespace App\Classes;

use App\Contracts\WhatsAppMessageApi;
use App\Entities\Zapi\MessagesResponse;
use App\Entities\Zapi\OptionsList;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Zapi implements WhatsAppMessageApi
{
    protected readonly PendingRequest $client;

    protected array $phones;

    public function __construct()
    {
        $this->client = Http::baseUrl(config('external_services.zapi_url'))
            ->withHeaders([
                'Content-type' => 'application/json',
                'Client-Token' => config('external_services.zapi_security_token'),
            ]);
    }

    public function getPhones(): array
    {
        return $this->phones;
    }

    public function setPhones(string|array $phone): self
    {
        $this->phones = is_array($phone) ? $phone : [$phone];

        return $this;
    }

    /**
     * @return Collection<int, MessagesResponse>
     */
    private function performRequest(string $endpoint, array $data, string $method = 'post'): Collection
    {
        $responses = collect();

        foreach ($this->phones as $phone) {
            $data['phone'] = $phone;

            /** @var \Illuminate\Http\Client\Response $response */
            $response = $this->client->$method($endpoint, $data);

            if ($response->failed()) {
                Log::alert('Zapi request failed', [
                    'phone' => $phone,
                    'endpoint' => $endpoint,
                    'data' => $data,
                    'response' => $response->json(),
                ]);

                continue;
            }

            $responses->push(new MessagesResponse($response->json()));
        }

        return $responses;
    }

    /**
     * @return Collection<int, MessagesResponse>
     */
    public function sendTextMessage(string $message): Collection
    {
        return $this->performRequest('send-text', [
            'phone' => $this->phones,
            'message' => $message,
        ]);
    }

    /**
     * @return Collection<int, MessagesResponse>
     */
    public function sendButtonsList(string $message, OptionsList $optionsList): Collection
    {
        return $this->performRequest('send-button-list', [
            'phone' => $this->phones,
            'message' => $message,
            'optionList' => $optionsList->toArray(),
        ]);
    }

    /**
     * @return Collection<int, MessagesResponse>
     */
    public function sendOptionButtons(string $message, OptionsList $optionsList): Collection
    {
        return $this->performRequest('send-button-actions', [
            'phone' => $this->phones,
            'message' => $message,
            'buttonActions' => $optionsList->options->toArray(),
        ]);
    }
}
