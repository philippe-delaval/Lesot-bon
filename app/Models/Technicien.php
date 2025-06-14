<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Technicien extends Model
{
    /** @use HasFactory<\Database\Factories\TechnicienFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'matricule',
        'competences',
        'certifications',
        'niveau_experience',
        'tarif_horaire',
        'zone_intervention',
        'statut',
        'planning',
        'latitude',
        'longitude',
        'derniere_position',
        'vehicule_type',
        'vehicule_immatriculation',
        'charge_travail_actuelle',
        'equipements_mobiles',
        'actif'
    ];

    protected $casts = [
        'competences' => 'array',
        'certifications' => 'array',
        'zone_intervention' => 'array',
        'planning' => 'array',
        'equipements_mobiles' => 'array',
        'derniere_position' => 'datetime',
        'tarif_horaire' => 'decimal:2',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'charge_travail_actuelle' => 'integer',
        'actif' => 'boolean'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function interventions(): HasMany
    {
        return $this->hasMany(Intervention::class);
    }

    public function interventionLogs(): HasMany
    {
        return $this->hasMany(InterventionLog::class);
    }

    public function equipements(): HasMany
    {
        return $this->hasMany(Equipement::class);
    }

    public function scopeDisponible(Builder $query): void
    {
        $query->where('statut', 'disponible')->where('actif', true);
    }

    public function scopeAvecCompetence(Builder $query, string $competence): void
    {
        $query->whereJsonContains('competences', $competence);
    }

    public function scopeDansZone(Builder $query, float $latitude, float $longitude, float $rayonKm = 50): void
    {
        $query->whereRaw(
            "ST_DWithin(ST_Point(longitude, latitude)::geography, ST_Point(?, ?)::geography, ?)",
            [$longitude, $latitude, $rayonKm * 1000]
        );
    }

    public function peutRealiserIntervention(array $competencesRequises): bool
    {
        return empty(array_diff($competencesRequises, $this->competences ?? []));
    }

    public function calculerDistanceVers(float $latitude, float $longitude): ?float
    {
        if (!$this->latitude || !$this->longitude) {
            return null;
        }

        $earthRadius = 6371;
        $latFrom = deg2rad($this->latitude);
        $lonFrom = deg2rad($this->longitude);
        $latTo = deg2rad($latitude);
        $lonTo = deg2rad($longitude);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos($latFrom) * cos($latTo) *
             sin($lonDelta / 2) * sin($lonDelta / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    public function mettreAJourPosition(float $latitude, float $longitude): void
    {
        $this->update([
            'latitude' => $latitude,
            'longitude' => $longitude,
            'derniere_position' => now()
        ]);
    }

    public function obtenirInterventionEnCours(): ?Intervention
    {
        return $this->interventions()
            ->whereIn('statut', ['en_route', 'sur_site', 'en_cours'])
            ->first();
    }

    public function calculerChargeTravailde(): int
    {
        $interventionsEnCours = $this->interventions()
            ->whereIn('statut', ['planifiee', 'en_route', 'sur_site', 'en_cours'])
            ->count();

        return min(100, $interventionsEnCours * 25);
    }
}
