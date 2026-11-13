<?php

namespace App\Http\Controllers;

use App\Services\CadenaMarkov;
use App\Services\PrediccionDisponibilidad;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Módulo de predicción de disponibilidad (Cadenas de Markov). */
class PrediccionController extends Controller
{
    public const HISTORIAS = [
        30 => 'Último mes (30 días)',
        90 => 'Últimos 3 meses (90 días)',
        180 => 'Últimos 6 meses (180 días)',
        365 => 'Último año',
        730 => 'Últimos 2 años',
        1500 => 'Todo el historial',
    ];

    /** Unidades para "cuánto adelante predecir" y cuántos días vale cada una. */
    public const UNIDADES = ['dias' => 'Días', 'semanas' => 'Semanas', 'meses' => 'Meses'];

    private const DIAS_POR_UNIDAD = ['dias' => 1, 'semanas' => 7, 'meses' => 30];

    public function index(Request $request, PrediccionDisponibilidad $prediccion): View
    {
        $datos = $request->validate([
            'estacion' => ['nullable', 'integer', 'exists:estacion,COD_EST'],
            'cantidad' => ['nullable', 'integer', 'between:1,365'],
            'unidad' => ['nullable', 'in:dias,semanas,meses'],
            'umbral' => ['nullable', 'numeric', 'between:1,100'],
            'historia' => ['nullable', 'integer', 'in:'.implode(',', array_keys(self::HISTORIAS))],
            'hoy' => ['nullable', 'in:0,1,2'],
        ]);

        $cantidad = (int) ($datos['cantidad'] ?? 3);
        $unidad = $datos['unidad'] ?? 'dias';
        $dias = min(365, $cantidad * self::DIAS_POR_UNIDAD[$unidad]);
        $umbral = (float) ($datos['umbral'] ?? 20);
        $historia = (int) ($datos['historia'] ?? 365);
        $hoy = isset($datos['hoy']) && $datos['hoy'] !== '' ? (int) $datos['hoy'] : null;

        $resultados = $prediccion->analizar($dias, $historia, $hoy);

        // Ranking: de mayor a menor probabilidad de estar agotada; las que no tienen datos, al final
        $ranking = collect($resultados)->sortByDesc(fn ($r) => $r['prob_agotado'] ?? -1)->values();

        $seleccionada = isset($datos['estacion']) ? ($resultados[(int) $datos['estacion']] ?? null) : null;

        return view('prediccion.index', [
            'ranking' => $ranking,
            'seleccionada' => $seleccionada,
            'estaciones' => collect($resultados)->mapWithKeys(fn ($r, $id) => [$id => $r['estacion']->NOM_EST])->all(),
            'estados' => CadenaMarkov::NOMBRES,
            'historias' => self::HISTORIAS,
            'dias' => $dias,
            'cantidad' => $cantidad,
            'unidad' => $unidad,
            'unidades' => self::UNIDADES,
            'umbral' => $umbral,
            'historia' => $historia,
            'hoy' => $hoy,
        ]);
    }
}
