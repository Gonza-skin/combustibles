<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Estaciones DE PRUEBA (nombres ficticios, ubicación aproximada de cada zona de La Paz)
 * para ver el mapa y probar el sistema. Se puede ejecutar varias veces sin duplicar.
 *
 *   php artisan db:seed --class=EstacionesDemoSeeder
 */
class EstacionesDemoSeeder extends Seeder
{
    public function run(): void
    {
        // nombre, zona, dirección, lat, lng, litros de gasolina, litros de diésel
        $estaciones = [
            ['Estación Demo Sopocachi',   'Sopocachi',   'Av. 20 de Octubre (zona)', -16.5115, -68.1280, 9000, 8000],
            ['Estación Demo Miraflores',  'Miraflores',  'Av. Busch (zona)',         -16.5000, -68.1190, 1200,  900],
            ['Estación Demo San Pedro',   'San Pedro',   'Av. Montes (zona)',        -16.4960, -68.1330,    0,    0],
            ['Estación Demo Obrajes',     'Obrajes',     'Av. Hernando Siles (zona)', -16.5280, -68.1050, 15000, 12000],
            ['Estación Demo Calacoto',    'Calacoto',    'Av. Ballivián (zona)',     -16.5370, -68.0820, 800,  1500],
            ['Estación Demo Villa Fátima', 'Villa Fátima', 'Av. Tejada Sorzano (zona)', -16.4850, -68.1180, 11000, 9500],
            ['Estación Demo Max Paredes', 'Max Paredes', 'Av. Buenos Aires (zona)',  -16.4950, -68.1500, 6000, 5000],
        ];

        $gasolina = DB::table('tipo_combustible')->where('DES_COM', 'like', 'Gasolina%')->value('COD_COM');
        $diesel = DB::table('tipo_combustible')->where('DES_COM', 'like', 'Di%sel%')->value('COD_COM');

        foreach ($estaciones as [$nombre, $zona, $dir, $lat, $lng, $litrosGas, $litrosDie]) {
            $codigo = DB::table('estacion')->where('NOM_EST', $nombre)->value('COD_EST');

            if (! $codigo) {
                $codigo = DB::table('estacion')->insertGetId([
                    'NOM_EST' => $nombre, 'DIR_EST' => $dir, 'ZON_EST' => $zona,
                    'LAT_EST' => $lat, 'LON_EST' => $lng,
                    'MIN_EST' => 2000, 'MAX_EST' => 20000, 'TPT_EST' => 45, 'ACT_EST' => 1,
                ]);
            }

            // Las existencias hacen que la base calcule sola el estado de la estación
            foreach ([[$gasolina, $litrosGas], [$diesel, $litrosDie]] as [$combustible, $litros]) {
                if ($combustible) {
                    DB::table('existencia_estacion')->updateOrInsert(
                        ['COD_EST' => $codigo, 'COD_COM' => $combustible],
                        ['CAN_EXI' => $litros]
                    );
                }
            }
        }
    }
}