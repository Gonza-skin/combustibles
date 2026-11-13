<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Abastecimiento extends Model
{
    protected $table = 'abastecimiento';
    protected $primaryKey = 'COD_ABA';
    public $timestamps = false;

    public const ESTADOS = [
        'pendiente' => 'Pendiente',
        'asignado' => 'Cisterna asignada',
        'en_transito' => 'En tránsito',
        'entregado' => 'Entregado',
        'cerrado' => 'Cerrado',
        'cancelado' => 'Cancelado',
    ];

    /** el estados se puede pasar desde cada estado */
    public const FLUJO = [
        'pendiente' => ['asignado', 'cancelado'],
        'asignado' => ['en_transito', 'cancelado'],
        'en_transito' => ['entregado', 'cancelado'],
        'entregado' => ['cerrado'],
        'cerrado' => [],
        'cancelado' => [],
    ];

    protected $fillable = [
        'COD_SOL', 'COD_COM', 'COD_CIS', 'CAN_ABA', 'ESD_ABA',
        'HSA_ABA', 'HEL_ABA', 'HRL_ABA', 'CCA_ABA', 'CRE_ABA', 'COD_TAN', 'COD_USU',
    ];

    protected function casts(): array
    {
        return [
            'HSA_ABA' => 'datetime',
            'HEL_ABA' => 'datetime',
            'HRL_ABA' => 'datetime',
            'FEC_ABA' => 'datetime',
            'RET_ABA' => 'boolean',
        ];
    }

    public function solicitud(): BelongsTo
    {
        return $this->belongsTo(SolicitudAbastecimiento::class, 'COD_SOL', 'COD_SOL');
    }

    public function combustible(): BelongsTo
    {
        return $this->belongsTo(TipoCombustible::class, 'COD_COM', 'COD_COM');
    }

    public function cisterna(): BelongsTo
    {
        return $this->belongsTo(Cisterna::class, 'COD_CIS', 'COD_CIS');
    }
    /** Tanque de la planta del que se cargó la cisterna */
    public function tanque(): BelongsTo
    {
        return $this->belongsTo(TanqueAlmacenamiento::class, 'COD_TAN', 'COD_TAN');
    }

    public function historial(): HasMany
    {
        return $this->hasMany(HistorialEstadoAbastecimiento::class, 'COD_ABA', 'COD_ABA');
    }
}