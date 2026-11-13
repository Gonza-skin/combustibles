<?php

namespace App\Http\Controllers;

use App\Models\Abastecimiento;
use App\Models\Alerta;
use App\Models\Incidencia;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;


class SeguimientoController extends Controller
{
    public function index(Request $request): View
    {
        $filtros = $request->validate([
            'desde' => ['nullable', 'date'],
            'hasta' => ['nullable', 'date', 'after_or_equal:desde'],
            'estado' => ['nullable', Rule::in(array_keys(Abastecimiento::ESTADOS))],
            'problema' => ['nullable', Rule::in(['retraso', 'diferencia', 'cualquiera'])],
        ], [], ['hasta' => 'fecha "hasta"']);

        $consulta = Abastecimiento::query()
            ->when($filtros['desde'] ?? null, fn ($q, $v) => $q->whereDate('FEC_ABA', '>=', $v))
            ->when($filtros['hasta'] ?? null, fn ($q, $v) => $q->whereDate('FEC_ABA', '<=', $v))
            ->when($filtros['estado'] ?? null, fn ($q, $v) => $q->where('ESD_ABA', $v))
            ->when($filtros['problema'] ?? null, function ($q, $v) {
                match ($v) {
                    'retraso' => $q->where('RET_ABA', 1),
                    'diferencia' => $q->where('DIF_ABA', '>', 0),
                    default => $q->where(fn ($w) => $w->where('RET_ABA', 1)->orWhere('DIF_ABA', '>', 0)),
                };
            });

        // resumen sobre las entregas ya realizadas 
        $entregadas = (clone $consulta)->whereNotNull('HRL_ABA');
        $totalEntregas = (clone $entregadas)->count();
        $conRetraso = (clone $entregadas)->where('RET_ABA', 1)->count();
        $resumen = [
            'entregas' => $totalEntregas,
            'puntualidad' => $totalEntregas > 0 ? (int) round(($totalEntregas - $conRetraso) / $totalEntregas * 100) : null,
            'retrasadas' => $conRetraso,
            'minutos_promedio' => (int) round((clone $entregadas)->where('RET_ABA', 1)->avg('MRE_ABA') ?? 0),
            'litros_faltantes' => (float) (clone $entregadas)->where('DIF_ABA', '>', 0)->sum('DIF_ABA'),
        ];

        $abastecimientos = $consulta->with(['solicitud.estacion', 'combustible', 'cisterna.conductor'])
            ->orderByDesc('COD_ABA')
            ->paginate(15)
            ->withQueryString();

        return view('seguimiento.index', [
            'abastecimientos' => $abastecimientos,
            'filtros' => $filtros,
            'resumen' => $resumen,
            'incidenciasAbiertas' => Incidencia::whereIn('ESD_INC', ['detectada', 'en_revision'])->count(),
            'alertasSinLeer' => Alerta::where('LEI_ALE', 0)->count(),
        ]);
    }
}