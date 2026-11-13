<x-layouts.app title="Mantenimiento de cisternas">
    <x-page-header titulo="Mantenimiento de cisternas" subtitulo="Historial de mantenimientos preventivos, correctivos y de documentación">
        <x-button :href="route('mantenimiento-cisternas.create')" variant="success">+ Nuevo mantenimiento</x-button>
    </x-page-header>

    <x-card>
        <form method="GET" action="{{ route('mantenimiento-cisternas.index') }}" class="filtros">
            <x-select name="cisterna" label="Cisterna" :options="$cisternas" :selected="$filtros['cisterna'] ?? null" placeholder="Todas" />
            <x-select name="tipo" label="Tipo" :options="\App\Models\MantenimientoCisterna::TIPOS" :selected="$filtros['tipo'] ?? null" placeholder="Todos" />
            <div class="filtros__botones">
                <x-button variant="primary">Filtrar</x-button>
                <x-button :href="route('mantenimiento-cisternas.index')" variant="ghost">Limpiar</x-button>
            </div>
        </form>

        <x-table>
            <x-slot:head>
                <th>Fecha</th>
                <th>Cisterna</th>
                <th>Tipo</th>
                <th>Descripción</th>
                <th></th>
            </x-slot:head>

            @forelse ($mantenimientos as $m)
                <tr>
                    <td class="sec">{{ $m->FEC_MAN->format('d/m/Y') }}</td>
                    <td><strong>{{ $m->cisterna?->PLC_CIS }}</strong></td>
                    <td><span class="badge badge--man-{{ $m->TIP_MAN }}">{{ \App\Models\MantenimientoCisterna::TIPOS[$m->TIP_MAN] ?? $m->TIP_MAN }}</span></td>
                    <td>{{ $m->DES_MAN ? \Illuminate\Support\Str::limit($m->DES_MAN, 80) : '—' }}</td>
                    <td class="acciones-celda">
                        <x-button :href="route('mantenimiento-cisternas.edit', $m)" variant="outline" class="btn--sm">Editar</x-button>
                        <x-delete-button :action="route('mantenimiento-cisternas.destroy', $m)" mensaje="¿Eliminar este registro de mantenimiento?">Eliminar</x-delete-button>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="vacio">No hay mantenimientos con esos filtros.</td></tr>
            @endforelse
        </x-table>

        {{ $mantenimientos->links('partials.paginacion') }}
    </x-card>
</x-layouts.app>