<x-layouts.app title="Incidencia #{{ $incidencia->COD_INC }}">
    @php($sig = \App\Models\Incidencia::SIGUIENTE[$incidencia->ESD_INC] ?? null)

    <x-page-header :titulo="'Incidencia #'.$incidencia->COD_INC" :subtitulo="\App\Models\Incidencia::TIPOS[$incidencia->TIP_INC]">
        <x-button :href="route('incidencias.index')" variant="outline">Volver</x-button>
        @if ($incidencia->ESD_INC !== 'cerrada')
            <x-button :href="route('incidencias.edit', $incidencia)" variant="outline">Editar</x-button>
        @endif
    </x-page-header>

    <x-card>
        <dl class="detalle">
            <div><dt>Estado</dt><dd><span class="badge badge--inc-{{ $incidencia->ESD_INC }}">{{ \App\Models\Incidencia::ESTADOS[$incidencia->ESD_INC] }}</span></dd></div>
            <div><dt>Registrada</dt><dd>{{ $incidencia->FEC_INC?->format('d/m/Y H:i') }}</dd></div>
            <div><dt>Registrada por</dt><dd>{{ $incidencia->registrador?->NOM_USU ?? '—' }}</dd></div>
            <div><dt>Abastecimiento</dt>
                <dd>
                    @if ($incidencia->abastecimiento)
                        <a href="{{ route('abast.gestion.show', $incidencia->abastecimiento) }}">#{{ $incidencia->COD_ABA }}</a>
                        — {{ $incidencia->abastecimiento->solicitud?->estacion?->NOM_EST }}
                    @else — @endif
                </dd>
            </div>
            <div><dt>Tanque</dt><dd>{{ $incidencia->tanque ? $incidencia->tanque->NOM_TAN.' ('.$incidencia->tanque->planta?->NOM_PLA.')' : '—' }}</dd></div>
            <div><dt>Resuelta el</dt><dd>{{ $incidencia->FRS_INC?->format('d/m/Y H:i') ?? '—' }}</dd></div>
        </dl>
        <h3 class="card__subtitulo">Descripción</h3>
        <p>{{ $incidencia->DES_INC }}</p>
        @if ($incidencia->ACC_INC)
            <h3 class="card__subtitulo">Acción tomada</h3>
            <p>{{ $incidencia->ACC_INC }}</p>
        @endif
    </x-card>

    @if ($sig)
        <x-card>
            <h2 class="card__titulo">Pasar a «{{ \App\Models\Incidencia::ESTADOS[$sig] }}»</h2>
            <form method="POST" action="{{ route('incidencias.avanzar', $incidencia) }}" class="formulario">
                @csrf
                @if ($sig === 'resuelta')
                    <x-textarea name="ACC_INC" label="Acción tomada" :value="$incidencia->ACC_INC" maxlength="2000" required
                                hint="Explica cómo se resolvió. Es obligatorio para marcarla como resuelta." />
                @endif
                <x-button variant="primary">Pasar a {{ \App\Models\Incidencia::ESTADOS[$sig] }}</x-button>
            </form>
            <p class="sec">El orden es fijo: detectada → en revisión → resuelta → cerrada.</p>
        </x-card>
    @endif
</x-layouts.app>