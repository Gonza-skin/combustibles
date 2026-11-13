<form method="POST" action="{{ $accion }}" class="formulario">
    @csrf
    @if ($incidencia->exists) @method('PUT') @endif

    <x-select name="TIP_INC" label="Tipo de incidencia" :options="\App\Models\Incidencia::TIPOS" :selected="$incidencia->TIP_INC"
              placeholder="Selecciona un tipo" required />

    <div class="grid-2">
        <x-select name="COD_ABA" label="Abastecimiento relacionado (opcional)" :options="$abastecimientos" :selected="$incidencia->COD_ABA" placeholder="Ninguno" />
        <x-select name="COD_TAN" label="Tanque relacionado (opcional)" :options="$tanques" :selected="$incidencia->COD_TAN" placeholder="Ninguno" />
    </div>

    <x-textarea name="DES_INC" label="Descripción" :value="$incidencia->DES_INC" maxlength="2000" required
                hint="Qué pasó, cuándo y dónde." />

    <div class="acciones">
        <x-button variant="primary">Guardar</x-button>
        <x-button :href="route('incidencias.index')" variant="outline">Cancelar</x-button>
    </div>
</form>