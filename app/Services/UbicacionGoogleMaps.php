<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

/**
 * Trabaja con los enlaces de Google Maps de las estaciones:
 *  - esEnlace():     comprueba que el enlace sea de Google Maps (https).
 *  - coordenadas():  saca latitud y longitud del enlace (si es un enlace corto, lo sigue hasta el enlace largo).
 */
class UbicacionGoogleMaps
{
    /** Solo se aceptan enlaces https de Google Maps (así nadie guarda un enlace a otra página). */
    public function esEnlace(string $url): bool
    {
        $partes = parse_url(trim($url));

        if (($partes['scheme'] ?? '') !== 'https' || empty($partes['host'])) {
            return false;
        }

        $host = strtolower($partes['host']);
        $ruta = $partes['path'] ?? '';

        return $host === 'maps.app.goo.gl'                                              // enlace corto del botón Compartir
            || ($host === 'goo.gl' && str_starts_with($ruta, '/maps'))                  // enlace corto antiguo
            || (bool) preg_match('/^maps\.google\.(com(\.[a-z]{2})?|[a-z]{2})$/', $host) // maps.google.com
            || ((bool) preg_match('/^(www\.)?google\.(com(\.[a-z]{2})?|[a-z]{2})$/', $host) && str_starts_with($ruta, '/maps')); // google.com/maps
    }

    /** @return array{0: float, 1: float}|null  [latitud, longitud] o null si no se pudo leer */
    public function coordenadas(string $url): ?array
    {
        $url = trim($url);

        // Un enlace corto redirige al largo (que sí trae las coordenadas): se siguen hasta 5 redirecciones.
        for ($salto = 0; $salto < 6; $salto++) {
            if (! $this->esEnlace($url)) {
                return null;
            }

            if ($coordenadas = $this->leer($url)) {
                return $coordenadas;
            }

            try {
                $respuesta = Http::timeout(8)
                    ->withoutRedirecting()
                    ->withHeaders(['User-Agent' => 'Mozilla/5.0 (compatible; SCACMLP)'])
                    ->get($url);
            } catch (\Throwable) {
                return null;
            }

            $destino = $respuesta->header('Location');

            if (! $destino) {
                // Sin redirección: a veces las coordenadas vienen dentro de la propia página
                return $this->leer((string) $respuesta->body());
            }

            $url = str_starts_with($destino, '/')
                ? 'https://'.parse_url($url, PHP_URL_HOST).$destino
                : $destino;
        }

        return null;
    }

    /** Busca las coordenadas dentro de un texto (el enlace o la página). Público para poder probarlo. */
    public function leer(string $texto): ?array
    {
        $texto = urldecode($texto);

        $patrones = [
            '/!3d(-?\d{1,3}\.\d+)!4d(-?\d{1,3}\.\d+)/',                       // el pin exacto del lugar
            '/@(-?\d{1,3}\.\d+),(-?\d{1,3}\.\d+)/',                           // .../@-16.50,-68.13,17z
            '/[?&](?:q|ll|query|destination|center)=(-?\d{1,3}\.\d+),(-?\d{1,3}\.\d+)/i', // ?q=-16.50,-68.13
        ];

        foreach ($patrones as $patron) {
            if (preg_match($patron, $texto, $c)) {
                $lat = (float) $c[1];
                $lon = (float) $c[2];

                if (abs($lat) <= 90 && abs($lon) <= 180) {
                    return [$lat, $lon];
                }
            }
        }

        return null;
    }
}
