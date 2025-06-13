<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'email',
        'adresse',
        'complement_adresse',
        'code_postal',
        'ville',
        'telephone',
        'notes',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function attachements(): HasMany
    {
        return $this->hasMany(Attachement::class);
    }

    public function demandes(): HasMany
    {
        return $this->hasMany(Demande::class);
    }

    public function getAdresseCompleteAttribute(): string
    {
        $adresse = $this->adresse;
        if ($this->complement_adresse) {
            $adresse .= "\n" . $this->complement_adresse;
        }
        $adresse .= "\n" . $this->code_postal . ' ' . $this->ville;
        
        return $adresse;
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($query) use ($search) {
            $query->where('nom', 'LIKE', "%{$search}%")
                ->orWhere('email', 'LIKE', "%{$search}%")
                ->orWhere('ville', 'LIKE', "%{$search}%");
        });
    }
}