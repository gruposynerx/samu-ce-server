<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            'super-admin',
            'admin',
            'TARM',
            'medic',
            'radio-operator',
            'attendance-or-ambulance-team',
            'team-scale',
            'hospital',
            'support',
            'reports-consulting',
            'monitor',
            'fleet-control',
            'team-leader',
            'manager',
            'coordinator',
        ];

        foreach ($roles as $role) {
            Role::findOrCreate($role);
        }
    }
}
