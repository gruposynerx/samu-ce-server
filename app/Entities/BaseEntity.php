<?php

namespace App\Entities;

use Illuminate\Contracts\Support\Arrayable;

abstract class BaseEntity implements Arrayable
{
    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
