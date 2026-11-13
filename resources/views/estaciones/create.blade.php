<x-layouts.app title="Nueva estación">
    <x-page-header titulo="Nueva estación" subtitulo="El estado de la estación se calcula solo según sus existencias de combustible." />
    <x-card>
        @include('estaciones._form', ['accion' => route('estaciones.store')])
    </x-card>
</x-layouts.app>