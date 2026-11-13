<x-layouts.app title="Nuevo tanque">
    <x-page-header titulo="Nuevo tanque" />
    <x-card>
        @include('abastecimiento.tanques._form', ['accion' => route('abast.tanques.store')])
    </x-card>
</x-layouts.app>