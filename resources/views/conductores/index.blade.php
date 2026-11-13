<x-layouts.app title="Conductores">
    <x-page-header titulo="Conductores" subtitulo="Conductores de las cisternas">
        <x-button :href="route('conductores.create')" variant="success">+ Nuevo conductor</x-button>
    </x-page-header>

    <x-card>
        <form method="GET" action="{{ route('conductores.index') }}" class="buscador">
            <input type="search" name="q" value="{{ $q }}" placeholder="Buscar por nombre, cédula o licencia" aria-label="Buscar conductores">
            <x-button variant="outline">Buscar</x-button>
            @if ($q !== '')
                <x-button :href="route('conductores.index')" variant="ghost">Limpiar</x-button>
            @endif
        </form>

        <x-table>
            <x-slot:head>
                <th>Nombre</th>
                <th>Cédula</th>
                <th>Licencia</th>
                <th>Teléfono</th>
                <th>Usuario</th>
                <th>Estado</th>
                <th></th>
            </x-slot:head>

            @forelse ($conductores as $c)
                <tr>
                    <td><strong>{{ $c->NOM_CON }}</strong></td>
                    <td>{{ $c->CI_CON }}</td>
                    <td>{{ $c->LIC_CON }} @if ($c->CAT_LIC)<span class="sec">(cat. {{ $c->CAT_LIC }})</span>@endif</td>
                    <td>{{ $c->TEL_CON ?: '—' }}</td>
                    <td class="sec">{{ $c->usuario?->EML_USU ?? '—' }}</td>
                    <td>
                        @if ($c->ACT_CON)
                            <span class="badge badge--activo">Activo</span>
                        @else
                            <span class="badge badge--inactivo">Inactivo</span>
                        @endif
                    </td>
                    <td class="acciones-celda">
                        <x-button :href="route('conductores.edit', $c)" variant="outline" class="btn--sm">Editar</x-button>
                        <x-delete-button :action="route('conductores.destroy', $c)" mensaje="¿Eliminar a {{ $c->NOM_CON }}? Si está asignado a una cisterna, mejor desactívalo.">Eliminar</x-delete-button>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="vacio">No se encontraron conductores.</td></tr>
            @endforelse
        </x-table>

        {{ $conductores->links('partials.paginacion') }}
    </x-card>
</x-layouts.app>