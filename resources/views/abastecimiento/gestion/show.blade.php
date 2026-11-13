<x-layouts.app title="Abastecimiento #{{ $abastecimiento->COD_ABA }}">
    <x-page-header :titulo="'Abastecimiento #'.$abastecimiento->COD_ABA" :subtitulo="$abastecimiento->solicitud?->estacion?->NOM_EST">
        <x-button :href="route('abast.gestion.index')" variant="outline">Volver</x-button>
    </x-page-header>

    <x-card>
        <dl class="detalle">
            <div><dt>Combustible</dt><dd>{{ $abastecimiento->combustible?->DES_COM }}</dd></div>
            <div><dt>Cantidad</dt><dd>{{ number_format($abastecimiento->CAN_ABA, 2, ',', '.') }} L</dd></div>
            <div><dt>Estado</dt><dd><span class="badge badge--aba-{{ $abastecimiento->ESD_ABA }}">{{ \App\Models\Abastecimiento::ESTADOS[$abastecimiento->ESD_ABA] }}</span></dd></div>
            <div><dt>Cisterna</dt><dd>{{ $abastecimiento->cisterna?->PLC_CIS ?? 'Sin asignar' }}@if ($abastecimiento->cisterna?->conductor) · {{ $abastecimiento->cisterna->conductor->NOM_CON }} @endif</dd></div>
            <div><dt>Salida</dt><dd>{{ $abastecimiento->HSA_ABA?->format('d/m/Y H:i') ?? '—' }}</dd></div>
            <div><dt>Llegada estimada</dt><dd>{{ $abastecimiento->HEL_ABA?->format('d/m/Y H:i') ?? '—' }}</dd></div>
            <div><dt>Llegada real</dt><dd>{{ $abastecimiento->HRL_ABA?->format('d/m/Y H:i') ?? '—' }}</dd></div>
        </dl>
    </x-card>

    @if ($abastecimiento->ESD_ABA === 'pendiente')
        <x-card>
            <h2 class="card__titulo">Asignar cisterna</h2>
            @if ($cisternas->isEmpty())
                <p class="sec">No hay cisternas disponibles con ese combustible, capacidad suficiente y conductor activo.</p>
            @else
                <form method="POST" action="{{ route('abast.gestion.asignar', $abastecimiento) }}" class="formulario">
                    @csrf
                    <x-select name="COD_CIS" label="Cisterna" placeholder="Selecciona una cisterna" required
                        :options="$cisternas->mapWithKeys(fn ($c) => [$c->COD_CIS => $c->PLC_CIS.' — '.number_format($c->CAP_CIS, 0, ',', '.').' L — '.$c->conductor->NOM_CON])->all()" />
                    <x-input name="OBS_HIS" label="Observación (opcional)" maxlength="255" />
                    <x-button variant="primary">Asignar cisterna</x-button>
                </form>
            @endif
        </x-card>
    @endif

    @if ($siguientes)
        <x-card>
            <h2 class="card__titulo">Cambiar estado</h2>
            <form method="POST" action="{{ route('abast.gestion.estado', $abastecimiento) }}" class="formulario">
                @csrf
                <x-select name="ESD_ABA" label="Nuevo estado" placeholder="Selecciona"
                    :options="collect($siguientes)->mapWithKeys(fn ($e) => [$e => \App\Models\Abastecimiento::ESTADOS[$e]])->all()" required />
                <x-input name="OBS_HIS" label="Observación (opcional)" maxlength="255" />
                <x-button variant="primary">Actualizar estado</x-button>
            </form>
            @if (in_array('entregado', $siguientes, true))
                <p class="sec">Al marcar «Entregado», los litros se suman automáticamente a la existencia de la estación.</p>
            @endif
        </x-card>
    @endif

    <x-card>
        <h2 class="card__titulo">Historial de estados</h2>
        <x-table>
            <x-slot:head><th>Fecha</th><th>Estado</th><th>Usuario</th><th>Observación</th></x-slot:head>
            @foreach ($historial as $h)
                <tr>
                    <td class="sec">{{ $h->FEC_HIS?->format('d/m/Y H:i') }}</td>
                    <td><span class="badge badge--aba-{{ $h->ESD_HIS }}">{{ \App\Models\Abastecimiento::ESTADOS[$h->ESD_HIS] ?? $h->ESD_HIS }}</span></td>
                    <td>{{ $h->usuario?->NOM_USU ?? '—' }}</td>
                    <td>{{ $h->OBS_HIS ?: '—' }}</td>
                </tr>
            @endforeach
        </x-table>
    </x-card>
</x-layouts.app>