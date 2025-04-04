<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cours extends Model
{
    use HasFactory;

    protected $fillable = ['idMatiere'];

    public function matiere()
    {
        return $this->belongsTo(Matiere::class, 'idMatiere');
    }

    public function creneaux()
    {
        return $this->belongsToMany(Creneau::class, 'cours_creneau', 'cours_id', 'creneau_id');
    }

    public function salles()
    {
        return $this->belongsToMany(Salle::class, 'cours_salle', 'cours_id', 'salle_id');
    }

    public function edts()
    {
        return $this->hasMany(Edt::class, 'idCours');
    }

    public function edtsRelation()
    {
        return $this->belongsToMany(Edt::class, 'cours_edts', 'cours_id', 'edt_id');
    }
}
