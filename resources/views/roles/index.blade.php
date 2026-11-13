<x-layouts.app title="Roles">
    <x-page-header titulo="Roles" subtitulo="Roles del sistema, sus permisos y cuántos usuarios tiene cada uno" />

    <x-card>
        <x-table>
            <x-slot:head>
                <th>Rol</th>
                <th>Código</th>
                <th>Usuarios</th>
                <th>Permisos</th>
            </x-slot:head>

            @forelse ($roles as $rol)
                <tr>
                    <td>{{ $rol->nombre }}</td>
                    <td class="sec">{{ $rol->DES_ROL }}</td>
                    <td>{{ $rol->usuarios_count }}</td>
                    <td class="sec">{{ ($permisos[$rol->DES_ROL] ?? collect())->implode(', ') ?: '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="vacio">No hay roles registrados.</td></tr>
            @endforelse
        </x-table>
    </x-card>
</x-layouts.app>
