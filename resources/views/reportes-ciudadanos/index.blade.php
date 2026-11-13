<x-layouts.app title="Reportes ciudadanos">
    <x-page-header titulo="Reportes ciudadanos" subtitulo="Lo que los ciudadanos informan sobre filas y disponibilidad" />

    <x-card>
        <form method="GET" action="{{ route('reportes-ciudadanos.index') }}" class="filtros">
            <x-select name="estacion" label="Estación" :options="$estaciones" :selected="$filtros['estacion'] ?? null" placeholder="Todas" />
            <x-select name="fila" label="Fila" :options="\App\Models\ReporteCiudadano::FILAS" :selected="$filtros['fila'] ?? null" placeholder="Todas" />
            <x-select name="disponibilidad" label="Disponibilidad" :options="\App\Models\ReporteCiudadano::DISPONIBILIDAD" :selected="$filtros['disponibilidad'] ?? null" placeholder="Todas" />
            <div class="filtros__botones">
                <x-button variant="primary">Filtrar</x-button>
                <x-button :href="route('reportes-ciudadanos.index')" variant="ghost">Limpiar</x-button>
            </div>
        </form>

        <x-table>
            <x-slot:head>
                <th>Fecha</th><th>Estación</th><th>Fila</th><th>Disponibilidad</th><th>Espera (min)</th><th>Ciudadano</th>
            </x-slot:head>
            @forelse ($reportes as $r)
                <tr>
                    <td class="sec">{{ $r->FEC_REP?->format('d/m/Y H:i') }}</td>
                    <td>{{ $r->estacion?->NOM_EST }}</td>
                    <td>{{ \App\Models\ReporteCiudadano::FILAS[$r->FIL_REP] ?? '—' }}</td>
                    <td>{{ \App\Models\ReporteCiudadano::DISPONIBILIDAD[$r->DIS_REP] ?? '—' }}</td>
                    <td>{{ $r->TES_REP ?? '—' }}</td>
                    <td>{{ $r->ciudadano?->NOM_USU ?? 'Anónimo' }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="vacio">Todavía no hay reportes.</td></tr>
            @endforelse
        </x-table>

        {{ $reportes->links('partials.paginacion') }}
    </x-card>
</x-layouts.app>