<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexOccupationRequest;
use App\Http\Resources\OccupationResource;
use App\Models\Sigtap\Occupation;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Knuckles\Scribe\Attributes\Group;

#[Group(name: 'CBOs', description: 'Gestão de CBOs')]
class OccupationController extends Controller
{
    /**
     * GET api/occupations
     *
     * Retorna uma lista paginada de CBOs.
     *
     * @urlParam search string Texto para pesquisa. Example: Clínico
     */
    public function index(IndexOccupationRequest $request): ResourceCollection
    {
        $codesOrder = ['322230', '322205', '223505', '515135'];
        $professionalCodes = ['422205', '322230', '322205', '223505', '515135'];
        $clinicalDoctorCode = '2251';

        $occupations = Occupation::when($request->has('search'), static function ($query) use ($request) {
            $query->where(function ($query) use ($request) {
                $query->whereRaw('unaccent(name) ilike unaccent(?)', "%{$request->search}%")
                    ->orWhere('code', 'ilike', "%{$request->search}%");
            });
        })
            ->when($request->search_professionals, function ($query) use ($professionalCodes, $clinicalDoctorCode) {
                $query->where(function ($query) use ($professionalCodes, $clinicalDoctorCode) {
                    $query->whereIn('code', $professionalCodes)
                        ->orWhere('code', 'like', "$clinicalDoctorCode%");
                });
            })
            ->orderByRaw("CASE WHEN code IN ('" . implode("','", $codesOrder) . "') THEN 0 WHEN code ~ '^2251' THEN 1 ELSE 2 END, code")
            ->paginate();

        return OccupationResource::collection($occupations);
    }

    /**
     * GET api/occupations/{code}
     *
     *
     */
    public function show(string $code): OccupationResource
    {
        $occupation = Occupation::where('code', $code)->firstOrFail();
        return new OccupationResource($occupation);
    }
}
