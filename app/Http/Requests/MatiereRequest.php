<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MatiereRequest extends FormRequest
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
            'filieres' => 'sometimes|array',
            'filieres.*' => 'exists:filieres,id',
        ];

        return $rules;
    }

    /**
     * Messages d'erreur personnalisés
     */
    public function messages()
    {
        return [
            'nom.required' => 'Le nom de la matière est obligatoire',
            'nom.max' => 'Le nom de la matière ne doit pas dépasser 255 caractères',
            'filieres.array' => 'Les filières doivent être fournies sous forme de tableau',
            'filieres.*.exists' => 'Une des filières sélectionnées n\'existe pas',
        ];
    }
}
