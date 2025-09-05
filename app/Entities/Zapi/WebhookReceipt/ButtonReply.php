<?php

namespace App\Entities\Zapi\WebhookReceipt;

use App\Entities\BaseEntity;

class ButtonReply extends BaseEntity
{
    public string $buttonId;

    public string $message;

    public string $referenceMessageId;

    public function __construct(array $data)
    {
        $this->buttonId = data_get($data, 'buttonId');
        $this->message = data_get($data, 'message');
        $this->referenceMessageId = data_get($data, 'referenceMessageId');
    }
}
