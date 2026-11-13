<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Incidencia extends Model
{
    protected $table = 'incidencia';
    protected $primaryKey = 'COD_INC';
    public $timestamps = false;

    public const TIPOS = [
        'retraso' => 'Retraso',
        'diferencia_combustible' => 'Diferencia de combustible',
        'almacenamiento' => 'Almacenamiento',
        'otro' => 'Otro',
    ];

    public const ESTADOS = [
        'detectada' => 'Detectada',
        'en_revision' => 'En revisión',
        'resuelta' => 'Resuelta',
        'cerrada' => 'Cerrada',
    ];

    public const SIGUIENTE = [
        'detectada' => 'en_revision',
        'en_revision' => 'resuelta',
        'resuelta' => 'cerrada',
    ];

    protected $fillable = ['TIP_INC', 'COD_ABA', 'COD_TAN', 'DES_INC', 'ESD_INC', 'COD_USU', 'ACC_INC', 'FRS_INC'];

    protected function casts(): array
    {
        return ['FEC_INC' => 'datetime', 'FRS_INC' => 'datetime'];
    }

    public function tanque(): BelongsTo
    {
        return $this->belongsTo(TanqueAlmacenamiento::class, 'COD_TAN', 'COD_TAN');
    }

    public function abastecimiento(): BelongsTo
    {
        return $this->belongsTo(Abastecimiento::class, 'COD_ABA', 'COD_ABA');
    }

    public function registrador(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'COD_USU', 'COD_USU');
    }
}