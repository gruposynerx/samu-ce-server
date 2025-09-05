<?php

namespace App\Observers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class UrcObserver
{
    public function creating(Model $model): void
    {
        if (!$model->getAttribute('urc_id')) {
            $model->setAttribute('urc_id', $this->urcId());
        }
    }

    public function updating(Model $model): void
    {
        if (!$model->getAttribute('urc_id')) {
            $model->setAttribute('urc_id', $this->urcId());
        }
    }

    public function deleting(Model $model): void
    {
        if ($model->getAttribute('urc_id') !== $this->urcId()) {
            throw ValidationException::withMessages(['message' => 'Você não tem permissão para realizar essa ação, pois as bases diferem.']);
        }
    }

    public function urcId(): string
    {
        return auth()->user()->urc_id;
    }
}
