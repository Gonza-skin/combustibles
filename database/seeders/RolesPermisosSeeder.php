<?php

namespace Database\Seeders;

use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Crea en Spatie los roles (los mismos de la tabla rol) y los permisos de Administración,
 * y le copia a cada usuario su rol actual (COD_ROL).
 * Se puede correr varias veces sin duplicar:  php artisan db:seed --class=RolesPermisosSeeder
 */
class RolesPermisosSeeder extends Seeder
{
    /** Permisos del módulo de Administración */
    public const PERMISOS = [
        'usuarios.ver', 'usuarios.crear', 'usuarios.editar', 'usuarios.desactivar',
        'roles.ver',
        'precios.ver', 'precios.editar',
    ];

    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        foreach (self::PERMISOS as $permiso) {
            Permission::findOrCreate($permiso, 'web');
        }

        // Un rol de Spatie por cada fila de la tabla rol
        foreach (Rol::pluck('DES_ROL') as $nombre) {
            Role::findOrCreate($nombre, 'web');
        }

        // El administrador tiene todos los permisos
        Role::findByName('administrador', 'web')->syncPermissions(Permission::all());

        // Cada usuario recibe en Spatie el rol que ya tenía en COD_ROL
        Usuario::with('rol')->get()->each->sincronizarRol();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
