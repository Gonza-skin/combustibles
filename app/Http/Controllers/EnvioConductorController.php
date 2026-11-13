<?php

namespace App\Http\Controllers;

use App\Models\Abastecimiento;
use App\Models\Alerta;
use App\Models\Cisterna;
use App\Models\Conductor;
use App\Models\HistorialEstadoAbastecimiento;
use App\Models\Incidencia;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EnvioConductorController extends Controller
{
    /** Diferencia tolerada entre litros cargados y recibidos es del 1% */
    private const TOLERANCIA = 0.01;

    public function index(Request $request): View
    {
        $conductor = $this->conductor($request);
        $cisterna = $conductor ? Cisterna::with('combustible')->where('COD_CON', $conductor->COD_CON)->first() : null;

        $activos = collect();
        $recientes = collect();
        if ($cisterna) {
            $con = ['solicitud.estacion', 'combustible'];
            $activos = Abastecimiento::with($con)->where('COD_CIS', $cisterna->COD_CIS)
                ->whereIn('ESD_ABA', ['asignado', 'en_transito'])->orderBy('COD_ABA')->get();
            $recientes = Abastecimiento::with($con)->where('COD_CIS', $cisterna->COD_CIS)
                ->whereIn('ESD_ABA', ['entregado', 'cerrado'])->orderByDesc('COD_ABA')->limit(10)->get();
        }

        return view('conductor.index', compact('conductor', 'cisterna', 'activos', 'recientes'));
    }

    public function show(Request $request, Abastecimiento $abastecimiento): View
    {
        $this->propio($request, $abastecimiento);
        $abastecimiento->load(['solicitud.estacion', 'combustible', 'cisterna']);

        return view('conductor.show', compact('abastecimiento'));
    }

    /** cuando llegue a la estacion se regitsran los litros que llegaron */
    public function entregar(Request $request, Abastecimiento $abastecimiento): RedirectResponse
    {
        $this->propio($request, $abastecimiento);

        $datos = $request->validate([
            'CRE_ABA' => ['required', 'numeric', 'gt:0', 'max:99999999.99'],
        ]);
        $recibidos = round((float) $datos['CRE_ABA'], 2);
        $aviso = null;

        try {
            DB::transaction(function () use ($request, $abastecimiento, $recibidos, &$aviso) {
                $aba = Abastecimiento::with('solicitud')->lockForUpdate()->findOrFail($abastecimiento->COD_ABA);
                if ($aba->ESD_ABA !== 'en_transito') {
                    throw new \RuntimeException('Este envío no está en tránsito.');
                }

                $cargados = (float) ($aba->CCA_ABA ?? $aba->CAN_ABA);
                if ($recibidos > $cargados) {
                    throw new \RuntimeException('No puedes haber recibido más de lo cargado ('.number_format($cargados, 2, ',', '.').' L).');
                }

                $aba->update(['CRE_ABA' => $recibidos, 'ESD_ABA' => 'entregado', 'HRL_ABA' => now()]);
                $aba->cisterna?->update(['ESD_CIS' => 'disponible']);

                $this->sumarAExistencia($aba, $recibidos, $request);
                $this->historial($aba, 'entregado', $request, 'Recibidos '.number_format($recibidos, 2, ',', '.').' L');

                // TODA EN PRUEBA si falta combustible se avisa automaticamente
                $diferencia = round($cargados - $recibidos, 2);
                if ($diferencia > $cargados * self::TOLERANCIA) {
                    $texto = 'Se cargaron '.number_format($cargados, 2, ',', '.').' L y se recibieron '.number_format($recibidos, 2, ',', '.').' L (faltan '.number_format($diferencia, 2, ',', '.').' L).';
                    $inc = Incidencia::create([
                        'TIP_INC' => 'diferencia_combustible', 'COD_ABA' => $aba->COD_ABA,
                        'DES_INC' => $texto, 'ESD_INC' => 'detectada', 'COD_USU' => $request->user()->COD_USU,
                    ]);
                    Alerta::create([
                        'TIP_ALE' => 'diferencia_combustible', 'MEN_ALE' => 'Abastecimiento #'.$aba->COD_ABA.': '.$texto,
                        'NIV_ALE' => 'alta', 'COD_EST' => $aba->solicitud->COD_EST, 'COD_ABA' => $aba->COD_ABA,
                        'COD_INC' => $inc->COD_INC, 'LEI_ALE' => false,
                    ]);
                    $aviso = ' Se registró una incidencia por la diferencia de combustible.';
                }
            });
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('conductor.envios.show', $abastecimiento)->with('exito', 'Entrega registrada.'.$aviso);
    }

    /** el conductor puede reportar si algo paso en su viaje al dejar el combustible*/
    public function incidencia(Request $request, Abastecimiento $abastecimiento): RedirectResponse
    {
        $this->propio($request, $abastecimiento);

        $datos = $request->validate([
            'TIP_INC' => ['required', Rule::in(['retraso', 'diferencia_combustible', 'otro'])],
            'DES_INC' => ['required', 'string', 'max:2000'],
        ], [], ['TIP_INC' => 'tipo de problema', 'DES_INC' => 'descripción']);

        DB::transaction(function () use ($request, $abastecimiento, $datos) {
            $inc = Incidencia::create($datos + [
                'COD_ABA' => $abastecimiento->COD_ABA, 'ESD_INC' => 'detectada', 'COD_USU' => $request->user()->COD_USU,
            ]);

            Alerta::create([
                'TIP_ALE' => ['retraso' => 'cisterna_retrasada', 'diferencia_combustible' => 'diferencia_combustible', 'otro' => 'otro'][$datos['TIP_INC']],
                'MEN_ALE' => mb_substr('Abastecimiento #'.$abastecimiento->COD_ABA.' (reportado por el conductor): '.$datos['DES_INC'], 0, 255),
                'NIV_ALE' => 'media', 'COD_ABA' => $abastecimiento->COD_ABA, 'COD_INC' => $inc->COD_INC, 'LEI_ALE' => false,
            ]);
        });

        return redirect()->route('conductor.envios.show', $abastecimiento)->with('exito', 'Problema reportado. El supervisor ya fue avisado.');
    }

    private function conductor(Request $request): ?Conductor
    {
        return Conductor::where('COD_USU', $request->user()->COD_USU)->first();
    }

    /** el envío tiene que ser de la cisterna del conductor que inició sesión (si no, 404) */
    private function propio(Request $request, Abastecimiento $abastecimiento): void
    {
        $conductor = $this->conductor($request);
        $cisterna = $conductor ? Cisterna::where('COD_CON', $conductor->COD_CON)->first() : null;

        abort_unless($cisterna && (int) $abastecimiento->COD_CIS === (int) $cisterna->COD_CIS, 404);
    }

    /** al salir de la planta si el deposito ya registro la carga solo se confirma la salida */
    public function iniciar(Request $request, Abastecimiento $abastecimiento): RedirectResponse
    {
        $this->propio($request, $abastecimiento);
        $capacidad = (float) $abastecimiento->cisterna->CAP_CIS;
        $yaCargado = $abastecimiento->CCA_ABA !== null;

        $datos = $yaCargado ? [] : $request->validate([
            'CCA_ABA' => ['required', 'numeric', 'gt:0', 'max:'.$capacidad],
        ], ['CCA_ABA.max' => 'No puede superar la capacidad de la cisterna ('.number_format($capacidad, 0, ',', '.').' L).']);

        try {
            DB::transaction(function () use ($request, $abastecimiento, $datos) {
                $aba = Abastecimiento::lockForUpdate()->findOrFail($abastecimiento->COD_ABA);
                if ($aba->ESD_ABA !== 'asignado') {
                    throw new \RuntimeException('Este envío no está listo para salir.');
                }

                $cargados = $aba->CCA_ABA ?? $datos['CCA_ABA'];
                $aba->update(['CCA_ABA' => $cargados, 'ESD_ABA' => 'en_transito', 'HSA_ABA' => now()]);
                $aba->cisterna?->update(['ESD_CIS' => 'en_transito']);

                $this->historial($aba, 'en_transito', $request, 'Salida con '.number_format((float) $cargados, 2, ',', '.').' L cargados');
            });
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('conductor.envios.show', $abastecimiento)->with('exito', 'Viaje iniciado. ¡Buen camino!');
    }

    private function sumarAExistencia(Abastecimiento $aba, float $litros, Request $request): void
    {
        $estacion = $aba->solicitud->COD_EST;
        $filtro = ['COD_EST' => $estacion, 'COD_COM' => $aba->COD_COM];

        if (DB::table('existencia_estacion')->where($filtro)->lockForUpdate()->exists()) {
            DB::table('existencia_estacion')->where($filtro)->update(['CAN_EXI' => DB::raw('CAN_EXI + '.$litros)]);
        } else {
            DB::table('existencia_estacion')->insert($filtro + ['CAN_EXI' => $litros]);
        }

        DB::table('movimiento_existencia')->insert($filtro + [
            'TIP_MOV' => 'entrada', 'CAN_MOV' => $litros,
            'COD_USU' => $request->user()->COD_USU, 'REF_MOV' => 'Abastecimiento #'.$aba->COD_ABA,
        ]);
    }

    private function historial(Abastecimiento $aba, string $estado, Request $request, ?string $obs): void
    {
        HistorialEstadoAbastecimiento::create([
            'COD_ABA' => $aba->COD_ABA, 'ESD_HIS' => $estado, 'COD_USU' => $request->user()->COD_USU, 'OBS_HIS' => $obs,
        ]);
    }
}