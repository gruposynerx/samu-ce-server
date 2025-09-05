<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class Playground extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'playground';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Playground for testing stuff';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        return Command::SUCCESS;
    }
}
