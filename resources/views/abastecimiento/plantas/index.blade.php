<x-layouts.app title="Plantas">
    <x-page-header titulo="Plantas de depósito" subtitulo="Lugares donde se almacena el combustible">
        <x-button :href="route('abast.plantas.create')" variant="success">+ Nueva planta</x-button>
    </x-page-header>

    <x-card>
        <x-table>
            <x-slot:head>
                <th>Planta</th><th>Ubicación</th><th>Tanques</th><th>Estado</th><th></th>
            </x-slot:head>
            @forelse ($plantas as $p)
                <tr>
                    <td><strong>{{ $p->NOM_PLA }}</strong></td>
                    <td>{{ $p->UBI_PLA ?: '—' }}</td>
                    <td>{{ $p->tanques_count }}</td>
                    <td><span class="badge badge--{{ $p->ACT_PLA ? 'activo' : 'inactivo' }}">{{ $p->ACT_PLA ? 'Activa' : 'Inactiva' }}</span></td>
                    <td class="acciones-celda">
                        <x-button :href="route('abast.tanques.index', ['planta' => $p->COD_PLA])" variant="ghost" class="btn--sm">Ver tanques</x-button>
                        <x-button :href="route('abast.plantas.edit', $p)" variant="outline" class="btn--sm">Editar</x-button>
                        <x-delete-button :action="route('abast.plantas.destroy', $p)" mensaje="¿Eliminar la planta {{ $p->NOM_PLA }}?">Eliminar</x-delete-button>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="vacio">Aún no hay plantas registradas.</td></tr>
            @endforelse
        </x-table>
    </x-card>
</x-layouts.app>