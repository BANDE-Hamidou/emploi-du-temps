<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cours extends Model
{
    use HasFactory;
    
    /**
     * Les attributs assignables en masse.
     *
     * @var array
     */
    protected $fillable = [
        'idMatiere',
        'titre',
        'description',
        // Ajoutez les champs existants ici
        'estFait',
        'commentaire',
        'validePar',
        'dateValidation'
    ];
    
    /**
     * Relation vers la matière associée au cours.
     */
    public function matiere()
    {
        return $this->belongsTo(Matiere::class, 'idMatiere');
    }
    
    /**
     * Relation vers les créneaux horaires du cours.
     */
    public function creneaux()
    {
        return $this->belongsToMany(Creneau::class, 'cours_creneau');
    }
    
    /**
     * Relation vers les salles où le cours est dispensé.
     */
    public function salles()
    {
        return $this->belongsToMany(Salle::class, 'cours_salle');
    }
    
    /**
     * Relation vers les emplois du temps contenant ce cours.
     */
    public function edts()
    {
        return $this->belongsToMany(Edt::class, 'edt_cours');
    }
    
    /**
     * Relation vers l'utilisateur qui a validé le cours.
     */
    public function validateur()
    {
        return $this->belongsTo(User::class, 'validePar');
    }
}