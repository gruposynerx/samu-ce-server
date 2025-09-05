<?php

namespace Database\Seeders;

use App\Models\FederalUnit;
use Illuminate\Database\Seeder;

class FederalUnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        FederalUnit::insertOrIgnore([
            [
                'id' => '1',
                'name' => 'Acre',
                'uf' => 'AC',
                'ibge_code' => '12',
                'slug' => 'acre',
            ],
            [
                'id' => '2',
                'title' => 'Alagoas',
                'uf' => 'AL',
                'ibge_code' => '27',
                'slug' => 'alagoas',
            ],
            [
                'id' => '3',
                'title' => 'Amazonas',
                'uf' => 'AM',
                'ibge_code' => '13',
                'slug' => 'amazonas',
            ],
            [
                'id' => '4',
                'title' => 'Amapá',
                'uf' => 'AP',
                'ibge_code' => '16',
                'slug' => 'amapa',
            ],
            [
                'id' => '5',
                'title' => 'Bahia',
                'uf' => 'BA',
                'ibge_code' => '29',
                'slug' => 'bahia',
            ],
            [
                'id' => '6',
                'title' => 'Ceará',
                'uf' => 'CE',
                'ibge_code' => '23',
                'slug' => 'ceara',
            ],
            [
                'id' => '7',
                'title' => 'Distrito Federal',
                'uf' => 'DF',
                'ibge_code' => '53',
                'slug' => 'distrito-federal',
            ],
            [
                'id' => '8',
                'title' => 'Espírito Santo',
                'uf' => 'ES',
                'ibge_code' => '32',
                'slug' => 'espirito-santo',
            ],
            [
                'id' => '9',
                'title' => 'Goiás',
                'uf' => 'GO',
                'ibge_code' => '52',
                'slug' => 'goias',
            ],
            [
                'id' => '10',
                'title' => 'Maranhão',
                'uf' => 'MA',
                'ibge_code' => '21',
                'slug' => 'maranhao',
            ],
            [
                'id' => '11',
                'title' => 'Minas Gerais',
                'uf' => 'MG',
                'ibge_code' => '31',
                'slug' => 'minas-gerais',
            ],
            [
                'id' => '12',
                'title' => 'Mato Grosso do Sul',
                'uf' => 'MS',
                'ibge_code' => '50',
                'slug' => 'mato-grosso-do-sul',
            ],
            [
                'id' => '13',
                'title' => 'Mato Grosso',
                'uf' => 'MT',
                'ibge_code' => '51',
                'slug' => 'mato-grosso',
            ],
            [
                'id' => '14',
                'title' => 'Pará',
                'uf' => 'PA',
                'ibge_code' => '15',
                'slug' => 'para',
            ],
            [
                'id' => '15',
                'title' => 'Paraiba',
                'uf' => 'PB',
                'ibge_code' => '25',
                'slug' => 'paraiba',
            ],
            [
                'id' => '16',
                'title' => 'Pernambuco',
                'uf' => 'PE',
                'ibge_code' => '26',
                'slug' => 'pernambuco',
            ],
            [
                'id' => '17',
                'title' => 'Piauí',
                'uf' => 'PI',
                'ibge_code' => '22',
                'slug' => 'piaui',
            ],
            [
                'id' => '18',
                'title' => 'Paraná',
                'uf' => 'PR',
                'ibge_code' => '41',
                'slug' => 'parana',
            ],
            [
                'id' => '19',
                'title' => 'Rio de Janeiro',
                'uf' => 'RJ',
                'ibge_code' => '33',
                'slug' => 'rio-de-janeiro',
            ],
            [
                'id' => '20',
                'title' => 'Rio Grande do Norte',
                'uf' => 'RN',
                'ibge_code' => '24',
                'slug' => 'rio-grande-do-norte',
            ],
            [
                'id' => '21',
                'title' => 'Rondônia',
                'uf' => 'RO',
                'ibge_code' => '11',
                'slug' => 'rondonia',
            ],
            [
                'id' => '22',
                'title' => 'Roraima',
                'uf' => 'RR',
                'ibge_code' => '14',
                'slug' => 'roraima',
            ],
            [
                'id' => '23',
                'title' => 'Rio Grande do Sul',
                'uf' => 'RS',
                'ibge_code' => '43',
                'slug' => 'rio-grande-do-sul',
            ],
            [
                'id' => '24',
                'title' => 'Santa Catarina',
                'uf' => 'SC',
                'ibge_code' => '42',
                'slug' => 'santa-catarina',
            ],
            [
                'id' => '25',
                'title' => 'Sergipe',
                'uf' => 'SE',
                'ibge_code' => '28',
                'slug' => 'sergipe',
            ],
            [
                'id' => '26',
                'title' => 'São Paulo',
                'uf' => 'SP',
                'ibge_code' => '35',
                'slug' => 'sao-paulo',
            ],
            [
                'id' => '27',
                'title' => 'Tocantins',
                'uf' => 'TO',
                'ibge_code' => '17',
                'slug' => 'tocantins',
            ],
        ]);
    }
}
