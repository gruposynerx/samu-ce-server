<?php

namespace App\Observers;

use App\Models\User;

class UserPasswordHistoryObserver
{
    public function updating(User $model): void
    {
        if ($model->isDirty('password')) {
            $model->passwordHistories()->create([
                'user_id' => $model->id,
                'updated_by' => auth()->user()->id ?? $model->id,
                'urc_id' => auth()->user()->urc_id ?? null,
            ]);
        }
    }
}
