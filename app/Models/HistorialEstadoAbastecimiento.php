<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistorialEstadoAbastecimiento extends Model
{
    protected $table = 'historial_estado_abastecimiento';
    protected $primaryKey = 'COD_HIS';
    public $timestamps = false;

    protected $fillable = ['COD_ABA', 'ESD_HIS', 'COD_USU', 'OBS_HIS'];

    protected function casts(): array
    {
        return ['FEC_HIS' => 'datetime'];
    }

    public function abastecimiento(): BelongsTo
    {
        return $this->belongsTo(Abastecimiento::class, 'COD_ABA', 'COD_ABA');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'COD_USU', 'COD_USU');
    }
}