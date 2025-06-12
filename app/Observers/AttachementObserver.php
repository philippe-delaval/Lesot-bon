<?php

namespace App\Observers;

use App\Models\Attachement;
use Illuminate\Support\Facades\Log;

class AttachementObserver
{
    /**
     * Handle the Attachement "created" event.
     */
    public function created(Attachement $attachement): void
    {
        Log::info('Attachement créé', [
            'id' => $attachement->id,
            'numero_dossier' => $attachement->numero_dossier,
            'client_nom' => $attachement->client_nom,
            'date_intervention' => $attachement->date_intervention,
            'created_by' => $attachement->created_by,
            'action' => 'created',
            'timestamp' => now()
        ]);
    }

    /**
     * Handle the Attachement "updated" event.
     */
    public function updated(Attachement $attachement): void
    {
        $changes = $attachement->getChanges();
        $original = $attachement->getOriginal();
        
        // Ne log que si il y a des changements significatifs
        $significantFields = [
            'client_nom', 'nom_signataire_client', 'client_email',
            'date_intervention', 'designation_travaux', 'fournitures_travaux',
            'temps_total_passe', 'signature_entreprise_path', 'signature_client_path'
        ];
        
        $significantChanges = array_intersect_key($changes, array_flip($significantFields));
        
        if (!empty($significantChanges)) {
            Log::info('Attachement modifié', [
                'id' => $attachement->id,
                'numero_dossier' => $attachement->numero_dossier,
                'changes' => $significantChanges,
                'original_values' => array_intersect_key($original, $significantChanges),
                'action' => 'updated',
                'timestamp' => now()
            ]);
        }
    }

    /**
     * Handle the Attachement "deleted" event.
     */
    public function deleted(Attachement $attachement): void
    {
        Log::warning('Attachement supprimé', [
            'id' => $attachement->id,
            'numero_dossier' => $attachement->numero_dossier,
            'client_nom' => $attachement->client_nom,
            'date_intervention' => $attachement->date_intervention,
            'action' => 'deleted',
            'timestamp' => now()
        ]);
    }

    /**
     * Handle the Attachement "restored" event.
     */
    public function restored(Attachement $attachement): void
    {
        Log::info('Attachement restauré', [
            'id' => $attachement->id,
            'numero_dossier' => $attachement->numero_dossier,
            'action' => 'restored',
            'timestamp' => now()
        ]);
    }

    /**
     * Handle the Attachement "force deleted" event.
     */
    public function forceDeleted(Attachement $attachement): void
    {
        Log::warning('Attachement supprimé définitivement', [
            'id' => $attachement->id,
            'numero_dossier' => $attachement->numero_dossier,
            'action' => 'force_deleted',
            'timestamp' => now()
        ]);
    }

    /**
     * Handle signatures updates
     */
    public function saving(Attachement $attachement): void
    {
        // Vérifier si les signatures ont été ajoutées
        if ($attachement->isDirty('signature_entreprise_path') && !empty($attachement->signature_entreprise_path)) {
            Log::info('Signature entreprise ajoutée', [
                'id' => $attachement->id,
                'numero_dossier' => $attachement->numero_dossier,
                'signature_path' => $attachement->signature_entreprise_path,
                'timestamp' => now()
            ]);
        }

        if ($attachement->isDirty('signature_client_path') && !empty($attachement->signature_client_path)) {
            Log::info('Signature client ajoutée', [
                'id' => $attachement->id,
                'numero_dossier' => $attachement->numero_dossier,
                'signature_path' => $attachement->signature_client_path,
                'nom_signataire' => $attachement->nom_signataire_client,
                'timestamp' => now()
            ]);
        }

        // Vérifier si le PDF a été généré
        if ($attachement->isDirty('pdf_path') && !empty($attachement->pdf_path)) {
            Log::info('PDF généré', [
                'id' => $attachement->id,
                'numero_dossier' => $attachement->numero_dossier,
                'pdf_path' => $attachement->pdf_path,
                'timestamp' => now()
            ]);
        }
    }
}