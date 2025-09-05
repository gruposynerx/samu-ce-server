<?php

namespace App\Entities\Zapi;

use App\Entities\BaseEntity;

class OptionItems extends BaseEntity
{
    public ?int $id;

    public ?string $title;

    public ?string $description;

    public ?string $label;

    public ?string $type;

    public ?string $url; // in case of type URL

    /**
     * @param  array{id:int, title:string, description:string|null}  $data
     */
    public function __construct(array $data)
    {
        $this->id = data_get($data, 'id');
        $this->title = data_get($data, 'title');
        $this->description = data_get($data, 'description');
        $this->type = data_get($data, 'type');
        $this->label = data_get($data, 'label');
        $this->url = data_get($data, 'url');
    }
}
