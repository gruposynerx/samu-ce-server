<?php

namespace App\Console\Commands;

use App\Models\Unit;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImportUnits extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-units';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Importando unidades no arquivo ceara_unidades.json.');
        $units = collect(json_decode(Storage::get('ceara_unidades.json'), true));
        $formattedUnits = $units->map(function ($unit) {
            $unit['id'] = Str::orderedUuid();

            return \Arr::except($unit, ['city_code', 'original_code', 'unit_code']);
        });

        Unit::insert($formattedUnits->toArray());
        $this->info('Importação concluída!');
    }
}
