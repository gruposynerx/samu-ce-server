<?php

namespace App\Http\Controllers;

use App\Enums\TicketTypeEnum;
use App\Http\Requests\SearchRequest;
use App\Models\Requester;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;

class PrankCallController extends Controller
{
    public function check(SearchRequest $request): JsonResponse
    {
        $search = $request->get('search');

        $hasPrankCallHistory = Requester::whereHas('ticket', fn (Builder $q) => $q->where('ticket_type_id', TicketTypeEnum::PRANK_CALL))
            ->where(function (Builder $query) use ($search) {
                $query->where(DB::raw("NULLIF(regexp_replace(primary_phone, '\D','','g'), '')::numeric"), $search)
                    ->orWhere(DB::raw("NULLIF(regexp_replace(secondary_phone, '\D','','g'), '')::numeric"), $search);
            })
            ->exists();

        return response()->json([
            'hasPrankCallHistory' => $hasPrankCallHistory,
        ]);
    }
}
