<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attachement extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'client_id',
        'numero_dossier',
        'client_nom',
        'client_email',
        'client_adresse_facturation',
        'lieu_intervention',
        'date_intervention',
        'designation_travaux',
        'fournitures_travaux',
        'temps_total_passe',
        'signature_entreprise_path',
        'signature_client_path',
        'pdf_path',
        'geolocation',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date_intervention' => 'date',
        'fournitures_travaux' => 'array',
        'geolocation' => 'array',
        'temps_total_passe' => 'decimal:2',
    ];

    /**
     * Get the user who created the attachement.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the client associated with the attachement.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the full URL for the PDF file.
     */
    public function getPdfUrlAttribute(): string
    {
        return asset('storage/' . $this->pdf_path);
    }

    /**
     * Get the full URL for the entreprise signature.
     */
    public function getSignatureEntrepriseUrlAttribute(): string
    {
        return asset('storage/' . $this->signature_entreprise_path);
    }

    /**
     * Get the full URL for the client signature.
     */
    public function getSignatureClientUrlAttribute(): string
    {
        return asset('storage/' . $this->signature_client_path);
    }

    /**
     * Get formatted geolocation string.
     */
    public function getFormattedGeolocationAttribute(): ?string
    {
        if (!$this->geolocation || !isset($this->geolocation['latitude']) || !isset($this->geolocation['longitude'])) {
            return null;
        }

        return sprintf(
            '%.6f, %.6f',
            $this->geolocation['latitude'],
            $this->geolocation['longitude']
        );
    }

    /**
     * Scope a query to search attachements.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('numero_dossier', 'like', "%{$search}%")
                ->orWhere('client_nom', 'like', "%{$search}%")
                ->orWhere('lieu_intervention', 'like', "%{$search}%")
                ->orWhere('designation_travaux', 'like', "%{$search}%");
        });
    }

    /**
     * Scope a query to filter by date range.
     */
    public function scopeDateBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('date_intervention', [$startDate, $endDate]);
    }
}
