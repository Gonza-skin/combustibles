<?php

namespace App\Services;

use App\Models\Alerta;
use Illuminate\Support\Facades\DB;

/**
 * Revisa el sistema y crea las alertas que correspondan (sin repetir las que ya están sin leer):
 *  - Existencia mínima: estaciones con nivel bajo o sin combustible.
 *  - Tanque crítico: tanques de planta por debajo del 15 % de su capacidad.
 *  - Cisterna retrasada: envíos en tránsito que ya pasaron la hora estimada de llegada.
 */
class GeneradorAlertas
{
    /** @return int cantidad de alertas nuevas creadas */
    public function generar(): int
    {
        $creadas = 0;

        // 1) Estaciones con poco o nada de combustible (por cada combustible)
        $filas = DB::table('existencia_estacion as x')
            ->join('estacion as e', 'e.COD_EST', '=', 'x.COD_EST')
            ->join('tipo_combustible as c', 'c.COD_COM', '=', 'x.COD_COM')
            ->where('e.ACT_EST', 1)
            ->get(['e.COD_EST', 'e.NOM_EST', 'e.MIN_EST', 'c.DES_COM', 'x.CAN_EXI']);

        foreach ($filas as $f) {
            if ((float) $f->CAN_EXI > (float) $f->MIN_EST) {
                continue;
            }
            $agotado = (float) $f->CAN_EXI <= 0;
            $creadas += $this->crear(
                'existencia_minima',
                ($agotado ? 'Sin ' : 'Nivel bajo de ').$f->DES_COM.' en '.$f->NOM_EST.' ('.number_format($f->CAN_EXI, 0, ',', '.').' L).',
                $agotado ? 'alta' : 'media',
                ['COD_EST' => $f->COD_EST],
                $f->DES_COM
            );
        }

        // 2) Tanques casi vacíos
        $tanques = DB::table('tanque_almacenamiento')
            ->whereNotIn('ESD_TAN', ['inactivo', 'mantenimiento'])
            ->whereRaw('NIV_TAN < CAP_TAN * 0.15')
            ->get(['COD_TAN', 'NOM_TAN', 'NIV_TAN', 'CAP_TAN']);

        foreach ($tanques as $t) {
            $pct = round($t->NIV_TAN / $t->CAP_TAN * 100);
            $creadas += $this->crear(
                'tanque_critico',
                'El tanque '.$t->NOM_TAN.' está al '.$pct.' % de su capacidad.',
                $pct < 5 ? 'alta' : 'media',
                ['COD_TAN' => $t->COD_TAN]
            );
        }

        // 3) Envíos en tránsito que ya deberían haber llegado
        $retrasados = DB::table('abastecimiento')
            ->where('ESD_ABA', 'en_transito')
            ->whereNotNull('HEL_ABA')
            ->where('HEL_ABA', '<', now())
            ->get(['COD_ABA', 'HEL_ABA']);

        foreach ($retrasados as $a) {
            $creadas += $this->crear(
                'cisterna_retrasada',
                'El abastecimiento #'.$a->COD_ABA.' debía llegar el '.date('d/m/Y H:i', strtotime($a->HEL_ABA)).' y sigue en tránsito.',
                'alta',
                ['COD_ABA' => $a->COD_ABA]
            );
        }

        return $creadas;
    }

    /** Crea la alerta solo si no hay otra igual sin leer. */
    private function crear(string $tipo, string $mensaje, string $nivel, array $ref, ?string $marca = null): int
    {
        $existe = Alerta::where('TIP_ALE', $tipo)
            ->where('LEI_ALE', 0)
            ->where($ref)
            ->when($marca, fn ($q) => $q->where('MEN_ALE', 'like', '%'.$marca.'%'))
            ->exists();

        if ($existe) {
            return 0;
        }

        Alerta::create(['TIP_ALE' => $tipo, 'MEN_ALE' => mb_substr($mensaje, 0, 255), 'NIV_ALE' => $nivel, 'LEI_ALE' => false] + $ref);

        return 1;
    }
}