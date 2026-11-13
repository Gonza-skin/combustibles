<x-layouts.app title="Editar conductor">
    <x-page-header titulo="Editar conductor" :subtitulo="$conductor->NOM_CON" />
    <x-card>
        @include('conductores._form', ['accion' => route('conductores.update', $conductor)])
    </x-card>
</x-layouts.app>