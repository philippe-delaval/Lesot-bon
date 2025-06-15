<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
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

    public function demandesCreees(): HasMany
    {
        return $this->hasMany(Demande::class, 'createur_id');
    }

    public function demandesRecues(): HasMany
    {
        return $this->hasMany(Demande::class, 'receveur_id');
    }

    public function technicien()
    {
        return $this->hasOne(Technicien::class);
    }

    // Scopes pour optimiser les requêtes
    public function scopeActifs($query)
    {
        return $query->where('active', true);
    }

    public function scopePourFormulaires($query)
    {
        return $query->select('id', 'name', 'email')
            ->where('active', true)
            ->orderBy('name');
    }
}
