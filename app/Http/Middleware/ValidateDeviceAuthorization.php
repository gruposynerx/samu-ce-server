<?php

namespace App\Http\Middleware;

use App\Exceptions\AuthException;
use App\Models\Pin;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateDeviceAuthorization
{
    public function handle(Request $request, Closure $next): Response
    {
        $authCode = $request->header('Authorization-pin');
        $macAddress = $request->header('Device-Mac-Address');

        if ($authCode) {
            $pin = Pin::where([
                ['code', $authCode],
                ['device_mac_address', $macAddress],
            ])->whereNotNull('device_mac_address')
                ->whereRelation('mobileDevice', 'is_active', true);

            if ($pin->doesntExist()) {
                throw AuthException::invalidDevice();
            }
        }

        return $next($request);
    }
}
