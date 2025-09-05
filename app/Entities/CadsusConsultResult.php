<?php

namespace App\Entities;

class CadsusConsultResult
{
    public ?string $fullName;

    public ?string $cpf;

    public ?string $cns;

    public ?string $genderCode;

    public ?string $phone;

    public ?string $email;

    public ?string $streetTypeCode;

    public ?string $street;

    public ?string $houseNumber;

    public ?string $neighborhood;

    public ?string $cityCode;

    public ?string $cityName;

    public ?string $stateCode;

    public ?string $birthDate;

    public function __construct(array $data)
    {
        $this->fullName = data_get($data, 'fullName');
        $this->cpf = data_get($data, 'cpf');
        $this->cns = data_get($data, 'cns');
        $this->genderCode = data_get($data, 'genderCode');
        $this->phone = data_get($data, 'phone');
        $this->email = data_get($data, 'email');
        $this->streetTypeCode = data_get($data, 'streetTypeCode');
        $this->street = data_get($data, 'street');
        $this->houseNumber = data_get($data, 'houseNumber');
        $this->neighborhood = data_get($data, 'neighborhood');
        $this->cityCode = data_get($data, 'cityCode');
        $this->cityName = data_get($data, 'cityName');
        $this->stateCode = data_get($data, 'stateCode');
        $this->birthDate = data_get($data, 'birthDate');
        $this->cityId = data_get($data, 'cityId');
        $this->stateId = data_get($data, 'stateId');
        $this->stateUf = data_get($data, 'stateUf');
    }
}
