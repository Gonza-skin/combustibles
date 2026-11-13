<?php

namespace App\Http\Controllers;

use App\Models\Alerta;
use App\Services\GeneradorAlertas;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**  AQUI LAS ALERTAS DEL SISTEMA  CON LOS ROLES SUPERVISRO ADMIN Y SUB ADMIN */
class AlertaController extends Controller
{
    public function index(Request $request): View
    {
        $filtros = $request->validate([
            'tipo' => ['nullable', Rule::in(array_keys(Alerta::TIPOS))],
            'nivel' => ['nullable', Rule::in(array_keys(Alerta::NIVELES))],
            'leida' => ['nullable', Rule::in(['0', '1'])],
        ]);

        $alertas = Alerta::with('estacion')
            ->when($filtros['tipo'] ?? null, fn ($q, $v) => $q->where('TIP_ALE', $v))
            ->when($filtros['nivel'] ?? null, fn ($q, $v) => $q->where('NIV_ALE', $v))
            ->when(isset($filtros['leida']) && $filtros['leida'] !== '', fn ($q) => $q->where('LEI_ALE', (int) $filtros['leida']))
            ->orderBy('LEI_ALE')
            ->orderByDesc('COD_ALE')
            ->paginate(15)
            ->withQueryString();

        return view('alertas.index', [
            'alertas' => $alertas,
            'filtros' => $filtros,
            'sinLeer' => Alerta::where('LEI_ALE', 0)->count(),
        ]);
    }

    /** BUSCA LAS ALERTAS PARA CREAR (TODAVIA EN PRUEBA) */
    public function generar(GeneradorAlertas $generador): RedirectResponse
    {
        $n = $generador->generar();

        return back()->with('exito', $n > 0 ? "Se crearon $n alertas nuevas." : 'Todo en orden: no hay alertas nuevas.');
    }

    public function leer(Alerta $alerta): RedirectResponse
    {
        $alerta->update(['LEI_ALE' => true]);

        return back()->with('exito', 'Alerta marcada como leída.');
    }

    public function leerTodas(): RedirectResponse
    {
        Alerta::where('LEI_ALE', 0)->update(['LEI_ALE' => 1]);

        return back()->with('exito', 'Todas las alertas quedaron como leídas.');
    }
}