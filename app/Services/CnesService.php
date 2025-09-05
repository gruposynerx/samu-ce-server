<?php

namespace App\Services;

use App\Entities\CNES\HealthUnit;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class CnesService
{
    private PendingRequest $api;

    public function __construct()
    {
        $this->api = Http::baseUrl(config('external_services.cnes_api_url'));
    }

    public function fetchByRegistration(string $registration, bool $withValidationException = true): HealthUnit
    {
        $result = $this->api->get("estabelecimentos/$registration")->json();

        if ((empty($result) || !isset($result['codigo_cnes'])) && $withValidationException) {
            throw ValidationException::withMessages(['message' => 'Nenhum registro encontrado.']);
        }

        return new HealthUnit($result);
    }
}
