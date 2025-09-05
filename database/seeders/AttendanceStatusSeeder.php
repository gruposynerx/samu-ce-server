<?php

namespace Database\Seeders;

use App\Models\AttendanceStatus;
use Illuminate\Database\Seeder;

class AttendanceStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AttendanceStatus::firstOrCreate([
            'id' => 1,
            'name' => 'Aguardando Regulação Médica',
        ]);

        AttendanceStatus::firstOrCreate([
            'id' => 2,
            'name' => 'Aguardando Empenho de Viatura',
        ]);

        AttendanceStatus::firstOrCreate([
            'id' => 3,
            'name' => 'Viatura Enviada',
        ]);

        AttendanceStatus::firstOrCreate([
            'id' => 4,
            'name' => 'Aguardando Vaga',
        ]);

        AttendanceStatus::firstOrCreate([
            'id' => 5,
            'name' => 'Aguardando Conduta',
        ]);

        AttendanceStatus::firstOrCreate([
            'id' => 6,
            'name' => 'Conduta',
        ]);

        AttendanceStatus::firstOrCreate([
            'id' => 7,
            'name' => 'Concluído',
        ]);

        AttendanceStatus::firstOrCreate([
            'id' => 8,
            'name' => 'Cancelado',
        ]);

        AttendanceStatus::firstOrCreate([
            'id' => 9,
            'name' => 'Em Atendimento (Regulação Médica)',
        ]);

        AttendanceStatus::firstOrCreate([
            'id' => 10,
            'name' => 'Em Atendimento (Registro de Cena)',
        ]);

        AttendanceStatus::firstOrCreate([
            'id' => 11,
            'name' => 'Em Atendimento (Rádio Operação)',
        ]);

        AttendanceStatus::firstOrCreate([
            'id' => 12,
            'name' => 'Aguardando Retorno',
        ]);

        AttendanceStatus::firstOrCreate([
            'id' => 13,
            'name' => 'Vinculado',
        ]);

        AttendanceStatus::firstOrCreate([
            'id' => 14,
            'name' => 'Sem Resposta da VTR',
        ]);
    }
}
