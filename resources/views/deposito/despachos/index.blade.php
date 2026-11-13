<x-layouts.app title="Despachos">
    <x-page-header titulo="Despachos" subtitulo="Carga de cisternas desde los tanques de la planta">
        <x-button :href="route('abast.tanques.index')" variant="outline">Ver tanques</x-button>
    </x-page-header>

    <x-card>
        <h2 class="card__titulo">Cisternas por cargar ({{ $porCargar->count() }})</h2>
        <x-table>
            <x-slot:head><th>Envío</th><th>Destino</th><th>Combustible</th><th>Litros</th><th>Cisterna</th><th></th></x-slot:head>
            @forelse ($porCargar as $a)
                <tr>
                    <td>#{{ $a->COD_ABA }}</td>
                    <td>{{ $a->solicitud?->estacion?->NOM_EST }}</td>
                    <td>{{ $a->combustible?->DES_COM }}</td>
                    <td><strong>{{ number_format($a->CAN_ABA, 0, ',', '.') }}</strong></td>
                    <td>{{ $a->cisterna?->PLC_CIS }} <span class="sec">{{ $a->cisterna?->conductor?->NOM_CON }}</span></td>
                    <td class="acciones-celda"><x-button :href="route('deposito.despachos.show', $a)" variant="success" class="btn--sm">Cargar</x-button></td>
                </tr>
            @empty
                <tr><td colspan="6" class="vacio">No hay cisternas esperando carga.</td></tr>
            @endforelse
        </x-table>
    </x-card>

    <x-card>
        <h2 class="card__titulo">Últimas cargas</h2>
        <x-table>
            <x-slot:head><th>Envío</th><th>Destino</th><th>Cargados</th><th>Tanque</th><th>Estado</th><th></th></x-slot:head>
            @forelse ($cargados as $a)
                <tr>
                    <td>#{{ $a->COD_ABA }}</td>
                    <td>{{ $a->solicitud?->estacion?->NOM_EST }}</td>
                    <td>{{ number_format($a->CCA_ABA, 2, ',', '.') }} L</td>
                    <td>{{ $a->tanque?->NOM_TAN }} <span class="sec">{{ $a->tanque?->planta?->NOM_PLA }}</span></td>
                    <td><span class="badge badge--aba-{{ $a->ESD_ABA }}">{{ \App\Models\Abastecimiento::ESTADOS[$a->ESD_ABA] }}</span></td>
                    <td class="acciones-celda"><x-button :href="route('deposito.despachos.show', $a)" variant="ghost" class="btn--sm">Ver</x-button></td>
                </tr>
            @empty
                <tr><td colspan="6" class="vacio">Todavía no se cargó ninguna cisterna.</td></tr>
            @endforelse
        </x-table>
    </x-card>
</x-layouts.app>