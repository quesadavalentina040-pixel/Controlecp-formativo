<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Modules\SICA\Entities\Person;
use Modules\SICA\Entities\Role;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'nickname',
        'person_id',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Persona vinculada al usuario
     */
    public function person()
    {
        return $this->belongsTo(Person::class, 'person_id');
    }

    /**
     * Roles asignados al usuario
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user')->withTimestamps();
    }

    /**
     * Verifica si el usuario tiene un rol por slug o si tiene acceso superadmin
     */
    public function hasRole(string $roleSlug): bool
    {
        // Si el usuario es superadmin, tiene acceso completo
        if ($this->hasSuperAdmin()) {
            return true;
        }

        return $this->roles->contains('slug', $roleSlug);
    }

    /**
     * Verifica si el usuario tiene alguno de los roles indicados
     */
    public function hasAnyRole(array $roles): bool
    {
        if ($this->hasSuperAdmin()) {
            return true;
        }

        return $this->roles->whereIn('slug', $roles)->isNotEmpty();
    }

    /**
     * Verifica si el usuario cuenta con el rol superadmin
     */
    public function hasSuperAdmin(): bool
    {
        return $this->roles->contains(function ($role) {
            return $role->slug === 'superadmin' || $role->full_access === 'Si';
        });
    }

    /**
     * Obtiene el nombre completo del usuario a partir de su persona o nickname
     */
    public function getFullNameAttribute(): string
    {
        if ($this->person) {
            $names = array_filter([
                $this->person->first_name,
                $this->person->first_last_name,
                $this->person->second_last_name
            ]);
            if (!empty($names)) {
                return implode(' ', $names);
            }
        }

        if (!empty($this->name)) {
            return $this->name;
        }

        if (!empty($this->nickname)) {
            return $this->nickname;
        }

        return $this->email ?? 'Usuario';
    }

    /**
     * Obtiene el nombre del rol principal del usuario
     */
    public function getPrimaryRoleAttribute(): string
    {
        if ($this->hasSuperAdmin()) {
            return 'Super Administrador';
        }

        $firstRole = $this->roles->first();
        if ($firstRole) {
            return $firstRole->name;
        }

        return 'Usuario';
    }

    /**
     * Obtiene las iniciales del usuario para mostrar en el avatar
     */
    public function getInitialsAttribute(): string
    {
        $name = trim($this->full_name);
        $words = explode(' ', $name);
        $initials = '';
        foreach (array_slice($words, 0, 2) as $w) {
            if (!empty($w)) {
                $initials .= mb_strtoupper(mb_substr($w, 0, 1));
            }
        }
        return !empty($initials) ? $initials : 'SE';
    }
}
