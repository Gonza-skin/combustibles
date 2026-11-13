<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReporteCiudadano extends Model
{
    protected $table = 'reporte_ciudadano';
    protected $primaryKey = 'COD_REP';
    public $timestamps = false;

    public const FILAS = ['sin_fila' => 'Sin fila', 'corta' => 'Fila corta', 'moderada' => 'Fila moderada', 'larga' => 'Fila larga'];
    public const DISPONIBILIDAD = ['disponible' => 'Disponible', 'poco_stock' => 'Poco stock', 'agotado' => 'Agotado'];

    protected $fillable = ['COD_EST', 'COD_USU', 'FIL_REP', 'DIS_REP', 'TES_REP'];

    protected function casts(): array
    {
        return ['FEC_REP' => 'datetime'];
    }

    public function estacion(): BelongsTo
    {
        return $this->belongsTo(Estacion::class, 'COD_EST', 'COD_EST');
    }

    public function ciudadano(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'COD_USU', 'COD_USU');
    }
}