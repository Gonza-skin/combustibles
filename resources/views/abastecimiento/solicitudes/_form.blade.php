<form method="POST" action="{{ $accion }}" class="formulario">
    @csrf
    @if ($solicitud->exists) @method('PUT') @endif

    <div class="grid-2">
        <x-select name="COD_COM" label="Combustible" :options="$combustibles" :selected="$solicitud->COD_COM" placeholder="Selecciona un combustible" required />
        <x-input name="CAN_SOL" type="number" step="0.01" min="0.01" label="Cantidad a pedir (litros)" :value="$solicitud->CAN_SOL" required
                 :hint="'Capacidad máxima de tu estación: '.number_format($maximo, 0, ',', '.').' L.'" />
    </div>

    @if ($existencias)
        <p class="sec">Existencia actual:
            @foreach ($existencias as $nombre => $litros){{ $nombre }} {{ number_format($litros, 0, ',', '.') }} L{{ $loop->last ? '' : ' · ' }}@endforeach
        </p>
    @endif

    <div class="acciones">
        <x-button variant="primary">Guardar</x-button>
        <x-button :href="route('abast.solicitudes.index')" variant="outline">Cancelar</x-button>
    </div>
</form>