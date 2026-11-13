<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TanqueAlmacenamiento extends Model
{
    protected $table = 'tanque_almacenamiento';
    protected $primaryKey = 'COD_TAN';
    public $timestamps = false;

    public const ESTADOS = [
        'operativo' => 'Operativo',
        'critico' => 'Critico',
        'mantenimiento' => 'En mantenimiento',
        'inactivo' => 'Inactivo',
    ];

    protected $fillable = ['COD_PLA', 'COD_COM', 'NOM_TAN', 'CAP_TAN', 'NIV_TAN', 'ESD_TAN'];

    public function planta(): BelongsTo
    {
        return $this->belongsTo(PlantaDeposito::class, 'COD_PLA', 'COD_PLA');
    }

    public function combustible(): BelongsTo
    {
        return $this->belongsTo(TipoCombustible::class, 'COD_COM', 'COD_COM');
    }

    /** porcentaje de llenado de 0 a 100*/
    public function porcentaje(): int
    {
        return (float) $this->CAP_TAN > 0
            ? (int) min(100, round((float) $this->NIV_TAN / (float) $this->CAP_TAN * 100))
            : 0;
    }
}