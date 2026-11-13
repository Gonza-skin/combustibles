@props(['action', 'mensaje' => '¿Seguro que quieres continuar?'])
{{-- Con SweetAlert2 cargado, data-confirmar muestra el aviso bonito. Si no cargó, se usa el confirm() normal. --}}
<form method="POST" action="{{ $action }}" data-confirmar="{{ $mensaje }}" onsubmit="if (!window.Swal) { return confirm(@js($mensaje)); }">
    @csrf
    @method('DELETE')
    <x-button variant="outline" class="btn--sm">{{ $slot }}</x-button>
</form>
