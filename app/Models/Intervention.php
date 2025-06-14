<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class Intervention extends Model
{
    /** @use HasFactory<\Database\Factories\InterventionFactory> */
    use HasFactory;

    protected $fillable = [
        'numero_intervention',
        'demande_id',
        'technicien_id',
        'client_id',
        'type_intervention',
        'description_technique',
        'competences_requises',
        'priorite',
        'statut',
        'date_planifiee',
        'heure_debut_prevue',
        'heure_fin_prevue',
        'heure_arrivee',
        'heure_debut_relle',
        'heure_fin_relle',
        'duree_estimee_minutes',
        'duree_relle_minutes',
        'adresse_intervention',
        'latitude',
        'longitude',
        'instructions_acces',
        'distance_technicien_km',
        'temps_trajet_estime_min',
        'equipements_necessaires',
        'pieces_detachees',
        'outils_speciaux',
        'cout_estime',
        'cout_reel',
        'diagnostic',
        'actions_realisees',
        'pieces_utilisees',
        'photos_avant',
        'photos_apres',
        'signature_client_path',
        'rapport_technique',
        'intervention_reussie',
        'problemes_rencontres',
        'recommandations',
        'note_client',
        'commentaire_client',
        'facturation_automatique',
        'date_facturation',
        'numero_facture',
        'first_time_fix',
        'temps_resolution_minutes',
        'kpis'
    ];

    protected $casts = [
        'competences_requises' => 'array',
        'date_planifiee' => 'datetime',
        'heure_debut_prevue' => 'datetime',
        'heure_fin_prevue' => 'datetime',
        'heure_arrivee' => 'datetime',
        'heure_debut_relle' => 'datetime',
        'heure_fin_relle' => 'datetime',
        'duree_estimee_minutes' => 'integer',
        'duree_relle_minutes' => 'integer',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'distance_technicien_km' => 'decimal:2',
        'temps_trajet_estime_min' => 'integer',
        'equipements_necessaires' => 'array',
        'pieces_detachees' => 'array',
        'outils_speciaux' => 'array',
        'cout_estime' => 'decimal:2',
        'cout_reel' => 'decimal:2',
        'pieces_utilisees' => 'array',
        'photos_avant' => 'array',
        'photos_apres' => 'array',
        'intervention_reussie' => 'boolean',
        'note_client' => 'integer',
        'facturation_automatique' => 'boolean',
        'date_facturation' => 'datetime',
        'first_time_fix' => 'boolean',
        'temps_resolution_minutes' => 'integer',
        'kpis' => 'array'
    ];

    public function demande(): BelongsTo
    {
        return $this->belongsTo(Demande::class);
    }

    public function technicien(): BelongsTo
    {
        return $this->belongsTo(Technicien::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(InterventionLog::class);
    }

    public function scopeEnCours(Builder $query): void
    {
        $query->whereIn('statut', ['planifiee', 'en_route', 'sur_site', 'en_cours']);
    }

    public function scopeUrgente(Builder $query): void
    {
        $query->where('priorite', 'urgente');
    }

    public function scopePourTechnicien(Builder $query, int $technicienId): void
    {
        $query->where('technicien_id', $technicienId);
    }

    public function scopeDansZone(Builder $query, float $latitude, float $longitude, float $rayonKm = 50): void
    {
        $query->whereRaw(
            "ST_DWithin(ST_Point(longitude, latitude)::geography, ST_Point(?, ?)::geography, ?)",
            [$longitude, $latitude, $rayonKm * 1000]
        );
    }

    public function demarrerIntervention(): void
    {
        $this->update([
            'statut' => 'en_cours',
            'heure_debut_relle' => now()
        ]);

        $this->ajouterLog('debut', 'planifiee', 'en_cours', 'Début de l\'intervention');
    }

    public function terminerIntervention(bool $succes = true, ?string $diagnostic = null): void
    {
        $dureeReelle = $this->heure_debut_relle 
            ? now()->diffInMinutes($this->heure_debut_relle)
            : null;

        $this->update([
            'statut' => 'terminee',
            'heure_fin_relle' => now(),
            'duree_relle_minutes' => $dureeReelle,
            'intervention_reussie' => $succes,
            'diagnostic' => $diagnostic
        ]);

        $this->calculerKPIs();
        $this->ajouterLog('fin', 'en_cours', 'terminee', 'Fin de l\'intervention');
    }

    public function calculerKPIs(): void
    {
        $kpis = [];

        if ($this->heure_debut_relle && $this->heure_fin_relle) {
            $kpis['duree_relle_minutes'] = $this->heure_fin_relle->diffInMinutes($this->heure_debut_relle);
        }

        if ($this->duree_estimee_minutes && isset($kpis['duree_relle_minutes'])) {
            $kpis['respect_planning'] = abs($kpis['duree_relle_minutes'] - $this->duree_estimee_minutes) <= 30;
        }

        if ($this->cout_estime && $this->cout_reel) {
            $kpis['respect_budget'] = $this->cout_reel <= $this->cout_estime * 1.1;
        }

        $kpis['satisfaction_client'] = $this->note_client >= 4;
        $kpis['first_time_fix'] = $this->first_time_fix ?? false;

        $this->update(['kpis' => $kpis]);
    }

    public function calculerDistanceDepuisTechnicien(): ?float
    {
        if (!$this->technicien || !$this->technicien->latitude || !$this->technicien->longitude) {
            return null;
        }

        return $this->technicien->calculerDistanceVers($this->latitude, $this->longitude);
    }

    public function estEnRetard(): bool
    {
        if (!$this->heure_debut_prevue) {
            return false;
        }

        return now()->isAfter($this->heure_debut_prevue) && 
               !in_array($this->statut, ['en_cours', 'terminee']);
    }

    public function peutEtreAssigneeA(Technicien $technicien): bool
    {
        if (!$technicien->actif || $technicien->statut !== 'disponible') {
            return false;
        }

        return $technicien->peutRealiserIntervention($this->competences_requises ?? []);
    }

    public function ajouterLog(string $action, ?string $statutAvant = null, ?string $statutApres = null, ?string $description = null): void
    {
        $this->logs()->create([
            'technicien_id' => $this->technicien_id,
            'action' => $action,
            'statut_avant' => $statutAvant,
            'statut_apres' => $statutApres,
            'description' => $description,
            'timestamp_action' => now(),
            'latitude' => $this->technicien?->latitude,
            'longitude' => $this->technicien?->longitude
        ]);
    }

    public function genererNumeroIntervention(): string
    {
        $date = now()->format('Ymd');
        $sequence = static::whereDate('created_at', today())->count() + 1;
        
        return "INT-{$date}-" . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($intervention) {
            if (!$intervention->numero_intervention) {
                $intervention->numero_intervention = $intervention->genererNumeroIntervention();
            }
        });
    }
}
