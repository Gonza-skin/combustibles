<?php

namespace App\Http\Controllers;

use App\Models\Abastecimiento;
use App\Models\HistorialEstadoAbastecimiento;
use App\Models\TanqueAlmacenamiento;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * PARA SU SALIDA DE LAS CISTERNAS DESDE LA PLANTA 
 * AL CARGAR SE SE REDUCE LOS LITROS 
 */
class DespachoDepositoController extends Controller
{
    public function index(): View
    {
        $con = ['solicitud.estacion', 'combustible', 'cisterna.conductor'];

        $porCargar = Abastecimiento::with($con)
            ->where('ESD_ABA', 'asignado')->whereNull('CCA_ABA')
            ->orderBy('COD_ABA')->get();

        $cargados = Abastecimiento::with([...$con, 'tanque.planta'])
            ->whereNotNull('COD_TAN')
            ->orderByDesc('COD_ABA')->limit(10)->get();

        return view('deposito.despachos.index', compact('porCargar', 'cargados'));
    }

    public function show(Abastecimiento $abastecimiento): View
    {
        $abastecimiento->load(['solicitud.estacion', 'combustible', 'cisterna.conductor', 'tanque.planta']);

        $puedeCargar = $abastecimiento->ESD_ABA === 'asignado' && $abastecimiento->CCA_ABA === null;

        // Tanques de plantas activas, con el mismo combustible, en servicio y con algo de nivel
        $tanques = $puedeCargar
            ? TanqueAlmacenamiento::with('planta')
                ->where('COD_COM', $abastecimiento->COD_COM)
                ->whereIn('ESD_TAN', ['operativo', 'critico'])
                ->where('NIV_TAN', '>', 0)
                ->whereHas('planta', fn ($p) => $p->where('ACT_PLA', 1))
                ->orderBy('COD_PLA')->orderBy('NOM_TAN')->get()
            : collect();

        return view('deposito.despachos.show', compact('abastecimiento', 'puedeCargar', 'tanques'));
    }

    public function cargar(Request $request, Abastecimiento $abastecimiento): RedirectResponse
    {
        $datos = $request->validate([
            'COD_TAN' => ['required', 'integer', Rule::exists('tanque_almacenamiento', 'COD_TAN')],
            'CCA_ABA' => ['required', 'numeric', 'gt:0', 'max:99999999.99'],
        ], [], ['CCA_ABA' => 'litros a cargar']);
        $litros = round((float) $datos['CCA_ABA'], 2);

        try {
            DB::transaction(function () use ($request, $abastecimiento, $datos, $litros) {
                $aba = Abastecimiento::with('cisterna')->lockForUpdate()->findOrFail($abastecimiento->COD_ABA);
                $tan = TanqueAlmacenamiento::with('planta')->lockForUpdate()->findOrFail($datos['COD_TAN']);

                if ($aba->ESD_ABA !== 'asignado' || $aba->CCA_ABA !== null) {
                    throw new \RuntimeException('Este envío ya fue cargado o ya no está esperando carga.');
                }
                if ((int) $tan->COD_COM !== (int) $aba->COD_COM) {
                    throw new \RuntimeException('Ese tanque tiene otro tipo de combustible.');
                }
                if (! in_array($tan->ESD_TAN, ['operativo', 'critico'], true) || ! $tan->planta?->ACT_PLA) {
                    throw new \RuntimeException('Ese tanque no está en servicio.');
                }
                if ($litros > (float) $aba->cisterna->CAP_CIS) {
                    throw new \RuntimeException('No cabe en la cisterna: su capacidad es '.number_format($aba->cisterna->CAP_CIS, 0, ',', '.').' L.');
                }
                if ($litros > (float) $tan->NIV_TAN) {
                    throw new \RuntimeException('El tanque solo tiene '.number_format($tan->NIV_TAN, 2, ',', '.').' L.');
                }

                $nivel = round((float) $tan->NIV_TAN - $litros, 2);
                $cambios = ['NIV_TAN' => $nivel];
                // Si queda por debajo del 15 % pasa a crítico (la alerta lo detecta)
                if ($tan->ESD_TAN === 'operativo' && $nivel < (float) $tan->CAP_TAN * 0.15) {
                    $cambios['ESD_TAN'] = 'critico';
                }
                $tan->update($cambios);

                $aba->update(['COD_TAN' => $tan->COD_TAN, 'CCA_ABA' => $litros]);

                HistorialEstadoAbastecimiento::create([
                    'COD_ABA' => $aba->COD_ABA, 'ESD_HIS' => 'asignado', 'COD_USU' => $request->user()->COD_USU,
                    'OBS_HIS' => mb_substr('Cargados '.number_format($litros, 2, ',', '.').' L del tanque '.$tan->NOM_TAN.' ('.$tan->planta?->NOM_PLA.')', 0, 255),
                ]);
            });
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('deposito.despachos.show', $abastecimiento)->with('exito', 'Cisterna cargada. El conductor ya puede salir.');
    }
}