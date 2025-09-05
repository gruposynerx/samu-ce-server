<?php

namespace App\Console\Commands;

use App\Models\SceneRecording;
use App\Models\SceneRecordingAntecedent;
use App\Scopes\UrcScope;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MigrateDataSceneRegisterAntecedentsTable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:migrate-data-scene-register-antecedents-table';

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
        $sceneRecordings = SceneRecording::select('id as scene_recording_id', 'antecedent_type_id')->withoutGlobalScope(UrcScope::class)->whereNotNull('antecedent_type_id')->get();

        SceneRecordingAntecedent::insert($sceneRecordings->map(function ($sceneRecording) {
            $sceneRecording->id = Str::orderedUuid();

            return $sceneRecording;
        })->toArray());
    }
}
