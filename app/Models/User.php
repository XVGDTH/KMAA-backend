<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, Notifiable, HasRoles;
     use \Laravel\Sanctum\HasApiTokens, Notifiable; 

    /**
     * Les attributs pouvant être remplis en masse.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'avatar',
        'bio',
        'is_active',
    ];

    /**
     * Les attributs cachés lors de la sérialisation.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Conversion des attributs.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Formations/cours enseignés par l'utilisateur.
     */
    public function coursesTaught()
    {
        return $this->hasMany(Course::class, 'instructor_id');
    }

    /**
     * Inscriptions de l'utilisateur.
     */
    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    /**
     * Paiements effectués par l'utilisateur.
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Détermine si l'utilisateur peut accéder au panneau Filament.
     *
     * Seuls les utilisateurs ayant le rôle "admin"
     * peuvent accéder au panneau d'administration.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $panel->getId() === 'admin'
            && $this->hasRole('admin');
    }
}