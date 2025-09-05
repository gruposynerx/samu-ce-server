<?php

namespace App\Entities\Zapi;

class MessagesResponse
{
    /*
     * zaapId: id no z-api
     * messageId: id no WhatsApp
     * id: Adicionado para compatibilidade com zapier, ele tem o mesmo valor do messageId
    */
    public string $apiId;

    public string $messageId;

    public string $id;

    public function __construct(array $data)
    {
        $this->apiId = data_get($data, 'zaapId');
        $this->messageId = data_get($data, 'messageId');
        $this->id = data_get($data, 'id');
    }
}
