<x-layouts.app title="Nueva venta">
    <x-page-header titulo="Nueva venta" subtitulo="El precio se toma automáticamente del precio vigente" />

    <x-card>
        <form method="POST" action="{{ route('ventas.store') }}" class="formulario">
            @csrf

            <x-input name="PLA_VEH" label="Placa del vehículo" :value="old('PLA_VEH')" maxlength="15" required autofocus
                     hint="Si la placa no está registrada, primero agrégala en Automóviles." />

            <div class="grid-2">
                <div class="campo">
                    <label for="COD_COM">Combustible</label>
                    <select id="COD_COM" name="COD_COM" required @class(['tiene-error' => $errors->has('COD_COM')])>
                        <option value="">Selecciona un combustible</option>
                        @foreach ($combustibles as $c)
                            <option value="{{ $c->COD_COM }}" @selected((string) old('COD_COM') === (string) $c->COD_COM)>
                                {{ $c->DES_COM }} — Bs {{ number_format($c->PRE_COM, 2, ',', '.') }}/L (quedan {{ number_format($existencias[$c->COD_COM] ?? 0, 0, ',', '.') }} L)
                            </option>
                        @endforeach
                    </select>
                    @error('COD_COM')<small class="error-texto">{{ $message }}</small>@enderror
                </div>

                <x-input name="CAN_VEN" type="number" step="0.01" min="0.01" label="Litros" :value="old('CAN_VEN')" required />
            </div>

            <div class="acciones">
                <x-button variant="primary">Registrar venta</x-button>
                <x-button :href="route('ventas.index')" variant="outline">Cancelar</x-button>
                <x-button :href="route('automoviles.create')" variant="ghost">Registrar un vehículo</x-button>
            </div>
        </form>
    </x-card>
</x-layouts.app>