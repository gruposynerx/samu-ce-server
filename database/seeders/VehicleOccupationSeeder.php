<?php

namespace Database\Seeders;

use App\Enums\VehicleTypeEnum;
use App\Models\Role;
use App\Models\VehicleOccupation;
use Illuminate\Database\Seeder;

class VehicleOccupationSeeder extends Seeder
{
    protected string $driver = '515135';

    protected string $nurse = '223505';

    protected string $medic = '2251';

    protected string $nurseAssistant = '322230';

    protected string $nurseTecnical = '322205';

    protected string $transportAmbulanceRoleId;

    protected string $conductorRoleId;

    public function __construct()
    {
        $this->transportAmbulanceRoleId = Role::findByName('attendance-or-ambulance-team')->id;
        $this->conductorRoleId = Role::findByName('radio-operator')->id;
    }

    public function run(): void
    {
        $this->seedTransportAmbulance();
        $this->seedBasicTransportUnit();
        $this->seedAdvancedTransportUnit();
        $this->seedEmbarcation();
        $this->seedAeromedical();
        $this->seedRapidInterventionVehicle();
        $this->seedMotocycleAmbulance();
    }

    protected function seedTransportAmbulance(): void
    {
        $vehicleType = VehicleTypeEnum::TRANSPORT_AMBULANCE->value;

        VehicleOccupation::updateOrCreate([
            'vehicle_type_id' => $vehicleType,
            'occupation_id' => $this->driver,
        ], [
            'role_id' => $this->conductorRoleId,
        ]);

        VehicleOccupation::updateOrCreate([
            'vehicle_type_id' => $vehicleType,
            'occupation_id' => $this->nurseAssistant,
            'required' => false,
        ], [
            'role_id' => $this->transportAmbulanceRoleId,
        ]);

        VehicleOccupation::updateOrCreate([
            'vehicle_type_id' => $vehicleType,
            'occupation_id' => $this->nurseTecnical,
            'required' => false,
        ], [
            'role_id' => $this->transportAmbulanceRoleId,
        ]);

        VehicleOccupation::updateOrCreate([
            'vehicle_type_id' => $vehicleType,
            'occupation_id' => $this->nurse,
            'required' => false,
        ], [
            'role_id' => $this->transportAmbulanceRoleId,
        ]);
    }

    protected function seedBasicTransportUnit(): void
    {
        $vehicleType = VehicleTypeEnum::BASIC_SUPPORT_UNIT->value;

        VehicleOccupation::updateOrCreate([
            'vehicle_type_id' => $vehicleType,
            'occupation_id' => $this->driver,
        ], [
            'role_id' => $this->conductorRoleId,
        ]);

        VehicleOccupation::updateOrCreate([
            'vehicle_type_id' => $vehicleType,
            'occupation_id' => $this->nurse,
            'required' => false,
        ], [
            'role_id' => $this->transportAmbulanceRoleId,
        ]);

        VehicleOccupation::updateOrCreate([
            'vehicle_type_id' => $vehicleType,
            'occupation_id' => $this->nurseAssistant,
            'required' => false,
        ], [
            'role_id' => $this->transportAmbulanceRoleId,
        ]);

        VehicleOccupation::updateOrCreate([
            'vehicle_type_id' => $vehicleType,
            'occupation_id' => $this->nurseTecnical,
            'required' => false,
        ], [
            'role_id' => $this->transportAmbulanceRoleId,
        ]);
    }

    protected function seedAdvancedTransportUnit(): void
    {
        $vehicleType = VehicleTypeEnum::ADVANCED_SUPPORT_UNIT->value;

        VehicleOccupation::updateOrCreate([
            'vehicle_type_id' => $vehicleType,
            'occupation_id' => $this->driver,
        ], [
            'role_id' => $this->conductorRoleId,
        ]);

        VehicleOccupation::updateOrCreate([
            'vehicle_type_id' => $vehicleType,
            'occupation_id' => $this->medic,
        ], [
            'role_id' => $this->transportAmbulanceRoleId,
        ]);

        VehicleOccupation::updateOrCreate([
            'vehicle_type_id' => $vehicleType,
            'occupation_id' => $this->nurse,
        ], [
            'role_id' => $this->transportAmbulanceRoleId,
        ]);
    }

    protected function seedEmbarcation(): void
    {
        $vehicleType = VehicleTypeEnum::EMBARCATION->value;

        VehicleOccupation::updateOrCreate([
            'vehicle_type_id' => $vehicleType,
            'occupation_id' => $this->driver,
        ], [
            'role_id' => $this->conductorRoleId,
        ]);

        VehicleOccupation::updateOrCreate([
            'vehicle_type_id' => $vehicleType,
            'occupation_id' => $this->nurse,
        ], [
            'role_id' => $this->transportAmbulanceRoleId,
        ]);

        VehicleOccupation::updateOrCreate([
            'vehicle_type_id' => $vehicleType,
            'occupation_id' => $this->nurseAssistant,
            'required' => false,
        ], [
            'role_id' => $this->transportAmbulanceRoleId,
        ]);

        VehicleOccupation::updateOrCreate([
            'vehicle_type_id' => $vehicleType,
            'occupation_id' => $this->nurseTecnical,
            'required' => false,
        ], [
            'role_id' => $this->transportAmbulanceRoleId,
        ]);
    }

    protected function seedAeromedical(): void
    {
        $vehicleType = VehicleTypeEnum::AEROMEDICAL->value;

        VehicleOccupation::updateOrCreate([
            'vehicle_type_id' => $vehicleType,
            'occupation_id' => $this->driver,
        ], [
            'role_id' => $this->conductorRoleId,
        ]);

        VehicleOccupation::updateOrCreate([
            'vehicle_type_id' => $vehicleType,
            'occupation_id' => $this->medic,
        ], [
            'role_id' => $this->transportAmbulanceRoleId,
        ]);

        VehicleOccupation::updateOrCreate([
            'vehicle_type_id' => $vehicleType,
            'occupation_id' => $this->nurse,
        ], [
            'role_id' => $this->transportAmbulanceRoleId,
        ]);
    }

    protected function seedRapidInterventionVehicle(): void
    {
        $vehicleType = VehicleTypeEnum::RAPID_INTERVENTION_VEHICLE->value;

        VehicleOccupation::updateOrCreate([
            'vehicle_type_id' => $vehicleType,
            'occupation_id' => $this->driver,
        ], [
            'role_id' => $this->conductorRoleId,
        ]);

        VehicleOccupation::updateOrCreate([
            'vehicle_type_id' => $vehicleType,
            'occupation_id' => $this->medic,
        ], [
            'role_id' => $this->transportAmbulanceRoleId,
        ]);

        VehicleOccupation::updateOrCreate([
            'vehicle_type_id' => $vehicleType,
            'occupation_id' => $this->nurse,
        ], [
            'role_id' => $this->transportAmbulanceRoleId,
        ]);
    }

    protected function seedMotocycleAmbulance(): void
    {
        $vehicleType = VehicleTypeEnum::MOTORCYCLE_AMBULANCE->value;

        VehicleOccupation::updateOrCreate([
            'vehicle_type_id' => $vehicleType,
            'occupation_id' => $this->nurseAssistant,
            'required' => false,
        ], [
            'role_id' => $this->transportAmbulanceRoleId,
        ]);

        VehicleOccupation::updateOrCreate([
            'vehicle_type_id' => $vehicleType,
            'occupation_id' => $this->nurseTecnical,
            'required' => false,
        ], [
            'role_id' => $this->transportAmbulanceRoleId,
        ]);

        VehicleOccupation::updateOrCreate([
            'vehicle_type_id' => $vehicleType,
            'occupation_id' => $this->nurse,
            'required' => false,
        ], [
            'role_id' => $this->transportAmbulanceRoleId,
        ]);
    }
}
