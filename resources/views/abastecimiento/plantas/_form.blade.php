<form method="POST" action="{{ $accion }}" class="formulario">
    @csrf
    @if ($planta->exists) @method('PUT') @endif

    <x-input name="NOM_PLA" label="Nombre de la planta" :value="$planta->NOM_PLA" maxlength="120" required />
    <x-input name="UBI_PLA" label="Ubicación (dirección o zona)" :value="$planta->UBI_PLA" maxlength="200" />
    <x-checkbox name="ACT_PLA" label="Planta activa" :checked="$planta->ACT_PLA" />

    <div class="acciones">
        <x-button variant="primary">Guardar</x-button>
        <x-button :href="route('abast.plantas.index')" variant="outline">Cancelar</x-button>
    </div>
</form>