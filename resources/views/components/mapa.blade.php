{{--
    Mapa con marcadores (Leaflet + OpenStreetMap: gratis, sin clave ni tarjeta).
    Uso:  <x-mapa :puntos="$estaciones" />
    Cada punto:  ['nombre' => ..., 'direccion' => ..., 'estado' => 'normal|nivel_bajo|sin_combustible', 'lat' => -16.5, 'lng' => -68.1]
    Se permite un solo mapa por página.
--}}
@props(['puntos' => [], 'alto' => '440px'])

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">

<div id="mapa" class="mapa" style="height: {{ $alto }}"></div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    (function () {
        const puntos = @json($puntos);
        const colores = { normal: '#27AE60', nivel_bajo: '#f1c40f', sin_combustible: '#c0392b' };

        const mapa = L.map('mapa').setView([-16.5, -68.15], 12);   // La Paz

        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
        }).addTo(mapa);

        const coordenadas = [];

        puntos.forEach(function (p) {
            const color = colores[p.estado] || colores.normal;

            const marcador = L.circleMarker([p.lat, p.lng], {
                radius: 10,
                color: '#ffffff',
                weight: 2,
                fillColor: color,
                fillOpacity: 1
            }).addTo(mapa);

            // Contenido del globo (se arma con texto plano para evitar inyecciones)
            const caja = document.createElement('div');

            const titulo = document.createElement('strong');
            titulo.textContent = p.nombre;
            caja.appendChild(titulo);

            if (p.direccion) {
                const dir = document.createElement('div');
                dir.textContent = p.direccion;
                caja.appendChild(dir);
            }

            const enlace = document.createElement('a');
            enlace.href = 'https://www.google.com/maps/dir/?api=1&destination=' + p.lat + ',' + p.lng;
            enlace.target = '_blank';
            enlace.rel = 'noopener';
            enlace.textContent = 'Cómo llegar';
            caja.appendChild(enlace);

            marcador.bindPopup(caja);
            coordenadas.push([p.lat, p.lng]);
        });

        // Ajusta el zoom para que se vean todas las estaciones
        if (coordenadas.length > 1) {
            mapa.fitBounds(coordenadas, { padding: [40, 40] });
        } else if (coordenadas.length === 1) {
            mapa.setView(coordenadas[0], 15);
        }
    })();
</script>