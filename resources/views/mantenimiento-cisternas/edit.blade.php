<x-layouts.app title="Editar mantenimiento">
    <x-page-header titulo="Editar mantenimiento" :subtitulo="$mantenimiento->cisterna?->PLC_CIS" />
    <x-card>
        @include('mantenimiento-cisternas._form', ['accion' => route('mantenimiento-cisternas.update', $mantenimiento)])
    </x-card>
</x-layouts.app>