<x-layouts.app title="Nuevo automóvil">
    <x-page-header titulo="Nuevo automóvil" />
    <x-card>
        @include('automoviles._form', ['accion' => route('automoviles.store')])
    </x-card>
</x-layouts.app>