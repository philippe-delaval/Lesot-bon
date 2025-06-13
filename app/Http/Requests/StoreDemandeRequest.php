<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDemandeRequest extends FormRequest
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
            'priorite' => 'required|in:basse,normale,haute,urgente',
            'client_id' => 'nullable|exists:clients,id',
            'lieu_intervention' => 'nullable|string|max:255',
            'date_souhaite_intervention' => 'nullable|date|after:today',
            'receveur_id' => 'nullable|exists:users,id',
        ];
    }

    public function messages(): array
    {
        return [
            'titre.required' => 'Le titre est requis.',
            'titre.max' => 'Le titre ne peut pas dépasser 255 caractères.',
            'description.required' => 'La description est requise.',
            'priorite.required' => 'La priorité est requise.',
            'priorite.in' => 'La priorité doit être: basse, normale, haute ou urgente.',
            'client_id.exists' => 'Le client sélectionné n\'existe pas.',
            'lieu_intervention.max' => 'Le lieu d\'intervention ne peut pas dépasser 255 caractères.',
            'date_souhaite_intervention.date' => 'La date souhaitée doit être une date valide.',
            'date_souhaite_intervention.after' => 'La date souhaitée doit être dans le futur.',
            'receveur_id.exists' => 'L\'utilisateur assigné n\'existe pas.',
        ];
    }
}
