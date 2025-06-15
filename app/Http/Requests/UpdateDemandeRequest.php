<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDemandeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'titre' => 'required|string|max:255',
            'description' => 'required|string',
            'priorite' => 'required|in:normale,haute,urgente',
            'statut' => 'required|in:en_attente,assignee,en_cours,terminee,annulee',
            'client_id' => 'nullable|exists:clients,id',
            'lieu_intervention' => 'nullable|string|max:255',
            'date_souhaite_intervention' => 'nullable|date',
            'receveur_id' => 'nullable|exists:users,id',
            'notes_receveur' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'titre.required' => 'Le titre est requis.',
            'titre.max' => 'Le titre ne peut pas dépasser 255 caractères.',
            'description.required' => 'La description est requise.',
            'priorite.required' => 'La priorité est requise.',
            'priorite.in' => 'La priorité doit être: normale, haute ou urgente.',
            'statut.required' => 'Le statut est requis.',
            'statut.in' => 'Le statut doit être: en_attente, assignee, en_cours, terminee ou annulee.',
            'client_id.exists' => 'Le client sélectionné n\'existe pas.',
            'lieu_intervention.max' => 'Le lieu d\'intervention ne peut pas dépasser 255 caractères.',
            'date_souhaite_intervention.date' => 'La date souhaitée doit être une date valide.',
            'receveur_id.exists' => 'L\'utilisateur assigné n\'existe pas.',
        ];
    }
}
