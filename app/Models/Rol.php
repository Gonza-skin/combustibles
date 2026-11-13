<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Rol extends Model
{
    protected $table = 'rol';
    protected $primaryKey = 'COD_ROL';
    public $timestamps = false;

    protected $fillable = ['DES_ROL'];

    /** Roles base del sistema */
    public const DEL_SISTEMA = [
        'ciudadano', 'encargado_estacion', 'sub_administrador', 'conductor_cisterna',
        'administrador', 'administrador_deposito', 'supervisor',
    ];

    /** Cómo se muestran en pantalla (con tildes). */
    public const ETIQUETAS = [
        'ciudadano' => 'Ciudadano',
        'encargado_estacion' => 'Encargado de estación',
        'sub_administrador' => 'Subadministrador',
        'conductor_cisterna' => 'Conductor de cisterna',
        'administrador' => 'Administrador',
        'administrador_deposito' => 'Administrador de depósito',
        'supervisor' => 'Supervisor',
    ];

    public function usuarios(): HasMany
    {
        return $this->hasMany(Usuario::class, 'COD_ROL', 'COD_ROL');
    }

    public function esDelSistema(): bool
    {
        return in_array($this->DES_ROL, self::DEL_SISTEMA, true);
    }


    protected function nombre(): Attribute
    {
        return Attribute::get(fn () => self::ETIQUETAS[$this->DES_ROL]
            ?? Str::of($this->DES_ROL)->replace('_', ' ')->ucfirst()->toString());
    }
}