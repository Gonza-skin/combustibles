<x-layouts.app title="Editar estación">
    <x-page-header titulo="Editar estación" :subtitulo="$estacion->NOM_EST" />
    <x-card>
        @include('estaciones._form', ['accion' => route('estaciones.update', $estacion)])
    </x-card>
</x-layouts.app>