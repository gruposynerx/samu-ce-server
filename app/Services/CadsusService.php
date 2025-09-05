<?php

namespace App\Services;

use App\Entities\CadsusConsultResult;
use App\Models\City;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class CadsusService
{
    public function consult($identifier): CadsusConsultResult
    {
        $client = Http::baseUrl(config('external_services.cadsus_api_url'))->withHeaders([
            'x-functions-key' => config('external_services.cadsus_api_token'),
        ]);

        $operator = strlen($identifier) === 11 ? 'cpf' : 'cns';

        $response = $client->get('SearchQuery', [
            $operator => $identifier,
        ])->json();

        if (empty($response)) {
            throw ValidationException::withMessages(['message' => 'Nenhum registro encontrado.']);
        }

        $response = $response[0];

        $city = City::with('federalUnit')->where('ibge_code', 'ilike', "{$response['cityCode']}%")->first();

        $response['cityId'] = $city->id;
        $response['stateId'] = $city->federal_unit_id;
        $response['stateUf'] = $city->federalUnit->uf;

        return new CadsusConsultResult($response);
    }
}
