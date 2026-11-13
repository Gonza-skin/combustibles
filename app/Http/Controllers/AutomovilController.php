<?php

namespace App\Http\Controllers;

use App\Models\Automovil;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * AQUI EL REGISTRO DE AUTOMOVILES (SOLO PARA PRUEBA) LA RAZON ESQ NO SE PUEDE CREAR EL REGISTRO DE UN AUTO
 * EL ADMIN PUEDE EDITAR CREAR Y ELIMINAR
 * EL ENCARGADO DE LA ESTACION BUSCA LA PLACA PARA PODER VENDER
 */
class AutomovilController extends Controller
{
    public function index(Request $request): View
    {
        $esAdmin = $request->user()->tieneRol('administrador');
        $q = trim((string) $request->query('q'));

        $automoviles = Automovil::query()
            ->when($q !== '', fn ($c) => $c->where(
                fn ($w) => $w->where('PLA_VEH', 'like', "%{$q}%")
                    ->orWhere('NOM_PROP', 'like', "%{$q}%")
                    ->orWhere('MOD_VEH', 'like', "%{$q}%")
            ))
            ->orderBy('PLA_VEH')
            ->paginate(15)
            ->withQueryString();

        return view('automoviles.index', compact('automoviles', 'q', 'esAdmin'));
    }

    public function create(): View
    {
        return view('automoviles.create', ['automovil' => new Automovil()]);
    }

    public function store(Request $request): RedirectResponse
    {
        Automovil::create($request->validate($this->reglas(), $this->mensajes()));
        return redirect()->route('automoviles.index')->with('exito', 'Automóvil registrado correctamente.');
    }

    public function edit(Automovil $automovil): View
    {
        return view('automoviles.edit', compact('automovil'));
    }

    public function update(Request $request, Automovil $automovil): RedirectResponse
    {
        $automovil->update($request->validate($this->reglas($automovil), $this->mensajes()));
        return redirect()->route('automoviles.index')->with('exito', 'Automóvil actualizado correctamente.');
    }

    public function destroy(Automovil $automovil): RedirectResponse
    {
        if (DB::table('venta_combustible')->where('COD_VEH', $automovil->COD_VEH)->exists()) {
            return back()->with('error', 'No se puede eliminar el automóvil '.$automovil->PLA_VEH.': tiene ventas registradas.');
        }

        $automovil->delete();
        return redirect()->route('automoviles.index')->with('exito', 'Automóvil eliminado.');
    }

    private function reglas(?Automovil $automovil = null): array
    {
        return [
            'PLA_VEH' => ['required', 'string', 'max:15', Rule::unique('automovil', 'PLA_VEH')->ignore($automovil?->COD_VEH, 'COD_VEH')],
            'TIP_VEH' => ['required', 'string', 'max:20'],
            'MOD_VEH' => ['required', 'string', 'max:60'],
            'NOM_PROP' => ['nullable', 'string', 'max:150'],
            'TEL_PROP' => ['nullable', 'digits:8'],
        ];
    }

    private function mensajes(): array
    {
        return [
            'PLA_VEH.unique' => 'Ya existe un automóvil con esa placa.',
            'TEL_PROP.digits' => 'El teléfono debe tener exactamente 8 dígitos.',
        ];
    }
}