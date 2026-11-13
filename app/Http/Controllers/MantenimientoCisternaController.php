<?php

namespace App\Http\Controllers;

use App\Models\Cisterna;
use App\Models\MantenimientoCisterna;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** mantenimiento de cisternas son preventivo, correctivo, documentacion solo el administrador. */
class MantenimientoCisternaController extends Controller
{
    public function index(Request $request): View
    {
        $filtros = $request->validate([
            'cisterna' => ['nullable', 'integer'],
            'tipo' => ['nullable', Rule::in(array_keys(MantenimientoCisterna::TIPOS))],
        ]);

        $mantenimientos = MantenimientoCisterna::with('cisterna')
            ->when($filtros['cisterna'] ?? null, fn ($q, $v) => $q->where('COD_CIS', $v))
            ->when($filtros['tipo'] ?? null, fn ($q, $v) => $q->where('TIP_MAN', $v))
            ->orderByDesc('FEC_MAN')
            ->orderByDesc('COD_MAN')
            ->paginate(15)
            ->withQueryString();

        $cisternas = Cisterna::orderBy('PLC_CIS')->pluck('PLC_CIS', 'COD_CIS')->all();

        return view('mantenimiento-cisternas.index', compact('mantenimientos', 'cisternas', 'filtros'));
    }

    public function create(): View
    {
        return view('mantenimiento-cisternas.create', $this->datosFormulario(new MantenimientoCisterna(['FEC_MAN' => today()])));
    }

    public function store(Request $request): RedirectResponse
    {
        MantenimientoCisterna::create($request->validate($this->reglas()));

        return redirect()->route('mantenimiento-cisternas.index')->with('exito', 'Mantenimiento registrado correctamente.');
    }

    public function edit(MantenimientoCisterna $mantenimiento_cisterna): View
    {
        return view('mantenimiento-cisternas.edit', $this->datosFormulario($mantenimiento_cisterna));
    }

    public function update(Request $request, MantenimientoCisterna $mantenimiento_cisterna): RedirectResponse
    {
        $mantenimiento_cisterna->update($request->validate($this->reglas()));

        return redirect()->route('mantenimiento-cisternas.index')->with('exito', 'Mantenimiento actualizado correctamente.');
    }

    public function destroy(MantenimientoCisterna $mantenimiento_cisterna): RedirectResponse
    {
        $mantenimiento_cisterna->delete();

        return redirect()->route('mantenimiento-cisternas.index')->with('exito', 'Registro de mantenimiento eliminado.');
    }

    private function reglas(): array
    {
        return [
            'COD_CIS' => ['required', 'integer', Rule::exists('cisterna', 'COD_CIS')],
            'TIP_MAN' => ['required', Rule::in(array_keys(MantenimientoCisterna::TIPOS))],
            'DES_MAN' => ['nullable', 'string', 'max:2000'],
            'FEC_MAN' => ['required', 'date', 'before_or_equal:today'],
        ];
    }

    private function datosFormulario(MantenimientoCisterna $mantenimiento): array
    {
        return [
            'mantenimiento' => $mantenimiento,
            'cisternas' => Cisterna::orderBy('PLC_CIS')->pluck('PLC_CIS', 'COD_CIS')->all(),
        ];
    }
}