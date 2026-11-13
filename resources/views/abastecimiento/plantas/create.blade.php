<x-layouts.app title="Nueva planta">
    <x-page-header titulo="Nueva planta" />
    <x-card>
        @include('abastecimiento.plantas._form', ['accion' => route('abast.plantas.store')])
    </x-card>
</x-layouts.app>