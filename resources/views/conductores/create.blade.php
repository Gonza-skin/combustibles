<x-layouts.app title="Nuevo conductor">
    <x-page-header titulo="Nuevo conductor" />
    <x-card>
        @include('conductores._form', ['accion' => route('conductores.store')])
    </x-card>
</x-layouts.app>