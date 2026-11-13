<x-layouts.app title="Editar automóvil">
    <x-page-header titulo="Editar automóvil" :subtitulo="$automovil->PLA_VEH" />
    <x-card>
        @include('automoviles._form', ['accion' => route('automoviles.update', $automovil)])
    </x-card>
</x-layouts.app>