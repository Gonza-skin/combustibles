<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * Arma, para cada estación, la Cadena de Markov a partir de los reportes de los ciudadanos.
 *
 * Estado de una estación en un día (según los reportes de ese día, gana el más frecuente;
 * si hay empate, el peor):
 *   Agotado        → DIS_REP = 'agotado'
 *   Con fila larga → hay combustible y FIL_REP = 'larga'
 *   Disponible     → todo lo demás (disponible o poco stock, con fila normal)
 */
class PrediccionDisponibilidad
{
    /** Mínimo de transiciones observadas para fiarse de la matriz de una estación. */
    public const MIN_TRANSICIONES = 15;

    public function __construct(private CadenaMarkov $markov)
    {
    }

    /**
     * @param  int          $dias     días hacia el futuro a predecir
     * @param  int          $historia días de historial de reportes a usar
     * @param  int|null     $hoy      estado de hoy forzado (0, 1, 2) o null = el último reportado
     * @return array<int, array>  COD_EST => resultado
     */
    public function analizar(int $dias, int $historia, ?int $hoy = null): array
    {
        $estaciones = DB::table('estacion')->where('ACT_EST', 1)->orderBy('NOM_EST')
            ->get(['COD_EST', 'NOM_EST', 'ZON_EST'])->keyBy('COD_EST');

        $series = $this->seriesDiarias($historia);
        $resultado = [];

        foreach ($estaciones as $id => $estacion) {
            $serie = $series[$id] ?? [];
            $conteos = $this->markov->contarTransiciones($serie);
            $m = $this->markov->matriz($conteos);

            $ultimoDia = $serie ? array_key_last($serie) : null;
            $actual = $hoy ?? ($serie ? $serie[$ultimoDia] : null);

            $fila = [
                'estacion' => $estacion,
                'dias_con_reportes' => count($serie),
                'conteos' => $conteos,
                'transiciones' => $m['total'],
                'P' => $m['P'],
                'filas_sin_datos' => $m['filas_sin_datos'],
                'suficiente' => $m['total'] >= self::MIN_TRANSICIONES && $m['filas_sin_datos'] === [],
                'ultimo_dia' => $ultimoDia,
                'estado_actual' => $actual,
                'a_n' => null,
                'prob_agotado' => null,
                'pi' => null,
                'dias_hasta_agotarse' => null,
            ];

            if ($m['total'] > 0) {
                $fila['pi'] = $this->markov->estacionaria($m['P']);
                $fila['dias_hasta_agotarse'] = $this->markov->diasHastaAgotarse($m['P']);

                if ($actual !== null) {
                    $a0 = [0.0, 0.0, 0.0];
                    $a0[$actual] = 1.0;
                    $fila['a_n'] = $this->markov->distribucion($a0, $m['P'], $dias);
                    $fila['prob_agotado'] = $fila['a_n'][CadenaMarkov::AGOTADO];
                }
            }

            $resultado[$id] = $fila;
        }

        return $resultado;
    }

    /**
     * Estado diario de cada estación.
     *
     * @return array<int, array<string, int>>  COD_EST => ['YYYY-MM-DD' => estado]
     */
    private function seriesDiarias(int $historia): array
    {
        $filas = DB::table('reporte_ciudadano')
            ->selectRaw("COD_EST, DATE(FEC_REP) AS dia,
                SUM(CASE WHEN DIS_REP = 'agotado' THEN 1 ELSE 0 END) AS ag,
                SUM(CASE WHEN DIS_REP = 'agotado' THEN 0 WHEN FIL_REP = 'larga' THEN 1 ELSE 0 END) AS lg,
                COUNT(*) AS total")
            ->where('FEC_REP', '>=', now()->subDays($historia)->startOfDay())
            ->groupBy('COD_EST', 'dia')
            ->orderBy('COD_EST')->orderBy('dia')
            ->get();

        $series = [];

        foreach ($filas as $f) {
            $ag = (int) $f->ag;
            $lg = (int) $f->lg;
            $dp = (int) $f->total - $ag - $lg;

            $series[(int) $f->COD_EST][$f->dia] = match (true) {
                $ag >= $lg && $ag >= $dp => CadenaMarkov::AGOTADO,
                $lg >= $dp => CadenaMarkov::FILA_LARGA,
                default => CadenaMarkov::DISPONIBLE,
            };
        }

        return $series;
    }
}
