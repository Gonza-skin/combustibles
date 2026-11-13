<?php

namespace App\Http\Controllers;

use App\Models\Conductor;
use App\Models\Usuario;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * LOS CONDUCTORES DEL CISTERNA QUE EL ADMIN LES DA EL ROL
 */
class ConductorController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q'));

        $conductores = Conductor::with('usuario')
            ->when($q !== '', fn ($c) => $c->where(
                fn ($w) => $w->where('NOM_CON', 'like', "%{$q}%")
                    ->orWhere('CI_CON', 'like', "%{$q}%")
                    ->orWhere('LIC_CON', 'like', "%{$q}%")
            ))
            ->orderBy('NOM_CON')
            ->paginate(10)
            ->withQueryString();

        return view('conductores.index', compact('conductores', 'q'));
    }

    public function create(): View
    {
        return view('conductores.create', $this->datosFormulario(new Conductor(['ACT_CON' => true])));
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate($this->reglas(), $this->mensajes());
        $datos['ACT_CON'] = $request->boolean('ACT_CON');

        Conductor::create($datos);

        return redirect()->route('conductores.index')->with('exito', 'Conductor registrado correctamente.');
    }

    public function edit(Conductor $conductor): View
    {
        return view('conductores.edit', $this->datosFormulario($conductor));
    }

    public function update(Request $request, Conductor $conductor): RedirectResponse
    {
        $datos = $request->validate($this->reglas($conductor), $this->mensajes());
        $datos['ACT_CON'] = $request->boolean('ACT_CON');

        $conductor->update($datos);

        return redirect()->route('conductores.index')->with('exito', 'Conductor actualizado correctamente.');
    }

    public function destroy(Conductor $conductor): RedirectResponse
    {
        try {
            $conductor->delete();
        } catch (QueryException $e) {
            return back()->with('error', 'No se puede eliminar a '.$conductor->NOM_CON.': está asignado a una cisterna. Desasígnalo primero o desactívalo.');
        }

        return redirect()->route('conductores.index')->with('exito', 'Conductor eliminado.');
    }

    private function reglas(?Conductor $conductor = null): array
    {
        return [
            'COD_USU' => ['required', 'integer', Rule::exists('usuario', 'COD_USU'),
                Rule::unique('conductor', 'COD_USU')->ignore($conductor?->COD_CON, 'COD_CON')],
            'NOM_CON' => ['required', 'string', 'max:150'],
            'CI_CON' => ['required', 'string', 'max:20', Rule::unique('conductor', 'CI_CON')->ignore($conductor?->COD_CON, 'COD_CON')],
            'LIC_CON' => ['required', 'string', 'max:20'],
            'CAT_LIC' => ['nullable', 'string', 'max:10'],
            'TEL_CON' => ['nullable', 'digits:8'],
        ];
    }

    private function mensajes(): array
    {
        return [
            'COD_USU.unique' => 'Este usuario ya está vinculado a otro conductor.',
            'CI_CON.unique' => 'Ya existe un conductor con esa cédula.',
            'TEL_CON.digits' => 'El teléfono debe tener exactamente 8 dígitos.',
        ];
    }

    private function datosFormulario(Conductor $conductor): array
    {
        // SE TIENE QUE AÑADIR A UN ROL EL ROL DE CONDUCTOR
        $usuarios = Usuario::whereHas('rol', fn ($r) => $r->where('DES_ROL', 'conductor_cisterna'))
            ->where(fn ($w) => $w->whereNotIn('COD_USU', Conductor::query()->select('COD_USU'))
                ->when($conductor->COD_USU, fn ($x) => $x->orWhere('COD_USU', $conductor->COD_USU)))
            ->orderBy('NOM_USU')
            ->get()
            ->mapWithKeys(fn ($u) => [$u->COD_USU => $u->NOM_USU.' ('.$u->EML_USU.')'])
            ->all();

        return ['conductor' => $conductor, 'usuarios' => $usuarios];
    }
}