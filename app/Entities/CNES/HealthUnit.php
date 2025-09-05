<?php

namespace App\Entities\CNES;

use Illuminate\Support\Str;

class HealthUnit
{
    public ?string $nationalHealthRegistration;

    public ?string $companyRegistrationNumber;

    public ?string $companyName;

    public ?string $name;

    public ?int $unitTypeId;

    public ?string $cityCode;

    public ?string $federalUnitCode;

    public ?string $latitude;

    public ?string $longitude;

    public ?string $street;

    public ?string $neighborhood;

    public ?string $houseNumber;

    public ?string $telephone;

    public ?string $zipCode;

    public function __construct(array $data)
    {
        $this->nationalHealthRegistration = Str::padBoth(data_get($data, 'codigo_cnes'), '7', '0');
        $this->companyRegistrationNumber = data_get($data, 'numero_cnpj_entidade');
        $this->companyName = data_get($data, 'nome_razao_social');
        $this->name = data_get($data, 'nome_fantasia');
        $this->unitTypeId = data_get($data, 'codigo_tipo_unidade');
        $this->cityCode = data_get($data, 'codigo_municipio');
        $this->federalUnitCode = data_get($data, 'codigo_uf');
        $this->latitude = data_get($data, 'latitude_estabelecimento_decimo_grau');
        $this->longitude = data_get($data, 'longitude_estabelecimento_decimo_grau');
        $this->street = data_get($data, 'endereco_estabelecimento');
        $this->neighborhood = data_get($data, 'bairro_estabelecimento');
        $this->houseNumber = data_get($data, 'numero_estabelecimento');
        $this->telephone = data_get($data, 'numero_telefone_estabelecimento');
        $this->zipCode = data_get($data, 'codigo_cep_estabelecimento');
    }
}
