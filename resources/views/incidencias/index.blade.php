<x-layouts.app title="Incidencias">
    <x-page-header titulo="Incidencias" subtitulo="Problemas detectados en el abastecimiento">
        <x-button :href="route('incidencias.create')" variant="success">+ Nueva incidencia</x-button>
    </x-page-header>

    <x-card>
        <form method="GET" action="{{ route('incidencias.index') }}" class="filtros">
            <x-select name="tipo" label="Tipo" :options="\App\Models\Incidencia::TIPOS" :selected="$filtros['tipo'] ?? null" placeholder="Todos" />
            <x-select name="estado" label="Estado" :options="\App\Models\Incidencia::ESTADOS" :selected="$filtros['estado'] ?? null" placeholder="Todos" />
            <div class="filtros__botones">
                <x-button variant="primary">Filtrar</x-button>
                <x-button :href="route('incidencias.index')" variant="ghost">Limpiar</x-button>
            </div>
        </form>

        <x-table>
            <x-slot:head>
                <th>N.º</th><th>Fecha</th><th>Tipo</th><th>Descripción</th><th>Estado</th><th></th>
            </x-slot:head>
            @forelse ($incidencias as $i)
                <tr>
                    <td>#{{ $i->COD_INC }}</td>
                    <td class="sec">{{ $i->FEC_INC?->format('d/m/Y H:i') }}</td>
                    <td>{{ \App\Models\Incidencia::TIPOS[$i->TIP_INC] ?? $i->TIP_INC }}</td>
                    <td>{{ \Illuminate\Support\Str::limit($i->DES_INC, 70) }}</td>
                    <td><span class="badge badge--inc-{{ $i->ESD_INC }}">{{ \App\Models\Incidencia::ESTADOS[$i->ESD_INC] }}</span></td>
                    <td class="acciones-celda"><x-button :href="route('incidencias.show', $i)" variant="outline" class="btn--sm">Ver</x-button></td>
                </tr>
            @empty
                <tr><td colspan="6" class="vacio">No hay incidencias.</td></tr>
            @endforelse
        </x-table>

        {{ $incidencias->links('partials.paginacion') }}
    </x-card>
</x-layouts.app>