<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MantenimientoCisterna extends Model
{
    protected $table = 'mantenimiento_cisterna';
    protected $primaryKey = 'COD_MAN';
    public $timestamps = false;

    public const TIPOS = [
        'preventivo' => 'Preventivo',
        'correctivo' => 'Correctivo',
        'documentacion' => 'Documentación',
    ];

    protected $fillable = [
        'COD_CIS', 'TIP_MAN', 'DES_MAN', 'FEC_MAN',
    ];

    protected function casts(): array
    {
        return ['FEC_MAN' => 'date'];
    }

    public function cisterna(): BelongsTo
    {
        return $this->belongsTo(Cisterna::class, 'COD_CIS', 'COD_CIS');
    }
}