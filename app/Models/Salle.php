<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Salle extends Model
{
    use HasFactory;

    protected $fillable = ['nom'];

    public function cours()
    {
        return $this->belongsToMany(Cours::class, 'cours_salle', 'salle_id', 'cours_id');
    }

    public function edts()
    {
        return $this->hasMany(Edt::class, 'idSalle');
    }
}
