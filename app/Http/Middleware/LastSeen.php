<?php

namespace App\Http\Middleware;

use App\Exceptions\AuthException;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class LastSeen
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $activityLimit = config('auth.logout_after_inactivity_time');
        $user = Auth::user();

        if ($user) {
            if ($user->last_seen && $user->last_seen < now()->subMinutes($activityLimit)) {
                $user->tokens()->delete();

                $user->update([
                    'urc_id' => null,
                    'current_role' => null,
                ]);

                throw AuthException::inactivityExpiration();
            }

            Auth::user()->update(['last_seen' => now()]);
        }

        return $next($request);
    }
}
