<?php

/*
 * Menú lateral del sistema.
 *   'roles' => []  → lo ve cualquier usuario con sesión.
 *   'roles' => ['administrador', ...] → solo esos roles.
 * Si la ruta todavía no existe, el ítem no se muestra (así podemos ir agregando pantallas de a poco).
 */
return [
    ['titulo' => 'Inicio', 'ruta' => 'dashboard', 'icono' => 'fa-house', 'roles' => []],

    // ---- Administración ----
    ['grupo' => 'Administración', 'titulo' => 'Usuarios', 'ruta' => 'usuarios.index', 'icono' => 'fa-users', 'roles' => [], 'permiso' => 'usuarios.ver'],
    ['grupo' => 'Administración', 'titulo' => 'Roles', 'ruta' => 'roles.index', 'icono' => 'fa-id-badge', 'roles' => [], 'permiso' => 'roles.ver'],
    ['grupo' => 'Administración', 'titulo' => 'Precios de combustible', 'ruta' => 'precios-combustible.index', 'icono' => 'fa-tag', 'roles' => [], 'permiso' => 'precios.ver'],

    // ---- Estaciones y existencias ----
    ['grupo' => 'Estaciones', 'titulo' => 'Estaciones', 'ruta' => 'estaciones.index', 'icono' => 'fa-gas-pump', 'roles' => []],
    ['grupo' => 'Estaciones', 'titulo' => 'Existencias', 'ruta' => 'existencias.index', 'icono' => 'fa-boxes-stacked', 'roles' => ['encargado_estacion', 'administrador']],
    ['grupo' => 'Estaciones', 'titulo' => 'Movimientos', 'ruta' => 'movimientos.index', 'icono' => 'fa-arrow-right-arrow-left', 'roles' => ['encargado_estacion', 'administrador', 'supervisor']],
    ['grupo' => 'Estaciones', 'titulo' => 'Ventas', 'ruta' => 'ventas.index', 'icono' => 'fa-cash-register', 'roles' => ['encargado_estacion']],
    ['grupo' => 'Estaciones', 'titulo' => 'Recomendación de abastecimiento', 'ruta' => 'recomendaciones.index', 'icono' => 'fa-lightbulb', 'roles' => ['sub_administrador', 'administrador']],
    ['grupo' => 'Estaciones', 'titulo' => 'Predicción de disponibilidad', 'ruta' => 'prediccion.index', 'icono' => 'fa-chart-line', 'roles' => ['sub_administrador', 'administrador', 'supervisor']],

    // ---- Flota ----
    ['grupo' => 'Flota', 'titulo' => 'Cisternas', 'ruta' => 'cisternas.index', 'icono' => 'fa-truck', 'roles' => ['administrador']],
    ['grupo' => 'Flota', 'titulo' => 'Conductores', 'ruta' => 'conductores.index', 'icono' => 'fa-user', 'roles' => ['administrador']],
    ['grupo' => 'Flota', 'titulo' => 'Mantenimiento de cisternas', 'ruta' => 'mantenimiento-cisternas.index', 'icono' => 'fa-wrench', 'roles' => ['administrador']],
    ['grupo' => 'Flota', 'titulo' => 'Automóviles', 'ruta' => 'automoviles.index', 'icono' => 'fa-car', 'roles' => ['administrador', 'encargado_estacion']],

    // ---- Abastecimiento ----
        // ---- Conductor de cisterna ----
    ['grupo' => 'Mi trabajo', 'titulo' => 'Mis envíos', 'ruta' => 'conductor.envios.index', 'icono' => 'fa-route', 'roles' => ['conductor_cisterna']],
    ['grupo' => 'Abastecimiento', 'titulo' => 'Plantas', 'ruta' => 'abast.plantas.index', 'icono' => 'fa-industry', 'roles' => ['administrador_deposito', 'administrador']],
    ['grupo' => 'Abastecimiento', 'titulo' => 'Tanques', 'ruta' => 'abast.tanques.index', 'icono' => 'fa-droplet', 'roles' => ['administrador_deposito', 'administrador']],
    ['grupo' => 'Abastecimiento', 'titulo' => 'Despachos', 'ruta' => 'deposito.despachos.index', 'icono' => 'fa-truck-ramp-box', 'roles' => ['administrador_deposito', 'administrador']],
    ['grupo' => 'Abastecimiento', 'titulo' => 'Solicitudes', 'ruta' => 'abast.solicitudes.index', 'icono' => 'fa-clipboard-list', 'roles' => ['encargado_estacion', 'sub_administrador', 'administrador']],
    ['grupo' => 'Abastecimiento', 'titulo' => 'Gestión de abastecimiento', 'ruta' => 'abast.gestion.index', 'icono' => 'fa-list-check', 'roles' => ['sub_administrador', 'administrador']],
    ['grupo' => 'Abastecimiento', 'titulo' => 'Historial', 'ruta' => 'abast.historial.index', 'icono' => 'fa-clock-rotate-left', 'roles' => ['sub_administrador', 'administrador', 'supervisor']],
    ['grupo' => 'Abastecimiento', 'titulo' => 'Recomendación', 'ruta' => 'recomendaciones.index', 'icono' => 'fa-lightbulb', 'roles' => ['sub_administrador', 'administrador']],
    // ---- Control ----
    ['grupo' => 'Control', 'titulo' => 'Incidencias', 'ruta' => 'incidencias.index', 'icono' => 'fa-triangle-exclamation', 'roles' => ['supervisor', 'administrador']],
    ['grupo' => 'Control', 'titulo' => 'Seguimiento de entregas', 'ruta' => 'seguimiento.index', 'icono' => 'fa-location-dot', 'roles' => ['supervisor', 'administrador', 'sub_administrador']],
    ['grupo' => 'Control', 'titulo' => 'Alertas', 'ruta' => 'alertas.index', 'icono' => 'fa-bell', 'roles' => ['supervisor', 'administrador', 'sub_administrador', 'administrador_deposito']],
    ['grupo' => 'Control', 'titulo' => 'Reportes ciudadanos', 'ruta' => 'reportes-ciudadanos.index', 'icono' => 'fa-comments', 'roles' => ['administrador']],

    // ---- Mi cuenta (Jetstream) ----
    ['grupo' => 'Mi cuenta', 'titulo' => 'Mi perfil', 'ruta' => 'profile.show', 'icono' => 'fa-circle-user', 'roles' => []],
    ['grupo' => 'Mi cuenta', 'titulo' => 'Tokens de API', 'ruta' => 'api-tokens.index', 'icono' => 'fa-key', 'roles' => ['administrador']],
];
