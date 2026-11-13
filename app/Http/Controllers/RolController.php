<?php

namespace App\Http\Controllers;

use App\Models\Rol;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

/** Catalogo de roles: solo consulta (con los permisos de cada rol, que salen de Spatie) */
class RolController extends Controller
{
    public function index(): View
    {
        $roles = Rol::withCount('usuarios')->orderBy('COD_ROL')->get();

        // nombre del rol => lista de permisos
        $permisos = Role::with('permissions')->get()
            ->mapWithKeys(fn ($r) => [$r->name => $r->permissions->pluck('name')->sort()->values()]);

        return view('roles.index', compact('roles', 'permisos'));
    }
}
