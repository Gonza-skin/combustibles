<?php

namespace App\Http\Controllers;

use App\Models\Estacion;
use App\Models\ReporteCiudadano;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** reportes que envian los ciudadanos sobre filas y disponibilidad solo para ver admin. */
class ReporteCiudadanoAdminController extends Controller
{
    public function index(Request $request): View
    {
        $filtros = $request->validate([
            'estacion' => ['nullable', 'integer'],
            'fila' => ['nullable', Rule::in(array_keys(ReporteCiudadano::FILAS))],
            'disponibilidad' => ['nullable', Rule::in(array_keys(ReporteCiudadano::DISPONIBILIDAD))],
        ]);

        $reportes = ReporteCiudadano::with(['estacion', 'ciudadano'])
            ->when($filtros['estacion'] ?? null, fn ($q, $v) => $q->where('COD_EST', $v))
            ->when($filtros['fila'] ?? null, fn ($q, $v) => $q->where('FIL_REP', $v))
            ->when($filtros['disponibilidad'] ?? null, fn ($q, $v) => $q->where('DIS_REP', $v))
            ->orderByDesc('COD_REP')
            ->paginate(15)
            ->withQueryString();

        return view('reportes-ciudadanos.index', [
            'reportes' => $reportes,
            'filtros' => $filtros,
            'estaciones' => Estacion::orderBy('NOM_EST')->pluck('NOM_EST', 'COD_EST')->all(),
        ]);
    }
}