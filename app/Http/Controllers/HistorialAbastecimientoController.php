<?php

namespace App\Http\Controllers;

use App\Models\Abastecimiento;
use App\Models\HistorialEstadoAbastecimiento;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** Historial de cambios de estado de los abastecimientos solo para ver */
class HistorialAbastecimientoController extends Controller
{
    public function index(Request $request): View
    {
        $filtros = $request->validate([
            'abastecimiento' => ['nullable', 'integer', 'min:1'],
            'estado' => ['nullable', Rule::in(array_keys(Abastecimiento::ESTADOS))],
        ]);

        $historial = HistorialEstadoAbastecimiento::with(['usuario', 'abastecimiento.solicitud.estacion'])
            ->when($filtros['abastecimiento'] ?? null, fn ($q, $v) => $q->where('COD_ABA', $v))
            ->when($filtros['estado'] ?? null, fn ($q, $v) => $q->where('ESD_HIS', $v))
            ->orderByDesc('COD_HIS')
            ->paginate(20)
            ->withQueryString();

        return view('abastecimiento.historial.index', compact('historial', 'filtros'));
    }
}