<form method="POST" action="{{ $accion }}" class="formulario">
    @csrf
    @if ($automovil->exists) @method('PUT') @endif

    <div class="grid-2">
        <x-input name="PLA_VEH" label="Placa" :value="$automovil->PLA_VEH" maxlength="15" required />
        <x-input name="TIP_VEH" label="Tipo de vehículo" :value="$automovil->TIP_VEH" maxlength="20" list="tipos-vehiculo" required
                 hint="Ejemplo: Automóvil, Camioneta, Motocicleta." />
        <datalist id="tipos-vehiculo">
            <option value="Automóvil"><option value="Camioneta"><option value="Motocicleta"><option value="Minibús"><option value="Camión">
        </datalist>

        <x-input name="MOD_VEH" label="Modelo" :value="$automovil->MOD_VEH" maxlength="60" required />
        <x-input name="NOM_PROP" label="Propietario (opcional)" :value="$automovil->NOM_PROP" maxlength="150" />
        <x-input name="TEL_PROP" label="Teléfono del propietario (opcional)" :value="$automovil->TEL_PROP" inputmode="numeric" maxlength="8" hint="8 dígitos, sin espacios." />
    </div>

    <div class="acciones">
        <x-button variant="primary">Guardar</x-button>
        <x-button :href="route('automoviles.index')" variant="outline">Cancelar</x-button>
    </div>
</form>