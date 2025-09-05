<?php

namespace App\Http\Controllers;

use App\Http\Requests\PinDeviceRequest;
use App\Models\Pin;
use App\Scopes\UrcScope;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class PinController extends Controller
{
    private function pinValidations(string $code): array
    {
        $pin = Pin::with('mobileDevice')->where('code', $code)->first();

        if (!$pin) {
            throw ValidationException::withMessages([
                'pin' => 'O PIN informado não é válido.',
            ]);
        }

        $mobileDevice = $pin->mobileDevice;

        if (!$mobileDevice) {
            throw ValidationException::withMessages([
                'pin' => 'O PIN informado Não possui dispositivo associado.',
            ]);
        }

        if (!$mobileDevice->is_active || !$mobileDevice->vehicle_id) {
            throw ValidationException::withMessages([
                'pin' => 'O Dispositivo associado ao PIN informado está inativo.',
            ]);
        }

        $mobileDevice->load([
            'vehicle' => fn ($query) => $query->withoutGlobalScope(UrcScope::class),
            'vehicle.base' => fn ($query) => $query->withoutGlobalScope(UrcScope::class),
        ]);

        return [
            'device' => $mobileDevice,
            'pin' => $pin,
            'is_valid' => true,
        ];
    }

    /**
     * GET api/pin/validate
     *
     * Verifica se um PIN é válido ou existente.
     */
    public function checkPin(PinDeviceRequest $request): JsonResponse
    {
        $data = $request->validated();

        ['device' => $device, 'pin' => $pin, 'is_valid' => $isValid] = $this->pinValidations($data['pin']);

        if (!$pin->device_mac_address) {
            throw ValidationException::withMessages([
                'pin' => 'Dispositivo móvel alterado, por favor confirme o PIN novamente.',
            ]);
        }

        if ($pin->device_mac_address !== $request->get('mac_address')) {
            throw ValidationException::withMessages([
                'pin' => 'O PIN informado não é válido ou foi inserido em outro dispositivo.',
            ]);
        }

        return response()->json([
            'is_valid' => $isValid,
            'device' => $device,
            'pin' => $pin->code,
        ]);
    }

    /**
     * PATCH api/pin/sync
     *
     * Sincroniza um dispositivo móvel com um PIN.
     */
    public function syncDevice(PinDeviceRequest $request): JsonResponse
    {
        $data = $request->validated();

        ['pin' => $pin, 'device' => $device] = $this->pinValidations($data['pin']);

        $pin->update([
            'device_mac_address' => $data['mac_address'],
        ]);

        $device->latestHistory()->update([
            'device_mac_address' => $data['mac_address'],
        ]);

        $device->load([
            'vehicle.base' => fn ($q) => $q->withoutGlobalScopes(),
            'vehicle.vehicleType' => fn ($q) => $q->withoutGlobalScopes(),
        ]);

        return response()->json([
            'message' => 'Dispositivo sincronizado com sucesso.',
            'device' => $device,
        ]);
    }
}
