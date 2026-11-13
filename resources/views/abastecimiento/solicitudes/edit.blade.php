<x-layouts.app title="Editar solicitud">
    <x-page-header :titulo="'Editar solicitud #'.$solicitud->COD_SOL" />
    <x-card>
        @include('abastecimiento.solicitudes._form', ['accion' => route('abast.solicitudes.update', $solicitud)])
    </x-card>
</x-layouts.app>