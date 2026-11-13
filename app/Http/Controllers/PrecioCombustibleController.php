<?php

namespace App\Http\Controllers;

use App\Models\HistorialPrecio;
use App\Models\TipoCombustible;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PrecioCombustibleController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:precios.ver', only: ['index']),
            new Middleware('permission:precios.editar', only: ['edit', 'update']),
        ];
    }

    public function index(): View
    {
        $combustibles = TipoCombustible::orderBy('COD_COM')->get();

        $historial = HistorialPrecio::with(['combustible', 'usuario'])
            ->orderByDesc('COD_HIS')
            ->limit(15)
            ->get();

        return view('precios-combustible.index', compact('combustibles', 'historial'));
    }

    public function edit(TipoCombustible $precios_combustible): View
    {
        return view('precios-combustible.edit', ['combustible' => $precios_combustible]);
    }

    public function update(Request $request, TipoCombustible $precios_combustible): RedirectResponse
    {
        $datos = $request->validate([
            'PRE_COM' => ['required', 'numeric', 'decimal:0,2', 'gt:0', 'max:9999.99'],
        ]);

        $nuevo = (float) $datos['PRE_COM'];
        $anterior = (float) $precios_combustible->PRE_COM;

        if ($nuevo === $anterior) {
            return redirect()->route('precios-combustible.index')
                ->with('exito', 'El precio es el mismo; no hubo cambios.');
        }

        // guarda el precio junto con el historial de cuanto se guardo 
        DB::transaction(function () use ($precios_combustible, $nuevo, $anterior, $request) {
            $precios_combustible->update(['PRE_COM' => $nuevo]);

            HistorialPrecio::create([
                'COD_COM' => $precios_combustible->COD_COM,
                'PRE_ANT' => $anterior,
                'PRE_NUE' => $nuevo,
                'COD_USU' => $request->user()->COD_USU,
            ]);
        });

        return redirect()->route('precios-combustible.index')
            ->with('exito', 'Precio de '.$precios_combustible->DES_COM.' actualizado.');
    }
}