<form method="POST" action="{{ $accion }}" class="formulario">
    @csrf
    @if ($usuario->exists) @method('PUT') @endif

    <div class="grid-2">
        <x-input name="NOM_USU" label="Nombre completo" :value="$usuario->NOM_USU" maxlength="150" required />
        <x-input name="EML_USU" type="email" label="Correo electrónico" :value="$usuario->EML_USU" maxlength="150" required />

        <x-input name="PAS_USU" type="password" label="Contraseña" autocomplete="new-password"
                 :required="! $usuario->exists"
                 :hint="$usuario->exists ? 'Déjala vacía para no cambiarla. Mínimo 8 caracteres.' : 'Mínimo 8 caracteres.'" />
        <x-input name="PAS_USU_confirmation" type="password" label="Repite la contraseña" autocomplete="new-password"
                 :required="! $usuario->exists" />

        <x-select name="COD_ROL" label="Rol" :options="$roles" :selected="$usuario->COD_ROL"
                  placeholder="Selecciona un rol" :disabled="$esPropio" required
                  :hint="$esPropio ? 'No puedes cambiar tu propio rol.' : null" />
        <x-select name="COD_EST" label="Estación (opcional)" :options="$estaciones" :selected="$usuario->COD_EST"
                  placeholder="Sin estación asignada" hint="Solo para quienes trabajan en una estación (encargados)." />
    </div>

    <x-checkbox name="ACT_USU" label="Usuario activo (puede iniciar sesión)" :checked="$usuario->ACT_USU"
                :disabled="$esPropio" />

    <div class="acciones">
        <x-button variant="primary">Guardar</x-button>
        <x-button :href="route('usuarios.index')" variant="outline">Cancelar</x-button>
    </div>
</form>