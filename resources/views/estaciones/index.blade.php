<x-layouts.app title="Estaciones">
    @php
        $esAdmin = auth()->user()->tieneRol('administrador');
        // Color del puntito según el estado de la estación
        $puntos = ['normal' => 'punto--disponible', 'nivel_bajo' => 'punto--escasez', 'sin_combustible' => 'punto--agotado'];
    @endphp

    <x-page-header titulo="Estaciones" subtitulo="Estaciones de servicio del Municipio de La Paz y su estado de combustible">
        @if ($esAdmin)
            <x-button :href="route('estaciones.create')" variant="success">+ Nueva estación</x-button>
        @endif
    </x-page-header>

    <form method="GET" action="{{ route('estaciones.index') }}" class="buscador">
        <input type="search" name="q" value="{{ $q }}" placeholder="Buscar por nombre, zona o dirección" aria-label="Buscar estaciones">
        <x-button variant="outline">Buscar</x-button>
        @if ($q !== '')
            <x-button :href="route('estaciones.index')" variant="ghost">Limpiar</x-button>
        @endif
    </form>

    <div class="leyenda leyenda--estaciones">
        <span><i class="punto punto--disponible"></i> Normal</span>
        <span><i class="punto punto--escasez"></i> Nivel bajo</span>
        <span><i class="punto punto--agotado"></i> Sin combustible</span>
    </div>

    <div class="estaciones-grid">
        @forelse ($estaciones as $e)
            <article @class(['est-card', 'est-card--inactiva' => ! $e->ACT_EST])>
                <header class="est-card__cabeza">
                    <h2>{{ $e->NOM_EST }}</h2>
                    <span class="est-estado est-estado--{{ $e->ESD_EST }}">
                        <i class="punto {{ $puntos[$e->ESD_EST] ?? 'punto--agotado' }}"></i>
                        {{ \App\Models\Estacion::ESTADOS[$e->ESD_EST] ?? $e->ESD_EST }}
                    </span>
                </header>

                <dl class="est-card__datos">
                    <div><dt>Zona</dt><dd>{{ $e->ZON_EST ?: '—' }}</dd></div>
                    <div>
                        <dt>Ubicación</dt>
                        <dd>
                            @if ($e->tieneEnlaceMaps())
                                <a href="{{ $e->urlComoLlegar() }}" target="_blank" rel="noopener noreferrer">Ver en Google Maps</a>
                            @else
                                {{ $e->DIR_EST ?: '—' }}
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt>Activa</dt>
                        <dd>
                            @if ($e->ACT_EST)
                                <span class="badge badge--activo">Sí</span>
                            @else
                                <span class="badge badge--inactivo">No</span>
                            @endif
                        </dd>
                    </div>
                </dl>

                <footer class="est-card__pie">
                    <x-button :href="$e->urlComoLlegar()" variant="success" class="btn--sm" target="_blank" rel="noopener noreferrer">Cómo llegar</x-button>
                    @if ($esAdmin)
                        <x-button :href="route('estaciones.edit', $e)" variant="outline" class="btn--sm">Editar</x-button>
                        <x-delete-button :action="route('estaciones.destroy', $e)" mensaje="¿Eliminar {{ $e->NOM_EST }}? Si ya tiene registros, mejor desactívala.">Eliminar</x-delete-button>
                    @endif
                </footer>
            </article>
        @empty
            <x-card class="est-vacio">No se encontraron estaciones.</x-card>
        @endforelse
    </div>

    {{ $estaciones->links('partials.paginacion') }}
</x-layouts.app>