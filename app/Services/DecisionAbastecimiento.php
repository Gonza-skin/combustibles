<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Teoría de Decisiones para el envío de la cisterna (alternativas: estación A, B o C).
 *
 * Estados de la naturaleza: demanda ALTA, MEDIA o BAJA.
 * Cada día con ventas se clasifica según el total de vehículos atendidos por las 3 estaciones:
 *   ALTA  si el total >= 125 % del promedio diario,
 *   BAJA  si el total <=  75 % del promedio diario,
 *   MEDIA en los demás casos.
 * Pago de cada estación en cada estado = promedio de vehículos atendidos (ventas) en los días de ese estado.
 * Probabilidad de cada estado = frecuencia histórica (días en ese estado / días con ventas).
 *
 * Criterios:
 *   Valor esperado: elige la estación con mayor  Σ probabilidad × pago.
 *   Wald (maximin): elige la estación cuyo PEOR escenario es el mejor.
 */
class DecisionAbastecimiento
{
    public const ESTADOS = ['alta' => 'Alta', 'media' => 'Media', 'baja' => 'Baja'];

    public const UMBRAL_ALTA = 1.25;

    public const UMBRAL_BAJA = 0.75;

    /**
     * @param  Collection  $estaciones  estaciones candidatas (A, B, C)
     * @param  int         $dias        cuántos días hacia atrás se miran
     */
    public function analizar(Collection $estaciones, int $dias = 60): array
    {
        $ids = $estaciones->pluck('COD_EST')->map(fn ($id) => (int) $id)->all();

        $filas = DB::table('venta_combustible')
            ->selectRaw('COD_EST, DATE(FEC_VEN) as dia, COUNT(*) as n')
            ->whereIn('COD_EST', $ids)
            ->where('FEC_VEN', '>=', now()->subDays($dias)->startOfDay())
            ->groupBy('COD_EST', 'dia')
            ->get();

        $porDia = [];   // dia => [COD_EST => vehículos atendidos]
        foreach ($filas as $f) {
            $porDia[$f->dia][(int) $f->COD_EST] = (int) $f->n;
        }

        return $this->calcular($porDia, $ids);
    }

    /**
     * Cálculo puro (sin base de datos), para poder probarlo.
     *
     * @param  array<string, array<int, int>>  $porDia  dia => [COD_EST => vehículos]
     * @param  array<int, int>                 $ids
     */
    public function calcular(array $porDia, array $ids): array
    {
        $totalDias = count($porDia);

        if ($totalDias === 0) {
            return ['hay_datos' => false];
        }

        $totales = array_map('array_sum', $porDia);
        $promedio = array_sum($totales) / $totalDias;

        $estadoDia = [];
        foreach ($totales as $dia => $total) {
            $estadoDia[$dia] = $total >= $promedio * self::UMBRAL_ALTA ? 'alta'
                : ($total <= $promedio * self::UMBRAL_BAJA ? 'baja' : 'media');
        }

        $diasPorEstado = array_fill_keys(array_keys(self::ESTADOS), []);
        foreach ($estadoDia as $dia => $estado) {
            $diasPorEstado[$estado][] = $dia;
        }

        $probabilidad = [];
        foreach ($diasPorEstado as $estado => $lista) {
            $probabilidad[$estado] = count($lista) / $totalDias;
        }

        $pagos = [];          // COD_EST => estado => promedio de vehículos (o null si no hubo días así)
        $valorEsperado = [];
        $minimo = [];

        foreach ($ids as $id) {
            $conDatos = [];

            foreach ($diasPorEstado as $estado => $lista) {
                if ($lista === []) {
                    $pagos[$id][$estado] = null;
                    continue;
                }

                $suma = 0;
                foreach ($lista as $dia) {
                    $suma += $porDia[$dia][$id] ?? 0;
                }

                $pagos[$id][$estado] = $suma / count($lista);
                $conDatos[$estado] = $pagos[$id][$estado];
            }

            $valorEsperado[$id] = 0.0;
            foreach ($conDatos as $estado => $pago) {
                $valorEsperado[$id] += $probabilidad[$estado] * $pago;
            }

            $minimo[$id] = min($conDatos);
        }

        return [
            'hay_datos' => true,
            'dias_con_ventas' => $totalDias,
            'promedio_diario' => $promedio,
            'dias_por_estado' => array_map('count', $diasPorEstado),
            'probabilidad' => $probabilidad,
            'pagos' => $pagos,
            'valor_esperado' => $valorEsperado,
            'minimo' => $minimo,
            'mejor_valor_esperado' => array_search(max($valorEsperado), $valorEsperado),
            'mejor_wald' => array_search(max($minimo), $minimo),
        ];
    }
}
