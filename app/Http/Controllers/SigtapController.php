<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImportSigtapRequest;
use App\Services\SigtapService;

class SigtapController extends Controller
{
    public function store(ImportSigtapRequest $request)
    {
        $file = $request->file('file');

        SigtapService::import($file);

        return response()->noContent();
    }
}
