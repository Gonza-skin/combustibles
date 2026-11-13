<?php

namespace App\Http\Controllers;

use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Usuarios: solo el administrador.
 * Los usuarios NO se borran, se desactivan (ACT_USU = 0), porque otras tablas
 * (abastecimientos, reportes, auditoría...) guardan quién hizo cada cosa.
 */
class UsuarioController extends Controller implements HasMiddleware
{
    /** Cada acción pide su permiso (Spatie). El administrador los tiene todos. */
    public static function middleware(): array
    {
        return [
            new Middleware('permission:usuarios.ver', only: ['index']),
            new Middleware('permission:usuarios.crear', only: ['create', 'store']),
            new Middleware('permission:usuarios.editar', only: ['edit', 'update']),
            new Middleware('permission:usuarios.desactivar', only: ['destroy']),
        ];
    }

    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q'));

        $usuarios = Usuario::with('rol')
            ->when($q !== '', fn ($consulta) => $consulta->where(
                fn ($w) => $w->where('NOM_USU', 'like', "%{$q}%")->orWhere('EML_USU', 'like', "%{$q}%")
            ))
            ->orderBy('NOM_USU')
            ->paginate(10)
            ->withQueryString();

        return view('usuarios.index', compact('usuarios', 'q'));
    }

    public function create(): View
    {
        return view('usuarios.create', $this->datosFormulario(new Usuario(['ACT_USU' => true])));
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate($this->reglas());

        Usuario::create([
            'NOM_USU' => $datos['NOM_USU'],
            'EML_USU' => $datos['EML_USU'],
            'PAS_USU' => Hash::make($datos['PAS_USU']),
            'COD_ROL' => $datos['COD_ROL'],
            'COD_EST' => $datos['COD_EST'] ?? null,
            'ACT_USU' => $request->boolean('ACT_USU'),
        ]);

        return redirect()->route('usuarios.index')->with('exito', 'Usuario creado correctamente.');
    }

    public function edit(Usuario $usuario): View
    {
        return view('usuarios.edit', $this->datosFormulario($usuario));
    }

    public function update(Request $request, Usuario $usuario): RedirectResponse
    {
        // Nadie puede quitarse a sí mismo el rol ni desactivarse: se quedaría sin acceso al sistema
        if ($usuario->is($request->user())) {
            $request->merge(['COD_ROL' => $usuario->COD_ROL, 'ACT_USU' => 1]);
        }

        $datos = $request->validate($this->reglas($usuario));

        $usuario->fill([
            'NOM_USU' => $datos['NOM_USU'],
            'EML_USU' => $datos['EML_USU'],
            'COD_ROL' => $datos['COD_ROL'],
            'COD_EST' => $datos['COD_EST'] ?? null,
            'ACT_USU' => $request->boolean('ACT_USU'),
        ]);

        // La contraseña solo cambia si escribieron una nueva
        if (! empty($datos['PAS_USU'])) {
            $usuario->PAS_USU = Hash::make($datos['PAS_USU']);
        }

        $usuario->save();

        return redirect()->route('usuarios.index')->with('exito', 'Usuario actualizado correctamente.');
    }

    /** Desactiva al usuario (no lo borra) */
    public function destroy(Request $request, Usuario $usuario): RedirectResponse
    {
        if ($usuario->is($request->user())) {
            return back()->with('error', 'No puedes desactivar tu propio usuario.');
        }

        $usuario->update(['ACT_USU' => false]);

        return redirect()->route('usuarios.index')->with('exito', 'Usuario desactivado. Ya no podrá iniciar sesión.');
    }

    private function reglas(?Usuario $usuario = null): array
    {
        return [
            'NOM_USU' => ['required', 'string', 'max:150'],
            'EML_USU' => [
                'required', 'email', 'max:150',
                Rule::unique('usuario', 'EML_USU')->ignore($usuario?->COD_USU, 'COD_USU'),
            ],
            // Al crear es obligatoria; al editar, solo si quieren cambiarla
            'PAS_USU' => [$usuario ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
            'COD_ROL' => ['required', 'integer', Rule::exists('rol', 'COD_ROL')],
            'COD_EST' => ['nullable', 'integer', Rule::exists('estacion', 'COD_EST')],
            'ACT_USU' => ['boolean'],
        ];
    }

    private function datosFormulario(Usuario $usuario): array
    {
        return [
            'usuario' => $usuario,
            'roles' => Rol::orderBy('DES_ROL')->get()->pluck('nombre', 'COD_ROL')->all(),
            'estaciones' => DB::table('estacion')->orderBy('NOM_EST')->pluck('NOM_EST', 'COD_EST')->all(),
            'esPropio' => $usuario->exists && $usuario->is(auth()->user()),
        ];
    }
}