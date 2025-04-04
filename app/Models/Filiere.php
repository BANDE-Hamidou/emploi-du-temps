<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Filiere extends Model
{
    use HasFactory;

    protected $fillable = ['nom', 'edts_id'];

    public function edt()
    {
        return $this->belongsTo(Edt::class, 'edts_id');
    }

    public function personnes()
    {
        return $this->hasMany(Personne::class, 'filiere_id');
    }

    public function matieres()
    {
        return $this->belongsToMany(Matiere::class, 'filieres_matiere', 'filiere_id', 'matiere_id');
    }
}
