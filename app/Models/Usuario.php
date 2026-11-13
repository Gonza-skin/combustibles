<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;

class Usuario extends Authenticatable
{
    use HasApiTokens, HasRoles, TwoFactorAuthenticatable;

    protected $table = 'usuario';
    protected $primaryKey = 'COD_USU';
    public $timestamps = false;

    protected $fillable = [
        'NOM_USU', 'EML_USU', 'PAS_USU', 'COD_ROL', 'COD_EST', 'ACT_USU',
    ];

    protected $hidden = ['PAS_USU', 'two_factor_secret', 'two_factor_recovery_codes'];

    // Jetstream busca "name" y "email": los apuntamos a tus columnas.
    protected $appends = ['name', 'email'];

    /**
     * La columna COD_ROL sigue siendo el rol "oficial" del usuario (así está diseñada la BD).
     * Cada vez que se crea un usuario o se le cambia el rol, se refleja en Spatie.
     */
    protected static function booted(): void
    {
        static::saved(function (Usuario $usuario) {
            if ($usuario->wasRecentlyCreated || $usuario->wasChanged('COD_ROL')) {
                $usuario->sincronizarRol();
            }
        });
    }

    /** Copia el rol de COD_ROL (tabla rol) a los roles de Spatie. */
    public function sincronizarRol(): void
    {
        $nombre = $this->rol()->value('DES_ROL');

        $this->syncRoles($nombre ? [Role::findOrCreate($nombre, 'web')] : []);
    }

    protected function casts(): array
    {
        return [
            'ACT_USU' => 'boolean',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    protected function name(): Attribute
    {
        return Attribute::get(fn () => $this->NOM_USU);
    }

    protected function email(): Attribute
    {
        return Attribute::get(fn () => $this->EML_USU);
    }

    // Laravel debe leer la contraseña desde PAS_USU (no "password").
    public function getAuthPasswordName()
    {
        return 'PAS_USU';
    }

    public function getAuthPassword()
    {
        return $this->PAS_USU;
    }

    // Tu tabla no tiene columna remember_token
    public function getRememberTokenName()
    {
        return '';
    }

    public function rol(): BelongsTo
    {
        return $this->belongsTo(Rol::class, 'COD_ROL', 'COD_ROL');
    }

    /**
     * ¿El usuario tiene alguno de estos roles?
     *   $usuario->tieneRol('administrador', 'supervisor')
     * Sin argumentos devuelve true (cualquier usuario con sesión).
     */
    public function tieneRol(string ...$roles): bool
    {
        if ($roles === []) {
            return true;
        }

        // Si el usuario aún no tiene rol en Spatie (por ejemplo, lo insertaron por SQL), se sincroniza solo.
        if ($this->roles->isEmpty() && $this->COD_ROL) {
            $this->sincronizarRol();
            $this->load('roles');
        }

        return $this->hasAnyRole($roles);
    }
}
