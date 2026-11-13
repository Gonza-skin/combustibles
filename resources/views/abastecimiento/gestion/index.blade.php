<x-layouts.app title="Gestión de abastecimiento">
    <x-page-header titulo="Gestión de abastecimiento" subtitulo="Convierte solicitudes en envíos y sigue su estado" />

    <x-card>
        <h2 class="card__titulo">Solicitudes pendientes ({{ $pendientes->count() }})</h2>
        <x-table>
            <x-slot:head>
                <th>N.º</th><th>Estación</th><th>Combustible</th><th>Litros</th><th></th>
            </x-slot:head>
            @forelse ($pendientes as $s)
                <tr>
                    <td>#{{ $s->COD_SOL }}</td>
                    <td>{{ $s->estacion?->NOM_EST }}</td>
                    <td>{{ $s->combustible?->DES_COM }}</td>
                    <td><strong>{{ number_format($s->CAN_SOL, 0, ',', '.') }}</strong></td>
                    <td class="acciones-celda">
                        <form method="POST" action="{{ route('abast.gestion.crear', $s) }}">
                            @csrf
                            <x-button variant="success" class="btn--sm">Crear abastecimiento</x-button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="vacio">No hay solicitudes pendientes.</td></tr>
            @endforelse
        </x-table>
    </x-card>

    <x-card>
        <h2 class="card__titulo">Abastecimientos</h2>
        <form method="GET" action="{{ route('abast.gestion.index') }}" class="filtros">
            <x-select name="estado" label="Estado" :options="\App\Models\Abastecimiento::ESTADOS" :selected="$estado" placeholder="Todos" />
            <div class="filtros__botones">
                <x-button variant="primary">Filtrar</x-button>
                <x-button :href="route('abast.gestion.index')" variant="ghost">Limpiar</x-button>
            </div>
        </form>

        <x-table>
            <x-slot:head>
                <th>N.º</th><th>Estación</th><th>Combustible</th><th>Litros</th><th>Cisterna</th><th>Estado</th><th></th>
            </x-slot:head>
            @forelse ($abastecimientos as $a)
                <tr>
                    <td>#{{ $a->COD_ABA }}</td>
                    <td>{{ $a->solicitud?->estacion?->NOM_EST }}</td>
                    <td>{{ $a->combustible?->DES_COM }}</td>
                    <td><strong>{{ number_format($a->CAN_ABA, 0, ',', '.') }}</strong></td>
                    <td>{{ $a->cisterna?->PLC_CIS ?? '—' }}</td>
                    <td><span class="badge badge--aba-{{ $a->ESD_ABA }}">{{ \App\Models\Abastecimiento::ESTADOS[$a->ESD_ABA] }}</span></td>
                    <td class="acciones-celda"><x-button :href="route('abast.gestion.show', $a)" variant="outline" class="btn--sm">Gestionar</x-button></td>
                </tr>
            @empty
                <tr><td colspan="7" class="vacio">No hay abastecimientos.</td></tr>
            @endforelse
        </x-table>

        {{ $abastecimientos->links('partials.paginacion') }}
    </x-card>
</x-layouts.app>