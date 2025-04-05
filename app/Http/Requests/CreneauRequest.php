<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreneauRequest extends FormRequest
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
            'HeureDebut' => 'required|date_format:H:i:s',
            'HeureFin' => 'required|date_format:H:i:s|after:HeureDebut',
        ];

        return $rules;
    }

    /**
     * Messages d'erreur personnalisés
     */
    public function messages()
    {
        return [
            'HeureDebut.required' => 'L\'heure de début est obligatoire',
            'HeureDebut.date_format' => 'Le format de l\'heure de début est invalide',
            'HeureFin.required' => 'L\'heure de fin est obligatoire',
            'HeureFin.date_format' => 'Le format de l\'heure de fin est invalide',
            'HeureFin.after' => 'L\'heure de fin doit être postérieure à l\'heure de début',
        ];
    }
}
