<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Conductor extends Model
{
    protected $table = 'conductor';
    protected $primaryKey = 'COD_CON';
    public $timestamps = false;

    protected $fillable = [
        'COD_USU', 'NOM_CON', 'CI_CON', 'LIC_CON',
        'CAT_LIC', 'TEL_CON', 'ACT_CON',
    ];

    protected function casts(): array
    {
        return ['ACT_CON' => 'boolean'];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'COD_USU', 'COD_USU');
    }
}