<x-layouts.app title="Sin permiso">
    <x-card class="error-pagina">
        <div class="codigo">403</div>
        <h1>No tienes permiso para entrar aquí</h1>
        <p>Tu rol no permite ver esta pantalla. Si crees que es un error, consulta con el administrador.</p>
        @auth
            <x-button :href="route('dashboard')" variant="primary">Volver al inicio</x-button>
        @else
            <x-button :href="route('login')" variant="primary">Iniciar sesión</x-button>
        @endauth
    </x-card>
</x-layouts.app>