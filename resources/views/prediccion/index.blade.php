<x-layouts.app title="Predicción de disponibilidad">
    <x-page-header titulo="Predicción de disponibilidad"
                   subtitulo="Cadenas de Markov: con los reportes de los ciudadanos se estima qué estado tendrá cada estación en los próximos días" />

    @php
        $claseEstado = [0 => 'normal', 1 => 'nivel_bajo', 2 => 'sin_combustible'];
        $pct = fn ($v) => $v === null ? '—' : number_format($v * 100, 1, ',', '.').' %';
    @endphp

    <x-card>
        <form method="GET" action="{{ route('prediccion.index') }}" class="formulario">
            <div class="grid-2">
                <x-select name="estacion" label="Estación (para ver el detalle)" :options="$estaciones"
                          :selected="$seleccionada['estacion']->COD_EST ?? null" placeholder="Todas (solo ranking)" />
                <div class="grid-2">
                    <x-input name="cantidad" label="Predecir a" type="number" :value="$cantidad" min="1" max="365" />
                    <x-select name="unidad" label="Unidad" :options="$unidades" :selected="$unidad" />
                </div>
            </div>
            <div class="grid-2">
                <x-input name="umbral" label="Umbral de alerta (% de probabilidad de agotarse)" type="number" :value="$umbral"
                         min="1" max="100" step="1" hint="Si la probabilidad de estar agotada supera este valor, se marca ALERTA." />
                <x-select name="historia" label="Historial de reportes a usar" :options="$historias" :selected="$historia" />
            </div>
            <x-select name="hoy" label="Estado de hoy" :options="$estados" :selected="$hoy"
                      placeholder="El último reportado por los ciudadanos" hint="Sirve para simular: ¿y si hoy estuviera agotada?" />
            <div class="acciones">
                <x-button variant="primary">Calcular</x-button>
            </div>
        </form>
    </x-card>

    @if ($seleccionada)
        @php
            $r = $seleccionada;
        @endphp
        <x-card>
            <h2 class="card__titulo">{{ $r['estacion']->NOM_EST }}</h2>

            @if ($r['transiciones'] === 0)
                <p>Todavía no hay reportes de días seguidos para esta estación, así que no se puede armar la matriz.</p>
            @else
                @unless ($r['suficiente'])
                    <div class="alerta alerta--error" role="alert">
                        Hay pocos datos ({{ $r['transiciones'] }} transiciones observadas
                        @if ($r['filas_sin_datos']) y falta algún estado @endif):
                        el resultado es solo orientativo.
                    </div>
                @endunless

                <h3 class="sec">Matriz de transición P (hoy → mañana)</h3>
                <x-table>
                    <x-slot:head>
                        <th>Hoy / Mañana</th>
                        @foreach ($estados as $nombre)<th>{{ $nombre }}</th>@endforeach
                        <th>Veces observado</th>
                    </x-slot:head>
                    @foreach ($estados as $i => $nombre)
                        <tr>
                            <th>{{ $nombre }}</th>
                            @foreach ($estados as $j => $_)
                                <td>{{ number_format($r['P'][$i][$j], 2, ',', '.') }}</td>
                            @endforeach
                            <td class="sec">{{ array_sum($r['conteos'][$i]) }}</td>
                        </tr>
                    @endforeach
                </x-table>
                <p class="sec">Cada fila suma 1 y sale de pij = nij / ni (cuántas veces se pasó del estado i al j, entre días seguidos).
                    @if ($r['filas_sin_datos']) Los estados que nunca se observaron se rellenan en partes iguales. @endif</p>

                <h3 class="sec">Estado a los {{ $cantidad }} {{ $unidades[$unidad] ? strtolower($unidades[$unidad]) : 'días' }}
                    — hoy: <span class="badge badge--{{ $claseEstado[$r['estado_actual']] ?? 'normal' }}">{{ $estados[$r['estado_actual']] ?? 'sin reportes' }}</span>
                    @if ($r['ultimo_dia'] && $hoy === null)<span class="sec">(último reporte: {{ \Illuminate\Support\Carbon::parse($r['ultimo_dia'])->format('d/m/Y') }})</span>@endif
                </h3>

                @if ($r['a_n'])
                    <x-table>
                        <x-slot:head>
                            @foreach ($estados as $nombre)<th>{{ $nombre }}</th>@endforeach
                        </x-slot:head>
                        <tr>
                            @foreach ($estados as $j => $_)<td><strong>{{ $pct($r['a_n'][$j]) }}</strong></td>@endforeach
                        </tr>
                    </x-table>
                    <p>
                        @if ($r['prob_agotado'] * 100 >= $umbral)
                            <span class="badge badge--sin_combustible">ALERTA</span>
                        @endif
                        Probabilidad de estar agotada en {{ $cantidad }} {{ $unidades[$unidad] ? strtolower($unidades[$unidad]) : 'días' }}: <strong>{{ $pct($r['prob_agotado']) }}</strong>
                        (umbral {{ number_format($umbral, 0) }} %).
                    </p>
                @endif

                <h3 class="sec">Largo plazo y tiempo hasta agotarse</h3>
                <x-table>
                    <x-slot:head><th>Pregunta</th><th>Resultado</th></x-slot:head>
                    <tr><th>Porcentaje del tiempo en cada estado (π)</th>
                        <td>@foreach ($estados as $j => $nombre){{ $nombre }}: <strong>{{ $pct($r['pi'][$j]) }}</strong>@unless($loop->last) · @endunless @endforeach</td></tr>
                    <tr><th>Días agotada por mes</th>
                        <td><strong>{{ number_format($r['pi'][2] * 30, 1, ',', '.') }} días</strong></td></tr>
                    <tr><th>Días hasta el primer agotamiento, desde Disponible</th>
                        <td>{{ $r['dias_hasta_agotarse'] ? number_format($r['dias_hasta_agotarse'][0], 2, ',', '.').' días' : 'No se puede estimar' }}</td></tr>
                    <tr><th>Días hasta el primer agotamiento, desde Fila larga</th>
                        <td>{{ $r['dias_hasta_agotarse'] ? number_format($r['dias_hasta_agotarse'][1], 2, ',', '.').' días' : 'No se puede estimar' }}</td></tr>
                </x-table>
            @endif
        </x-card>
    @endif

    <x-card>
        <h2 class="card__titulo">Ranking de estaciones por riesgo de agotamiento a {{ $cantidad }} {{ $unidades[$unidad] ? strtolower($unidades[$unidad]) : 'días' }}</h2>
        <x-table>
            <x-slot:head>
                <th>#</th><th>Estación</th><th>Zona</th><th>Estado actual</th>
                <th>Prob. de agotarse</th><th>Agotada a largo plazo</th><th>Días hasta agotarse</th><th></th>
            </x-slot:head>
            @foreach ($ranking as $k => $r)
                <tr>
                    <td>{{ $k + 1 }}</td>
                    <td><a href="{{ route('prediccion.index', request()->except('estacion') + ['estacion' => $r['estacion']->COD_EST]) }}">{{ $r['estacion']->NOM_EST }}</a></td>
                    <td>{{ $r['estacion']->ZON_EST ?: '—' }}</td>
                    <td>
                        @if ($r['estado_actual'] !== null)
                            <span class="badge badge--{{ $claseEstado[$r['estado_actual']] }}">{{ $estados[$r['estado_actual']] }}</span>
                        @else — @endif
                    </td>
                    <td><strong>{{ $pct($r['prob_agotado']) }}</strong></td>
                    <td>{{ $pct($r['pi'][2] ?? null) }}</td>
                    <td>{{ $r['dias_hasta_agotarse'] ? number_format($r['dias_hasta_agotarse'][0], 1, ',', '.').' d' : '—' }}</td>
                    <td>
                        @if ($r['prob_agotado'] !== null && $r['prob_agotado'] * 100 >= $umbral)
                            <span class="badge badge--sin_combustible">ALERTA</span>
                        @elseif (! $r['suficiente'])
                            <span class="sec">pocos datos</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </x-table>
        <p class="sec">
            Los estados salen de los reportes de los ciudadanos, una vez al día: <strong>Disponible</strong> (hay combustible y la fila es normal),
            <strong>Con fila larga</strong> (hay combustible pero hay congestión) y <strong>Agotado</strong>. Si hay varios reportes el mismo día,
            gana el más frecuente. Es una herramienta de apoyo y no reemplaza el criterio del subadministrador.
        </p>
    </x-card>
</x-layouts.app>
