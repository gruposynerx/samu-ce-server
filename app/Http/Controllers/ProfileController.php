<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchRequest;
use App\Http\Resources\ProfileResource;
use App\Models\Role;
use Knuckles\Scribe\Attributes\Group;

#[Group(name: 'Perfil', description: 'Gestão de perfis')]
class ProfileController extends Controller
{
    /**
     * GET api/profiles
     *
     * Retorna as informações de todos os perfis.
     *
     * @urlParam search string Texto para pesquisa. Example: Médico
     */
    public function index(SearchRequest $request)
    {
        $profile = Role::when($request->has('search'), function ($query) use ($request) {
            $query->whereRaw('unaccent(name) ilike unaccent(?)', "%{$request->search}%");
        })->paginate(20);
        return ProfileResource::collection($profile);
    }
}
