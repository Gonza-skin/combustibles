<x-layouts.app title="Solicitud #{{ $solicitud->COD_SOL }}">
    <x-page-header :titulo="'Solicitud #'.$solicitud->COD_SOL" :subtitulo="$solicitud->estacion?->NOM_EST">
        <x-button :href="route('abast.solicitudes.index')" variant="outline">Volver</x-button>
    </x-page-header>

    <x-card>
        <dl class="detalle">
            <div><dt>Fecha</dt><dd>{{ $solicitud->FEC_SOL?->format('d/m/Y H:i') }}</dd></div>
            <div><dt>Combustible</dt><dd>{{ $solicitud->combustible?->DES_COM }}</dd></div>
            <div><dt>Cantidad</dt><dd>{{ number_format($solicitud->CAN_SOL, 2, ',', '.') }} L</dd></div>
            <div><dt>Solicitada por</dt><dd>{{ $solicitud->solicitante?->NOM_USU ?? '—' }}</dd></div>
            <div><dt>Estado</dt><dd><span class="badge badge--sol-{{ $solicitud->ESD_SOL }}">{{ \App\Models\SolicitudAbastecimiento::ESTADOS[$solicitud->ESD_SOL] }}</span></dd></div>
            <div><dt>Abastecimiento</dt>
                <dd>
                    @if ($solicitud->abastecimiento)
                        #{{ $solicitud->abastecimiento->COD_ABA }} —
                        {{ \App\Models\Abastecimiento::ESTADOS[$solicitud->abastecimiento->ESD_ABA] }}
                        @if ($solicitud->abastecimiento->cisterna) · cisterna {{ $solicitud->abastecimiento->cisterna->PLC_CIS }} @endif
                    @else Aún sin envío @endif
                </dd>
            </div>
        </dl>
    </x-card>
</x-layouts.app>