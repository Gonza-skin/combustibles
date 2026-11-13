<x-layouts.app title="Envío #{{ $abastecimiento->COD_ABA }}">
    <x-page-header :titulo="'Envío #'.$abastecimiento->COD_ABA" :subtitulo="'Destino: '.$abastecimiento->solicitud?->estacion?->NOM_EST">
        <x-button :href="route('conductor.envios.index')" variant="outline">Volver</x-button>
    </x-page-header>

    <x-card>
        <dl class="detalle">
            <div><dt>Estado</dt><dd><span class="badge badge--aba-{{ $abastecimiento->ESD_ABA }}">{{ \App\Models\Abastecimiento::ESTADOS[$abastecimiento->ESD_ABA] }}</span></dd></div>
            <div><dt>Combustible</dt><dd>{{ $abastecimiento->combustible?->DES_COM }}</dd></div>
            <div><dt>Litros pedidos</dt><dd>{{ number_format($abastecimiento->CAN_ABA, 2, ',', '.') }} L</dd></div>
            <div><dt>Dirección</dt><dd>{{ $abastecimiento->solicitud?->estacion?->DIR_EST ?: '—' }}</dd></div>
            <div><dt>Salida</dt><dd>{{ $abastecimiento->HSA_ABA?->format('d/m/Y H:i') ?? '—' }}</dd></div>
            <div><dt>Llegada estimada</dt><dd>{{ $abastecimiento->HEL_ABA?->format('d/m/Y H:i') ?? '—' }}</dd></div>
            <div><dt>Litros cargados</dt><dd>{{ $abastecimiento->CCA_ABA !== null ? number_format($abastecimiento->CCA_ABA, 2, ',', '.').' L' : '—' }}</dd></div>
            <div><dt>Litros recibidos</dt><dd>{{ $abastecimiento->CRE_ABA !== null ? number_format($abastecimiento->CRE_ABA, 2, ',', '.').' L' : '—' }}</dd></div>
        </dl>
        @if ($abastecimiento->solicitud?->estacion)
            <p class="sec"><a href="{{ $abastecimiento->solicitud->estacion->urlComoLlegar() }}" target="_blank" rel="noopener">Cómo llegar a la estación (Google Maps)</a></p>
        @endif
    </x-card>

        @if ($abastecimiento->ESD_ABA === 'asignado')
        <x-card>
            <h2 class="card__titulo">Iniciar viaje</h2>
            <form method="POST" action="{{ route('conductor.envios.iniciar', $abastecimiento) }}" class="formulario">
                @csrf
                @if ($abastecimiento->CCA_ABA !== null)
                    <p>El depósito ya cargó tu cisterna con <strong>{{ number_format($abastecimiento->CCA_ABA, 2, ',', '.') }} L</strong>.</p>
                @else
                    <x-input name="CCA_ABA" type="number" step="0.01" min="0.01" label="Litros cargados en la planta" :value="$abastecimiento->CAN_ABA" required
                             :hint="'Capacidad de tu cisterna: '.number_format($abastecimiento->cisterna->CAP_CIS, 0, ',', '.').' L.'" />
                @endif
                <x-button variant="success">Salir hacia la estación</x-button>
            </form>
        </x-card>

   
    @elseif ($abastecimiento->ESD_ABA === 'en_transito')
        <x-card>
            <h2 class="card__titulo">Confirmar entrega</h2>
            <form method="POST" action="{{ route('conductor.envios.entregar', $abastecimiento) }}" class="formulario">
                @csrf
                <x-input name="CRE_ABA" type="number" step="0.01" min="0.01" label="Litros recibidos por la estación" :value="$abastecimiento->CCA_ABA ?? $abastecimiento->CAN_ABA" required
                         hint="Si es menos de lo cargado, se avisará automáticamente al supervisor." />
                <x-button variant="success">Confirmar entrega</x-button>
            </form>
        </x-card>
    @endif

    @if (in_array($abastecimiento->ESD_ABA, ['asignado', 'en_transito'], true))
        <x-card>
            <h2 class="card__titulo">Reportar un problema</h2>
            <form method="POST" action="{{ route('conductor.envios.incidencia', $abastecimiento) }}" class="formulario">
                @csrf
                <x-select name="TIP_INC" label="Tipo de problema" placeholder="Selecciona"
                          :options="['retraso' => 'Retraso', 'diferencia_combustible' => 'Diferencia de combustible', 'otro' => 'Otro']" required />
                <x-textarea name="DES_INC" label="¿Qué pasó?" maxlength="2000" required />
                <x-button variant="outline">Enviar reporte</x-button>
            </form>
        </x-card>
    @endif
</x-layouts.app>