<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Demande extends Model
{
    use HasFactory;

    protected $fillable = [
        'numero_demande',
        'titre',
        'description',
        'priorite',
        'statut',
        'createur_id',
        'receveur_id',
        'client_id',
        'lieu_intervention',
        'date_demande',
        'date_souhaite_intervention',
        'date_assignation',
        'date_completion',
        'notes_receveur',
        'attachement_id',
    ];

    protected $casts = [
        'date_demande' => 'datetime',
        'date_souhaite_intervention' => 'datetime',
        'date_assignation' => 'datetime',
        'date_completion' => 'datetime',
    ];

    public function createur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'createur_id');
    }

    public function receveur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receveur_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function attachement(): BelongsTo
    {
        return $this->belongsTo(Attachement::class);
    }

    public function scopeEnCours($query)
    {
        return $query->whereNotIn('statut', ['terminee', 'annulee']);
    }

    public function scopeAssigneesA($query, $userId)
    {
        return $query->where('receveur_id', $userId)->whereIn('statut', ['assignee', 'en_cours']);
    }

    public function scopeCreesPar($query, $userId)
    {
        return $query->where('createur_id', $userId);
    }

    public function getStatutBadgeAttribute(): string
    {
        return match ($this->statut) {
            'en_attente' => 'bg-yellow-100 text-yellow-800',
            'assignee' => 'bg-blue-100 text-blue-800',
            'en_cours' => 'bg-purple-100 text-purple-800',
            'terminee' => 'bg-green-100 text-green-800',
            'annulee' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getPrioriteColorAttribute(): string
    {
        return match ($this->priorite) {
            'normale' => 'bg-green-100 text-green-800 border-green-200',
            'haute' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
            'urgente' => 'bg-red-100 text-red-800 border-red-200',
            default => 'bg-green-100 text-green-800 border-green-200',
        };
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($demande) {
            if (empty($demande->numero_demande)) {
                $demande->numero_demande = self::generateNumerodemande();
            }
        });
    }

    private static function generateNumeroDemande(): string
    {
        $year = now()->year;
        $prefix = "DEM-{$year}-";
        $latest = self::where('numero_demande', 'like', $prefix . '%')
            ->orderBy('numero_demande', 'desc')
            ->first();

        if ($latest) {
            $lastNumber = (int) substr($latest->numero_demande, -3);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return $prefix . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }
}
