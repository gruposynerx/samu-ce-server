<?php

namespace App\Console\Commands;

use App\Models\Base;
use App\Scopes\UrcScope;
use App\Services\CnesService;
use Illuminate\Console\Command;

class ImportBasesLocation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-bases-location';

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
        $originalBases = Base::withoutGlobalScope(UrcScope::class)->get();

        $originalBases->map(function ($base) {
            $newBase = app(CnesService::class)->fetchByRegistration($base->national_health_registration, false);

            $base->update([
                'latitude' => $newBase->latitude,
                'longitude' => $newBase->longitude,
            ]);
        });
    }
}
