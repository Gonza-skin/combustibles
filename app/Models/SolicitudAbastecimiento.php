<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SolicitudAbastecimiento extends Model
{
    protected $table = 'solicitud_abastecimiento';
    protected $primaryKey = 'COD_SOL';
    public $timestamps = false;

    public const ESTADOS = [
        'pendiente' => 'Pendiente',
        'atendida' => 'Atendida',
        'cancelada' => 'Cancelada',
    ];

    protected $fillable = ['COD_EST', 'COD_COM', 'CAN_SOL', 'ESD_SOL', 'COD_USU'];

    protected function casts(): array
    {
        return ['FEC_SOL' => 'datetime'];
    }

    public function estacion(): BelongsTo
    {
        return $this->belongsTo(Estacion::class, 'COD_EST', 'COD_EST');
    }

    public function combustible(): BelongsTo
    {
        return $this->belongsTo(TipoCombustible::class, 'COD_COM', 'COD_COM');
    }

    public function solicitante(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'COD_USU', 'COD_USU');
    }

    public function abastecimiento(): HasOne
    {
        return $this->hasOne(Abastecimiento::class, 'COD_SOL', 'COD_SOL');
    }
}