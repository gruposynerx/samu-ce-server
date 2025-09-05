<?php

namespace App\Contracts;

use App\Entities\Zapi\OptionsList;
use Illuminate\Support\Collection;

interface WhatsAppMessageApi
{
    public function getPhones(): array;

    public function setPhones(string|array $phone): self;

    public function sendTextMessage(string $message): Collection;

    public function sendButtonsList(string $message, OptionsList $optionsList): Collection;
}
