<x-layouts.app title="Historial de abastecimiento">
    <x-page-header titulo="Historial de abastecimiento" subtitulo="Todos los cambios de estado de los envíos" />

    <x-card>
        <form method="GET" action="{{ route('abast.historial.index') }}" class="filtros">
            <x-input name="abastecimiento" type="number" min="1" label="N.º de abastecimiento" :value="$filtros['abastecimiento'] ?? null" />
            <x-select name="estado" label="Estado" :options="\App\Models\Abastecimiento::ESTADOS" :selected="$filtros['estado'] ?? null" placeholder="Todos" />
            <div class="filtros__botones">
                <x-button variant="primary">Filtrar</x-button>
                <x-button :href="route('abast.historial.index')" variant="ghost">Limpiar</x-button>
            </div>
        </form>

        <x-table>
            <x-slot:head><th>Fecha</th><th>Abastecimiento</th><th>Estación</th><th>Estado</th><th>Usuario</th><th>Observación</th></x-slot:head>
            @forelse ($historial as $h)
                <tr>
                    <td class="sec">{{ $h->FEC_HIS?->format('d/m/Y H:i') }}</td>
                    <td>#{{ $h->COD_ABA }}</td>
                    <td>{{ $h->abastecimiento?->solicitud?->estacion?->NOM_EST }}</td>
                    <td><span class="badge badge--aba-{{ $h->ESD_HIS }}">{{ \App\Models\Abastecimiento::ESTADOS[$h->ESD_HIS] ?? $h->ESD_HIS }}</span></td>
                    <td>{{ $h->usuario?->NOM_USU ?? '—' }}</td>
                    <td>{{ $h->OBS_HIS ?: '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="vacio">No hay registros.</td></tr>
            @endforelse
        </x-table>

        {{ $historial->links('partials.paginacion') }}
    </x-card>
</x-layouts.app>