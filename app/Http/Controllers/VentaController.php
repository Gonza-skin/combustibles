<?php

namespace App\Http\Controllers;

use App\Models\Automovil;
use App\Models\TipoCombustible;
use App\Models\VentaCombustible;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Ventas de combustible de la estación del encargado.
 *  - El precio se toma automáticamente del precio vigente del combustible.
 *  - La base de datos (trigger) descuenta la existencia y escribe el movimiento de salida;
 *    por eso aquí NO se toca la existencia.
 *  - Una venta no se edita ni se elimina (es un registro contable).
 */
class VentaController extends Controller
{
    public function index(Request $request): View
    {
        $estacion = $this->estacionDe($request);

        $filtros = $request->validate(['fecha' => ['nullable', 'date']]);
        $fecha = Carbon::parse($filtros['fecha'] ?? today());

        $base = VentaCombustible::where('COD_EST', $estacion)->whereDate('FEC_VEN', $fecha);

        $ventas = (clone $base)->with(['automovil', 'combustible', 'vendedor'])
            ->orderByDesc('COD_VEN')->paginate(15)->withQueryString();

        // Resumen del día por combustible
        $resumen = (clone $base)->with('combustible')->get()
            ->groupBy('COD_COM')
            ->map(fn ($g) => (object) [
                'combustible' => $g->first()->combustible?->DES_COM,
                'litros' => $g->sum(fn ($v) => (float) $v->CAN_VEN),
                'total' => $g->sum(fn ($v) => $v->total()),
            ])->values();

        return view('ventas.index', compact('ventas', 'resumen', 'fecha'));
    }

    public function create(Request $request): View
    {
        $estacion = $this->estacionDe($request);

        return view('ventas.create', [
            'combustibles' => TipoCombustible::orderBy('DES_COM')->get(),
            'existencias' => DB::table('existencia_estacion')->where('COD_EST', $estacion)->pluck('CAN_EXI', 'COD_COM')->all(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $estacion = $this->estacionDe($request);

        $datos = $request->validate([
            'PLA_VEH' => ['required', 'string', 'max:15'],
            'COD_COM' => ['required', 'integer', Rule::exists('tipo_combustible', 'COD_COM')],
            'CAN_VEN' => ['required', 'numeric', 'gt:0', 'max:9999.99'],
        ], [], ['PLA_VEH' => 'placa', 'CAN_VEN' => 'litros']);

        $placa = strtoupper(trim($datos['PLA_VEH']));
        $auto = Automovil::where('PLA_VEH', $placa)->first();
        if (! $auto) {
            return back()->withInput()->withErrors(['PLA_VEH' => 'La placa '.$placa.' no está registrada. Regístrala primero en Automóviles.']);
        }

        $combustible = TipoCombustible::findOrFail($datos['COD_COM']);
        if ((float) $combustible->PRE_COM <= 0) {
            return back()->withInput()->withErrors(['COD_COM' => 'Ese combustible todavía no tiene precio. Avisa al administrador.']);
        }

        $litros = round((float) $datos['CAN_VEN'], 2);

        try {
            $venta = DB::transaction(function () use ($estacion, $auto, $combustible, $litros, $request) {
                $stock = DB::table('existencia_estacion')
                    ->where('COD_EST', $estacion)->where('COD_COM', $combustible->COD_COM)
                    ->lockForUpdate()->value('CAN_EXI');

                if ($stock === null || (float) $stock < $litros) {
                    throw new \RuntimeException('No hay suficiente '.$combustible->DES_COM.': quedan '.number_format((float) $stock, 2, ',', '.').' L.');
                }

                return VentaCombustible::create([
                    'COD_EST' => $estacion,
                    'COD_VEH' => $auto->COD_VEH,
                    'COD_COM' => $combustible->COD_COM,
                    'CAN_VEN' => $litros,
                    'PRE_VEN' => $combustible->PRE_COM,
                    'COD_USU' => $request->user()->COD_USU,
                ]);
            });
        } catch (\RuntimeException $e) {
            return back()->withInput()->withErrors(['CAN_VEN' => $e->getMessage()]);
        }

        return redirect()->route('ventas.index')
            ->with('exito', 'Venta registrada: '.number_format($venta->CAN_VEN, 2, ',', '.').' L de '.$combustible->DES_COM
                .' a '.$auto->PLA_VEH.' — Bs '.number_format($venta->total(), 2, ',', '.'));
    }

    /** Estación del encargado; si no tiene una asignada, no puede vender. */
    private function estacionDe(Request $request): int
    {
        $estacion = $request->user()->COD_EST;
        abort_if($estacion === null, 403, 'Tu usuario no tiene una estación asignada. Pide al administrador que te la asigne.');

        return (int) $estacion;
    }
}