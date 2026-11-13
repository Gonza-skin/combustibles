<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cisterna extends Model
{
    protected $table = 'cisterna';
    protected $primaryKey = 'COD_CIS';
    public $timestamps = false;

    /** estados posibles de una cisterna */
    public const ESTADOS = [
        'disponible' => 'Disponible',
        'asignada' => 'Asignada',
        'en_transito' => 'En transito',
        'mantenimiento' => 'En mantenimiento',
    ];

    protected $fillable = [
        'PLC_CIS', 'CAP_CIS', 'COD_COM', 'COD_CON', 'ESD_CIS',
    ];

    public function combustible(): BelongsTo
    {
        return $this->belongsTo(TipoCombustible::class, 'COD_COM', 'COD_COM');
    }

    public function conductor(): BelongsTo
    {
        return $this->belongsTo(Conductor::class, 'COD_CON', 'COD_CON');
    }

    public function mantenimientos(): HasMany
    {
        return $this->hasMany(MantenimientoCisterna::class, 'COD_CIS', 'COD_CIS');
    }
}