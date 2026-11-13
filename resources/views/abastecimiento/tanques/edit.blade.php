<x-layouts.app title="Editar tanque">
    <x-page-header titulo="Editar tanque" :subtitulo="$tanque->NOM_TAN" />
    <x-card>
        @include('abastecimiento.tanques._form', ['accion' => route('abast.tanques.update', $tanque)])
    </x-card>
</x-layouts.app>