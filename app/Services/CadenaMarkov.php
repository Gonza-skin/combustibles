<?php

namespace App\Services;

/**
 * Cálculos de la Cadena de Markov (3 estados) del informe, sin base de datos.
 *
 * Estados: 0 = Disponible, 1 = Con fila larga, 2 = Agotado.
 * El estado de una estación se observa una vez al día; el de mañana depende solo del de hoy.
 */
class CadenaMarkov
{
    public const DISPONIBLE = 0;

    public const FILA_LARGA = 1;

    public const AGOTADO = 2;

    public const NOMBRES = ['Disponible', 'Con fila larga', 'Agotado'];

    /**
     * Cuenta las transiciones n[i][j] (hoy i → mañana j) entre DÍAS CONSECUTIVOS.
     *
     * @param  array<string, int>  $diaEstado  'YYYY-MM-DD' => estado (0, 1 o 2)
     * @return array<int, array<int, int>>
     */
    public function contarTransiciones(array $diaEstado): array
    {
        $n = array_fill(0, 3, array_fill(0, 3, 0));

        ksort($diaEstado);
        $anteriorDia = null;
        $anteriorEstado = null;

        foreach ($diaEstado as $dia => $estado) {
            if ($anteriorDia !== null
                && (strtotime($dia) - strtotime($anteriorDia)) === 86400) {
                $n[$anteriorEstado][$estado]++;
            }

            $anteriorDia = $dia;
            $anteriorEstado = $estado;
        }

        return $n;
    }

    /**
     * Matriz de transición pij = nij / ni. Si un estado nunca apareció como "hoy" (ni = 0),
     * su fila queda en partes iguales y se avisa en 'filas_sin_datos'.
     *
     * @return array{P: array<int, array<int, float>>, filas_sin_datos: array<int, int>, total: int}
     */
    public function matriz(array $conteos): array
    {
        $p = [];
        $sinDatos = [];
        $total = 0;

        for ($i = 0; $i < 3; $i++) {
            $fila = array_sum($conteos[$i]);
            $total += $fila;

            if ($fila === 0) {
                $p[$i] = [1 / 3, 1 / 3, 1 / 3];
                $sinDatos[] = $i;
                continue;
            }

            for ($j = 0; $j < 3; $j++) {
                $p[$i][$j] = $conteos[$i][$j] / $fila;
            }
        }

        return ['P' => $p, 'filas_sin_datos' => $sinDatos, 'total' => $total];
    }

    /** a_n = a_0 · P^n */
    public function distribucion(array $a0, array $p, int $dias): array
    {
        $a = $a0;

        for ($d = 0; $d < $dias; $d++) {
            $siguiente = [0.0, 0.0, 0.0];
            for ($j = 0; $j < 3; $j++) {
                for ($i = 0; $i < 3; $i++) {
                    $siguiente[$j] += $a[$i] * $p[$i][$j];
                }
            }
            $a = $siguiente;
        }

        return $a;
    }

    /** Distribución de largo plazo π (π·P = π, suma 1), por iteración. */
    public function estacionaria(array $p): array
    {
        $pi = [1 / 3, 1 / 3, 1 / 3];

        for ($k = 0; $k < 20000; $k++) {
            $nuevo = $this->distribucion($pi, $p, 1);
            $dif = abs($nuevo[0] - $pi[0]) + abs($nuevo[1] - $pi[1]) + abs($nuevo[2] - $pi[2]);
            $pi = $nuevo;

            if ($dif < 1e-13) {
                break;
            }
        }

        return $pi;
    }

    /**
     * Días promedio hasta el primer agotamiento: μ = (I − N)^-1 · 1,
     * con N = P sin la fila y la columna de Agotado.
     *
     * @return array{0: float, 1: float}|null  [desde Disponible, desde Fila larga]; null si no se puede calcular
     */
    public function diasHastaAgotarse(array $p): ?array
    {
        $a = 1 - $p[0][0];
        $b = -$p[0][1];
        $c = -$p[1][0];
        $d = 1 - $p[1][1];

        $det = $a * $d - $b * $c;

        if (abs($det) < 1e-12) {
            return null;
        }

        // (I − N)^-1 = 1/det · [[d, -b], [-c, a]]
        $mu0 = ($d - $b) / $det;
        $mu1 = (-$c + $a) / $det;

        if ($mu0 < 0 || $mu1 < 0) {
            return null;
        }

        return [$mu0, $mu1];
    }
}
