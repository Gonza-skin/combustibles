<x-layouts.app title="Alertas">
    <x-page-header titulo="Alertas" :subtitulo="$sinLeer.' sin leer'">
        <form method="POST" action="{{ route('alertas.generar') }}">@csrf <x-button variant="success">Revisar ahora</x-button></form>
        @if ($sinLeer > 0)
            <form method="POST" action="{{ route('alertas.leer-todas') }}">@csrf <x-button variant="outline">Marcar todas como leídas</x-button></form>
        @endif
    </x-page-header>

    <x-card>
        <form method="GET" action="{{ route('alertas.index') }}" class="filtros">
            <x-select name="tipo" label="Tipo" :options="\App\Models\Alerta::TIPOS" :selected="$filtros['tipo'] ?? null" placeholder="Todos" />
            <x-select name="nivel" label="Nivel" :options="\App\Models\Alerta::NIVELES" :selected="$filtros['nivel'] ?? null" placeholder="Todos" />
            <x-select name="leida" label="Lectura" :options="['0' => 'Sin leer', '1' => 'Leídas']" :selected="$filtros['leida'] ?? null" placeholder="Todas" />
            <div class="filtros__botones">
                <x-button variant="primary">Filtrar</x-button>
                <x-button :href="route('alertas.index')" variant="ghost">Limpiar</x-button>
            </div>
        </form>

        <x-table>
            <x-slot:head>
                <th>Fecha</th><th>Nivel</th><th>Tipo</th><th>Mensaje</th><th></th>
            </x-slot:head>
            @forelse ($alertas as $a)
                <tr class="{{ $a->LEI_ALE ? 'fila-leida' : '' }}">
                    <td class="sec">{{ $a->FEC_ALE?->format('d/m/Y H:i') }}</td>
                    <td><span class="badge badge--niv-{{ $a->NIV_ALE }}">{{ \App\Models\Alerta::NIVELES[$a->NIV_ALE] }}</span></td>
                    <td>{{ \App\Models\Alerta::TIPOS[$a->TIP_ALE] ?? $a->TIP_ALE }}</td>
                    <td>{{ $a->MEN_ALE }}</td>
                    <td class="acciones-celda">
                        @unless ($a->LEI_ALE)
                            <form method="POST" action="{{ route('alertas.leer', $a) }}">@csrf <x-button variant="outline" class="btn--sm">Marcar leída</x-button></form>
                        @endunless
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="vacio">No hay alertas. Pulsa «Revisar ahora» para buscar nuevas.</td></tr>
            @endforelse
        </x-table>

        {{ $alertas->links('partials.paginacion') }}
    </x-card>
</x-layouts.app>