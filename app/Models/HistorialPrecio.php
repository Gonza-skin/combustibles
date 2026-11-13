<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** bitacora de del precio del combustible */
class HistorialPrecio extends Model
{
    protected $table = 'historial_precio';
    protected $primaryKey = 'COD_HIS';
    public $timestamps = false;

    protected $fillable = ['COD_COM', 'PRE_ANT', 'PRE_NUE', 'COD_USU'];

    public function combustible(): BelongsTo
    {
        return $this->belongsTo(TipoCombustible::class, 'COD_COM', 'COD_COM');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'COD_USU', 'COD_USU');
    }
}