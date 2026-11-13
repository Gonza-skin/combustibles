<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Ventas FICTICIAS a vehículos desde el 01/01/2024 hasta ayer (o hasta antes de la primera venta ya existente).
 *
 *   php artisan db:seed --class=VentasHistoricasSeeder
 *
 * Precio por litro según el año:
 *   2024 y 2025 (con subvención): Gasolina Bs 3,74 · Diésel Bs 3,72
 *   2026: el precio actual de la tabla tipo_combustible.
 *
 * Cada día tiene demanda ALTA / MEDIA / BAJA común a todas las estaciones (más ventas los viernes y sábados,
 * menos los domingos). Los triggers descuentan existencias y anotan movimientos: el seeder sube el stock mientras
 * inserta y al final DEJA LAS EXISTENCIAS COMO ESTABAN y borra los movimientos generados.
 * Puede tardar unos minutos (un trigger por venta).
 */
class VentasHistoricasSeeder extends Seeder
{
    private const INICIO = '2024-01-01';

    private const PRECIO_SUBVENCIONADO = ['gasolina' => 3.74, 'diesel' => 3.72];

    public function run(): void
    {
        $primera = DB::table('venta_combustible')->min('FEC_VEN');

        if ($primera && $primera < self::INICIO.' 23:59:59') {
            $this->command?->warn('Ya hay ventas desde 2024 o antes: no se agrega nada.');

            return;
        }

        $fin = $primera ? Carbon::parse($primera)->startOfDay()->subDay() : now()->subDay()->startOfDay();
        $inicio = Carbon::parse(self::INICIO);

        $vehiculos = DB::table('automovil')->limit(60)->pluck('COD_VEH')->all();
        $usuario = DB::table('usuario')->orderBy('COD_USU')->value('COD_USU');
        $combustibles = DB::table('tipo_combustible')->get(['COD_COM', 'DES_COM', 'PRE_COM']);

        if (! $vehiculos || ! $usuario || $combustibles->isEmpty()) {
            $this->command?->warn('Faltan datos base: registra al menos un automóvil (menú Automóviles).');

            return;
        }

        $estaciones = DB::table('estacion')->where('ACT_EST', 1)
            ->whereIn('COD_EST', DB::table('existencia_estacion')->select('COD_EST'))
            ->pluck('COD_EST')->all();

        if (! $estaciones) {
            $this->command?->warn('Ninguna estación activa tiene existencias registradas.');

            return;
        }

        $existencias = DB::table('existencia_estacion')->get();
        $ultimoMov = (int) DB::table('movimiento_existencia')->max('COD_MOV');

        mt_srand(2024);
        $base = [];
        foreach ($estaciones as $id) {
            $base[$id] = mt_rand(6, 16);
        }

        $total = 0;

        try {
            DB::table('existencia_estacion')->update(['CAN_EXI' => 50000000]);

            for ($dia = $inicio->copy(); $dia <= $fin; $dia->addDay()) {
                $r = mt_rand(1, 100);
                $factor = $r <= 25 ? mt_rand(150, 180) / 100
                    : ($r <= 75 ? mt_rand(90, 110) / 100 : mt_rand(45, 65) / 100);

                $factor *= match ($dia->dayOfWeekIso) {
                    5, 6 => 1.15,
                    7 => 0.8,
                    default => 1.0,
                };

                $filas = [];

                foreach ($estaciones as $id) {
                    $ventas = max(1, (int) round($base[$id] * $factor + mt_rand(-1, 1)));

                    for ($i = 0; $i < $ventas; $i++) {
                        $comb = $combustibles[mt_rand(0, $combustibles->count() - 1)];

                        $filas[] = [
                            'COD_EST' => $id,
                            'COD_VEH' => $vehiculos[array_rand($vehiculos)],
                            'COD_COM' => $comb->COD_COM,
                            'CAN_VEN' => mt_rand(5, 40),
                            'PRE_VEN' => $this->precio($comb, $dia->year),
                            'COD_USU' => $usuario,
                            'FEC_VEN' => $dia->copy()->setTime(mt_rand(6, 21), mt_rand(0, 59), mt_rand(0, 59))->toDateTimeString(),
                        ];
                    }
                }

                foreach (array_chunk($filas, 300) as $lote) {
                    DB::table('venta_combustible')->insert($lote);
                }

                $total += count($filas);

                if ($dia->day === 1) {
                    $this->command?->info('  '.$dia->format('Y-m').' listo ('.$total.' ventas hasta ahora)');
                }
            }
        } finally {
            foreach ($existencias as $e) {
                DB::table('existencia_estacion')
                    ->where('COD_EST', $e->COD_EST)->where('COD_COM', $e->COD_COM)
                    ->update(['CAN_EXI' => $e->CAN_EXI]);
            }

            DB::table('movimiento_existencia')->where('COD_MOV', '>', $ultimoMov)->delete();
        }

        $this->command?->info("Listo: {$total} ventas ficticias entre {$inicio->toDateString()} y {$fin->toDateString()}.");

        $masAntigua = DB::table('venta_combustible')->min('FEC_VEN');
        if ($masAntigua && $masAntigua > self::INICIO.' 23:59:59') {
            $this->command?->warn('Ojo: un trigger cambió las fechas de venta. Avísame para ajustarlo.');
        }
    }

    private function precio(object $combustible, int $anio): float
    {
        if ($anio >= 2026) {
            return (float) $combustible->PRE_COM;
        }

        $nombre = mb_strtolower($combustible->DES_COM);

        if (str_contains($nombre, 'gasolina')) {
            return self::PRECIO_SUBVENCIONADO['gasolina'];
        }

        if (str_contains($nombre, 'di')) {   // diésel / diesel
            return self::PRECIO_SUBVENCIONADO['diesel'];
        }

        return (float) $combustible->PRE_COM;
    }
}
