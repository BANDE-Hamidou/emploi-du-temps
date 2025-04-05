<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CoursRequest extends FormRequest
{
    /**
     * Déterminer si l'utilisateur est autorisé à faire cette requête
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Règles de validation
     */
    public function rules()
    {
        $rules = [
            'idMatiere' => 'required|exists:matieres,id',
            'creneaux' => 'sometimes|array',
            'creneaux.*' => 'exists:creneaux,id',
            'salles' => 'sometimes|array',
            'salles.*' => 'exists:salles,id',
        ];

        return $rules;
    }
    
    /**
     * Messages d'erreur personnalisés
     */
    public function messages()
    {
        return [
            'idMatiere.required' => 'La matière est obligatoire',
            'idMatiere.exists' => 'La matière sélectionnée n\'existe pas',
            'creneaux.array' => 'Les créneaux doivent être fournis sous forme de tableau',
            'creneaux.*.exists' => 'Un des créneaux sélectionnés n\'existe pas',
            'salles.array' => 'Les salles doivent être fournies sous forme de tableau',
            'salles.*.exists' => 'Une des salles sélectionnées n\'existe pas',
        ];
    }
}
