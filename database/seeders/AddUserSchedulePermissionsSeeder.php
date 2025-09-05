<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Role;

use Illuminate\Database\Seeder;

class AddUserSchedulePermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
     {
        $roles = [
            'schedule-manager',
        ];

        foreach ($roles as $role) {
            Role::findOrCreate($role);
        }
    }
}
