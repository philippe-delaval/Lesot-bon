<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Equipement extends Model
{
    /** @use HasFactory<\Database\Factories\EquipementFactory> */
    use HasFactory;

    protected $fillable = [
        'reference',
        'nom',
        'description',
        'type',
        'categorie',
        'marque',
        'modele',
        'numero_serie',
        'stock_total',
        'stock_disponible',
        'stock_minimum',
        'localisation_depot',
        'latitude',
        'longitude',
        'prix_unitaire',
        'cout_maintenance',
        'fournisseur',
        'contact_fournisseur',
        'date_achat',
        'date_mise_service',
        'duree_vie_mois',
        'prochaine_maintenance',
        'historique_maintenance',
        'etat',
        'technicien_id',
        'statut',
        'competences_associees',
        'transportable',
        'poids_kg',
        'instructions_utilisation',
        'historique_utilisation',
        'derniere_utilisation',
        'nombre_utilisations',
        'actif'
    ];

    protected $casts = [
        'stock_total' => 'integer',
        'stock_disponible' => 'integer',
        'stock_minimum' => 'integer',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'prix_unitaire' => 'decimal:2',
        'cout_maintenance' => 'decimal:2',
        'date_achat' => 'date',
        'date_mise_service' => 'date',
        'duree_vie_mois' => 'integer',
        'prochaine_maintenance' => 'date',
        'competences_associees' => 'array',
        'transportable' => 'boolean',
        'poids_kg' => 'decimal:2',
        'historique_utilisation' => 'array',
        'derniere_utilisation' => 'datetime',
        'nombre_utilisations' => 'integer',
        'actif' => 'boolean'
    ];

    public function technicien(): BelongsTo
    {
        return $this->belongsTo(Technicien::class);
    }

    public function scopeDisponible(Builder $query): void
    {
        $query->where('statut', 'disponible')
              ->where('stock_disponible', '>', 0)
              ->where('actif', true);
    }

    public function scopeEnRupture(Builder $query): void
    {
        $query->where('stock_disponible', '<=', 'stock_minimum');
    }

    public function scopeParType(Builder $query, string $type): void
    {
        $query->where('type', $type);
    }

    public function scopeParCategorie(Builder $query, string $categorie): void
    {
        $query->where('categorie', $categorie);
    }

    public function scopeTransportable(Builder $query): void
    {
        $query->where('transportable', true);
    }

    public function scopeAvecCompetence(Builder $query, string $competence): void
    {
        $query->whereJsonContains('competences_associees', $competence);
    }

    public function scopeMaintenanceRequise(Builder $query): void
    {
        $query->where('prochaine_maintenance', '<=', now()->addDays(7))
              ->where('actif', true);
    }

    public function reserver(int $quantite = 1): bool
    {
        if ($this->stock_disponible < $quantite) {
            return false;
        }

        $this->decrement('stock_disponible', $quantite);
        
        if ($this->stock_disponible == 0) {
            $this->update(['statut' => 'reserve']);
        }

        $this->ajouterHistoriqueUtilisation('reservation', $quantite);
        
        return true;
    }

    public function liberer(int $quantite = 1): void
    {
        $nouvelleQuantite = min($this->stock_total, $this->stock_disponible + $quantite);
        
        $this->update([
            'stock_disponible' => $nouvelleQuantite,
            'statut' => $nouvelleQuantite > 0 ? 'disponible' : 'reserve'
        ]);

        $this->ajouterHistoriqueUtilisation('liberation', $quantite);
    }

    public function utiliser(int $technicienId, int $quantite = 1): bool
    {
        if (!$this->reserver($quantite)) {
            return false;
        }

        $this->update([
            'technicien_id' => $technicienId,
            'statut' => 'en_utilisation',
            'derniere_utilisation' => now()
        ]);

        $this->increment('nombre_utilisations');
        $this->ajouterHistoriqueUtilisation('utilisation', $quantite, $technicienId);

        return true;
    }

    public function retourner(): void
    {
        $this->update([
            'technicien_id' => null,
            'statut' => 'disponible'
        ]);

        $this->ajouterHistoriqueUtilisation('retour');
    }

    public function planifierMaintenance(\DateTime $date, ?string $description = null): void
    {
        $this->update([
            'prochaine_maintenance' => $date,
            'statut' => 'maintenance'
        ]);

        $historique = $this->historique_maintenance ? json_decode($this->historique_maintenance, true) : [];
        $historique[] = [
            'date' => $date->format('Y-m-d'),
            'description' => $description,
            'planifiee_le' => now()->format('Y-m-d H:i:s')
        ];

        $this->update(['historique_maintenance' => json_encode($historique)]);
    }

    public function terminerMaintenance(string $etat = 'bon', ?string $rapport = null): void
    {
        $this->update([
            'etat' => $etat,
            'statut' => $etat === 'reforme' ? 'reforme' : 'disponible',
            'prochaine_maintenance' => $this->calculerProchaineMaintenance()
        ]);

        $historique = $this->historique_maintenance ? json_decode($this->historique_maintenance, true) : [];
        $dernierIndex = count($historique) - 1;
        
        if ($dernierIndex >= 0) {
            $historique[$dernierIndex]['terminee_le'] = now()->format('Y-m-d H:i:s');
            $historique[$dernierIndex]['rapport'] = $rapport;
            $historique[$dernierIndex]['nouvel_etat'] = $etat;
        }

        $this->update(['historique_maintenance' => json_encode($historique)]);
    }

    public function estEnRupture(): bool
    {
        return $this->stock_disponible <= $this->stock_minimum;
    }

    public function necessiteMaintenance(): bool
    {
        return $this->prochaine_maintenance && 
               $this->prochaine_maintenance->isPast() && 
               $this->actif;
    }

    public function calculerValeurStock(): float
    {
        return $this->stock_total * $this->prix_unitaire;
    }

    public function obtenirTauxUtilisation(): float
    {
        if ($this->stock_total == 0) {
            return 0;
        }

        return (($this->stock_total - $this->stock_disponible) / $this->stock_total) * 100;
    }

    private function calculerProchaineMaintenance(): ?\DateTime
    {
        if (!$this->duree_vie_mois) {
            return null;
        }

        $intervalleMaintenanceMois = max(1, intval($this->duree_vie_mois / 12));
        return now()->addMonths($intervalleMaintenanceMois)->toDateTime();
    }

    private function ajouterHistoriqueUtilisation(string $action, ?int $quantite = null, ?int $technicienId = null): void
    {
        $historique = $this->historique_utilisation ?? [];
        
        $historique[] = [
            'action' => $action,
            'quantite' => $quantite,
            'technicien_id' => $technicienId,
            'timestamp' => now()->toISOString(),
            'stock_apres' => $this->stock_disponible
        ];

        $historique = array_slice($historique, -100);
        
        $this->update(['historique_utilisation' => $historique]);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($equipement) {
            if (!$equipement->reference) {
                $equipement->reference = $equipement->genererReference();
            }
        });
    }

    private function genererReference(): string
    {
        $prefix = strtoupper(substr($this->type, 0, 3));
        $sequence = static::whereYear('created_at', now()->year)->count() + 1;
        
        return $prefix . '-' . now()->format('Y') . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}
