<?php

namespace App\Console\Commands;

use App\Enums\UserStatusEnum;
use App\Models\User;
use App\Models\UserStatusHistory;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class FillFirstUserStatusHistory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fill-first-user-status-history';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cria o primeiro histórico de status para os usuários que não possuem.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $users = User::select('id')->whereDoesntHave('statusesHistory')->get();
        $admin = User::where('identifier', '02248916345')->first();

        $data = $users->map(function ($user) use ($admin) {
            return [
                'id' => Str::orderedUuid(),
                'user_id' => $user->id,
                'status_id' => UserStatusEnum::ACTIVE->value,
                'created_by' => $admin->id,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        });

        UserStatusHistory::insert($data->toArray());
    }
}
