<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class AttendanceScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        return $builder->whereHas('attendable', function (Builder $builder) {
            $builder->where('attendances.urc_id', auth()->user()->urc_id);
        });
    }
}
