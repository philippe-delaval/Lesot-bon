<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttachementStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Informations client
            'client_id' => 'nullable|exists:clients,id',
            'client_nom' => 'required|string|max:255',
            'nom_signataire_client' => 'required|string|max:255',
            'client_email' => 'required|email|max:255',
            'client_adresse' => 'required|string|max:255',
            'client_complement_adresse' => 'nullable|string|max:255',
            'client_code_postal' => 'required|string|max:10',
            'client_ville' => 'required|string|max:255',

            // Informations de l'intervention
            'numero_dossier' => 'required|string|max:255|unique:attachements,numero_dossier',
            'lieu_intervention' => 'required|string|max:255',
            'date_intervention' => 'required|date',
            'designation_travaux' => 'required|string',
            
            // Fournitures et travaux
            'fournitures_travaux' => 'required|array|min:1',
            'fournitures_travaux.*.designation' => 'required|string|max:255',
            'fournitures_travaux.*.quantite' => 'required|string|max:100',
            'fournitures_travaux.*.observations' => 'nullable|string|max:500',
            
            // Temps et signatures
            'temps_total_passe' => 'required|numeric|min:0|max:999.99',
            'signature_entreprise' => 'required|string',
            'signature_client' => 'required|string',
            
            // Données optionnelles
            'geolocation' => 'nullable|array',
            'geolocation.latitude' => 'nullable|numeric|between:-90,90',
            'geolocation.longitude' => 'nullable|numeric|between:-180,180',
        ];
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nom_signataire_client.required' => 'Le nom de la personne qui signe est obligatoire.',
            'nom_signataire_client.string' => 'Le nom de la personne qui signe doit être une chaîne de caractères.',
            'nom_signataire_client.max' => 'Le nom de la personne qui signe ne peut pas dépasser 255 caractères.',
            
            'client_nom.required' => 'Le nom du client est obligatoire.',
            'client_email.required' => 'L\'email du client est obligatoire.',
            'client_email.email' => 'L\'email du client doit être une adresse email valide.',
            
            'fournitures_travaux.required' => 'Au moins une ligne de fourniture est requise.',
            'fournitures_travaux.*.designation.required' => 'La désignation est obligatoire pour chaque ligne.',
            'fournitures_travaux.*.quantite.required' => 'La quantité est obligatoire pour chaque ligne.',
            
            'signature_entreprise.required' => 'La signature de l\'entreprise est obligatoire.',
            'signature_client.required' => 'La signature du client est obligatoire.',
            
            'numero_dossier.unique' => 'Ce numéro de dossier existe déjà.',
        ];
    }

    /**
     * Get custom attribute names for validation errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'nom_signataire_client' => 'nom de la personne qui signe',
            'client_nom' => 'nom du client',
            'client_email' => 'email du client',
            'client_adresse' => 'adresse du client',
            'client_code_postal' => 'code postal',
            'client_ville' => 'ville',
            'numero_dossier' => 'numéro de dossier',
            'lieu_intervention' => 'lieu d\'intervention',
            'date_intervention' => 'date d\'intervention',
            'designation_travaux' => 'désignation des travaux',
            'temps_total_passe' => 'temps total passé',
            'signature_entreprise' => 'signature de l\'entreprise',
            'signature_client' => 'signature du client',
        ];
    }
}
