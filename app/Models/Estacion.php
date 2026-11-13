<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Estacion extends Model
{
    protected $table = 'estacion';
    protected $primaryKey = 'COD_EST';
    public $timestamps = false;

    public const ESTADOS = [
        'normal' => 'Normal',
        'nivel_bajo' => 'Nivel bajo',
        'sin_combustible' => 'Sin combustible',
    ];

    protected $fillable = [
        'NOM_EST', 'DIR_EST', 'ZON_EST', 'LAT_EST', 'LON_EST',
        'MIN_EST', 'MAX_EST', 'TPT_EST', 'ESD_EST', 'ACT_EST',
    ];

    protected function casts(): array
    {
        return ['ACT_EST' => 'boolean'];
    }

    public function tieneCoordenadas(): bool
    {
        return $this->LAT_EST !== null && $this->LON_EST !== null;
    }

    /** la direccion es un un link del google mpas?*/
    public function tieneEnlaceMaps(): bool
    {
        return (bool) $this->DIR_EST && preg_match('~^https://~i', $this->DIR_EST) === 1;
    }

    /**
     * en link esta dentro del boton para que se abra el link
     * si en DIR_EST se puso un enlace de google maps se abre el link, pero
     * si se puso una direccion se arma una busqueda en google maps 
     */
    public function urlComoLlegar(): string
    {
        if ($this->tieneEnlaceMaps()) {
            return trim($this->DIR_EST);
        }

        $lugar = ($this->DIR_EST ?: $this->NOM_EST)
            .($this->ZON_EST ? ', '.$this->ZON_EST : '')
            .', La Paz, Bolivia';

        return 'https://www.google.com/maps/search/?api=1&query='.rawurlencode($lugar);
    }
}
