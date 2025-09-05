<?php

namespace App\Console\Commands;

use App\Models\SceneRecording;
use App\Scopes\UrcScope;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateSceneRecordingDestinationUnitHistories extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:migrate-scene-recording-destination-unit-histories';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cria um regitro de histórico de unidade de destino para cada registro de cena que tenha a unidade de destino definida.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $results = SceneRecording::select('id as scene_recording_id', 'unit_destination_id', 'created_by', 'created_at')->withoutGlobalScope(UrcScope::class)->whereNotNull('unit_destination_id')->get()->map(function ($result) {
            return [
                'scene_recording_id' => $result->scene_recording_id,
                'unit_destination_id' => $result->unit_destination_id,
                'created_by' => $result->created_by,
                'created_at' => $result->created_at,
                'updated_at' => $result->created_at,
                'is_counter_reference' => false,
            ];
        });

        DB::transaction(function () use ($results) {
            foreach ($results->chunk(5000) as $chunk) {
                DB::table('scene_recording_destination_unit_histories')->insert($chunk->toArray());
            }
        });
    }
}
