<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            LocationTypesSeeder::class,
            DistanceTypeSeeder::class,
            RequesterTypesSeeder::class,
            ResourcesSeeder::class,
            TicketTypeSeeder::class,
            TransferReasonsSeeder::class,
            AttendanceStatusSeeder::class,
            FederalUnitSeeder::class,
            CitySeeder::class,
            UrgencyRegulationCentersSeeder::class,
            NatureTypesSeeder::class,
            PriorityTypesSeeder::class,
            ConsciousnessLevelsSeeder::class,
            RespirationTypesSeeder::class,
            ActionTypesSeeder::class,
            VehicleMovementCodesSeeder::class,
            UserStatusSeeder::class,
            DefaultUserSeeder::class,
            UnitTypeSeeder::class,
            VehicleTypesSeeder::class,
            VehicleStatusSeeder::class,
            PatrimonyStatusSeeder::class,
            PatrimonyTypeSeeder::class,
            BleedingTypeSeeder::class,
            SweatingTypeSeeder::class,
            SkinColorationTypeSeeder::class,
            WoundTypeSeeder::class,
            WoundPlaceTypeSeeder::class,
            ClosingTypeSeeder::class,
            AntecedentTypeSeeder::class,
            ConductSeeder::class,
            RadioOperationFleetStatusSeeder::class,
            VehicleOccupationSeeder::class,
            TimeUnitSeeder::class,
            PatrimonyStatusSeeder::class,
            CounterreferralReasonTypeSeeder::class,
            PeriodTypeSeeder::class,
            DutyReportTypeSeeder::class,
            PlaceStatusSeeder::class,
            ScheduleTypesSeeder::class,
            MonitoringSettingSeeder::class,
            SatisfactionScaleSeeder::class,
            SatisfactionTimeAmbulanceArriveSeeder::class,
            SatisfactionTimeSpentPhoneSeeder::class,
            FormSettingSeeder::class,
            UserLogLoginTypesSeeder::class,
            AddUserSchedulePermissionsSeeder::class,
            NotificationTypesSeeder::class,
            PositionJobSeeder::class,
        ]);
    }
}
