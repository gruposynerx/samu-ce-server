<?php

namespace App\Console\Commands;

use App\Models\MedicalRegulation;
use App\Models\SceneRecording;
use App\Scopes\UrcScope;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateDiagnosticHypotheses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:migrate-diagnostic-hypotheses';

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
        $medicalRegulationAlias = getMorphAlias(MedicalRegulation::class);
        $sceneRecordingAlias = getMorphAlias(SceneRecording::class);

        $medicalRegulations = MedicalRegulation::withoutGlobalScope(UrcScope::class)->select([
            \DB::raw("'$medicalRegulationAlias' as form_type"),
            \DB::raw('id as form_id'),
            'nature_type_id',
            'diagnostic_hypothesis_id',
            'attendance_id',
            'created_by',
            'created_at',
            'updated_at',
        ])
            ->whereNotNull('diagnostic_hypothesis_id')
            ->get();

        $sceneRecordings = SceneRecording::withoutGlobalScope(UrcScope::class)->select([
            \DB::raw("'$sceneRecordingAlias' as form_type"),
            \DB::raw('id as form_id'),
            'nature_type_id',
            'diagnostic_hypothesis_id',
            'attendance_id',
            'created_by',
            'created_at',
            'updated_at',
        ])
            ->whereNotNull('diagnostic_hypothesis_id')
            ->get();

        $merged = $medicalRegulations->concat($sceneRecordings)->values();

        DB::transaction(function () use ($merged) {
            foreach ($merged->chunk(5000) as $chunk) {
                \DB::table('form_diagnostic_hypotheses')->insert($chunk->toArray());
            }
        });
    }
}
