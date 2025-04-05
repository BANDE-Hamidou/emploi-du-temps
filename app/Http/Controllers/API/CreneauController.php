<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EdtController extends Controller
{
    public function index(Request $request)
    {
        $query = Edt::query();
        
        // Recherche avancée
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('Semestre', 'ILIKE', "%{$search}%")
                  ->orWhereHas('cours.matiere', function ($q2) use ($search) {
                      $q2->where('nom', 'ILIKE', "%{$search}%");
                  });
            });
        }
        
        // Filtres
        if ($request->has('date_debut')) {
            $query->where('DateDebut', '>=', $request->date_debut);
        }
        
        if ($request->has('date_fin')) {
            $query->where('DateFin', '<=', $request->date_fin);
        }
        
        if ($request->has('cours_id')) {
            $query->where('idCours', $request->cours_id);
        }
        
        if ($request->has('salle_id')) {
            $query->where('idSalle', $request->salle_id);
        }
        
        if ($request->has('creneau_id')) {
            $query->where('idCreneau', $request->creneau_id);
        }
        
        if ($request->has('filiere_id')) {
            $query->whereHas('filieres', function ($q) use ($request) {
                $q->where('filieres.id', $request->filiere_id);
            });
        }
        
        // Relations
        if ($request->has('with')) {
            $relations = explode(',', $request->with);
            $allowedRelations = ['cours', 'cours.matiere', 'salle', 'creneau', 'filieres'];
            $validRelations = array_intersect($allowedRelations, $relations);
            
            if (!empty($validRelations)) {
                $query->with($validRelations);
            }
        }
        
        return $query->paginate($request->get('per_page', 15));
    }

    public function store(EdtRequest $request)
    {
        // Vérifier la disponibilité de la salle
        $salleDisponible = $this->verifySalleAvailability(
            $request->idSalle,
            $request->idCreneau,
            $request->DateDebut,
            $request->DateFin
        );
        
        if (!$salleDisponible) {
            return response()->json([
                'message' => 'La salle est déjà réservée pour ce créneau pendant cette période'
            ], 422);
        }
        
        $edt = Edt::create($request->validated());
        
        return response()->json($edt->load('cours.matiere', 'salle', 'creneau'), 201);
    }

    public function show(Request $request, Edt $edt)
    {
        if ($request->has('with')) {
            $relations = explode(',', $request->with);
            $allowedRelations = ['cours', 'cours.matiere', 'salle', 'creneau', 'filieres'];
            $validRelations = array_intersect($allowedRelations, $relations);
            
            if (!empty($validRelations)) {
                $edt->load($validRelations);
            }
        }
        
        return response()->json($edt);
    }

    public function update(EdtRequest $request, Edt $edt)
    {
        // Vérifier la disponibilité de la salle si elle est modifiée
        if ($request->has('idSalle') && $edt->idSalle != $request->idSalle) {
            $salleDisponible = $this->verifySalleAvailability(
                $request->idSalle,
                $request->idCreneau ?? $edt->idCreneau,
                $request->DateDebut ?? $edt->DateDebut,
                $request->DateFin ?? $edt->DateFin,
                $edt->id
            );
            
            if (!$salleDisponible) {
                return response()->json([
                    'message' => 'La salle est déjà réservée pour ce créneau pendant cette période'
                ], 422);
            }
        }
        
        $edt->update($request->validated());
        
        return response()->json($edt->load('cours.matiere', 'salle', 'creneau'));
    }

    public function destroy(Edt $edt)
    {
        $edt->delete();
        return response()->json(null, 204);
    }
    
    public function getByFiliere(Request $request, $filiereId)
    {
        $query = Edt::whereHas('filieres', function ($q) use ($filiereId) {
            $q->where('filieres.id', $filiereId);
        });
        
        // Filtres de date
        if ($request->has('date_debut') && $request->has('date_fin')) {
            $query->where(function ($q) use ($request) {
                $q->whereBetween('DateDebut', [$request->date_debut, $request->date_fin])
                  ->orWhereBetween('DateFin', [$request->date_debut, $request->date_fin])
                  ->orWhere(function ($q2) use ($request) {
                      $q2->where('DateDebut', '<=', $request->date_debut)
                         ->where('DateFin', '>=', $request->date_fin);
                  });
            });
        }
        
        // Chargement des relations
        $query->with(['cours.matiere', 'salle', 'creneau']);
        
        return $query->paginate($request->get('per_page', 15));
    }
    
    private function verifySalleAvailability($salleId, $creneauId, $dateDebut, $dateFin, $excludeEdtId = null)
    {
        $query = Edt::where('idSalle', $salleId)
                    ->where('idCreneau', $creneauId)
                    ->where(function ($q) use ($dateDebut, $dateFin) {
                        $q->whereBetween('DateDebut', [$dateDebut, $dateFin])
                          ->orWhereBetween('DateFin', [$dateDebut, $dateFin])
                          ->orWhere(function ($q2) use ($dateDebut, $dateFin) {
                              $q2->where('DateDebut', '<=', $dateDebut)
                                 ->where('DateFin', '>=', $dateFin);
                          });
                    });
        
        if ($excludeEdtId) {
            $query->where('id', '!=', $excludeEdtId);
        }
        
        return $query->count() === 0;
    }
}
