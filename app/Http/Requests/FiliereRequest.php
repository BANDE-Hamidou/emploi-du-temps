<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FiliereRequest extends FormRequest
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
                'edts_id' => 'nullable|exists:edt,id',
                'matieres' => 'sometimes|array',
                'matieres.*' => 'exists:matieres,id',
            ];
    
            return $rules;
        }
        
        /**
         * Messages d'erreur personnalisés
         */
        public function messages()
        {
            return [
                'nom.required' => 'Le nom de la filière est obligatoire',
                'nom.max' => 'Le nom de la filière ne doit pas dépasser 255 caractères',
                'edts_id.exists' => 'L\'emploi du temps sélectionné n\'existe pas',
                'matieres.array' => 'Les matières doivent être fournies sous forme de tableau',
                'matieres.*.exists' => 'Une des matières sélectionnées n\'existe pas',
            ];
        }
}
