<x-layouts.app title="Nueva incidencia">
    <x-page-header titulo="Nueva incidencia" subtitulo="Se registra como «Detectada»" />
    <x-card>
        @include('incidencias._form', ['accion' => route('incidencias.store'), 'incidencia' => new \App\Models\Incidencia])
    </x-card>
</x-layouts.app>