<?php

namespace App\Http\Controllers;

use App\Models\Cisterna;
use App\Models\Conductor;
use App\Models\TipoCombustible;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**  CISTERNAS QUE LLEVAN EL COMBUSTIBLE A LAS ESTACIONES  */
class CisternaController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q'));
        $estado = $request->query('estado');

        $cisternas = Cisterna::with(['combustible', 'conductor'])
            ->when($q !== '', fn ($c) => $c->where('PLC_CIS', 'like', "%{$q}%"))
            ->when(array_key_exists((string) $estado, Cisterna::ESTADOS), fn ($c) => $c->where('ESD_CIS', $estado))
            ->orderBy('PLC_CIS')
            ->paginate(9)
            ->withQueryString();

        return view('cisternas.index', compact('cisternas', 'q', 'estado'));
    }

    public function create(): View
    {
        return view('cisternas.create', $this->datosFormulario(new Cisterna(['ESD_CIS' => 'disponible'])));
    }

    public function store(Request $request): RedirectResponse
    {
        Cisterna::create($request->validate($this->reglas(), $this->mensajes()));

        return redirect()->route('cisternas.index')->with('exito', 'Cisterna registrada correctamente.');
    }

    public function edit(Cisterna $cisterna): View
    {
        return view('cisternas.edit', $this->datosFormulario($cisterna));
    }

    public function update(Request $request, Cisterna $cisterna): RedirectResponse
    {
        $cisterna->update($request->validate($this->reglas($cisterna), $this->mensajes()));

        return redirect()->route('cisternas.index')->with('exito', 'Cisterna actualizada correctamente.');
    }

    public function destroy(Cisterna $cisterna): RedirectResponse
    {
        try {
            $cisterna->delete();
        } catch (QueryException $e) {
            return back()->with('error', 'No se puede eliminar la cisterna '.$cisterna->PLC_CIS.': tiene mantenimientos o abastecimientos registrados.');
        }

        return redirect()->route('cisternas.index')->with('exito', 'Cisterna eliminada.');
    }

    private function reglas(?Cisterna $cisterna = null): array
    {
        return [
            'PLC_CIS' => ['required', 'string', 'max:20', Rule::unique('cisterna', 'PLC_CIS')->ignore($cisterna?->COD_CIS, 'COD_CIS')],
            'CAP_CIS' => ['required', 'numeric', 'gt:0', 'max:99999999.99'],
            'COD_COM' => ['required', 'integer', Rule::exists('tipo_combustible', 'COD_COM')],
            'COD_CON' => ['nullable', 'integer', Rule::exists('conductor', 'COD_CON')],
            'ESD_CIS' => ['required', Rule::in(array_keys(Cisterna::ESTADOS))],
        ];
    }

    private function mensajes(): array
    {
        return ['PLC_CIS.unique' => 'Ya existe una cisterna con esa placa.'];
    }

    private function datosFormulario(Cisterna $cisterna): array
    {
        // Conductores activos 
        $conductores = Conductor::where('ACT_CON', 1)
            ->when($cisterna->COD_CON, fn ($c) => $c->orWhere('COD_CON', $cisterna->COD_CON))
            ->orderBy('NOM_CON')
            ->pluck('NOM_CON', 'COD_CON')
            ->all();

        return [
            'cisterna' => $cisterna,
            'combustibles' => TipoCombustible::orderBy('DES_COM')->pluck('DES_COM', 'COD_COM')->all(),
            'conductores' => $conductores,
        ];
    }
}