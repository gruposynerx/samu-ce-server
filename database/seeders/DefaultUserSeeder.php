<?php

namespace Database\Seeders;

use App\Enums\StreetTypeEnum;
use App\Enums\UserStatusEnum;
use App\Models\City;
use App\Models\Role;
use App\Models\UrgencyRegulationCenter;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DefaultUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {

            $urgencyRegulationCenters = UrgencyRegulationCenter::all();
            $urc = $urgencyRegulationCenters->first();
            $superAdminRole = Role::whereName('super-admin')->first();
            $city = City::whereSlug('pacajus')->first();

            $users = [];

            $users[] = User::firstOrCreate([
                'identifier' => '02248916345',
            ], [
                'name' => 'Multintegrada',
                'password' => bcrypt('multi258030'),
                'identifier' => '02248916345',
                'gender_code' => 'O',
                'phone' => '85991491657',
                'whatsapp' => '85991491657',
                'email' => 'contato@multintegrada.com.br',
                'street' => 'Rua Cônego Eduardo Araripe',
                'street_type' => StreetTypeEnum::STREET,
                'house_number' => 730,
                'neighborhood' => 'Coaçu',
                'city_id' => $city->id,
                'birthdate' => '2019/01/01',
                'is_active' => true,
                'urc_id' => $urc->id,
            ]);

            $users[] = User::firstOrCreate([
                'identifier' => '07775097337',
            ], [
                'name' => 'Carlos Daniel',
                'password' => bcrypt('@Sistema20'),
                'identifier' => '07775097337',
                'gender_code' => 'M',
                'phone' => '85991703489',
                'whatsapp' => '85991703489',
                'email' => 'carlos.daniel@multintegrada.com.br',
                'street' => 'Rua Cônego Eduardo Araripe',
                'street_type' => StreetTypeEnum::STREET,
                'house_number' => 730,
                'neighborhood' => 'Coaçu',
                'city_id' => $city->id,
                'birthdate' => '2005/04/26',
                'is_active' => true,
                'urc_id' => $urc->id,
            ]);

            $users[] = User::firstOrCreate([
                'identifier' => '08402275311',
            ], [
                'name' => 'Jeferson',
                'password' => bcrypt('6eF9svxsmlI5M1VpBpE2'),
                'identifier' => '08402275311',
                'gender_code' => 'O',
                'phone' => '8599109501',
                'whatsapp' => '85991095601',
                'email' => 'jeferson@multintegrada.com.br',
                'street' => 'Rua Cônego Eduardo Araripe',
                'street_type' => StreetTypeEnum::STREET,
                'house_number' => 730,
                'neighborhood' => 'Coaçu',
                'city_id' => $city->id,
                'birthdate' => '2003/02/03',
                'is_active' => true,
                'urc_id' => $urc->id,
            ]);

            $users[] = User::firstOrCreate([
                'identifier' => '07461865331',
            ], [
                'name' => 'Romulo',
                'password' => bcrypt('Sistema22#'),
                'identifier' => '07461865331',
                'gender_code' => 'M',
                'phone' => '85992725107',
                'whatsapp' => '85992725107',
                'email' => 'romulo@multintegrada.com.br',
                'street' => 'Rua João Facundo',
                'street_type' => StreetTypeEnum::STREET,
                'house_number' => 216,
                'neighborhood' => 'Centro',
                'city_id' => $city->id,
                'birthdate' => '2001/09/26',
                'is_active' => true,
                'urc_id' => $urc->id,
            ]);

            $users[] = User::firstOrCreate([
                'identifier' => '08406199369',
            ], [
                'name' => 'Nycolas',
                'password' => bcrypt('Sistema22#'),
                'identifier' => '08406199369',
                'gender_code' => 'M',
                'phone' => '85992725101',
                'whatsapp' => '85992725101',
                'email' => 'nycolas@multintegrada.com.br',
                'street' => 'Rua João Facundo',
                'street_type' => StreetTypeEnum::STREET,
                'house_number' => 216,
                'neighborhood' => 'Centro',
                'city_id' => $city->id,
                'birthdate' => '2001/09/26',
                'is_active' => true,
                'urc_id' => $urc->id,
            ]);

            $users[] = User::firstOrCreate([
                'identifier' => '05451649316',
            ], [
                'name' => 'Kayo Cordeiro',
                'password' => bcrypt('ac4490e1ff#'),
                'identifier' => '05451649316',
                'gender_code' => 'M',
                'phone' => '85981979797',
                'whatsapp' => '85981979797',
                'email' => 'kayo@multintegrada.com.br',
                'street' => 'Rua Cônego Eduardo Araripe',
                'street_type' => StreetTypeEnum::STREET,
                'house_number' => 730,
                'neighborhood' => 'Coaçu',
                'city_id' => $city->id,
                'birthdate' => '2019/01/01',
                'is_active' => true,
                'urc_id' => $urc->id,
            ]);

            $users[] = User::firstOrCreate([
                'identifier' => '12345678909',
            ], [
                'name' => 'Testes SAMU',
                'password' => bcrypt(Str::random(10)),
                'identifier' => '12345678909',
                'gender_code' => 'M',
                'phone' => '85999999999',
                'whatsapp' => '85999999999',
                'email' => 'testing@multintegrada.com.br',
                'street' => 'Rua Cônego Eduardo Araripe',
                'street_type' => StreetTypeEnum::STREET,
                'house_number' => 730,
                'neighborhood' => 'Coaçu',
                'city_id' => $city->id,
                'birthdate' => '2024/01/01',
                'is_active' => true,
                'urc_id' => $urc->id,
                'current_role' => $superAdminRole->id,
            ]);

            collect($users)->map(function (User $user) use ($urgencyRegulationCenters) {
                $user->assignRole(['super-admin', 'admin'], $urgencyRegulationCenters);

                $user->statusesHistory()->firstOrCreate(['user_id' => $user->id], [
                    'status_id' => UserStatusEnum::ACTIVE->value,
                    'created_by' => auth()?->id(),
                ]);
            });
        });
    }
}
