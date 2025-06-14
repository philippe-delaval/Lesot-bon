<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class InterventionLog extends Model
{
    /** @use HasFactory<\Database\Factories\InterventionLogFactory> */
    use HasFactory;

    protected $fillable = [
        'intervention_id',
        'technicien_id',
        'action',
        'statut_avant',
        'statut_apres',
        'description',
        'donnees_supplementaires',
        'latitude',
        'longitude',
        'timestamp_action',
        'photos',
        'commentaire_technicien'
    ];

    protected $casts = [
        'donnees_supplementaires' => 'array',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'timestamp_action' => 'datetime',
        'photos' => 'array'
    ];

    public function intervention(): BelongsTo
    {
        return $this->belongsTo(Intervention::class);
    }

    public function technicien(): BelongsTo
    {
        return $this->belongsTo(Technicien::class);
    }

    public function scopePourIntervention(Builder $query, int $interventionId): void
    {
        $query->where('intervention_id', $interventionId);
    }

    public function scopePourTechnicien(Builder $query, int $technicienId): void
    {
        $query->where('technicien_id', $technicienId);
    }

    public function scopeParAction(Builder $query, string $action): void
    {
        $query->where('action', $action);
    }

    public function scopeEntreDate(Builder $query, \DateTime $debut, \DateTime $fin): void
    {
        $query->whereBetween('timestamp_action', [$debut, $fin]);
    }

    public function scopeAvecGeolocalisation(Builder $query): void
    {
        $query->whereNotNull('latitude')->whereNotNull('longitude');
    }

    public function ajouterPhoto(string $cheminPhoto): void
    {
        $photos = $this->photos ?? [];
        $photos[] = $cheminPhoto;
        
        $this->update(['photos' => $photos]);
    }

    public function supprimerPhoto(string $cheminPhoto): void
    {
        $photos = $this->photos ?? [];
        $photos = array_filter($photos, fn($photo) => $photo !== $cheminPhoto);
        
        $this->update(['photos' => array_values($photos)]);
    }

    public function obtenirDureeDepuisAction(): ?int
    {
        if (!$this->timestamp_action) {
            return null;
        }

        return now()->diffInMinutes($this->timestamp_action);
    }

    public function estAction(string $action): bool
    {
        return $this->action === $action;
    }

    public function aGeolocalisation(): bool
    {
        return $this->latitude !== null && $this->longitude !== null;
    }

    public function calculerDistanceDepuis(float $latitude, float $longitude): ?float
    {
        if (!$this->aGeolocalisation()) {
            return null;
        }

        $earthRadius = 6371;
        $latFrom = deg2rad($latitude);
        $lonFrom = deg2rad($longitude);
        $latTo = deg2rad($this->latitude);
        $lonTo = deg2rad($this->longitude);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos($latFrom) * cos($latTo) *
             sin($lonDelta / 2) * sin($lonDelta / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    public function formaterPourRapport(): array
    {
        return [
            'timestamp' => $this->timestamp_action?->format('d/m/Y H:i:s'),
            'action' => $this->action,
            'description' => $this->description,
            'technicien' => $this->technicien?->user?->name,
            'statut_change' => $this->statut_avant && $this->statut_apres 
                ? "{$this->statut_avant} → {$this->statut_apres}"
                : null,
            'geolocalisation' => $this->aGeolocalisation() 
                ? ['lat' => $this->latitude, 'lng' => $this->longitude]
                : null,
            'photos_count' => count($this->photos ?? []),
            'commentaire' => $this->commentaire_technicien
        ];
    }

    public static function creerLog(
        int $interventionId,
        int $technicienId,
        string $action,
        ?string $description = null,
        ?array $donneesSupplementaires = null
    ): self {
        return static::create([
            'intervention_id' => $interventionId,
            'technicien_id' => $technicienId,
            'action' => $action,
            'description' => $description,
            'donnees_supplementaires' => $donneesSupplementaires,
            'timestamp_action' => now()
        ]);
    }

    public static function creerLogAvecPosition(
        int $interventionId,
        int $technicienId,
        string $action,
        float $latitude,
        float $longitude,
        ?string $description = null
    ): self {
        return static::create([
            'intervention_id' => $interventionId,
            'technicien_id' => $technicienId,
            'action' => $action,
            'description' => $description,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'timestamp_action' => now()
        ]);
    }

    public function obtenirResume(): string
    {
        $resume = $this->timestamp_action?->format('H:i') . ' - ' . ucfirst($this->action);
        
        if ($this->description) {
            $resume .= ': ' . $this->description;
        }
        
        if ($this->statut_avant && $this->statut_apres) {
            $resume .= " ({$this->statut_avant} → {$this->statut_apres})";
        }
        
        return $resume;
    }
}