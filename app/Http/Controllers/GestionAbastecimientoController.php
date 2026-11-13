<?php

namespace App\Http\Controllers;

use App\Models\Abastecimiento;
use App\Models\Cisterna;
use App\Models\HistorialEstadoAbastecimiento;
use App\Models\SolicitudAbastecimiento;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use App\Models\TanqueAlmacenamiento;

class GestionAbastecimientoController extends Controller
{
    public function index(Request $request): View
    {
        $filtros = $request->validate([
            'estado' => ['nullable', Rule::in(array_keys(Abastecimiento::ESTADOS))],
        ]);

        // las solcitudes se hacen y se tiene que esperar a que se convierta en un abastecimiento 
        $pendientes = SolicitudAbastecimiento::with(['estacion', 'combustible'])
            ->where('ESD_SOL', 'pendiente')
            ->whereDoesntHave('abastecimiento')
            ->orderBy('COD_SOL')
            ->get();

        $abastecimientos = Abastecimiento::with(['solicitud.estacion', 'combustible', 'cisterna'])
            ->when($filtros['estado'] ?? null, fn ($q, $v) => $q->where('ESD_ABA', $v))
            ->orderByDesc('COD_ABA')
            ->paginate(10)
            ->withQueryString();

        return view('abastecimiento.gestion.index', [
            'pendientes' => $pendientes,
            'abastecimientos' => $abastecimientos,
            'estado' => $filtros['estado'] ?? null,
        ]);
    }

    public function show(Abastecimiento $abastecimiento): View
    {
        $abastecimiento->load(['solicitud.estacion', 'solicitud.solicitante', 'combustible', 'cisterna.conductor']);

        //  a la cisterna que se puede dar la solicitud tiene que estar libre con el conducto activo 
        // y con la capacidad necesaria
        $cisternas = Cisterna::with('conductor')
            ->where('COD_COM', $abastecimiento->COD_COM)
            ->where('ESD_CIS', 'disponible')
            ->where('CAP_CIS', '>=', $abastecimiento->CAN_ABA)
            ->whereHas('conductor', fn ($c) => $c->where('ACT_CON', 1))
            ->orderBy('PLC_CIS')
            ->get();

        $siguientes = Abastecimiento::FLUJO[$abastecimiento->ESD_ABA] ?? [];
        // para  estra en la parte de asginados se tiene que elegir un cisterna
        $siguientes = array_values(array_diff($siguientes, ['asignado']));

        $historial = HistorialEstadoAbastecimiento::with('usuario')
            ->where('COD_ABA', $abastecimiento->COD_ABA)
            ->orderBy('COD_HIS')
            ->get();

        return view('abastecimiento.gestion.show', compact('abastecimiento', 'cisternas', 'siguientes', 'historial'));
    }

    /** hace que la solicitud pendiente se haga abastecimiento (TODAVIA ESTA POR VERSE)*/
    public function crear(Request $request, SolicitudAbastecimiento $solicitud): RedirectResponse
    {
        try {
            $abastecimiento = DB::transaction(function () use ($request, $solicitud) {
                $sol = SolicitudAbastecimiento::lockForUpdate()->findOrFail($solicitud->COD_SOL);

                if ($sol->ESD_SOL !== 'pendiente' || Abastecimiento::where('COD_SOL', $sol->COD_SOL)->exists()) {
                    throw new \RuntimeException('Esa solicitud ya fue atendida o cancelada.');
                }

                $aba = Abastecimiento::create([
                    'COD_SOL' => $sol->COD_SOL,
                    'COD_COM' => $sol->COD_COM,
                    'CAN_ABA' => $sol->CAN_SOL,
                    'ESD_ABA' => 'pendiente',
                    'COD_USU' => $request->user()->COD_USU,
                ]);

                $sol->update(['ESD_SOL' => 'atendida']);

                $this->registrar($aba, 'pendiente', $request, 'Abastecimiento creado desde la solicitud #'.$sol->COD_SOL);

                return $aba;
            });
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('abast.gestion.show', $abastecimiento)->with('exito', 'Abastecimiento creado. Ahora asígnale una cisterna.');
    }

    /** ASIGNA UNA CISTERNA DE PENDIENTE A ASIGNADOA*/
    public function asignar(Request $request, Abastecimiento $abastecimiento): RedirectResponse
    {
        $datos = $request->validate([
            'COD_CIS' => ['required', 'integer', Rule::exists('cisterna', 'COD_CIS')],
            'OBS_HIS' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            DB::transaction(function () use ($request, $abastecimiento, $datos) {
                $aba = Abastecimiento::lockForUpdate()->findOrFail($abastecimiento->COD_ABA);
                $cis = Cisterna::with('conductor')->lockForUpdate()->findOrFail($datos['COD_CIS']);

                if ($aba->ESD_ABA !== 'pendiente') {
                    throw new \RuntimeException('Solo se puede asignar cisterna a un abastecimiento pendiente.');
                }
                if ((int) $cis->COD_COM !== (int) $aba->COD_COM) {
                    throw new \RuntimeException('La cisterna transporta otro tipo de combustible.');
                }
                if ($cis->ESD_CIS !== 'disponible') {
                    throw new \RuntimeException('La cisterna no está disponible.');
                }
                if ((float) $cis->CAP_CIS < (float) $aba->CAN_ABA) {
                    throw new \RuntimeException('La cisterna no tiene capacidad suficiente para esa cantidad.');
                }
                if (! $cis->conductor || ! $cis->conductor->ACT_CON) {
                    throw new \RuntimeException('La cisterna no tiene un conductor activo asignado.');
                }

                $cis->update(['ESD_CIS' => 'asignada']);
                $aba->update(['COD_CIS' => $cis->COD_CIS, 'ESD_ABA' => 'asignado']);

                $this->registrar($aba, 'asignado', $request, $datos['OBS_HIS'] ?? 'Cisterna asignada: '.$cis->PLC_CIS);
            });
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('abast.gestion.show', $abastecimiento)->with('exito', 'Cisterna asignada.');
    }

    /** Cambia el estado A 'en_transito', 'entregado', 'cerrado', 'cancelado' */
    public function estado(Request $request, Abastecimiento $abastecimiento): RedirectResponse
    {
        $datos = $request->validate([
            'ESD_ABA' => ['required', Rule::in(['en_transito', 'entregado', 'cerrado', 'cancelado'])],
            'OBS_HIS' => ['nullable', 'string', 'max:255'],
        ]);
        $nuevo = $datos['ESD_ABA'];

        try {
            DB::transaction(function () use ($request, $abastecimiento, $datos, $nuevo) {
                $aba = Abastecimiento::lockForUpdate()->findOrFail($abastecimiento->COD_ABA);

                if (! in_array($nuevo, Abastecimiento::FLUJO[$aba->ESD_ABA] ?? [], true)) {
                    throw new \RuntimeException('No se puede pasar de «'.Abastecimiento::ESTADOS[$aba->ESD_ABA].'» a «'.Abastecimiento::ESTADOS[$nuevo].'».');
                }

                $cambios = ['ESD_ABA' => $nuevo];

                if ($nuevo === 'en_transito') {
                    $cambios['HSA_ABA'] = now();           // hora de salida
                    $aba->cisterna?->update(['ESD_CIS' => 'en_transito']);
                }
                if ($nuevo === 'entregado') {
                    $cambios['HRL_ABA'] = now();           // hora real de llegada
                    $this->sumarAExistencia($aba, $request);
                }
                // la cisterna queda libre otra vez al entregar o cancelar
                if (in_array($nuevo, ['entregado', 'cancelado'], true)) {
                    $aba->cisterna?->update(['ESD_CIS' => 'disponible']);
                }

                $aba->update($cambios);
                // si ya se haia cargado de un tanque y se cancela, el combustible vuelve al tanque
                $nota = $nuevo === 'cancelado' ? $this->devolverATanque($aba) : null;

                // Si se cancela el envio la solicitud tambien queda cancelada
                if ($nuevo === 'cancelado') {
                    SolicitudAbastecimiento::where('COD_SOL', $aba->COD_SOL)->update(['ESD_SOL' => 'cancelada']);
                }

                $this->registrar($aba, $nuevo, $request, mb_substr(trim(($datos['OBS_HIS'] ?? '').' '.$nota), 0, 255) ?: null);
            });
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('abast.gestion.show', $abastecimiento)->with('exito', 'Estado actualizado.');
    }

    /** al entregar el combustible entra al inventario de la estacion */
    private function sumarAExistencia(Abastecimiento $aba, Request $request): void
    {
        $estacion = SolicitudAbastecimiento::where('COD_SOL', $aba->COD_SOL)->value('COD_EST');

        $existe = DB::table('existencia_estacion')
            ->where('COD_EST', $estacion)->where('COD_COM', $aba->COD_COM)->lockForUpdate()->exists();

        if ($existe) {
            DB::table('existencia_estacion')
                ->where('COD_EST', $estacion)->where('COD_COM', $aba->COD_COM)
                ->update(['CAN_EXI' => DB::raw('CAN_EXI + '.(float) $aba->CAN_ABA)]);
        } else {
            DB::table('existencia_estacion')->insert([
                'COD_EST' => $estacion, 'COD_COM' => $aba->COD_COM, 'CAN_EXI' => $aba->CAN_ABA,
            ]);
        }

        DB::table('movimiento_existencia')->insert([
            'COD_EST' => $estacion,
            'COD_COM' => $aba->COD_COM,
            'TIP_MOV' => 'entrada',
            'CAN_MOV' => $aba->CAN_ABA,
            'COD_USU' => $request->user()->COD_USU,
            'REF_MOV' => 'Abastecimiento #'.$aba->COD_ABA,
        ]);
    }
    /** se cargan los litros pedidos y retorna una nota para el historial. */
    private function devolverATanque(Abastecimiento $aba): ?string
    {
        if ($aba->COD_TAN === null || $aba->CCA_ABA === null) {
            return null;
        }

        $tanque = TanqueAlmacenamiento::lockForUpdate()->find($aba->COD_TAN);
        if (! $tanque) {
            return null;
        }

        $nuevo = min((float) $tanque->CAP_TAN, round((float) $tanque->NIV_TAN + (float) $aba->CCA_ABA, 2));
        $cambios = ['NIV_TAN' => $nuevo];
        if ($tanque->ESD_TAN === 'critico' && $nuevo >= (float) $tanque->CAP_TAN * 0.15) {
            $cambios['ESD_TAN'] = 'operativo';
        }
        $tanque->update($cambios);

        return 'Se devolvieron '.number_format((float) $aba->CCA_ABA, 2, ',', '.').' L al tanque '.$tanque->NOM_TAN.'.';
    }

    private function registrar(Abastecimiento $aba, string $estado, Request $request, ?string $obs): void
    {
        HistorialEstadoAbastecimiento::create([
            'COD_ABA' => $aba->COD_ABA,
            'ESD_HIS' => $estado,
            'COD_USU' => $request->user()->COD_USU,
            'OBS_HIS' => $obs,
        ]);
    }
}