<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\FederalUnit;
use App\Models\UrgencyRegulationCenter;
use Illuminate\Database\Seeder;

class UrgencyRegulationCentersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $state = FederalUnit::whereSlug('ceara')->first();

        $eusebio = City::whereSlug('eusebio')->first();

        UrgencyRegulationCenter::firstOrCreate([
            'name' => 'CRU Eusébio',
            'city_id' => $eusebio->id,
            'street' => 'Rua da Paz',
            'house_number' => '30',
            'neighborhood' => 'Centro',
        ]);

        $sobral = City::whereSlug('sobral')->first();

        UrgencyRegulationCenter::firstOrCreate([
            'name' => 'CRU Sobral',
            'city_id' => $sobral->id,
            'street' => 'Avenida Dr Guarany',
            'house_number' => '340',
            'neighborhood' => 'Cidão',
        ]);

        $juazeiroDoNorte = City::whereSlug('juazeiro-do-norte')->first();

        UrgencyRegulationCenter::firstOrCreate([
            'name' => 'CRU Juazeiro do Norte',
            'city_id' => $juazeiroDoNorte->id,
            'street' => 'Rua Vicente Patu',
            'house_number' => '60',
            'neighborhood' => 'São Francisco',
        ]);
    }
}
