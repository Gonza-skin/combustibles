<x-layouts.app title="Nueva solicitud">
    <x-page-header titulo="Nueva solicitud de combustible" subtitulo="Pide combustible para tu estación" />
    <x-card>
        @include('abastecimiento.solicitudes._form', ['accion' => route('abast.solicitudes.store')])
    </x-card>
</x-layouts.app>