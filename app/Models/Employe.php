<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Employe extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'prenom',
        'email',
        'telephone',
        'matricule',
        'statut',
        'type_contrat',
        'date_debut',
        'date_fin',
        'salaire_base',
        'role_hierarchique',
        'charge_projet_id',
        'gestionnaire_id',
        'habilitations_electriques',
        'certifications',
        'competences',
        'date_derniere_formation',
        'disponibilite',
        'vehicule_attribue',
        'astreinte',
        'notes',
        'photo_path',
        'documents'
    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin' => 'date',
        'date_derniere_formation' => 'date',
        'habilitations_electriques' => 'array',
        'certifications' => 'array',
        'competences' => 'array',
        'documents' => 'array',
        'astreinte' => 'boolean',
        'salaire_base' => 'decimal:2'
    ];

    // Accesseurs
    protected function nomComplet(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->prenom . ' ' . $this->nom,
        );
    }

    protected function initiales(): Attribute
    {
        return Attribute::make(
            get: fn () => strtoupper(substr($this->prenom, 0, 1) . substr($this->nom, 0, 1)),
        );
    }

    protected function estDisponible(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->disponibilite === 'disponible',
        );
    }

    // Relations hiérarchiques
    public function chargeProjet(): BelongsTo
    {
        return $this->belongsTo(Employe::class, 'charge_projet_id');
    }

    public function gestionnaire(): BelongsTo
    {
        return $this->belongsTo(Employe::class, 'gestionnaire_id');
    }

    public function employesSupervises(): HasMany
    {
        return $this->hasMany(Employe::class, 'charge_projet_id');
    }

    public function employesGeres(): HasMany
    {
        return $this->hasMany(Employe::class, 'gestionnaire_id');
    }

    // Relations équipes
    public function equipes(): BelongsToMany
    {
        return $this->belongsToMany(Equipe::class, 'employe_equipe')
            ->withPivot(['role_equipe', 'date_debut_affectation', 'date_fin_affectation', 'active'])
            ->withTimestamps();
    }

    public function equipeActive(): BelongsToMany
    {
        return $this->equipes()->wherePivot('active', true);
    }

    // Relations planning
    public function plannings(): HasMany
    {
        return $this->hasMany(Planning::class);
    }

    public function planningActuel(): HasMany
    {
        return $this->plannings()
            ->whereDate('date_debut', '<=', now())
            ->whereDate('date_fin', '>=', now())
            ->where('statut', '!=', 'annule');
    }

    // Relations demandes (via User existant)
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'email', 'email');
    }

    // Scopes
    public function scopePermanents($query)
    {
        return $query->where('statut', 'permanent');
    }

    public function scopeInterimaires($query)
    {
        return $query->where('statut', 'interimaire');
    }

    public function scopeDisponibles($query)
    {
        return $query->where('disponibilite', 'disponible');
    }

    public function scopeAvecHabilitation($query, $habilitation)
    {
        return $query->whereJsonContains('habilitations_electriques', $habilitation);
    }

    public function scopeParChargeProjet($query, $chargeProjetId)
    {
        return $query->where('charge_projet_id', $chargeProjetId);
    }

    public function scopeGestionnaires($query)
    {
        return $query->where('role_hierarchique', 'gestionnaire');
    }

    public function scopeChargesProjets($query)
    {
        return $query->where('role_hierarchique', 'charge_projet');
    }

    public function scopeEmployes($query)
    {
        return $query->where('role_hierarchique', 'employe');
    }

    // Méthodes métier
    public function peutIntervenirSur($typeIntervention): bool
    {
        $competencesRequises = [
            'haute_tension' => ['H0', 'H1V', 'H2V'],
            'basse_tension' => ['B1V', 'B2V', 'BR'],
            'hors_tension' => ['B0'],
        ];

        if (!isset($competencesRequises[$typeIntervention])) {
            return false;
        }

        $habilitations = $this->habilitations_electriques ?? [];
        
        return !empty(array_intersect($competencesRequises[$typeIntervention], $habilitations));
    }

    public function formatHabilitations(): string
    {
        if (empty($this->habilitations_electriques)) {
            return 'Aucune';
        }

        return implode(', ', $this->habilitations_electriques);
    }

    public function getNiveauExperienceAttribute(): string
    {
        $anciennete = now()->diffInYears($this->date_debut);
        
        return match (true) {
            $anciennete < 1 => 'Débutant',
            $anciennete < 3 => 'Junior',
            $anciennete < 7 => 'Confirmé',
            $anciennete < 15 => 'Senior',
            default => 'Expert'
        };
    }

    public function estEnConge(): bool
    {
        return in_array($this->disponibilite, ['conge', 'arret_maladie']);
    }

    public function estEnFormation(): bool
    {
        return $this->disponibilite === 'formation';
    }
}