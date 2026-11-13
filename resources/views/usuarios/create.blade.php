<x-layouts.app title="Nuevo usuario">
    <x-page-header titulo="Nuevo usuario" />
    <x-card>
        @include('usuarios._form', ['accion' => route('usuarios.store')])
    </x-card>
</x-layouts.app>