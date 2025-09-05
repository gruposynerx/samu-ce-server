<?php

namespace App\Observers;

use App\Enums\UserStatusEnum;
use App\Models\User;

class UserStatusHistoryObserver
{
    public function created(User $model): void
    {
        $model->statusesHistory()->create([
            'status_id' => UserStatusEnum::ACTIVE->value,
            'created_by' => auth()?->id(),
        ]);
    }
}
