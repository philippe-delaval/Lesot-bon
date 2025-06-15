<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;

class Planning extends Model
{
    use HasFactory;

    protected $fillable = [
        'employe_id',
        'demande_id',
        'equipe_id',
        'date_debut',
        'date_fin',
        'heure_debut_prevue',
        'heure_fin_prevue',
        'heure_debut_reelle',
        'heure_fin_reelle',
        'type_affectation',
        'statut',
        'lieu_intervention',
        'coordonnees_gps',
        'description_tache',
        'materiels_requis',
        'duree_estimee_minutes',
        'duree_reelle_minutes',
        'vehicule_utilise',
        'kilometres_parcourus',
        'frais_deplacement',
        'cree_par_id',
        'valide_par_id',
        'date_validation',
        'commentaires',
        'rapport_intervention',
        'difficulte',
        'note_client',
        'objectifs_atteints'
    ];

    protected $casts = [
        'date_debut' => 'datetime',
        'date_fin' => 'datetime',
        'heure_debut_prevue' => 'datetime:H:i',
        'heure_fin_prevue' => 'datetime:H:i',
        'heure_debut_reelle' => 'datetime:H:i',
        'heure_fin_reelle' => 'datetime:H:i',
        'date_validation' => 'datetime',
        'coordonnees_gps' => 'array',
        'materiels_requis' => 'array',
        'kilometres_parcourus' => 'decimal:2',
        'frais_deplacement' => 'decimal:2',
        'note_client' => 'integer',
        'objectifs_atteints' => 'boolean'
    ];

    // Relations
    public function employe(): BelongsTo
    {
        return $this->belongsTo(Employe::class);
    }

    public function demande(): BelongsTo
    {
        return $this->belongsTo(Demande::class);
    }

    public function equipe(): BelongsTo
    {
        return $this->belongsTo(Equipe::class);
    }

    public function creePar(): BelongsTo
    {
        return $this->belongsTo(Employe::class, 'cree_par_id');
    }

    public function validePar(): BelongsTo
    {
        return $this->belongsTo(Employe::class, 'valide_par_id');
    }

    // Accesseurs
    protected function dureeEstimee(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->duree_estimee_minutes 
                ? $this->formatDuree($this->duree_estimee_minutes)
                : null,
        );
    }

    protected function dureeReelle(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->duree_reelle_minutes 
                ? $this->formatDuree($this->duree_reelle_minutes)
                : null,
        );
    }

    protected function estEnCours(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->statut === 'en_cours',
        );
    }

    protected function estTermine(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->statut === 'termine',
        );
    }

    protected function estEnRetard(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->date_fin < now() && !$this->est_termine,
        );
    }

    // Scopes
    public function scopeEnCours($query)
    {
        return $query->where('statut', 'en_cours');
    }

    public function scopePlanifies($query)
    {
        return $query->where('statut', 'planifie');
    }

    public function scopeTermines($query)
    {
        return $query->where('statut', 'termine');
    }

    public function scopeParEmploye($query, $employeId)
    {
        return $query->where('employe_id', $employeId);
    }

    public function scopeParEquipe($query, $equipeId)
    {
        return $query->where('equipe_id', $equipeId);
    }

    public function scopeParPeriode($query, $dateDebut, $dateFin)
    {
        return $query->whereBetween('date_debut', [$dateDebut, $dateFin]);
    }

    public function scopeParType($query, $type)
    {
        return $query->where('type_affectation', $type);
    }

    public function scopeInterventions($query)
    {
        return $query->where('type_affectation', 'intervention');
    }

    public function scopeConges($query)
    {
        return $query->where('type_affectation', 'conge');
    }

    public function scopeFormations($query)
    {
        return $query->where('type_affectation', 'formation');
    }

    public function scopeAstreintes($query)
    {
        return $query->where('type_affectation', 'astreinte');
    }

    public function scopeValides($query)
    {
        return $query->whereNotNull('valide_par_id');
    }

    public function scopeEnAttente($query)
    {
        return $query->whereNull('valide_par_id');
    }

    // Méthodes métier
    public function peutEtreModifie(): bool
    {
        return in_array($this->statut, ['planifie', 'en_attente']) && 
               $this->date_debut > now()->addHour();
    }

    public function peutEtreAnnule(): bool
    {
        return !in_array($this->statut, ['termine', 'annule']) &&
               $this->date_debut > now();
    }

    public function demarrer(): bool
    {
        if ($this->statut !== 'planifie') {
            return false;
        }

        $this->update([
            'statut' => 'en_cours',
            'heure_debut_reelle' => now()->format('H:i')
        ]);

        return true;
    }

    public function terminer(array $donneesFinales = []): bool
    {
        if ($this->statut !== 'en_cours') {
            return false;
        }

        $donneesUpdate = array_merge([
            'statut' => 'termine',
            'heure_fin_reelle' => now()->format('H:i')
        ], $donneesFinales);

        // Calculer la durée réelle si les heures sont disponibles
        if ($this->heure_debut_reelle && !isset($donneesUpdate['duree_reelle_minutes'])) {
            $debut = Carbon::createFromFormat('H:i', $this->heure_debut_reelle);
            $fin = Carbon::createFromFormat('H:i', $donneesUpdate['heure_fin_reelle']);
            $donneesUpdate['duree_reelle_minutes'] = $fin->diffInMinutes($debut);
        }

        $this->update($donneesUpdate);

        return true;
    }

    public function valider(Employe $validateur, string $commentaires = null): bool
    {
        $this->update([
            'valide_par_id' => $validateur->id,
            'date_validation' => now(),
            'commentaires' => $commentaires
        ]);

        return true;
    }

    public function calculerRetard(): ?int
    {
        if ($this->statut !== 'termine' || !$this->heure_fin_prevue || !$this->heure_fin_reelle) {
            return null;
        }

        $prevue = Carbon::createFromFormat('H:i', $this->heure_fin_prevue);
        $reelle = Carbon::createFromFormat('H:i', $this->heure_fin_reelle);

        $retard = $reelle->diffInMinutes($prevue, false);
        
        return $retard > 0 ? $retard : 0;
    }

    public function getStatutColor(): string
    {
        return match ($this->statut) {
            'planifie' => 'bg-blue-100 text-blue-800',
            'en_cours' => 'bg-yellow-100 text-yellow-800',
            'termine' => 'bg-green-100 text-green-800',
            'annule' => 'bg-red-100 text-red-800',
            'reporte' => 'bg-orange-100 text-orange-800',
            'en_attente' => 'bg-gray-100 text-gray-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    public function getTypeColor(): string
    {
        return match ($this->type_affectation) {
            'intervention' => 'bg-blue-500',
            'maintenance' => 'bg-green-500',
            'formation' => 'bg-purple-500',
            'conge' => 'bg-yellow-500',
            'arret_maladie' => 'bg-red-500',
            'deplacement' => 'bg-indigo-500',
            'administratif' => 'bg-gray-500',
            'astreinte' => 'bg-orange-500',
            default => 'bg-gray-400'
        };
    }

    private function formatDuree(int $minutes): string
    {
        $heures = intval($minutes / 60);
        $mins = $minutes % 60;

        if ($heures > 0) {
            return $mins > 0 ? "{$heures}h{$mins}min" : "{$heures}h";
        }

        return "{$mins}min";
    }

    public function conflitAvec(Planning $autrePlanning): bool
    {
        return $this->employe_id === $autrePlanning->employe_id &&
               $this->id !== $autrePlanning->id &&
               !(
                   $this->date_fin <= $autrePlanning->date_debut ||
                   $this->date_debut >= $autrePlanning->date_fin
               );
    }

    public function genererRapport(): array
    {
        return [
            'employe' => $this->employe->nom_complet,
            'periode' => $this->date_debut->format('d/m/Y H:i') . ' - ' . $this->date_fin->format('d/m/Y H:i'),
            'duree_prevue' => $this->duree_estimee,
            'duree_reelle' => $this->duree_reelle,
            'retard' => $this->calculerRetard(),
            'lieu' => $this->lieu_intervention,
            'statut' => $this->statut,
            'note_client' => $this->note_client,
            'objectifs_atteints' => $this->objectifs_atteints
        ];
    }
}