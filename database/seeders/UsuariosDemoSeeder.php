<?php

namespace Database\Seeders;

use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/* Usuarios de prueba: uno por rol */
class UsuariosDemoSeeder extends Seeder
{
    public function run(): void
    {
        $usuarios = [
            ['rol' => 'administrador',          'nombre' => 'Demo Administrador'],
            ['rol' => 'sub_administrador',      'nombre' => 'Demo Subadministrador'],
            ['rol' => 'administrador_deposito', 'nombre' => 'Demo Administrador de depósito'],
            ['rol' => 'encargado_estacion',     'nombre' => 'Demo Encargado de estación'],
            ['rol' => 'conductor_cisterna',     'nombre' => 'Demo Conductor de cisterna'],
            ['rol' => 'supervisor',             'nombre' => 'Demo Supervisor'],
            ['rol' => 'ciudadano',              'nombre' => 'Demo Ciudadano'],
        ];

        foreach ($usuarios as $u) {
            Usuario::updateOrCreate(
                // correo: <rol>@combustible.test  |  contraseña: 25242322
                ['EML_USU' => $u['rol'] . '@combustible.test'],
                [
                    'NOM_USU' => $u['nombre'],
                    'PAS_USU' => Hash::make('25242322'),
                    'COD_ROL' => Rol::where('DES_ROL', $u['rol'])->value('COD_ROL'),
                    'ACT_USU' => true,
                ]
            );
        }
    }
}