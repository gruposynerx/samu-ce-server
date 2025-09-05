<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TestChannelAuth extends Command
{
    protected $signature = 'app:test-channel-auth';
    protected $description = 'Testar autenticação do canal';

    public function handle()
    {
        $userId = '9f3bd54a-1d5e-4fa8-bb25-c97c6f4153f1';
        $user = User::find($userId);
        
        if (!$user) {
            $this->error('❌ Usuário não encontrado!');
            return;
        }
        
        $this->info('✅ Usuário encontrado:');
        $this->info("   - ID: {$user->id}");
        $this->info("   - Nome: {$user->name}");
        $this->info("   - Email: {$user->email}");
        
        // Forçar log de autenticação
        Log::info('Teste manual de autenticação do canal', [
            'user_id' => $user->id,
            'user_id_type' => gettype($user->id),
            'channel_id' => $userId,
            'channel_id_type' => gettype($userId),
            'comparison' => $user->id === $userId,
            'string_comparison' => (string) $user->id === (string) $userId,
        ]);
        
        $result = (string) $user->id === (string) $userId;
        $this->info('📊 Resultado: ' . ($result ? 'SUCESSO ✅' : 'FALHA ❌'));
        
        return Command::SUCCESS;
    }
}