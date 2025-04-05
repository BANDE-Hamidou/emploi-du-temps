<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SalleRequest extends FormRequest
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
        ];

        return $rules;
    }

    /**
     * Messages d'erreur personnalisés
     */
    public function messages()
    {
        return [
            'nom.required' => 'Le nom de la salle est obligatoire',
            'nom.max' => 'Le nom de la salle ne doit pas dépasser 255 caractères',
        ];
    }
}
