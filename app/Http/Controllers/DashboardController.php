<?php

namespace App\Http\Controllers;

use App\Models\Cisterna;
use App\Models\Estacion;
use App\Models\Incidencia;
use App\Models\TipoCombustible;

class DashboardController extends Controller
{
    public function index()
    {
        $estacionesActivas   = Estacion::where('ACT_EST', 1)->count();
        $estacionesNivelBajo = Estacion::where('ESD_EST', 'nivel_bajo')->count();
        $cisternasEnTransito = Cisterna::where('ESD_CIS', 'en_transito')->count();
        $incidenciasAbiertas = Incidencia::whereNotIn('ESD_INC', ['cerrada'])->count();

        // LOS NOMBRES DE LOS COMBUSTIBLES QUE SON SOLO 2
        $tiposCombustible = TipoCombustible::orderBy('COD_COM')->pluck('DES_COM');

        return view('dashboard', compact(
            'estacionesActivas',
            'estacionesNivelBajo',
            'cisternasEnTransito',
            'incidenciasAbiertas',
            'tiposCombustible'
        ));
    }
}