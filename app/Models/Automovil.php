<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Automovil extends Model
{
    protected $table = 'automovil';
    protected $primaryKey = 'COD_VEH';
    public $timestamps = false;

    protected $fillable = [
        'PLA_VEH', 'TIP_VEH', 'MOD_VEH', 'NOM_PROP', 'TEL_PROP',
    ];
}