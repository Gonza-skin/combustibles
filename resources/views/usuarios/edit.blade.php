<x-layouts.app title="Editar usuario">
    <x-page-header titulo="Editar usuario" :subtitulo="$usuario->EML_USU" />
    <x-card>
        @include('usuarios._form', ['accion' => route('usuarios.update', $usuario)])
    </x-card>
</x-layouts.app>