<x-layouts.app title="Tanques">
    @php($puntos = ['operativo' => 'punto--disponible', 'critico' => 'punto--agotado', 'mantenimiento' => 'punto--escasez', 'inactivo' => 'punto--gris'])

    <x-page-header titulo="Tanques de almacenamiento" subtitulo="Nivel de combustible en cada planta">
        <x-button :href="route('abast.tanques.create')" variant="success">+ Nuevo tanque</x-button>
    </x-page-header>

    <form method="GET" action="{{ route('abast.tanques.index') }}" class="buscador">
        <select name="planta" aria-label="Filtrar por planta">
            <option value="">Todas las plantas</option>
            @foreach ($plantas as $id => $nombre)
                <option value="{{ $id }}" @selected((string) $planta === (string) $id)>{{ $nombre }}</option>
            @endforeach
        </select>
        <select name="combustible" aria-label="Filtrar por combustible">
            <option value="">Todos los combustibles</option>
            @foreach ($combustibles as $id => $nombre)
                <option value="{{ $id }}" @selected((string) $combustible === (string) $id)>{{ $nombre }}</option>
            @endforeach
        </select>
        <x-button variant="outline">Filtrar</x-button>
        @if ($planta || $combustible)
            <x-button :href="route('abast.tanques.index')" variant="ghost">Limpiar</x-button>
        @endif
    </form>

    <div class="estaciones-grid">
        @forelse ($tanques as $t)
            @php($pct = $t->porcentaje())
            <article class="est-card">
                <header class="est-card__cabeza">
                    <h2>{{ $t->NOM_TAN }}</h2>
                    <span class="est-estado">
                        <i class="punto {{ $puntos[$t->ESD_TAN] ?? 'punto--gris' }}"></i>
                        {{ \App\Models\TanqueAlmacenamiento::ESTADOS[$t->ESD_TAN] ?? $t->ESD_TAN }}
                    </span>
                </header>

                <dl class="est-card__datos">
                    <div><dt>Planta</dt><dd>{{ $t->planta?->NOM_PLA }}</dd></div>
                    <div><dt>Combustible</dt><dd>{{ $t->combustible?->DES_COM }}</dd></div>
                    <div><dt>Capacidad</dt><dd>{{ number_format($t->CAP_TAN, 0, ',', '.') }} L</dd></div>
                    <div><dt>Nivel</dt><dd>{{ number_format($t->NIV_TAN, 0, ',', '.') }} L ({{ $pct }}%)</dd></div>
                </dl>
                <div class="exi__barra"><span class="exi__relleno exi__relleno--{{ $pct < 15 ? 'sin_combustible' : ($pct < 35 ? 'nivel_bajo' : 'normal') }}" style="width: {{ $pct }}%"></span></div>

                <footer class="est-card__pie">
                    <x-button :href="route('abast.tanques.edit', $t)" variant="outline" class="btn--sm">Editar</x-button>
                    <x-delete-button :action="route('abast.tanques.destroy', $t)" mensaje="¿Eliminar el tanque {{ $t->NOM_TAN }}?">Eliminar</x-delete-button>
                </footer>
            </article>
        @empty
            <x-card class="est-vacio">No hay tanques con esos filtros.</x-card>
        @endforelse
    </div>

    {{ $tanques->links('partials.paginacion') }}
</x-layouts.app>