<x-layouts.app title="Nueva cisterna">
    <x-page-header titulo="Nueva cisterna" />
    <x-card>
        @include('cisternas._form', ['accion' => route('cisternas.store')])
    </x-card>
</x-layouts.app>