<?php

namespace App\Observers;

use Illuminate\Database\Eloquent\Model;

class UserObserver
{
    public function creating(Model $model): void
    {
        if (!$model->getAttribute('user_id')) {
            $model->setAttribute('user_id', $this->userId());
        }
    }

    public function userId(): string
    {
        return auth()->user()->id;
    }
}
