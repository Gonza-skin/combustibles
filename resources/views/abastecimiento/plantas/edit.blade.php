<x-layouts.app title="Editar planta">
    <x-page-header titulo="Editar planta" :subtitulo="$planta->NOM_PLA" />
    <x-card>
        @include('abastecimiento.plantas._form', ['accion' => route('abast.plantas.update', $planta)])
    </x-card>
</x-layouts.app>