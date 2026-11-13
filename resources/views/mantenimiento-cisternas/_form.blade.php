<form method="POST" action="{{ $accion }}" class="formulario">
    @csrf
    @if ($mantenimiento->exists) @method('PUT') @endif

    <div class="grid-2">
        <x-select name="COD_CIS" label="Cisterna" :options="$cisternas" :selected="$mantenimiento->COD_CIS" placeholder="Selecciona una cisterna" required />
        <x-select name="TIP_MAN" label="Tipo de mantenimiento" :options="\App\Models\MantenimientoCisterna::TIPOS" :selected="$mantenimiento->TIP_MAN"
                  placeholder="Selecciona un tipo" required />
    </div>

    <x-input name="FEC_MAN" type="date" label="Fecha" :value="$mantenimiento->FEC_MAN?->format('Y-m-d')" max="{{ today()->format('Y-m-d') }}" required
             hint="No puede ser una fecha futura." />

    <x-textarea name="DES_MAN" label="Descripción (opcional)" :value="$mantenimiento->DES_MAN" maxlength="2000"
                hint="Qué se hizo: cambio de aceite, revisión de frenos, renovación de documentos…" />

    <div class="acciones">
        <x-button variant="primary">Guardar</x-button>
        <x-button :href="route('mantenimiento-cisternas.index')" variant="outline">Cancelar</x-button>
    </div>
</form>