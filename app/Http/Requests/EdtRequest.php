<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EdtRequest extends FormRequest
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
            'Semestre' => 'required|date',
            'DateDebut' => 'required|date',
            'DateFin' => 'required|date|after_or_equal:DateDebut',
            'idCours' => 'required|exists:cours,id',
            'idSalle' => 'required|exists:salles,id',
            'idCreneau' => 'required|exists:creneaux,id',
        ];

        return $rules;
    }
    
    /**
     * Messages d'erreur personnalisés
     */
    public function messages()
    {
        return [
            'Semestre.required' => 'Le semestre est obligatoire',
            'Semestre.date' => 'Le format du semestre est invalide',
            'DateDebut.required' => 'La date de début est obligatoire',
            'DateDebut.date' => 'Le format de la date de début est invalide',
            'DateFin.required' => 'La date de fin est obligatoire',
            'DateFin.date' => 'Le format de la date de fin est invalide',
            'DateFin.after_or_equal' => 'La date de fin doit être ultérieure ou égale à la date de début',
            'idCours.required' => 'Le cours est obligatoire',
            'idCours.exists' => 'Le cours sélectionné n\'existe pas',
            'idSalle.required' => 'La salle est obligatoire',
            'idSalle.exists' => 'La salle sélectionnée n\'existe pas',
            'idCreneau.required' => 'Le créneau horaire est obligatoire',
            'idCreneau.exists' => 'Le créneau horaire sélectionné n\'existe pas',
        ];
    }
}
