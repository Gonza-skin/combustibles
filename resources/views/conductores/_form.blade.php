<form method="POST" action="{{ $accion }}" class="formulario">
    @csrf
    @if ($conductor->exists) @method('PUT') @endif

    <x-select name="COD_USU" label="Usuario del sistema" :options="$usuarios" :selected="$conductor->COD_USU"
              placeholder="Selecciona un usuario" required
              hint="Solo aparecen usuarios con rol «Conductor de cisterna» que aún no son conductores. Si falta, créalo primero en Usuarios." />

    <div class="grid-2">
        <x-input name="NOM_CON" label="Nombre completo" :value="$conductor->NOM_CON" maxlength="150" required />
        <x-input name="CI_CON" label="Cédula de identidad" :value="$conductor->CI_CON" maxlength="20" required />

        <x-input name="LIC_CON" label="N.º de licencia" :value="$conductor->LIC_CON" maxlength="20" required />
        <x-input name="CAT_LIC" label="Categoría de licencia" :value="$conductor->CAT_LIC" maxlength="10" hint="Ejemplo: C, T." />

        <x-input name="TEL_CON" label="Teléfono" :value="$conductor->TEL_CON" inputmode="numeric" maxlength="8" hint="8 dígitos, sin espacios." />
    </div>

    <x-checkbox name="ACT_CON" label="Conductor activo" :checked="$conductor->ACT_CON" />

    <div class="acciones">
        <x-button variant="primary">Guardar</x-button>
        <x-button :href="route('conductores.index')" variant="outline">Cancelar</x-button>
    </div>
</form>