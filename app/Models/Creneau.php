<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Creneau extends Model
{
    use HasFactory;

    protected $table= 'creneaux';
    protected $fillable = ['HeureDebut', 'HeureFin'];

    public function cours()
    {
        return $this->belongsToMany(Cours::class, 'cours_creneau', 'creneau_id', 'cours_id');
    }

    public function creneau()
    {
        return $this->hasMany(Edt::class, 'idCreneau');
    }
}
