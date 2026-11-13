<?php

namespace App\Http\Controllers;

use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class RegistroController extends Controller
{
    public function create()
    {
        return view('auth.registro');
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'nombre'   => ['required', 'string', 'max:100'],
            'email'    => ['required', 'email', 'max:100', 'unique:usuario,EML_USU'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'nombre.required'    => 'Escribe tu nombre.',
            'email.required'     => 'Escribe tu correo.',
            'email.email'        => 'Ese correo no es válido.',
            'email.unique'       => 'Ya existe una cuenta con ese correo.',
            'password.required'  => 'Escribe una contraseña.',
            'password.min'       => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ]);

        $usuario = Usuario::create([
            'NOM_USU' => $datos['nombre'],
            'EML_USU' => $datos['email'],
            'PAS_USU' => Hash::make($datos['password']),
            'COD_ROL' => Rol::where('DES_ROL', 'ciudadano')->value('COD_ROL'), // siempre ciudadano
            'ACT_USU' => true,
        ]);

        Auth::login($usuario);
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }
}