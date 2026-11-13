<x-layouts.app title="Automóviles">
    <x-page-header titulo="Automóviles" subtitulo="Vehículos registrados en el sistema (pueden cargar combustible en cualquier estación)">
        <x-button :href="route('automoviles.create')" variant="success">+ Nuevo automóvil</x-button>
    </x-page-header>

    <x-card>
        <form method="GET" action="{{ route('automoviles.index') }}" class="buscador">
            <input type="search" name="q" value="{{ $q }}" placeholder="Buscar por placa, modelo o propietario" aria-label="Buscar automóviles">
            <x-button variant="outline">Buscar</x-button>
            @if ($q !== '')
                <x-button :href="route('automoviles.index')" variant="ghost">Limpiar</x-button>
            @endif
        </form>

        <x-table>
            <x-slot:head>
                <th>Placa</th>
                <th>Tipo</th>
                <th>Modelo</th>
                <th>Propietario</th>
                @if ($esAdmin)<th></th>@endif
            </x-slot:head>

            @forelse ($automoviles as $a)
                <tr>
                    <td><strong>{{ $a->PLA_VEH }}</strong></td>
                    <td>{{ $a->TIP_VEH }}</td>
                    <td>{{ $a->MOD_VEH }}</td>
                    <td>{{ $a->NOM_PROP ?: '—' }} @if ($a->TEL_PROP)<div class="sec">{{ $a->TEL_PROP }}</div>@endif</td>
                    @if ($esAdmin)
                        <td class="acciones-celda">
                            <x-button :href="route('automoviles.edit', $a)" variant="outline" class="btn--sm">Editar</x-button>
                            <x-delete-button :action="route('automoviles.destroy', $a)" mensaje="¿Eliminar el automóvil {{ $a->PLA_VEH }}?">Eliminar</x-delete-button>
                        </td>
                    @endif
                </tr>
            @empty
                <tr><td colspan="{{ $esAdmin ? 5 : 4 }}" class="vacio">No se encontraron automóviles.</td></tr>
            @endforelse
        </x-table>

        {{ $automoviles->links('partials.paginacion') }}
    </x-card>
</x-layouts.app>