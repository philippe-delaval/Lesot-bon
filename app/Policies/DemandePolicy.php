<?php

namespace App\Policies;

use App\Models\Demande;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class DemandePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Demande $demande): bool
    {
        return $user->id === $demande->createur_id || 
               $user->id === $demande->receveur_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Demande $demande): bool
    {
        if ($user->id === $demande->createur_id) {
            return in_array($demande->statut, ['en_attente', 'assignee']);
        }
        
        if ($user->id === $demande->receveur_id) {
            return in_array($demande->statut, ['assignee', 'en_cours']);
        }
        
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Demande $demande): bool
    {
        return $user->id === $demande->createur_id && 
               $demande->statut === 'en_attente';
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function assign(User $user, Demande $demande): bool
    {
        return $user->id === $demande->createur_id && 
               $demande->statut === 'en_attente';
    }

    public function complete(User $user, Demande $demande): bool
    {
        return $user->id === $demande->receveur_id && 
               in_array($demande->statut, ['assignee', 'en_cours']);
    }

    public function convertToAttachement(User $user, Demande $demande): bool
    {
        return ($user->id === $demande->receveur_id || $user->id === $demande->createur_id) &&
               $demande->statut === 'terminee' &&
               !$demande->attachement_id;
    }

    public function restore(User $user, Demande $demande): bool
    {
        return $user->id === $demande->createur_id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Demande $demande): bool
    {
        return $user->id === $demande->createur_id;
    }
}
