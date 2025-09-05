<?php

namespace App\Http\Controllers;

use App\Events\InvalidateDevice;
use App\Http\Requests\SearchRequest;
use App\Http\Requests\StoreUpdateMobileDevice;
use App\Http\Resources\MobileDeviceResource;
use App\Http\Resources\VehicleResource;
use App\Models\MobileDevice;
use App\Models\Pin;
use App\Models\Scopes\ActiveScope;
use App\Models\Vehicle;
use App\Scopes\UrcScope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Validation\ValidationException;
use Knuckles\Scribe\Attributes\Group;

#[Group(name: 'Dispositivos Móveis', description: 'Seção responsável pela gestão de dispositivos móveis')]
class MobileDeviceController extends Controller
{
    /**
     * GET api/mobile-device
     *
     * Retorna os dados de todos os dispositivos móveis registrados.
     */
    public function index(SearchRequest $request): ResourceCollection
    {
        $mobileDevices = MobileDevice::withoutGlobalScope(ActiveScope::class)
            ->with([
                'vehicle:id,code,license_plate,base_id',
                'vehicle.base:id,name,city_id',
                'pin',
            ])
            ->when($request->has('search'), function ($query) use ($request) {
                $query->whereRaw('unaccent(name) ilike unaccent(?)', "%{$request->search}%")
                    ->orWhereHas('vehicle', function ($query) use ($request) {
                        $query->whereRaw('unaccent(license_plate) ilike unaccent(?)', "%{$request->search}%");
                    })->orWhereHas('vehicle.base', function ($q) use ($request) {
                        $q->whereRaw('unaccent(name) ilike unaccent(?)', "%{$request->search}%");
                    });
            })
            ->orderBy('name')
            ->paginate(10);

        return MobileDeviceResource::collection($mobileDevices);
    }

    /**
     * POST api/mobile-device
     *
     * Cria um novo dispositivo móvel.
     */
    public function store(StoreUpdateMobileDevice $request): JsonResource
    {
        $data = $request->validated();

        $vehicleHasMobileDevice = MobileDevice::where('vehicle_id', $data['vehicle_id'])->exists();

        if ($vehicleHasMobileDevice) {
            throw ValidationException::withMessages([
                'vehicle_id' => 'O veículo informado já possui um dispositivo móvel associado.',
            ]);
        }

        $pin = $this->generatePin();

        $mobileDevice = $pin->mobileDevice()->create([
            'created_by' => auth()->id(),
            ...$data,
        ]);

        return new MobileDeviceResource($mobileDevice->load('pin'));
    }

    private function generatePin(): Pin
    {
        $randomCode = random_int(100, 999) . random_int(100, 999);

        while (Pin::where('code', $randomCode)->exists()) {
            $randomCode = random_int(100, 999) . random_int(100, 999);
        }

        return Pin::create([
            'code' => $randomCode,
            'device_mac_address' => null,
        ]);
    }

    /**
     * PUT api/mobile-device/{mobileDevice}
     *
     * Atualiza os dados de um dispositivo móvel.
     */
    public function update(StoreUpdateMobileDevice $request, string $mobileDevice): JsonResource
    {
        $mobileDevice = MobileDevice::withoutGlobalScope(ActiveScope::class)->findOrFail($mobileDevice);

        $data = $request->validated();

        if ($mobileDevice->pin->code !== $data['pin']) {
            throw ValidationException::withMessages([
                'pin' => 'O PIN informado não corresponde ao dispositivo ou não é válido.',
            ]);
        }

        if (!$mobileDevice->vehicle_id0) {
            $data['is_active'] = true;
        } elseif ($mobileDevice->vehicle_id !== $data['vehicle_id']) {
            $vehicleHasMobileDevice = MobileDevice::where('vehicle_id', $data['vehicle_id'])->exists();

            if ($vehicleHasMobileDevice) {
                throw ValidationException::withMessages([
                    'vehicle_id' => 'O veículo informado já possui um dispositivo móvel associado.',
                ]);
            }
        }

        $mobileDevice->update($data);
        $mobileDevice->pin()->update([
            'device_mac_address' => null,
        ]);

        InvalidateDevice::dispatch($mobileDevice->pin_id);

        return new MobileDeviceResource($mobileDevice);
    }

    /**
     * GET api/mobile-device/able-vehicles
     *
     * Retorna os dados dos veículos que não possuem dispositivos móveis associados.
     */
    public function getAbleVehicles(SearchRequest $request): ResourceCollection
    {
        $search = $request->get('search');

        $vehicles = Vehicle::with('base.city')
            ->whereDoesntHave('mobileDevice')
            ->when(!empty($search), function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('code', 'ilike', "%{$search}%")
                        ->orWhereRaw('unaccent(chassis) ilike unaccent(?)', "%{$search}%")
                        ->orWhereRaw('unaccent(license_plate) ilike unaccent(?)', "%{$search}%")
                        ->orWhereHas('base', function ($query) use ($search) {
                            $query->withoutGlobalScope(UrcScope::class)
                                ->whereRaw('unaccent(name) ilike unaccent(?)', "%{$search}%")
                                ->orWhereHas('city', fn ($query) => $query->whereRaw('unaccent(cities.name) ilike unaccent(?)', "%{$search}%"));
                        })
                        ->orWhereHas('vehicleType', function ($query) use ($search) {
                            $query->whereRaw('unaccent(vehicle_types.name) ilike unaccent(?)', "%{$search}%")
                                ->orWhereRaw('unaccent(code) ilike unaccent(?)', "%{$search}%");
                        });
                });
            })
            ->paginate();

        return VehicleResource::collection($vehicles);
    }

    /**
     * PATCH api/mobile-device/{mobileDevice}/unlink
     *
     * Desvincula um dispositivo móvel de um veículo.
     */
    public function unlinkDevice(MobileDevice $mobileDevice): JsonResponse
    {
        $mobileDevice->update([
            'vehicle_id' => null,
            'is_active' => false,
        ]);

        InvalidateDevice::dispatch($mobileDevice->pin_id);

        return response()->json([
            'message' => 'Dispositivo desvinculado com sucesso.',
        ]);
    }
}
