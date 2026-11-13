<x-layouts.app title="Inicio">
    <x-page-header titulo="Panel de control"
                   subtitulo="Sistema de Control de Abastecimiento de Combustible — Municipio de La Paz" />

    <div class="stats">
        <x-stat :valor="$estacionesActivas" texto="Estaciones activas" />
        <x-stat :valor="$estacionesNivelBajo" texto="Estaciones con nivel bajo" alerta />
        <x-stat :valor="$cisternasEnTransito" texto="Cisternas en tránsito" />
        <x-stat :valor="$incidenciasAbiertas" texto="Incidencias abiertas" alerta />

        {{-- Tarjeta pequeña de tipos de combustible --}}
        <div class="stat">
            <div class="stat__titulo">Tipos de combustible</div>
            <ul class="stat__lista">
                @foreach ($tiposCombustible as $tipo)
                    <li>{{ $tipo }}</li>
                @endforeach
            </ul>
        </div>
    </div>

    <x-card>
        <div class="tarjeta__titulo">
            <h2>Mapa de estaciones de servicio</h2>
            <span class="sec">Haz clic en una estación para ver su ubicación y cómo llegar</span>
        </div>

        <x-mapa-google />
    </x-card>
</x-layouts.app>