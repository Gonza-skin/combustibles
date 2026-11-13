<x-layouts.app title="Editar incidencia">
    <x-page-header :titulo="'Editar incidencia #'.$incidencia->COD_INC" />
    <x-card>
        @include('incidencias._form', ['accion' => route('incidencias.update', $incidencia)])
    </x-card>
</x-layouts.app>