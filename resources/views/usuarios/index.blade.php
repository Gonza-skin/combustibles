<x-layouts.app title="Usuarios">
    <x-page-header titulo="Usuarios" subtitulo="Cuentas que pueden iniciar sesión en el sistema">
        <x-button :href="route('usuarios.create')" variant="success">+ Nuevo usuario</x-button>
    </x-page-header>

    <x-card>
        <form method="GET" action="{{ route('usuarios.index') }}" class="buscador">
            <input type="search" name="q" value="{{ $q }}" placeholder="Buscar por nombre o correo" aria-label="Buscar usuarios">
            <x-button variant="outline">Buscar</x-button>
            @if ($q !== '')
                <x-button :href="route('usuarios.index')" variant="ghost">Limpiar</x-button>
            @endif
        </form>

        <x-table>
            <x-slot:head>
                <th>Nombre</th>
                <th>Correo</th>
                <th>Rol</th>
                <th>Estado</th>
                <th></th>
            </x-slot:head>

            @forelse ($usuarios as $u)
                <tr>
                    <td>{{ $u->NOM_USU }}</td>
                    <td>{{ $u->EML_USU }}</td>
                    <td>{{ $u->rol?->nombre ?? '-' }}</td>
                    <td>
                        @if ($u->ACT_USU)
                            <span class="badge badge--activo">Activo</span>
                        @else
                            <span class="badge badge--inactivo">Inactivo</span>
                        @endif
                    </td>
                    <td class="acciones-celda">
                        <x-button :href="route('usuarios.edit', $u)" variant="outline" class="btn--sm">Editar</x-button>
                        @if ($u->ACT_USU && ! $u->is(auth()->user()))
                            <x-delete-button :action="route('usuarios.destroy', $u)" mensaje="¿Desactivar a {{ $u->NOM_USU }}? Ya no podrá iniciar sesión.">Desactivar</x-delete-button>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="vacio">No se encontraron usuarios.</td></tr>
            @endforelse
        </x-table>

        {{ $usuarios->links('partials.paginacion') }}
    </x-card>
</x-layouts.app>