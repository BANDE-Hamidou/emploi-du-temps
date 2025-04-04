<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Edt extends Model
{
    use HasFactory;

    protected $table = 'edt';
    protected $fillable = ['Semestre', 'DateDebut', 'DateFin', 'idCours', 'idSalle', 'idCreneau'];

    public function cours()
    {
        return $this->belongsTo(Cours::class, 'idCours');
    }

    public function salle()
    {
        return $this->belongsTo(Salle::class, 'idSalle');
    }

    public function creneau()
    {
        return $this->belongsTo(Creneau::class, 'idCreneau');
    }

    public function filieres()
    {
        return $this->hasMany(Filiere::class, 'edts_id');
    }

    public function coursRelation()
    {
        return $this->belongsToMany(Cours::class, 'cours_edts', 'edt_id', 'cours_id');
    }
}
