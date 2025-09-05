<?php

namespace App\Http\Middleware;

use App\Models\UnauthenticatedAccessToken as UnauthenticatedAccessTokenModel;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UnauthenticatedAccessToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $corresponds = UnauthenticatedAccessTokenModel::where('token', $request->header('Authorization'))->first();

        if (!$corresponds || $corresponds->expires_at < now()) {
            return response()->json(['message' => 'Unauthenticated'], Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}
