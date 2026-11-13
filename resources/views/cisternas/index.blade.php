<x-layouts.app title="Cisternas">
    @php($puntos = ['disponible' => 'punto--disponible', 'asignada' => 'punto--azul', 'en_transito' => 'punto--escasez', 'mantenimiento' => 'punto--gris'])

    <x-page-header titulo="Cisternas" subtitulo="Camiones que llevan combustible a las estaciones">
        <x-button :href="route('cisternas.create')" variant="success">+ Nueva cisterna</x-button>
    </x-page-header>

    <form method="GET" action="{{ route('cisternas.index') }}" class="buscador">
        <input type="search" name="q" value="{{ $q }}" placeholder="Buscar por placa" aria-label="Buscar cisternas">
        <select name="estado" aria-label="Filtrar por estado">
            <option value="">Todos los estados</option>
            @foreach (\App\Models\Cisterna::ESTADOS as $valor => $texto)
                <option value="{{ $valor }}" @selected($estado === $valor)>{{ $texto }}</option>
            @endforeach
        </select>
        <x-button variant="outline">Buscar</x-button>
        @if ($q !== '' || $estado)
            <x-button :href="route('cisternas.index')" variant="ghost">Limpiar</x-button>
        @endif
    </form>

    <div class="estaciones-grid">
        @forelse ($cisternas as $c)
            <article class="est-card">
                <header class="est-card__cabeza">
                    <h2>{{ $c->PLC_CIS }}</h2>
                    <span class="est-estado est-estado--{{ $c->ESD_CIS }}">
                        <i class="punto {{ $puntos[$c->ESD_CIS] ?? 'punto--gris' }}"></i>
                        {{ \App\Models\Cisterna::ESTADOS[$c->ESD_CIS] ?? $c->ESD_CIS }}
                    </span>
                </header>

                <dl class="est-card__datos">
                    <div><dt>Combustible</dt><dd>{{ $c->combustible?->DES_COM ?? '—' }}</dd></div>
                    <div><dt>Capacidad</dt><dd>{{ number_format($c->CAP_CIS, 0, ',', '.') }} L</dd></div>
                    <div><dt>Conductor</dt><dd>{{ $c->conductor?->NOM_CON ?? 'Sin conductor' }}</dd></div>
                </dl>

                <footer class="est-card__pie">
                    <x-button :href="route('cisternas.edit', $c)" variant="outline" class="btn--sm">Editar</x-button>
                    <x-delete-button :action="route('cisternas.destroy', $c)" mensaje="¿Eliminar la cisterna {{ $c->PLC_CIS }}?">Eliminar</x-delete-button>
                </footer>
            </article>
        @empty
            <x-card class="est-vacio">No se encontraron cisternas.</x-card>
        @endforelse
    </div>

    {{ $cisternas->links('partials.paginacion') }}
</x-layouts.app>