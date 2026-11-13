<?php

namespace App\Http\Controllers;

use App\Models\PlantaDeposito;
use App\Models\TanqueAlmacenamiento;
use App\Models\TipoCombustible;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** Tanques de almacenamiento de cada planta: administrador y administrador de depósito. */
class TanqueController extends Controller
{
    public function index(Request $request): View
    {
        $planta = $request->query('planta');
        $combustible = $request->query('combustible');

        $tanques = TanqueAlmacenamiento::with(['planta', 'combustible'])
            ->when($planta, fn ($q, $v) => $q->where('COD_PLA', (int) $v))
            ->when($combustible, fn ($q, $v) => $q->where('COD_COM', (int) $v))
            ->orderBy('COD_PLA')
            ->orderBy('NOM_TAN')
            ->paginate(9)
            ->withQueryString();

        return view('abastecimiento.tanques.index', [
            'tanques' => $tanques,
            'plantas' => PlantaDeposito::orderBy('NOM_PLA')->pluck('NOM_PLA', 'COD_PLA')->all(),
            'combustibles' => TipoCombustible::orderBy('DES_COM')->pluck('DES_COM', 'COD_COM')->all(),
            'planta' => $planta,
            'combustible' => $combustible,
        ]);
    }

    public function create(): View
    {
        return view('abastecimiento.tanques.create', $this->datosFormulario(new TanqueAlmacenamiento(['ESD_TAN' => 'operativo', 'NIV_TAN' => 0])));
    }

    public function store(Request $request): RedirectResponse
    {
        TanqueAlmacenamiento::create($request->validate($this->reglas()));

        return redirect()->route('abast.tanques.index')->with('exito', 'Tanque registrado correctamente.');
    }

    public function edit(TanqueAlmacenamiento $tanque): View
    {
        return view('abastecimiento.tanques.edit', $this->datosFormulario($tanque));
    }

    public function update(Request $request, TanqueAlmacenamiento $tanque): RedirectResponse
    {
        $tanque->update($request->validate($this->reglas()));

        return redirect()->route('abast.tanques.index')->with('exito', 'Tanque actualizado correctamente.');
    }

    public function destroy(TanqueAlmacenamiento $tanque): RedirectResponse
    {
        try {
            $tanque->delete();
        } catch (QueryException $e) {
            return back()->with('error', 'No se puede eliminar "'.$tanque->NOM_TAN.'": otros registros lo usan (incidencias, alertas). Ponlo en estado «Inactivo».');
        }

        return redirect()->route('abast.tanques.index')->with('exito', 'Tanque eliminado.');
    }

    private function reglas(): array
    {
        return [
            'COD_PLA' => ['required', 'integer', Rule::exists('planta_deposito', 'COD_PLA')],
            'COD_COM' => ['required', 'integer', Rule::exists('tipo_combustible', 'COD_COM')],
            'NOM_TAN' => ['required', 'string', 'max:60'],
            'CAP_TAN' => ['required', 'numeric', 'gt:0', 'max:99999999.99'],
            'NIV_TAN' => ['required', 'numeric', 'min:0', 'lte:CAP_TAN'],
            'ESD_TAN' => ['required', Rule::in(array_keys(TanqueAlmacenamiento::ESTADOS))],
        ];
    }

    private function datosFormulario(TanqueAlmacenamiento $tanque): array
    {
        return [
            'tanque' => $tanque,
            'plantas' => PlantaDeposito::orderBy('NOM_PLA')->pluck('NOM_PLA', 'COD_PLA')->all(),
            'combustibles' => TipoCombustible::orderBy('DES_COM')->pluck('DES_COM', 'COD_COM')->all(),
        ];
    }
}