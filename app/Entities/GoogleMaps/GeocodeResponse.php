<?php

namespace App\Entities\GoogleMaps;

use App\Entities\BaseEntity;

class GeocodeResponse extends BaseEntity
{
    public ?Address $address;

    public ?string $place_id;

    public ?array $geometry;

    public ?string $formatted_address;

    public function __construct(array $data)
    {
        $this->address = new Address(data_get($data, 'address'));
        $this->place_id = data_get($data, 'place_id');
        $this->geometry = data_get($data, 'geometry');
        $this->formatted_address = data_get($data, 'formatted_address');
    }
}
