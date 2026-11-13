<x-layouts.app title="Editar cisterna">
    <x-page-header titulo="Editar cisterna" :subtitulo="$cisterna->PLC_CIS" />
    <x-card>
        @include('cisternas._form', ['accion' => route('cisternas.update', $cisterna)])
    </x-card>
</x-layouts.app>