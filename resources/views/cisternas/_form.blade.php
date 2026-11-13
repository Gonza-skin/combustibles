<form method="POST" action="{{ $accion }}" class="formulario">
    @csrf
    @if ($cisterna->exists) @method('PUT') @endif

    <div class="grid-2">
        <x-input name="PLC_CIS" label="Placa" :value="$cisterna->PLC_CIS" maxlength="20" required />
        <x-input name="CAP_CIS" type="number" step="0.01" min="0.01" label="Capacidad (litros)" :value="$cisterna->CAP_CIS" required />

        <x-select name="COD_COM" label="Combustible que transporta" :options="$combustibles" :selected="$cisterna->COD_COM"
                  placeholder="Selecciona un combustible" required />
        <x-select name="COD_CON" label="Conductor asignado (opcional)" :options="$conductores" :selected="$cisterna->COD_CON"
                  placeholder="Sin conductor" hint="Solo aparecen conductores activos." />
    </div>

    <x-select name="ESD_CIS" label="Estado" :options="\App\Models\Cisterna::ESTADOS" :selected="$cisterna->ESD_CIS" required />

    <div class="acciones">
        <x-button variant="primary">Guardar</x-button>
        <x-button :href="route('cisternas.index')" variant="outline">Cancelar</x-button>
    </div>
</form>