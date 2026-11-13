<?php
use App\Http\Controllers\EstacionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\RegistroController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RolController;
use App\Http\Controllers\PrecioCombustibleController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\AutomovilController;
use App\Http\Controllers\CisternaController;
use App\Http\Controllers\ConductorController;
use App\Http\Controllers\MantenimientoCisternaController;
use App\Http\Controllers\PlantaController;
use App\Http\Controllers\TanqueController;
use App\Http\Controllers\GestionAbastecimientoController;
use App\Http\Controllers\HistorialAbastecimientoController;
use App\Http\Controllers\SolicitudController;
use App\Http\Controllers\AlertaController;
use App\Http\Controllers\IncidenciaController;
use App\Http\Controllers\ReporteCiudadanoAdminController;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\EnvioConductorController;
use App\Http\Controllers\DespachoDepositoController;
use App\Http\Controllers\SeguimientoController;
use App\Http\Controllers\RecomendacionController;
use App\Http\Controllers\PrediccionController;
// ---- Inicio ----
Route::get('/', [HomeController::class, 'index'])->name('home');

// ---- Registro (solo para quien NO tiene sesión). El login y el logout los maneja Fortify ----
Route::middleware('guest')->group(function () {
    Route::get('/registro', [RegistroController::class, 'create'])->name('registro');
    Route::post('/registro', [RegistroController::class, 'store']);
});

// ---- Panel principal (solo con sesión) ----
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware('auth')
    ->name('dashboard');
    
Route::middleware('auth')->group(function () {
    Route::get('/roles', [RolController::class, 'index'])
        ->middleware('permission:roles.ver')
        ->name('roles.index');

    // Los permisos de usuarios y precios se piden dentro de sus controladores
    Route::resource('usuarios', UsuarioController::class)->except('show');
    Route::resource('precios-combustible', PrecioCombustibleController::class)->only(['index', 'edit', 'update']);
});
// ---- Estaciones ----
// Ver la lista y el mapa: cualquier usuario con sesión
Route::get('/estaciones', [EstacionController::class, 'index'])
    ->middleware('auth')
    ->name('estaciones.index');

// Crear, editar y eliminar: solo administrador
Route::middleware(['auth', 'role:administrador'])->group(function () {
    Route::resource('estaciones', EstacionController::class)
        ->parameters(['estaciones' => 'estacion'])
        ->except(['index', 'show']);
});
// ---- Flota: cisternas, conductores y mantenimientos (solo administrador) ----
Route::middleware(['auth', 'role:administrador'])->group(function () {
    Route::resource('cisternas', CisternaController::class)->except('show');

    Route::resource('conductores', ConductorController::class)
        ->parameters(['conductores' => 'conductor'])
        ->except('show');

    Route::resource('mantenimiento-cisternas', MantenimientoCisternaController::class)
        ->parameters(['mantenimiento-cisternas' => 'mantenimiento_cisterna'])
        ->except('show');
});

// ---- Automóviles ----
// Ver la lista y registrar uno nuevo: administrador y encargado de estación
Route::middleware(['auth', 'role:administrador,encargado_estacion'])->group(function () {
    Route::resource('automoviles', AutomovilController::class)
        ->parameters(['automoviles' => 'automovil'])
        ->only(['index', 'create', 'store']);
});

// Editar y eliminar: solo administrador
Route::middleware(['auth', 'role:administrador'])->group(function () {
    Route::resource('automoviles', AutomovilController::class)
        ->parameters(['automoviles' => 'automovil'])
        ->only(['edit', 'update', 'destroy']);
});
// ---- Abastecimiento ----
// Plantas y tanques: administrador y administrador de depósito
Route::middleware(['auth', 'role:administrador,administrador_deposito'])->prefix('abastecimiento')->name('abast.')->group(function () {
    Route::resource('plantas', PlantaController::class)->except('show');
    Route::resource('tanques', TanqueController::class)->except('show');
});
// Solicitudes (consulta): encargado (la suya), subadministrador y administrador
// Solicitudes: crear, editar y cancelar → solo encargado de estación (de SU estación)
Route::middleware(['auth', 'role:encargado_estacion'])->prefix('abastecimiento')->name('abast.')->group(function () {
    Route::get('/solicitudes/nueva', [SolicitudController::class, 'create'])->name('solicitudes.create');
    Route::post('/solicitudes', [SolicitudController::class, 'store'])->name('solicitudes.store');
    Route::get('/solicitudes/{solicitud}/editar', [SolicitudController::class, 'edit'])->whereNumber('solicitud')->name('solicitudes.edit');
    Route::put('/solicitudes/{solicitud}', [SolicitudController::class, 'update'])->whereNumber('solicitud')->name('solicitudes.update');
    Route::delete('/solicitudes/{solicitud}', [SolicitudController::class, 'cancelar'])->whereNumber('solicitud')->name('solicitudes.cancelar');
});
Route::middleware(['auth', 'role:encargado_estacion,sub_administrador,administrador'])
    ->prefix('abastecimiento')->name('abast.')->group(function () {
        Route::get('/solicitudes', [SolicitudController::class, 'index'])->name('solicitudes.index');
        Route::get('/solicitudes/{solicitud}', [SolicitudController::class, 'show'])->whereNumber('solicitud')->name('solicitudes.show');
    });

// Gestión de abastecimiento: subadministrador y administrador
Route::middleware(['auth', 'role:sub_administrador,administrador'])
    ->prefix('abastecimiento/gestion')->name('abast.gestion.')->group(function () {
        Route::get('/', [GestionAbastecimientoController::class, 'index'])->name('index');
        Route::get('/{abastecimiento}', [GestionAbastecimientoController::class, 'show'])->whereNumber('abastecimiento')->name('show');
        Route::post('/solicitud/{solicitud}/crear', [GestionAbastecimientoController::class, 'crear'])->whereNumber('solicitud')->name('crear');
        Route::post('/{abastecimiento}/asignar', [GestionAbastecimientoController::class, 'asignar'])->whereNumber('abastecimiento')->name('asignar');
        Route::post('/{abastecimiento}/estado', [GestionAbastecimientoController::class, 'estado'])->whereNumber('abastecimiento')->name('estado');
    });

// Historial (solo lectura)
Route::get('/abastecimiento/historial', [HistorialAbastecimientoController::class, 'index'])
    ->middleware(['auth', 'role:sub_administrador,administrador,supervisor'])
    ->name('abast.historial.index');
    // ---- Control ----
// Incidencias: supervisor y administrador
Route::middleware(['auth', 'role:supervisor,administrador'])->group(function () {
    Route::resource('incidencias', IncidenciaController::class)
        ->parameters(['incidencias' => 'incidencia'])
        ->except('destroy');
    Route::post('/incidencias/{incidencia}/avanzar', [IncidenciaController::class, 'avanzar'])
        ->whereNumber('incidencia')->name('incidencias.avanzar');
});

// Alertas: supervisor, administrador y subadministrador
Route::middleware(['auth', 'role:supervisor,administrador,sub_administrador,administrador_deposito'])->group(function () {
    Route::get('/alertas', [AlertaController::class, 'index'])->name('alertas.index');
    Route::post('/alertas/generar', [AlertaController::class, 'generar'])->name('alertas.generar');
    Route::post('/alertas/leer-todas', [AlertaController::class, 'leerTodas'])->name('alertas.leer-todas');
    Route::post('/alertas/{alerta}/leer', [AlertaController::class, 'leer'])->whereNumber('alerta')->name('alertas.leer');
});

// Reportes ciudadanos (consulta): solo administrador
Route::get('/reportes-ciudadanos', [ReporteCiudadanoAdminController::class, 'index'])
    ->middleware(['auth', 'role:administrador'])
    ->name('reportes-ciudadanos.index');
    // ---- Ventas de combustible: encargado de estación ----
Route::middleware(['auth', 'role:encargado_estacion'])->group(function () {
    Route::get('/ventas', [VentaController::class, 'index'])->name('ventas.index');
    Route::get('/ventas/nueva', [VentaController::class, 'create'])->name('ventas.create');
    Route::post('/ventas', [VentaController::class, 'store'])->name('ventas.store');
});
// ---- Conductor de cisterna: sus envíos ----
Route::middleware(['auth', 'role:conductor_cisterna'])->prefix('mis-envios')->name('conductor.envios.')->group(function () {
    Route::get('/', [EnvioConductorController::class, 'index'])->name('index');
    Route::get('/{abastecimiento}', [EnvioConductorController::class, 'show'])->whereNumber('abastecimiento')->name('show');
    Route::post('/{abastecimiento}/iniciar', [EnvioConductorController::class, 'iniciar'])->whereNumber('abastecimiento')->name('iniciar');
    Route::post('/{abastecimiento}/entregar', [EnvioConductorController::class, 'entregar'])->whereNumber('abastecimiento')->name('entregar');
    Route::post('/{abastecimiento}/incidencia', [EnvioConductorController::class, 'incidencia'])->whereNumber('abastecimiento')->name('incidencia');
});
// ---- Depósito: despacho (carga de cisternas desde los tanques) ----
Route::middleware(['auth', 'role:administrador_deposito,administrador'])->prefix('deposito/despachos')->name('deposito.despachos.')->group(function () {
    Route::get('/', [DespachoDepositoController::class, 'index'])->name('index');
    Route::get('/{abastecimiento}', [DespachoDepositoController::class, 'show'])->whereNumber('abastecimiento')->name('show');
    Route::post('/{abastecimiento}/cargar', [DespachoDepositoController::class, 'cargar'])->whereNumber('abastecimiento')->name('cargar');
});
// ---- Seguimiento de entregas (retrasos y diferencias): supervisor, administrador y subadministrador ----
Route::get('/seguimiento', [SeguimientoController::class, 'index'])
    ->middleware(['auth', 'role:supervisor,administrador,sub_administrador'])
    ->name('seguimiento.index');
    // ---- Recomendación de abastecimiento (subadministrador y administrador) ----
Route::middleware(['auth', 'role:sub_administrador,administrador'])->group(function () {
    Route::get('/recomendaciones', [RecomendacionController::class, 'index'])->name('recomendaciones.index');
});
Route::middleware(['auth', 'role:sub_administrador,administrador,supervisor'])->group(function () {
    Route::get('/prediccion', [PrediccionController::class, 'index'])->name('prediccion.index');
});