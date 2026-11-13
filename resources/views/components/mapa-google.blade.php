{{--
    Mapa de Google Maps con las gasolineras de la zona (sin clave de API).
    Al hacer clic en una gasolinera, Google muestra su información y el botón "Cómo llegar".
    Uso:  <x-mapa-google />
          <x-mapa-google :lat="-16.48" :lng="-68.12" :zoom="15" alto="520px" />
--}}
@props(['lat' => -16.4801393, 'lng' => -68.1219984, 'zoom' => 15, 'buscar' => 'gasolinera', 'alto' => '480px'])

@php
    $embed = 'https://maps.google.com/maps?q='.rawurlencode($buscar.' near '.$lat.','.$lng).'&z='.$zoom.'&output=embed';
    $enlace = 'https://www.google.com/maps/search/'.rawurlencode($buscar).'/@'.$lat.','.$lng.','.$zoom.'z';
@endphp

<div class="mapa-google">
    <iframe src="{{ $embed }}" class="mapa" style="height: {{ $alto }}; border: 0"
            title="Mapa de Google Maps con gasolineras cercanas en La Paz"
            loading="lazy" allowfullscreen referrerpolicy="no-referrer-when-downgrade"></iframe>
    <p class="mapa-google__pie">
        <a href="{{ $enlace }}" target="_blank" rel="noopener noreferrer">Abrir en Google Maps</a>
    </p>
</div>