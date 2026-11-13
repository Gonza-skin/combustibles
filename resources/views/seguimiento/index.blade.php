<x-layouts.app title="Seguimiento de entregas">
    <x-page-header titulo="Seguimiento de entregas" subtitulo="¿Llegan a tiempo y completas? Se considera retraso pasar de 30 minutos" />

    <div class="ventas-resumen">
        <x-card>
            <strong>Entregas realizadas</strong>
            <div class="ventas-resumen__valor">{{ $resumen['entregas'] }}</div>
            <div class="sec">{{ $resumen['retrasadas'] }} con retraso</div>
        </x-card>
        <x-card>
            <strong>Puntualidad</strong>
            <div class="ventas-resumen__valor">{{ $resumen['puntualidad'] !== null ? $resumen['puntualidad'].' %' : '-' }}</div>
            <div class="sec">{{ $resumen['retrasadas'] > 0 ? 'Retraso promedio: '.$resumen['minutos_promedio'].' min' : 'Sin retrasos' }}</div>
        </x-card>
        <x-card>
            <strong>Litros faltantes</strong>
            <div class="ventas-resumen__valor">{{ number_format($resumen['litros_faltantes'], 2, ',', '.') }} L</div>
            <div class="sec">Cargados menos recibidos</div>
        </x-card>
        <x-card>
            <strong>Pendiente de revisar</strong>
            <div class="ventas-resumen__valor">{{ $alertasSinLeer }}</div>
            <div class="sec">
                alertas sin leer +
                @if (auth()->user()->tieneRol('supervisor', 'administrador'))
                    <a href="{{ route('incidencias.index') }}">{{ $incidenciasAbiertas }} incidencias abiertas</a>
                @else
                    {{ $incidenciasAbiertas }} incidencias abiertas
                @endif
            </div>
        </x-card>
    </div>

    <x-card>
        <form method="GET" action="{{ route('seguimiento.index') }}" class="filtros">
            <x-input name="desde" type="date" label="Desde" :value="$filtros['desde'] ?? null" />
            <x-input name="hasta" type="date" label="Hasta" :value="$filtros['hasta'] ?? null" />
            <x-select name="estado" label="Estado" :options="\App\Models\Abastecimiento::ESTADOS" :selected="$filtros['estado'] ?? null" placeholder="Todos" />
            <x-select name="problema" label="Problema" :selected="$filtros['problema'] ?? null" placeholder="Todos"
                      :options="['retraso' => 'Con retraso', 'diferencia' => 'Con diferencia de combustible', 'cualquiera' => 'Cualquiera de los dos']" />
            <div class="filtros__botones">
                <x-button variant="primary">Filtrar</x-button>
                <x-button :href="route('seguimiento.index')" variant="ghost">Limpiar</x-button>
            </div>
        </form>

        <x-table>
            <x-slot:head>
                <th>Envio</th><th>Estacion</th><th>Cisterna</th><th>Estado</th><th>Llegada estimada</th><th>Llegada real</th><th>Retraso</th><th>Cargados</th><th>Recibidos</th><th>Diferencia</th>
            </x-slot:head>
            @forelse ($abastecimientos as $a)
                <tr>
                    <td>#{{ $a->COD_ABA }}</td>
                    <td>{{ $a->solicitud?->estacion?->NOM_EST }}</td>
                    <td>{{ $a->cisterna?->PLC_CIS ?? '-' }} <span class="sec">{{ $a->cisterna?->conductor?->NOM_CON }}</span></td>
                    <td><span class="badge badge--aba-{{ $a->ESD_ABA }}">{{ \App\Models\Abastecimiento::ESTADOS[$a->ESD_ABA] }}</span></td>
                    <td class="sec">{{ $a->HEL_ABA?->format('d/m H:i') ?? '-' }}</td>
                    <td class="sec">{{ $a->HRL_ABA?->format('d/m H:i') ?? '-' }}</td>
                    <td>
                        @if ($a->HRL_ABA === null) -
                        @elseif ($a->RET_ABA) <span class="badge badge--aba-cancelado">{{ $a->MRE_ABA }} min</span>
                        @else <span class="badge badge--aba-entregado">A tiempo</span>
                        @endif
                    </td>
                    <td>{{ $a->CCA_ABA !== null ? number_format($a->CCA_ABA, 2, ',', '.') : '-' }}</td>
                    <td>{{ $a->CRE_ABA !== null ? number_format($a->CRE_ABA, 2, ',', '.') : '-' }}</td>
                    <td>
                        @if ($a->DIF_ABA === null) -
                        @elseif ((float) $a->DIF_ABA > 0) <span class="badge badge--aba-cancelado">-{{ number_format($a->DIF_ABA, 2, ',', '.') }} L</span>
                        @else <span class="badge badge--aba-entregado">Completa</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="10" class="vacio">No hay envios con esos filtros.</td></tr>
            @endforelse
        </x-table>

        {{ $abastecimientos->links('partials.paginacion') }}
    </x-card>
</x-layouts.app>