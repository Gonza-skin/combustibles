<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoCombustible extends Model
{
    protected $table = 'tipo_combustible';
    protected $primaryKey = 'COD_COM';
    public $timestamps = false;

    protected $fillable = ['DES_COM', 'PRE_COM'];

    protected function casts(): array
    {
        return ['PRE_COM' => 'decimal:2'];
    }

    public function historialPrecios(): HasMany
    {
        return $this->hasMany(HistorialPrecio::class, 'COD_COM', 'COD_COM');
    }
}