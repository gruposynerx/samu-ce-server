<?php

namespace Database\Seeders;

use App\Models\PositionJob;
use Illuminate\Database\Seeder;

class PositionJobSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $positionJobs = [
            'Téc. expurgo',
            'Téc. preparo',
            'Téc. farmácia',
            'Dispensação',
            'Controlista',
            'IJF',
            'SESA',
            'TRANSPORTE',
            'ADMINISTRATIVO',
            'Carro administrativo',
        ];

        foreach ($positionJobs as $positionJob) {
            PositionJob::firstOrCreate([
                'name' => $positionJob,
            ]);
        }
    }
}