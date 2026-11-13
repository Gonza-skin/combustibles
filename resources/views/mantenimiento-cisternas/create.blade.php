<x-layouts.app title="Nuevo mantenimiento">
    <x-page-header titulo="Nuevo mantenimiento" />
    <x-card>
        @include('mantenimiento-cisternas._form', ['accion' => route('mantenimiento-cisternas.store')])
    </x-card>
</x-layouts.app>