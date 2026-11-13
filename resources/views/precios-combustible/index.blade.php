<x-layouts.app title="Precios de combustible">
    <x-page-header titulo="Precios de combustible"
                   subtitulo="Precio oficial por litro. Las estaciones lo usan automáticamente al registrar una venta." />

    <x-card>
        <x-table>
            <x-slot:head>
                <th>Combustible</th>
                <th>Precio por litro</th>
                <th></th>
            </x-slot:head>

            @forelse ($combustibles as $c)
                <tr>
                    <td>{{ $c->DES_COM }}</td>
                    <td><strong>Bs {{ number_format($c->PRE_COM, 2, ',', '.') }}</strong></td>
                    <td class="acciones-celda">
                        <x-button :href="route('precios-combustible.edit', $c)" variant="outline" class="btn--sm">Editar precio</x-button>
                    </td>
                </tr>
            @empty
                <tr><td colspan="3" class="vacio">No hay combustibles registrados.</td></tr>
            @endforelse
        </x-table>
    </x-card>

    <x-card>
        <div class="tarjeta__titulo"><h2>Últimos cambios de precio</h2></div>
        <x-table>
            <x-slot:head>
                <th>Fecha</th>
                <th>Combustible</th>
                <th>Precio anterior</th>
                <th>Precio nuevo</th>
                <th>Cambiado por</th>
            </x-slot:head>

            @forelse ($historial as $h)
                <tr>
                    <td class="sec">{{ \Illuminate\Support\Carbon::parse($h->FEC_HIS)->format('d/m/Y H:i') }}</td>
                    <td>{{ $h->combustible?->DES_COM }}</td>
                    <td>Bs {{ number_format($h->PRE_ANT, 2, ',', '.') }}</td>
                    <td><strong>Bs {{ number_format($h->PRE_NUE, 2, ',', '.') }}</strong></td>
                    <td>{{ $h->usuario?->NOM_USU ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="vacio">Todavía no se cambió ningún precio.</td></tr>
            @endforelse
        </x-table>
    </x-card>
</x-layouts.app>