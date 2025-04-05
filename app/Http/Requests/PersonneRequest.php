<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PersonneRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'date' => 'required|date',
            'sexe' => 'required|in:M,F',
            'tel' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'profil' => 'required|in:admin,enseignant,etudiant,parent,responsable',
            'password' => 'sometimes|min:8',
            'filiere_id' => 'nullable|exists:filieres,id',
            'matiere_id' => 'nullable|exists:matieres,id',
        ];

        // Si c'est une mise à jour, rendre email unique sauf pour le record actuel
        if ($this->method() == 'PUT' || $this->method() == 'PATCH') {
            $personne = $this->route('personne');
            $rules['email'] = 'required|email|max:255|unique:personnes,email,' . $personne->id;
        } else {
            $rules['email'] = 'required|email|max:255|unique:personnes,email';
        }

        return $rules;
    }
    
    /**
     * Messages d'erreur personnalisés
     */
    public function messages()
    {
        return [
            'nom.required' => 'Le nom est obligatoire',
            'nom.max' => 'Le nom ne doit pas dépasser 255 caractères',
            'prenom.required' => 'Le prénom est obligatoire',
            'prenom.max' => 'Le prénom ne doit pas dépasser 255 caractères',
            'date.required' => 'La date de naissance est obligatoire',
            'date.date' => 'Le format de la date de naissance est invalide',
            'sexe.required' => 'Le sexe est obligatoire',
            'sexe.in' => 'Le sexe doit être M ou F',
            'tel.required' => 'Le numéro de téléphone est obligatoire',
            'tel.max' => 'Le numéro de téléphone ne doit pas dépasser 20 caractères',
            'email.required' => 'L\'email est obligatoire',
            'email.email' => 'Le format de l\'email est invalide',
            'email.max' => 'L\'email ne doit pas dépasser 255 caractères',
            'email.unique' => 'Cet email est déjà utilisé',
            'profil.required' => 'Le profil est obligatoire',
            'profil.in' => 'Le profil doit être admin, enseignant, etudiant, parent ou responsable',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères',
            'filiere_id.exists' => 'La filière sélectionnée n\'existe pas',
            'matiere_id.exists' => 'La matière sélectionnée n\'existe pas',
        ];
    }
}