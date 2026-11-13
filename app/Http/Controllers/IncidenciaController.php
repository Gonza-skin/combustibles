<?php

namespace App\Http\Controllers;

use App\Models\Abastecimiento;
use App\Models\Incidencia;
use App\Models\TanqueAlmacenamiento;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class IncidenciaController extends Controller
{
    public function index(Request $request): View
    {
        $filtros = $request->validate([
            'tipo' => ['nullable', Rule::in(array_keys(Incidencia::TIPOS))],
            'estado' => ['nullable', Rule::in(array_keys(Incidencia::ESTADOS))],
        ]);

        $incidencias = Incidencia::with(['tanque', 'registrador'])
            ->when($filtros['tipo'] ?? null, fn ($q, $v) => $q->where('TIP_INC', $v))
            ->when($filtros['estado'] ?? null, fn ($q, $v) => $q->where('ESD_INC', $v))
            ->orderByDesc('COD_INC')
            ->paginate(15)
            ->withQueryString();

        return view('incidencias.index', compact('incidencias', 'filtros'));
    }

    public function create(): View
    {
        return view('incidencias.create', $this->listas());
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate($this->reglas());

        $incidencia = Incidencia::create($datos + [
            'ESD_INC' => 'detectada',
            'COD_USU' => $request->user()->COD_USU,
        ]);

        return redirect()->route('incidencias.show', $incidencia)->with('exito', 'Incidencia registrada.');
    }

    public function show(Incidencia $incidencia): View
    {
        $incidencia->load(['tanque.planta', 'abastecimiento.solicitud.estacion', 'registrador']);

        return view('incidencias.show', compact('incidencia'));
    }

    public function edit(Incidencia $incidencia): View
    {
        return view('incidencias.edit', ['incidencia' => $incidencia] + $this->listas());
    }

    public function update(Request $request, Incidencia $incidencia): RedirectResponse
    {
        abort_if($incidencia->ESD_INC === 'cerrada', 403, 'Una incidencia cerrada no se puede editar.');

        $incidencia->update($request->validate($this->reglas()));

        return redirect()->route('incidencias.show', $incidencia)->with('exito', 'Incidencia actualizada.');
    }

    /** la incidencia va pasando por estados */
    public function avanzar(Request $request, Incidencia $incidencia): RedirectResponse
    {
        $siguiente = Incidencia::SIGUIENTE[$incidencia->ESD_INC] ?? null;

        if (! $siguiente) {
            return back()->with('error', 'Esta incidencia ya está cerrada.');
        }

        $datos = $request->validate([
            // al resolver hay que dejar escrrito lo que se dijo
            'ACC_INC' => [$siguiente === 'resuelta' ? 'required' : 'nullable', 'string', 'max:2000'],
        ], [], ['ACC_INC' => 'acción tomada']);

        $cambios = ['ESD_INC' => $siguiente];
        if (! empty($datos['ACC_INC'])) {
            $cambios['ACC_INC'] = $datos['ACC_INC'];
        }
        if (in_array($siguiente, ['resuelta', 'cerrada'], true) && ! $incidencia->FRS_INC) {
            $cambios['FRS_INC'] = now();   // fecha de resolucion
        }

        $incidencia->update($cambios);

        return redirect()->route('incidencias.show', $incidencia)
            ->with('exito', 'Incidencia ahora: '.Incidencia::ESTADOS[$siguiente].'.');
    }

    private function reglas(): array
    {
        return [
            'TIP_INC' => ['required', Rule::in(array_keys(Incidencia::TIPOS))],
            'COD_ABA' => ['nullable', 'integer', Rule::exists('abastecimiento', 'COD_ABA')],
            'COD_TAN' => ['nullable', 'integer', Rule::exists('tanque_almacenamiento', 'COD_TAN')],
            'DES_INC' => ['required', 'string', 'max:2000'],
        ];
    }

    private function listas(): array
    {
        return [
            'abastecimientos' => Abastecimiento::with('solicitud.estacion')->orderByDesc('COD_ABA')->limit(100)->get()
                ->mapWithKeys(fn ($a) => [$a->COD_ABA => '#'.$a->COD_ABA.' — '.($a->solicitud?->estacion?->NOM_EST ?? '').' ('.Abastecimiento::ESTADOS[$a->ESD_ABA].')'])->all(),
            'tanques' => TanqueAlmacenamiento::orderBy('NOM_TAN')->pluck('NOM_TAN', 'COD_TAN')->all(),
        ];
    }
}