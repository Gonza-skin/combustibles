<x-layouts.app title="Despacho #{{ $abastecimiento->COD_ABA }}">
    <x-page-header :titulo="'Despacho del envío #'.$abastecimiento->COD_ABA" :subtitulo="'Destino: '.$abastecimiento->solicitud?->estacion?->NOM_EST">
        <x-button :href="route('deposito.despachos.index')" variant="outline">Volver</x-button>
    </x-page-header>

    <x-card>
        <dl class="detalle">
            <div><dt>Estado</dt><dd><span class="badge badge--aba-{{ $abastecimiento->ESD_ABA }}">{{ \App\Models\Abastecimiento::ESTADOS[$abastecimiento->ESD_ABA] }}</span></dd></div>
            <div><dt>Combustible</dt><dd>{{ $abastecimiento->combustible?->DES_COM }}</dd></div>
            <div><dt>Litros pedidos</dt><dd>{{ number_format($abastecimiento->CAN_ABA, 2, ',', '.') }} L</dd></div>
            <div><dt>Cisterna</dt><dd>{{ $abastecimiento->cisterna?->PLC_CIS ?? '—' }}@if ($abastecimiento->cisterna) ({{ number_format($abastecimiento->cisterna->CAP_CIS, 0, ',', '.') }} L)@endif</dd></div>
            <div><dt>Conductor</dt><dd>{{ $abastecimiento->cisterna?->conductor?->NOM_CON ?? '—' }}</dd></div>
            <div><dt>Cargado</dt><dd>{{ $abastecimiento->CCA_ABA !== null ? number_format($abastecimiento->CCA_ABA, 2, ',', '.').' L' : '—' }}</dd></div>
            <div><dt>Tanque de origen</dt><dd>{{ $abastecimiento->tanque ? $abastecimiento->tanque->NOM_TAN.' ('.$abastecimiento->tanque->planta?->NOM_PLA.')' : '—' }}</dd></div>
        </dl>
    </x-card>

    @if ($puedeCargar)
        <x-card>
            <h2 class="card__titulo">Cargar la cisterna</h2>
            @if ($tanques->isEmpty())
                <p class="sec">No hay tanques disponibles con {{ $abastecimiento->combustible?->DES_COM }}: deben estar operativos, en una planta activa y con nivel mayor que 0.</p>
            @else
                <form method="POST" action="{{ route('deposito.despachos.cargar', $abastecimiento) }}" class="formulario">
                    @csrf
                    <x-select name="COD_TAN" label="Tanque de origen" placeholder="Selecciona un tanque" required
                        :options="$tanques->mapWithKeys(fn ($t) => [$t->COD_TAN => $t->NOM_TAN.' — '.$t->planta?->NOM_PLA.' ('.number_format($t->NIV_TAN, 0, ',', '.').' L disponibles)'])->all()" />
                    <x-input name="CCA_ABA" type="number" step="0.01" min="0.01" label="Litros a cargar" :value="$abastecimiento->CAN_ABA" required
                             :hint="'Pedido: '.number_format($abastecimiento->CAN_ABA, 0, ',', '.').' L. Capacidad de la cisterna: '.number_format($abastecimiento->cisterna->CAP_CIS, 0, ',', '.').' L.'" />
                    <x-button variant="success">Cargar cisterna</x-button>
                </form>
            @endif
        </x-card>
    @endif
</x-layouts.app>