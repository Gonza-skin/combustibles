<?php

namespace App\Services;

use App\Models\Estacion;
use Illuminate\Support\Collection;

/**
 * Busca las estaciones más cercanas a un punto de referencia (en línea recta).
 * Si en la zona hay una sola estación, completa con las más próximas de otras zonas.
 */
class EstacionesCercanas
{
    /**
     * Las $cantidad estaciones activas más cercanas a la estación de referencia
     * (ella misma va primera, a 0 km). Cada una trae el atributo "distancia_km".
     *
     * @return Collection<int, Estacion>
     */
    public function cercanas(Estacion $referencia, int $cantidad = 3): Collection
    {
        return Estacion::query()
            ->where('ACT_EST', true)
            ->whereNotNull('LAT_EST')
            ->whereNotNull('LON_EST')
            ->get()
            ->each(fn (Estacion $e) => $e->setAttribute('distancia_km', self::distanciaKm(
                (float) $referencia->LAT_EST, (float) $referencia->LON_EST,
                (float) $e->LAT_EST, (float) $e->LON_EST,
            )))
            ->sortBy('distancia_km')
            ->take($cantidad)
            ->values();
    }

    /** Distancia en kilómetros entre dos puntos (fórmula de Haversine). */
    public static function distanciaKm(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $r = 6371.0; // radio de la Tierra en km
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;

        return $r * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
