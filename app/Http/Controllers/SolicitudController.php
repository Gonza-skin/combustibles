<?php

namespace App\Http\Controllers;

use App\Models\SolicitudAbastecimiento;
use App\Models\TipoCombustible;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SolicitudController extends Controller
{
    public function index(Request $request): View
    {
        $usuario = $request->user();
        $soloSuEstacion = $usuario->tieneRol('encargado_estacion');

        $filtros = $request->validate([
            'estado' => ['nullable', Rule::in(array_keys(SolicitudAbastecimiento::ESTADOS))],
        ]);

        $solicitudes = SolicitudAbastecimiento::with(['estacion', 'combustible', 'solicitante', 'abastecimiento'])
            ->when($soloSuEstacion, fn ($q) => $usuario->COD_EST
                ? $q->where('COD_EST', $usuario->COD_EST)
                : $q->whereRaw('1 = 0'))
            ->when($filtros['estado'] ?? null, fn ($q, $v) => $q->where('ESD_SOL', $v))
            ->orderByDesc('COD_SOL')
            ->paginate(15)
            ->withQueryString();

        return view('abastecimiento.solicitudes.index', [
            'solicitudes' => $solicitudes,
            'estado' => $filtros['estado'] ?? null,
            'soloSuEstacion' => $soloSuEstacion,
        ]);
    }

    public function show(Request $request, SolicitudAbastecimiento $solicitud): View
    {
        $usuario = $request->user();

        // el encargado solo puede ver solicitudes de su estacion
        abort_if(
            $usuario->tieneRol('encargado_estacion') && (int) $solicitud->COD_EST !== (int) $usuario->COD_EST,
            403,
            'Esa solicitud no es de tu estación.'
        );

        $solicitud->load(['estacion', 'combustible', 'solicitante', 'abastecimiento.cisterna']);

        return view('abastecimiento.solicitudes.show', compact('solicitud'));
    }

    //solo encargado de estacion

    public function create(Request $request): View
    {
        $this->estacionDe($request);

        return view('abastecimiento.solicitudes.create', $this->datosFormulario($request, new SolicitudAbastecimiento));
    }

    public function store(Request $request): RedirectResponse
    {
        $estacion = $this->estacionDe($request);
        $datos = $this->validar($request, $estacion);

        SolicitudAbastecimiento::create($datos + [
            'COD_EST' => $estacion,
            'COD_USU' => $request->user()->COD_USU,
            'ESD_SOL' => 'pendiente',
        ]);

        return redirect()->route('abast.solicitudes.index')->with('exito', 'Solicitud enviada. El subadministrador la atenderá.');
    }

    public function edit(Request $request, SolicitudAbastecimiento $solicitud): View|RedirectResponse
    {
        $this->propia($request, $solicitud);

        if (! $this->modificable($solicitud)) {
            return redirect()->route('abast.solicitudes.index')->with('error', 'Esa solicitud ya no se puede modificar.');
        }

        return view('abastecimiento.solicitudes.edit', $this->datosFormulario($request, $solicitud));
    }

    public function update(Request $request, SolicitudAbastecimiento $solicitud): RedirectResponse
    {
        $this->propia($request, $solicitud);

        if (! $this->modificable($solicitud)) {
            return redirect()->route('abast.solicitudes.index')->with('error', 'Esa solicitud ya no se puede modificar.');
        }

        $solicitud->update($this->validar($request, (int) $solicitud->COD_EST));

        return redirect()->route('abast.solicitudes.index')->with('exito', 'Solicitud actualizada.');
    }

    public function cancelar(Request $request, SolicitudAbastecimiento $solicitud): RedirectResponse
    {
        $this->propia($request, $solicitud);

        if (! $this->modificable($solicitud)) {
            return back()->with('error', 'Esa solicitud ya no se puede cancelar.');
        }

        $solicitud->update(['ESD_SOL' => 'cancelada']);

        return back()->with('exito', 'Solicitud cancelada.');
    }

    /** sin estacion no puede pedir combustible. */
    private function estacionDe(Request $request): int
    {
        $estacion = $request->user()->COD_EST;
        abort_if($estacion === null, 403, 'Tu usuario no tiene una estación asignada. Pide al administrador que te la asigne.');

        return (int) $estacion;
    }

    private function propia(Request $request, SolicitudAbastecimiento $solicitud): void
    {
        abort_unless((int) $solicitud->COD_EST === $this->estacionDe($request), 403, 'Esa solicitud no es de tu estación.');
    }

    /** solo se puede cambiar mientras este pendiente y nadie la haya convertido en envio. */
    private function modificable(SolicitudAbastecimiento $solicitud): bool
    {
        return $solicitud->ESD_SOL === 'pendiente' && ! $solicitud->abastecimiento()->exists();
    }

    private function validar(Request $request, int $estacion): array
    {
        $datos = $request->validate([
            'COD_COM' => ['required', 'integer', Rule::exists('tipo_combustible', 'COD_COM')],
            'CAN_SOL' => ['required', 'numeric', 'gt:0', 'max:99999999.99'],
        ], [], ['CAN_SOL' => 'cantidad']);

        // no se puede pedir mas de lo que cabe en la estacion
        $max = (float) DB::table('estacion')->where('COD_EST', $estacion)->value('MAX_EST');
        $actual = (float) DB::table('existencia_estacion')->where('COD_EST', $estacion)->where('COD_COM', $datos['COD_COM'])->value('CAN_EXI');

        if ($max > 0 && $actual + (float) $datos['CAN_SOL'] > $max) {
            $cabe = max(0, $max - $actual);
            throw \Illuminate\Validation\ValidationException::withMessages([
                'CAN_SOL' => 'Supera la capacidad de la estación. Como máximo puedes pedir '.number_format($cabe, 0, ',', '.').' L.',
            ]);
        }

        return $datos;
    }

    private function datosFormulario(Request $request, SolicitudAbastecimiento $solicitud): array
    {
        $estacion = $this->estacionDe($request);

        return [
            'solicitud' => $solicitud,
            'combustibles' => TipoCombustible::orderBy('DES_COM')->pluck('DES_COM', 'COD_COM')->all(),
            'existencias' => DB::table('existencia_estacion as x')
                ->join('tipo_combustible as c', 'c.COD_COM', '=', 'x.COD_COM')
                ->where('x.COD_EST', $estacion)->pluck('x.CAN_EXI', 'c.DES_COM')->all(),
            'maximo' => (float) DB::table('estacion')->where('COD_EST', $estacion)->value('MAX_EST'),
        ];
    }
}