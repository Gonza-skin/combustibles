<x-layouts.app title="Ventas">
    <x-page-header titulo="Ventas" :subtitulo="'Ventas del '.$fecha->format('d/m/Y')">
        <x-button :href="route('ventas.create')" variant="success">+ Nueva venta</x-button>
    </x-page-header>

    <div class="ventas-resumen">
        @forelse ($resumen as $r)
            <x-card>
                <strong>{{ $r->combustible }}</strong>
                <div class="ventas-resumen__valor">{{ number_format($r->litros, 2, ',', '.') }} L</div>
                <div class="sec">Bs {{ number_format($r->total, 2, ',', '.') }}</div>
            </x-card>
        @empty
            <x-card>Sin ventas en esta fecha.</x-card>
        @endforelse
    </div>

    <x-card>
        <form method="GET" action="{{ route('ventas.index') }}" class="filtros">
            <x-input name="fecha" type="date" label="Fecha" :value="$fecha->format('Y-m-d')" max="{{ today()->format('Y-m-d') }}" />
            <div class="filtros__botones">
                <x-button variant="primary">Ver</x-button>
                <x-button :href="route('ventas.index')" variant="ghost">Hoy</x-button>
            </div>
        </form>

        <x-table>
            <x-slot:head>
                <th>Hora</th><th>Placa</th><th>Combustible</th><th>Litros</th><th>Precio (Bs/L)</th><th>Total (Bs)</th><th>Vendedor</th>
            </x-slot:head>
            @forelse ($ventas as $v)
                <tr>
                    <td class="sec">{{ $v->FEC_VEN?->format('H:i') }}</td>
                    <td><strong>{{ $v->automovil?->PLA_VEH }}</strong></td>
                    <td>{{ $v->combustible?->DES_COM }}</td>
                    <td>{{ number_format($v->CAN_VEN, 2, ',', '.') }}</td>
                    <td>{{ number_format($v->PRE_VEN, 2, ',', '.') }}</td>
                    <td><strong>{{ number_format($v->total(), 2, ',', '.') }}</strong></td>
                    <td>{{ $v->vendedor?->NOM_USU }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="vacio">No hay ventas en esta fecha.</td></tr>
            @endforelse
        </x-table>

        {{ $ventas->links('partials.paginacion') }}
    </x-card>
</x-layouts.app>