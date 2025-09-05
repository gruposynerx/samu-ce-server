<?php

namespace App\Console\Commands;

use App\Services\SigtapService;
use Illuminate\Console\Command;
use Illuminate\Http\UploadedFile;

class ImportSigtap extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-sigtap';

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
        SigtapService::import(new UploadedFile(
            public_path('tempzip/sigtap.zip'),
            'sigtap.zip'
        ));

        $this->info('Importação realizada com sucesso!');
    }
}
