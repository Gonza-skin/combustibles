<?php

namespace App\Http\Controllers;

use App\Models\PlantaDeposito;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** la planata que es el deposito donde se guarda el combustible que lo ven el admin y el encargado del lugar */
class PlantaController extends Controller
{
    public function index(): View
    {
        $plantas = PlantaDeposito::withCount('tanques')->orderBy('NOM_PLA')->get();

        return view('abastecimiento.plantas.index', compact('plantas'));
    }

    public function create(): View
    {
        return view('abastecimiento.plantas.create', ['planta' => new PlantaDeposito(['ACT_PLA' => true])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate($this->reglas());
        $datos['ACT_PLA'] = $request->boolean('ACT_PLA');

        PlantaDeposito::create($datos);

        return redirect()->route('abast.plantas.index')->with('exito', 'Planta registrada correctamente.');
    }

    public function edit(PlantaDeposito $planta): View
    {
        return view('abastecimiento.plantas.edit', compact('planta'));
    }

    public function update(Request $request, PlantaDeposito $planta): RedirectResponse
    {
        $datos = $request->validate($this->reglas());
        $datos['ACT_PLA'] = $request->boolean('ACT_PLA');

        $planta->update($datos);

        return redirect()->route('abast.plantas.index')->with('exito', 'Planta actualizada correctamente.');
    }

    public function destroy(PlantaDeposito $planta): RedirectResponse
    {
        if ($planta->tanques()->exists()) {
            return back()->with('error', 'No se puede eliminar "'.$planta->NOM_PLA.'": tiene tanques. Desactívala en su lugar.');
        }

        $planta->delete();

        return redirect()->route('abast.plantas.index')->with('exito', 'Planta eliminada.');
    }

    private function reglas(): array
    {
        return [
            'NOM_PLA' => ['required', 'string', 'max:120'],
            'UBI_PLA' => ['nullable', 'string', 'max:200'],
        ];
    }
}