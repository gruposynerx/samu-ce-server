<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class UrcScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        return $builder->where($model->getTable() . '.urc_id', auth()->user()->urc_id);
    }
}
