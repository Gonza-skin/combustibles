<?php

namespace App\Console\Commands;

use App\Models\Estacion;
use App\Services\UbicacionGoogleMaps;
use Illuminate\Console\Command;

class CargarCoordenadasEstaciones extends Command
{
    protected $signature = 'estaciones:coordenadas {--todas : Vuelve a leer también las estaciones que ya tienen coordenadas}';

    protected $description = 'Lee la latitud y longitud de los enlaces de Google Maps de las estaciones';

    public function handle(UbicacionGoogleMaps $google): int
    {
        $estaciones = Estacion::query()
            ->where('DIR_EST', 'like', 'https://%')
            ->when(! $this->option('todas'), fn ($q) => $q->where(fn ($w) => $w->whereNull('LAT_EST')->orWhereNull('LON_EST')))
            ->orderBy('NOM_EST')
            ->get();

        if ($estaciones->isEmpty()) {
            $this->info('No hay estaciones con enlace pendientes de coordenadas.');

            return self::SUCCESS;
        }

        $listas = 0;
        $fallidas = [];

        foreach ($estaciones as $estacion) {
            $coordenadas = $google->coordenadas($estacion->DIR_EST);

            if ($coordenadas) {
                $estacion->update(['LAT_EST' => $coordenadas[0], 'LON_EST' => $coordenadas[1]]);
                $this->line("  ✔ {$estacion->NOM_EST}: {$coordenadas[0]}, {$coordenadas[1]}");
                $listas++;
            } else {
                $this->warn("  ✘ {$estacion->NOM_EST}: no se pudo leer");
                $fallidas[] = $estacion->NOM_EST;
            }
        }

        $this->newLine();
        $this->info("Listas: {$listas}. Sin leer: ".count($fallidas).'.');

        if ($fallidas) {
            $this->line('A las que no se pudieron leer, ponles la latitud y longitud a mano (Estaciones → Editar).');
        }

        return self::SUCCESS;
    }
}
