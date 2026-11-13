<x-layouts.app title="Mis envíos">
    <x-page-header titulo="Mis envíos" subtitulo="Entregas de combustible asignadas a tu cisterna" />

    @if (! $conductor)
        <x-card>Tu usuario todavía no está registrado como conductor. Pide al administrador que lo registre en <strong>Conductores</strong>.</x-card>
    @elseif (! $cisterna)
        <x-card>Aún no tienes una cisterna asignada. Pide al administrador que te asigne una.</x-card>
    @else
        <x-card>
            <dl class="detalle">
                <div><dt>Cisterna</dt><dd>{{ $cisterna->PLC_CIS }}</dd></div>
                <div><dt>Combustible</dt><dd>{{ $cisterna->combustible?->DES_COM }}</dd></div>
                <div><dt>Capacidad</dt><dd>{{ number_format($cisterna->CAP_CIS, 0, ',', '.') }} L</dd></div>
                <div><dt>Estado</dt><dd>{{ \App\Models\Cisterna::ESTADOS[$cisterna->ESD_CIS] ?? $cisterna->ESD_CIS }}</dd></div>
            </dl>
        </x-card>

        <x-card>
            <h2 class="card__titulo">Envíos por hacer ({{ $activos->count() }})</h2>
            <x-table>
                <x-slot:head><th>N.º</th><th>Estación de destino</th><th>Combustible</th><th>Litros</th><th>Estado</th><th></th></x-slot:head>
                @forelse ($activos as $a)
                    <tr>
                        <td>#{{ $a->COD_ABA }}</td>
                        <td>{{ $a->solicitud?->estacion?->NOM_EST }}</td>
                        <td>{{ $a->combustible?->DES_COM }}</td>
                        <td><strong>{{ number_format($a->CAN_ABA, 0, ',', '.') }}</strong></td>
                        <td><span class="badge badge--aba-{{ $a->ESD_ABA }}">{{ \App\Models\Abastecimiento::ESTADOS[$a->ESD_ABA] }}</span></td>
                        <td class="acciones-celda"><x-button :href="route('conductor.envios.show', $a)" variant="success" class="btn--sm">Abrir</x-button></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="vacio">No tienes envíos pendientes.</td></tr>
                @endforelse
            </x-table>
        </x-card>

        <x-card>
            <h2 class="card__titulo">Últimas entregas</h2>
            <x-table>
                <x-slot:head><th>N.º</th><th>Estación</th><th>Litros pedidos</th><th>Recibidos</th><th>Llegada</th><th></th></x-slot:head>
                @forelse ($recientes as $a)
                    <tr>
                        <td>#{{ $a->COD_ABA }}</td>
                        <td>{{ $a->solicitud?->estacion?->NOM_EST }}</td>
                        <td>{{ number_format($a->CAN_ABA, 0, ',', '.') }}</td>
                        <td>{{ $a->CRE_ABA !== null ? number_format($a->CRE_ABA, 2, ',', '.') : '—' }}</td>
                        <td class="sec">{{ $a->HRL_ABA?->format('d/m/Y H:i') ?? '—' }}</td>
                        <td class="acciones-celda"><x-button :href="route('conductor.envios.show', $a)" variant="ghost" class="btn--sm">Ver</x-button></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="vacio">Todavía no hiciste entregas.</td></tr>
                @endforelse
            </x-table>
        </x-card>
    @endif
</x-layouts.app>