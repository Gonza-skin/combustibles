<x-layouts.app title="Editar precio">
    <x-page-header :titulo="'Precio de '.$combustible->DES_COM" subtitulo="El cambio se aplica a las ventas nuevas; las ventas ya registradas conservan su precio." />

    <x-card>
        <form method="POST" action="{{ route('precios-combustible.update', $combustible) }}" class="formulario">
            @csrf
            @method('PUT')

            <x-input name="PRE_COM" type="number" step="0.01" min="0.01" label="Precio por litro (Bs)"
                     :value="$combustible->PRE_COM" required hint="Ejemplo: 3.74" />

            <div class="acciones">
                <x-button variant="primary">Guardar precio</x-button>
                <x-button :href="route('precios-combustible.index')" variant="outline">Cancelar</x-button>
            </div>
        </form>
    </x-card>
</x-layouts.app>