<?php

namespace App\Entities\Zapi\WebhookReceipt;

//https://developer.z-api.io/webhooks/on-message-received#retornos-dos-webhooks
use App\Entities\BaseEntity;

class WebhookReceiptResponse extends BaseEntity
{
    public bool $isStatusReply;

    public string $chatLid;

    public string $connectedPhone;

    public bool $waitingMessage;

    public bool $isEdit;

    public bool $isGroup;

    public bool $isNewsletter;

    public string $instanceId;

    public string $messageId;

    public string $phone;

    public bool $fromMe;

    public int $momment;

    public string $status;

    public string $chatName;

    public ?string $senderPhoto;

    public ?string $senderName;

    public ?string $photo;

    public bool $broadcast;

    public ?string $participantLid;

    public ?int $messageExpirationSeconds;

    public bool $forwarded;

    public string $type;

    public bool $fromApi;

    public ?string $referenceMessageId;

    public ?ButtonReply $buttonReply;

    /** @var array{message:string}|null */
    public ?array $text;

    public function __construct(array $data)
    {
        $this->isStatusReply = data_get($data, 'isStatusReply');
        $this->chatLid = data_get($data, 'chatLid');
        $this->connectedPhone = data_get($data, 'connectedPhone');
        $this->waitingMessage = data_get($data, 'waitingMessage');
        $this->isEdit = data_get($data, 'isEdit');
        $this->isGroup = data_get($data, 'isGroup');
        $this->isNewsletter = data_get($data, 'isNewsletter');
        $this->instanceId = data_get($data, 'instanceId');
        $this->messageId = data_get($data, 'messageId');
        $this->phone = data_get($data, 'phone');
        $this->fromMe = data_get($data, 'fromMe');
        $this->momment = data_get($data, 'momment');
        $this->status = data_get($data, 'status');
        $this->chatName = data_get($data, 'chatName');
        $this->senderPhoto = data_get($data, 'senderPhoto');
        $this->senderName = data_get($data, 'senderName');
        $this->photo = data_get($data, 'photo');
        $this->broadcast = data_get($data, 'broadcast');
        $this->participantLid = data_get($data, 'participantLid');
        $this->messageExpirationSeconds = data_get($data, 'messageExpirationSeconds');
        $this->forwarded = data_get($data, 'forwarded');
        $this->type = data_get($data, 'type');
        $this->fromApi = data_get($data, 'fromApi');
        $this->referenceMessageId = data_get($data, 'referenceMessageId');
        $this->buttonReply = isset($data['buttonReply']) ? new ButtonReply($data['buttonReply']) : null;
        $this->text = data_get($data, 'text');
    }
}
