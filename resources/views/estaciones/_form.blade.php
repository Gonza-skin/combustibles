<form method="POST" action="{{ $accion }}" class="formulario">
    @csrf
    @if ($estacion->exists) @method('PUT') @endif

    <div class="grid-2">
        <x-input name="NOM_EST" label="Nombre de la estación" :value="$estacion->NOM_EST" maxlength="120" required />
        <x-input name="ZON_EST" label="Zona" :value="$estacion->ZON_EST" maxlength="80" hint="Ejemplo: Sopocachi, Miraflores, Obrajes." />
    </div>

    <x-input name="DIR_EST" label="Enlace de Google Maps" :value="$estacion->DIR_EST" maxlength="500" required
             placeholder="https://maps.app.goo.gl/..."
             hint="En Google Maps busca la estación, pulsa «Compartir» y luego «Copiar vínculo». Pega aquí ese enlace: el botón «Cómo llegar» abrirá justo ese lugar." />

    <div class="grid-2">
        <x-input name="LAT_EST" type="number" step="any" label="Latitud" :value="$estacion->LAT_EST"
                 hint="Se llena sola al guardar el enlace. Si no se pudo leer, escríbela (ejemplo: -16.5000)." />
        <x-input name="LON_EST" type="number" step="any" label="Longitud" :value="$estacion->LON_EST"
                 hint="Ejemplo: -68.1300. En Google Maps, clic derecho sobre el lugar y copia los dos números." />
    </div>

    <div class="grid-2">
        <x-input name="MIN_EST" type="number" step="0.01" min="0" label="Nivel mínimo (litros)" :value="$estacion->MIN_EST ?? 0" required
                 hint="Si las existencias bajan de este nivel, la estación pasa a «Nivel bajo»." />
        <x-input name="MAX_EST" type="number" step="0.01" min="0" label="Capacidad máxima (litros)" :value="$estacion->MAX_EST ?? 0" required />
    </div>

    <x-input name="TPT_EST" type="number" min="1" label="Tiempo de tránsito (minutos)" :value="$estacion->TPT_EST ?? 60" required
             hint="Minutos que tarda una cisterna en llegar a esta estación." />

    <x-checkbox name="ACT_EST" label="Estación activa" :checked="$estacion->ACT_EST" />

    <div class="acciones">
        <x-button variant="primary">Guardar</x-button>
        <x-button :href="route('estaciones.index')" variant="outline">Cancelar</x-button>
    </div>
</form>