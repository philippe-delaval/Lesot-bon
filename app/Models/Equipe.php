<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Equipe extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'code',
        'description',
        'charge_projet_id',
        'specialisation',
        'capacite_max',
        'competences_requises',
        'vehicules_attribues',
        'active',
        'zones_intervention',
        'horaire_debut',
        'horaire_fin'
    ];

    protected $casts = [
        'competences_requises' => 'array',
        'vehicules_attribues' => 'array',
        'zones_intervention' => 'array',
        'active' => 'boolean',
        'horaire_debut' => 'datetime:H:i',
        'horaire_fin' => 'datetime:H:i'
    ];

    // Relations
    public function chargeProjet(): BelongsTo
    {
        return $this->belongsTo(Employe::class, 'charge_projet_id');
    }

    public function employes(): BelongsToMany
    {
        return $this->belongsToMany(Employe::class, 'employe_equipe')
            ->withPivot(['role_equipe', 'date_debut_affectation', 'date_fin_affectation', 'active'])
            ->withTimestamps();
    }

    public function employesActifs(): BelongsToMany
    {
        return $this->employes()->wherePivot('active', true);
    }

    public function chefEquipe(): BelongsToMany
    {
        return $this->employes()
            ->wherePivot('role_equipe', 'chef_equipe')
            ->wherePivot('active', true);
    }

    public function plannings(): HasMany
    {
        return $this->hasMany(Planning::class);
    }

    // Accesseurs
    protected function effectifActuel(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->employesActifs()->count(),
        );
    }

    protected function tauxOccupation(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->capacite_max > 0 
                ? round(($this->effectif_actuel / $this->capacite_max) * 100, 1)
                : 0,
        );
    }

    protected function estComplete(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->effectif_actuel >= $this->capacite_max,
        );
    }

    // Scopes
    public function scopeActives($query)
    {
        return $query->where('active', true);
    }

    public function scopeParSpecialisation($query, $specialisation)
    {
        return $query->where('specialisation', $specialisation);
    }

    public function scopeParChargeProjet($query, $chargeProjetId)
    {
        return $query->where('charge_projet_id', $chargeProjetId);
    }

    public function scopeDisponibles($query, $dateDebut, $dateFin)
    {
        return $query->whereDoesntHave('plannings', function ($query) use ($dateDebut, $dateFin) {
            $query->where(function ($q) use ($dateDebut, $dateFin) {
                $q->whereBetween('date_debut', [$dateDebut, $dateFin])
                  ->orWhereBetween('date_fin', [$dateDebut, $dateFin])
                  ->orWhere(function ($subQ) use ($dateDebut, $dateFin) {
                      $subQ->where('date_debut', '<=', $dateDebut)
                           ->where('date_fin', '>=', $dateFin);
                  });
            })->where('statut', '!=', 'annule');
        });
    }

    // Méthodes métier
    public function peutAccueillirEmploye(): bool
    {
        return $this->effectif_actuel < $this->capacite_max && $this->active;
    }

    public function ajouterEmploye(Employe $employe, string $role = 'membre'): bool
    {
        if (!$this->peutAccueillirEmploye()) {
            return false;
        }

        // Désactiver l'ancien rattachement s'il existe
        $employe->equipes()->updateExistingPivot(
            $employe->equipes()->wherePivot('active', true)->pluck('equipes.id'),
            ['active' => false, 'date_fin_affectation' => now()]
        );

        // Ajouter à la nouvelle équipe
        $this->employes()->attach($employe->id, [
            'role_equipe' => $role,
            'date_debut_affectation' => now(),
            'active' => true
        ]);

        return true;
    }

    public function retirerEmploye(Employe $employe): bool
    {
        return $this->employes()->updateExistingPivot($employe->id, [
            'active' => false,
            'date_fin_affectation' => now()
        ]) > 0;
    }

    public function getCompetencesDisponibles(): array
    {
        $competences = [];
        
        foreach ($this->employesActifs as $employe) {
            if ($employe->habilitations_electriques) {
                $competences = array_merge($competences, $employe->habilitations_electriques);
            }
        }

        return array_unique($competences);
    }

    public function peutIntervenirSur(array $competencesRequises): bool
    {
        $competencesDisponibles = $this->getCompetencesDisponibles();
        
        return !empty(array_intersect($competencesRequises, $competencesDisponibles));
    }

    public function getStatutEquipe(): string
    {
        if (!$this->active) {
            return 'Inactive';
        }

        $effectif = $this->effectif_actuel;
        $capacite = $this->capacite_max;

        return match (true) {
            $effectif == 0 => 'Vide',
            $effectif < $capacite * 0.5 => 'Sous-effectif',
            $effectif < $capacite => 'Partielle',
            $effectif == $capacite => 'Complète',
            default => 'Sur-effectif'
        };
    }

    public function getProchainePlanification()
    {
        return $this->plannings()
            ->where('date_debut', '>', now())
            ->where('statut', 'planifie')
            ->orderBy('date_debut')
            ->first();
    }

    public function estDisponiblePour($dateDebut, $dateFin): bool
    {
        $conflits = $this->plannings()
            ->where(function ($query) use ($dateDebut, $dateFin) {
                $query->whereBetween('date_debut', [$dateDebut, $dateFin])
                      ->orWhereBetween('date_fin', [$dateDebut, $dateFin])
                      ->orWhere(function ($subQuery) use ($dateDebut, $dateFin) {
                          $subQuery->where('date_debut', '<=', $dateDebut)
                                   ->where('date_fin', '>=', $dateFin);
                      });
            })
            ->where('statut', '!=', 'annule')
            ->exists();

        return !$conflits && $this->active;
    }
}