<form method="POST" action="{{ $accion }}" class="formulario">
    @csrf
    @if ($tanque->exists) @method('PUT') @endif

    <div class="grid-2">
        <x-select name="COD_PLA" label="Planta" :options="$plantas" :selected="$tanque->COD_PLA" placeholder="Selecciona una planta" required />
        <x-select name="COD_COM" label="Combustible" :options="$combustibles" :selected="$tanque->COD_COM" placeholder="Selecciona un combustible" required />
        <x-input name="NOM_TAN" label="Nombre del tanque" :value="$tanque->NOM_TAN" maxlength="60" required />
        <x-select name="ESD_TAN" label="Estado" :options="\App\Models\TanqueAlmacenamiento::ESTADOS" :selected="$tanque->ESD_TAN" required />
        <x-input name="CAP_TAN" type="number" step="0.01" min="0.01" label="Capacidad (litros)" :value="$tanque->CAP_TAN" required />
        <x-input name="NIV_TAN" type="number" step="0.01" min="0" label="Nivel actual (litros)" :value="$tanque->NIV_TAN" required />
    </div>

    <div class="acciones">
        <x-button variant="primary">Guardar</x-button>
        <x-button :href="route('abast.tanques.index')" variant="outline">Cancelar</x-button>
    </div>
</form>