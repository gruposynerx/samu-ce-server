<?php

namespace App\Console\Commands;

use App\Events\NotifyVehicleConfirmationEvent;
use Illuminate\Console\Command;

class TestNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-notification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Testar notificação de veículo';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Iniciando teste de notificação...');
        
        $this->info('📋 Dados do evento:');
        $this->info('   - Attendance ID: 9f599e72-4f4f-434f-bba3-10033877376c');
        $this->info('   - Fleet ID: 9f59a180-38ff-48f2-9daa-57c628d96350');
        $this->info('   - Creator ID: 9f3bd54a-1d5e-4fa8-bb25-c97c6f4153f1');
        $this->info('   - Number: 81500/6');
        
        $this->info('🔧 Configurações do Pusher:');
        $this->info('   - App ID: ' . config('broadcasting.connections.pusher.app_id'));
        $this->info('   - Key: ' . config('broadcasting.connections.pusher.key'));
        $this->info('   - Cluster: ' . config('broadcasting.connections.pusher.options.cluster'));
        $this->info('   - Driver: ' . config('broadcasting.default'));
        
        $this->info('📡 Criando evento...');
        
        $event = new NotifyVehicleConfirmationEvent(
            '9f599e72-4f4f-434f-bba3-10033877376c',
            '9f59a180-38ff-48f2-9daa-57c628d96350',
            '9f3bd54a-1d5e-4fa8-bb25-c97c6f4153f1',
            '81500/6'
        );

        $this->info('📤 Disparando evento...');
        broadcast($event);
        
        $this->info('✅ Evento disparado com sucesso!');
        $this->info('👀 Verifique o console do navegador para ver se o evento chegou.');
        $this->info('🔍 Verifique também os logs do servidor para mais detalhes.');
        
        return Command::SUCCESS;
    }
}