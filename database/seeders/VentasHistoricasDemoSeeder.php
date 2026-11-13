<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Historial FICTICIO de ventas a vehículos (para probar la tabla de decisiones).
 *
 *   php artisan db:seed --class=VentasHistoricasDemoSeeder
 *
 * - Crea ventas de los últimos 60 días (hasta ayer) para todas las estaciones activas.
 * - Cada día tiene una demanda ALTA, MEDIA o BAJA común a todas las estaciones.
 * - Los triggers de la base descuentan existencias al vender y anotan movimientos. Para no alterar el
 *   inventario real, el seeder sube el stock mientras inserta y al final DEJA LAS EXISTENCIAS COMO ESTABAN
 *   y borra los movimientos que los triggers generaron.
 * - No se repite: si ya hay ventas de hace más de 2 días, no hace nada.
 */
class VentasHistoricasDemoSeeder extends Seeder
{
    private const DIAS = 60;

    public function run(): void
    {
        if (DB::table('venta_combustible')->where('FEC_VEN', '<', now()->subDays(2))->exists()) {
            $this->aviso('Ya hay ventas de hace más de 2 días: no se agrega historial ficticio.');

            return;
        }

        $vehiculos = DB::table('automovil')->limit(30)->pluck('COD_VEH')->all();
        $usuario = DB::table('usuario')->orderBy('COD_USU')->value('COD_USU');
        $combustibles = DB::table('tipo_combustible')->get(['COD_COM', 'PRE_COM']);

        if (! $vehiculos || ! $usuario || $combustibles->isEmpty()) {
            $this->aviso('Faltan datos base: registra al menos un automóvil (menú Automóviles) y revisa que existan usuarios y combustibles.');

            return;
        }

        // Estaciones que tienen existencias registradas (el trigger las necesita)
        $estaciones = DB::table('estacion')->where('ACT_EST', 1)
            ->whereIn('COD_EST', DB::table('existencia_estacion')->select('COD_EST'))
            ->pluck('COD_EST')->all();

        // Copia de seguridad de las existencias y del último movimiento
        $existencias = DB::table('existencia_estacion')->get();
        $pkMov = $this->clavePrimariaMovimientos();
        $ultimoMov = $pkMov ? (int) DB::table('movimiento_existencia')->max($pkMov) : null;

        mt_srand(2026);

        // Cantidad base de ventas por día de cada estación
        $base = [];
        foreach ($estaciones as $id) {
            $base[$id] = mt_rand(6, 16);
        }

        $total = 0;

        try {
            DB::table('existencia_estacion')->update(['CAN_EXI' => 5000000]);

            for ($d = self::DIAS; $d >= 1; $d--) {
                $dia = now()->subDays($d)->startOfDay();

                // Demanda de la zona ese día
                $r = mt_rand(1, 100);
                [$estado, $factor] = $r <= 25 ? ['alta', mt_rand(150, 180) / 100]
                    : ($r <= 75 ? ['media', mt_rand(90, 110) / 100] : ['baja', mt_rand(45, 65) / 100]);

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
                            'PRE_VEN' => $comb->PRE_COM,
                            'COD_USU' => $usuario,
                            'FEC_VEN' => $dia->copy()->setTime(mt_rand(6, 21), mt_rand(0, 59), mt_rand(0, 59))->toDateTimeString(),
                        ];
                    }
                }

                foreach (array_chunk($filas, 200) as $lote) {
                    DB::table('venta_combustible')->insert($lote);
                }

                $total += count($filas);
            }
        } finally {
            // Se devuelven las existencias reales (los triggers recalculan el estado de cada estación)
            foreach ($existencias as $e) {
                DB::table('existencia_estacion')
                    ->where('COD_EST', $e->COD_EST)->where('COD_COM', $e->COD_COM)
                    ->update(['CAN_EXI' => $e->CAN_EXI]);
            }

            if ($pkMov !== null) {
                DB::table('movimiento_existencia')->where($pkMov, '>', $ultimoMov)->delete();
            }
        }

        $primera = DB::table('venta_combustible')->min('FEC_VEN');

        $this->command?->info("Listo: {$total} ventas ficticias en ".count($estaciones).' estaciones.');

        if ($primera && $primera > now()->subDays(5)->toDateTimeString()) {
            $this->aviso('Ojo: las fechas no quedaron como se pidió (un trigger las reemplaza por la fecha actual). Avísame para ajustarlo.');
        }
    }

    /** Nombre de la clave primaria de movimiento_existencia (o null si la tabla no existe). */
    private function clavePrimariaMovimientos(): ?string
    {
        $fila = DB::selectOne(
            "SELECT COLUMN_NAME AS c FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'movimiento_existencia' AND CONSTRAINT_NAME = 'PRIMARY' LIMIT 1"
        );

        return $fila->c ?? null;
    }

    private function aviso(string $mensaje): void
    {
        $this->command?->warn($mensaje);
    }
}
