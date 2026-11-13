<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Alerta extends Model
{
    protected $table = 'alerta';
    protected $primaryKey = 'COD_ALE';
    public $timestamps = false;

    public const TIPOS = [
        'existencia_minima' => 'Existencia mínima',
        'cisterna_retrasada' => 'Cisterna retrasada',
        'diferencia_combustible' => 'Diferencia de combustible',
        'tanque_critico' => 'Tanque crítico',
        'otro' => 'Otro',
    ];

    public const NIVELES = ['baja' => 'Baja', 'media' => 'Media', 'alta' => 'Alta'];

    protected $fillable = ['TIP_ALE', 'MEN_ALE', 'NIV_ALE', 'COD_EST', 'COD_ABA', 'COD_TAN', 'COD_INC', 'LEI_ALE'];

    protected function casts(): array
    {
        return ['LEI_ALE' => 'boolean', 'FEC_ALE' => 'datetime'];
    }

    public function estacion(): BelongsTo
    {
        return $this->belongsTo(Estacion::class, 'COD_EST', 'COD_EST');
    }
}