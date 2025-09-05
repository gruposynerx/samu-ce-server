<?php

namespace App\Entities\GoogleMaps;

use App\Entities\BaseEntity;

class Address extends BaseEntity
{
    public ?string $street;

    public ?string $state;

    public ?string $city;

    public ?string $neighborhood;

    public ?string $postal_code;

    public ?string $street_number;

    public function __construct(array $data)
    {
        $this->street = data_get($data, 'street');
        $this->state = data_get($data, 'state');
        $this->city = data_get($data, 'city');
        $this->neighborhood = data_get($data, 'neighborhood');
        $this->postal_code = data_get($data, 'postal_code');
        $this->street_number = data_get($data, 'street_number');
    }
}
