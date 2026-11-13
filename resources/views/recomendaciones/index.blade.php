<x-layouts.app title="Recomendacion de abastecimiento">
    <x-page-header titulo="Recomendacion de abastecimiento"
                   subtitulo="Elige un punto de referencia: se toman las 3 estaciones mas cercanas y se decide a cual enviar la cisterna" />

    @if ($sinCoordenadas > 0)
        <div class="alerta alerta--error" role="alert">
            {{ $sinCoordenadas }} {{ $sinCoordenadas === 1 ? 'estacion activa no tiene' : 'estaciones activas no tienen' }}
            ubicacion (latitud y longitud) y no entran en la busqueda.
            Pega su enlace de Google Maps en Estaciones ir a Editar, o corre <strong>php artisan estaciones:coordenadas</strong>.
        </div>
    @endif

    <x-card>
        <form method="GET" action="{{ route('recomendaciones.index') }}" class="formulario">
            <div class="grid-2">
                <x-select name="estacion" label="Punto de referencia (estacion)" :options="$referencias"
                          :selected="$referencia?->COD_EST" placeholder="Selecciona una estacion" required />
                <x-select name="dias" label="Historial de ventas a considerar" :options="$periodos" :selected="$dias" />
            </div>
            <x-select name="combustible" label="Combustible (para mostrar la existencia)" :options="$combustibles" :selected="$combustible" />
            <div class="acciones">
                <x-button variant="primary">Buscar y analizar</x-button>
            </div>
        </form>
    </x-card>

    @if ($referencia && $candidatas->isEmpty())
        <x-card>La estacion ({{ $referencia->NOM_EST }}) todavia no tiene ubicacion (latitud y longitud).</x-card>
    @elseif ($candidatas->isNotEmpty())
        @php
          $letras = ['A', 'B', 'C'];
        @endphp

        <x-card>
            <h2 class="card__titulo">Estaciones candidatas cerca de ({{ $referencia->NOM_EST }})</h2>

            <x-table>
                <x-slot:head>
                    <th></th>
                    @foreach ($candidatas as $i => $e)
                        <th>Estacion {{ $letras[$i] }}</th>
                    @endforeach
                </x-slot:head>

                <tr>
                    <th>Nombre</th>
                    @foreach ($candidatas as $e)<td><strong>{{ $e->NOM_EST }}</strong></td>@endforeach
                </tr>
                <tr>
                    <th>Zona</th>
                    @foreach ($candidatas as $e)<td>{{ $e->ZON_EST ?: '-' }}</td>@endforeach
                </tr>
                <tr>
                    <th>Distancia al punto</th>
                    @foreach ($candidatas as $e)
                        <td>{{ $e->distancia_km < 0.01 ? 'Es el punto de referencia' : number_format($e->distancia_km, 2, ',', '.').' km' }}</td>
                    @endforeach
                </tr>
                <tr>
                    <th>Existencia de {{ $combustibles[$combustible] ?? 'combustible' }}</th>
                    @foreach ($candidatas as $e)
                        <td>{{ number_format((float) ($existencias[$e->COD_EST] ?? 0), 0, ',', '.') }} L</td>
                    @endforeach
                </tr>
                <tr>
                    <th>Estado</th>
                    @foreach ($candidatas as $e)
                        <td><span class="badge badge--{{ $e->ESD_EST }}">{{ \App\Models\Estacion::ESTADOS[$e->ESD_EST] ?? $e->ESD_EST }}</span></td>
                    @endforeach
                </tr>
                <tr>
                    <th>Ubicacion</th>
                    @foreach ($candidatas as $e)
                        <td><x-button :href="$e->urlComoLlegar()" variant="outline" class="btn--sm" target="_blank" rel="noopener noreferrer">Como llegar</x-button></td>
                    @endforeach
                </tr>
            </x-table>

            <p class="sec">Las distancias son en linea recta desde el punto de referencia.
                @if ($candidatas->count() < 3) Solo hay {{ $candidatas->count() }} estacion(es) con ubicacion registrada. @endif
            </p>
        </x-card>

        <x-card>
            <h2 class="card__titulo">Tabla de decisiones: vehiculos atendidos por dia segun la demanda</h2>

            @if (! $analisis['hay_datos'])
                <p>todavia no hay ventas a vehiculos de estas estaciones en el periodo elegido, asi que no se puede calcular.</p>
            @else
                @php
                    $nombres = ['alta' => 'ALTA', 'media' => 'MEDIA', 'baja' => 'BAJA'];
                @endphp

                <x-table>
                    <x-slot:head>
                        <th></th>
                        @foreach ($nombres as $n)<th>{{ $n }}</th>@endforeach
                        <th>Valor esperado</th>
                        <th>Peor caso (Wald)</th>
                    </x-slot:head>

                    <tr>
                        <th>Probabilidad (frecuencia historica)</th>
                        @foreach ($nombres as $estado => $n)
                            <td>{{ number_format($analisis['probabilidad'][$estado] * 100, 1, ',', '.') }} %
                                <span class="sec">({{ $analisis['dias_por_estado'][$estado] }} dias)</span></td>
                        @endforeach
                        <td></td><td></td>
                    </tr>

                    @foreach ($candidatas as $i => $e)
                        <tr>
                            <th>Estacion {{ $letras[$i] }} - {{ $e->NOM_EST }}</th>
                            @foreach ($nombres as $estado => $n)
                                <td>
                                    @if ($analisis['pagos'][$e->COD_EST][$estado] === null)
                                        -
                                    @else
                                        {{ number_format($analisis['pagos'][$e->COD_EST][$estado], 0, ',', '.') }} vehiculos
                                    @endif
                                </td>
                            @endforeach
                            <td @class(['sec' => $analisis['mejor_valor_esperado'] !== $e->COD_EST])>
                                @if ($analisis['mejor_valor_esperado'] === $e->COD_EST)<strong>@endif
                                {{ number_format($analisis['valor_esperado'][$e->COD_EST], 1, ',', '.') }}
                                @if ($analisis['mejor_valor_esperado'] === $e->COD_EST)</strong> <span class="badge badge--activo">Mayor</span>@endif
                            </td>
                            <td @class(['sec' => $analisis['mejor_wald'] !== $e->COD_EST])>
                                @if ($analisis['mejor_wald'] === $e->COD_EST)<strong>@endif
                                {{ number_format($analisis['minimo'][$e->COD_EST], 0, ',', '.') }}
                                @if ($analisis['mejor_wald'] === $e->COD_EST)</strong> <span class="badge badge--activo">Mayor</span>@endif
                            </td>
                        </tr>
                    @endforeach
                </x-table>

                @php
                    $porId = $candidatas->keyBy('COD_EST');
                    $letraDe = $candidatas->pluck('COD_EST')->flip()->map(fn ($i) => $letras[$i]);
                @endphp

                <div class="detalle">
                    <p><strong>Valor esperado:</strong> conviene enviar la cisterna a la
                        <strong>Estacion {{ $letraDe[$analisis['mejor_valor_esperado']] }}
                        ({{ $porId[$analisis['mejor_valor_esperado']]->NOM_EST }})</strong>, que atendera en promedio
                        {{ number_format($analisis['valor_esperado'][$analisis['mejor_valor_esperado']], 1, ',', '.') }} vehiculos por dia.</p>
                    <p><strong>Wald (criterio conservador):</strong> la opcion con el mejor peor escenario es la
                        <strong>Estacion {{ $letraDe[$analisis['mejor_wald']] }}
                        ({{ $porId[$analisis['mejor_wald']]->NOM_EST }})</strong>, que aun con demanda baja atiende
                        {{ number_format($analisis['minimo'][$analisis['mejor_wald']], 0, ',', '.') }} vehiculos por dia.</p>
                </div>

                <p class="sec">
                    Se usaron {{ $analisis['dias_con_ventas'] }} dis con ventas (promedio de {{ number_format($analisis['promedio_diario'], 0, ',', '.') }}
                    vehiculos por dia entre las 3 estaciones). Un dia es de demanda ALTA si llego al 125 % del promedio o mas,
                    BAJA si fue 75 % o menos, y MEDIA en los demas casos. Cada venta registrada cuenta como un vehiculo atendido.
                    Esta tabla es de apoyo y no reemplaza el criterio del subadministrador.
                </p>
            @endif
        </x-card>
    @endif
</x-layouts.app>