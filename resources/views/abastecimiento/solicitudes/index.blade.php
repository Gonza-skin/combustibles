<x-layouts.app title="Solicitudes">
    <x-page-header titulo="Solicitudes de abastecimiento"
        :subtitulo="$soloSuEstacion ? 'Pedidos de combustible de tu estación' : 'Pedidos de combustible de las estaciones'">
        @if ($soloSuEstacion)
            <x-button :href="route('abast.solicitudes.create')" variant="success">+ Nueva solicitud</x-button>
        @endif
    </x-page-header>
    <x-card>
        <form method="GET" action="{{ route('abast.solicitudes.index') }}" class="filtros">
            <x-select name="estado" label="Estado" :options="\App\Models\SolicitudAbastecimiento::ESTADOS" :selected="$estado" placeholder="Todos" />
            <div class="filtros__botones">
                <x-button variant="primary">Filtrar</x-button>
                <x-button :href="route('abast.solicitudes.index')" variant="ghost">Limpiar</x-button>
            </div>
        </form>

        <x-table>
            <x-slot:head>
                <th>N.º</th><th>Fecha</th><th>Estación</th><th>Combustible</th><th>Litros</th><th>Estado</th><th></th>
            </x-slot:head>
            @forelse ($solicitudes as $s)
                <tr>
                    <td>#{{ $s->COD_SOL }}</td>
                    <td class="sec">{{ $s->FEC_SOL?->format('d/m/Y H:i') }}</td>
                    <td>{{ $s->estacion?->NOM_EST }}</td>
                    <td>{{ $s->combustible?->DES_COM }}</td>
                    <td><strong>{{ number_format($s->CAN_SOL, 0, ',', '.') }}</strong></td>
                    <td><span class="badge badge--sol-{{ $s->ESD_SOL }}">{{ \App\Models\SolicitudAbastecimiento::ESTADOS[$s->ESD_SOL] ?? $s->ESD_SOL }}</span></td>
                    <td class="acciones-celda">
                       <x-button :href="route('abast.solicitudes.show', $s)" variant="ghost" class="btn--sm">Ver</x-button>
                        @if ($soloSuEstacion && $s->ESD_SOL === 'pendiente' && ! $s->abastecimiento)
                            <x-button :href="route('abast.solicitudes.edit', $s)" variant="outline" class="btn--sm">Editar</x-button>
                            <x-delete-button :action="route('abast.solicitudes.cancelar', $s)" mensaje="¿Cancelar la solicitud #{{ $s->COD_SOL }}?">Cancelar</x-delete-button>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="vacio">No hay solicitudes.</td></tr>
            @endforelse
        </x-table>

        {{ $solicitudes->links('partials.paginacion') }}
    </x-card>
</x-layouts.app>