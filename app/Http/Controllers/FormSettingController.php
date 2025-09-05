<?php

namespace App\Http\Controllers;

use App\Http\Requests\FormSettingRequest;
use App\Http\Resources\FormSettingResource;
use App\Models\FormSetting;
use Knuckles\Scribe\Attributes\Group;

#[Group(name: 'Configurações de formulários', description: 'Seção responsável pela gestão das configurações dos formulários')]
class FormSettingController extends Controller
{
    /**
     * GET api/form-setting
     *
     * Retorna as configurações dos formulários da CRU logada.
     */
    public function show()
    {
        $result = FormSetting::where('urc_id', auth()->user()->urc_id)->first();

        return response()->json(new FormSettingResource($result));
    }

    /**
     * GET api/vehicles
     *
     * Atualiza as configurações de formulário da CRU logada.
     */
    public function update(FormSettingRequest $request)
    {
        $result = FormSetting::where('urc_id', auth()->user()->urc_id)->first();

        $result->update($request->all());

        return response()->json(new FormSettingResource($result->fresh()));
    }
}
