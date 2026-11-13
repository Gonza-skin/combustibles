<?php

namespace App\Http\Controllers;

use App\Models\Estacion;
use App\Models\TipoCombustible;
use App\Services\DecisionAbastecimiento;
use App\Services\EstacionesCercanas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Recomendación de abastecimiento (subadministrador).
 * 1) Dado un punto de referencia, toma las 3 estaciones más cercanas (A, B y C).
 * 2) Con las ventas a vehículos de cada una arma la tabla de decisiones (valor esperado y Wald).
 */
class RecomendacionController extends Controller
{
    public const PERIODOS = [30 => 'Últimos 30 días', 60 => 'Últimos 60 días', 90 => 'Últimos 90 días'];

    public function index(Request $request, EstacionesCercanas $cercanas, DecisionAbastecimiento $decision): View
    {
        $datos = $request->validate([
            'estacion' => ['nullable', 'integer', 'exists:estacion,COD_EST'],
            'combustible' => ['nullable', 'integer', 'exists:tipo_combustible,COD_COM'],
            'dias' => ['nullable', 'integer', 'in:'.implode(',', array_keys(self::PERIODOS))],
        ]);

        $referencias = Estacion::where('ACT_EST', true)->whereNotNull('LAT_EST')->whereNotNull('LON_EST')
            ->orderBy('NOM_EST')->pluck('NOM_EST', 'COD_EST')->all();
        $sinCoordenadas = Estacion::where('ACT_EST', true)
            ->where(fn ($q) => $q->whereNull('LAT_EST')->orWhereNull('LON_EST'))
            ->count();
        $combustibles = TipoCombustible::orderBy('COD_COM')->pluck('DES_COM', 'COD_COM')->all();

        $referencia = isset($datos['estacion']) ? Estacion::find($datos['estacion']) : null;
        $combustible = $datos['combustible'] ?? array_key_first($combustibles);
        $dias = $datos['dias'] ?? 60;

        $candidatas = collect();
        $existencias = [];
        $analisis = null;

        if ($referencia && $referencia->tieneCoordenadas()) {
            $candidatas = $cercanas->cercanas($referencia, 3);

            $existencias = DB::table('existencia_estacion')
                ->where('COD_COM', $combustible)
                ->whereIn('COD_EST', $candidatas->pluck('COD_EST'))
                ->pluck('CAN_EXI', 'COD_EST')
                ->all();

            $analisis = $decision->analizar($candidatas, $dias);
        }

        return view('recomendaciones.index', [
            'referencias' => $referencias,
            'combustibles' => $combustibles,
            'periodos' => self::PERIODOS,
            'referencia' => $referencia,
            'combustible' => $combustible,
            'dias' => $dias,
            'candidatas' => $candidatas,
            'existencias' => $existencias,
            'analisis' => $analisis,
            'sinCoordenadas' => $sinCoordenadas,
        ]);
    }
}
