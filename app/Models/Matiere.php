<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Matiere extends Model
{
    use HasFactory;

    protected $table = 'matieres';
    protected $fillable = ['nom'];

    public function cours()
    {
        return $this->hasMany(Cours::class, 'idMatiere');
    }

    public function personnes()
    {
        return $this->hasMany(Personne::class, 'matiere_id');
    }

    public function filieres()
    {
        return $this->belongsToMany(Filiere::class, 'filieres_matiere', 'matiere_id', 'filiere_id');
    }
}
