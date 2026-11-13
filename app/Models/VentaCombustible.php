<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VentaCombustible extends Model
{
    protected $table = 'venta_combustible';
    protected $primaryKey = 'COD_VEN';
    public $timestamps = false;

    // VAL_VEN (el total) lo calcula la base de datos; no se escribe desde aquí.
    protected $fillable = ['COD_EST', 'COD_VEH', 'COD_COM', 'CAN_VEN', 'PRE_VEN', 'COD_USU'];

    protected function casts(): array
    {
        return ['FEC_VEN' => 'datetime', 'CAN_VEN' => 'decimal:2', 'PRE_VEN' => 'decimal:2'];
    }

    /** Total de la venta (litros × precio) */
    public function total(): float
    {
        return round((float) $this->CAN_VEN * (float) $this->PRE_VEN, 2);
    }

    public function automovil(): BelongsTo
    {
        return $this->belongsTo(Automovil::class, 'COD_VEH', 'COD_VEH');
    }

    public function combustible(): BelongsTo
    {
        return $this->belongsTo(TipoCombustible::class, 'COD_COM', 'COD_COM');
    }

    public function vendedor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'COD_USU', 'COD_USU');
    }
}