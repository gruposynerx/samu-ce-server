<?php

namespace App\Entities\Zapi;

use App\Entities\BaseEntity;
use Illuminate\Support\Collection;

class OptionsList extends BaseEntity
{
    public ?string $title;

    public ?string $buttonLabel;

    /**
     * @var Collection<int, OptionItems>
     */
    public Collection $options;

    /**
     * @param  array{title:string, buttonLabel:string, options:array<OptionItems>}  $data
     */
    public function __construct(array $data)
    {
        $this->title = data_get($data, 'title');
        $this->buttonLabel = data_get($data, 'buttonLabel');
        $this->options = collect(data_get($data, 'options'))->map(fn ($option) => new OptionItems($option));
    }
}
