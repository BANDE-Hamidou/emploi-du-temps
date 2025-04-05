<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Salle;
use App\Http\Requests\SalleRequest;
use Illuminate\Database\QueryException;

class SalleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = Salle::query();
            
            // Recherche
            if ($request->has('search')) {
                $search = $request->search;
                $query->where('nom', 'ILIKE', "%{$search}%");
            }
            
            // Filtre par disponibilité
            if ($request->has('disponible') && $request->has('date') && $request->has('creneau_id')) {
                $date = $request->date;
                $creneauId = $request->creneau_id;
                $query->whereDoesntHave('edts', function ($q) use ($date, $creneauId) {
                    $q->where('DateDebut', '<=', $date)
                      ->where('DateFin', '>=', $date)
                      ->where('idCreneau', $creneauId);
                });
            }
            
            // Filtres additionnels
            if ($request->has('capacite_min')) {
                $query->where('capacite', '>=', $request->capacite_min);
            }
            
            if ($request->has('capacite_max')) {
                $query->where('capacite', '<=', $request->capacite_max);
            }
            
            if ($request->has('type')) {
                $query->where('type', $request->type);
            }
            
            // Relations
            if ($request->has('with')) {
                $relations = explode(',', $request->with);
                $allowedRelations = ['cours', 'edts'];
                $validRelations = array_intersect($allowedRelations, $relations);
                
                if (!empty($validRelations)) {
                    $query->with($validRelations);
                }
            }
            
            return $query->paginate($request->get('per_page', 15));
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Une erreur est survenue lors de la récupération des salles',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SalleRequest $request)
    {
        try {
            $salle = Salle::create($request->validated());
            
            return response()->json([
                'message' => 'La salle a été créée avec succès',
                'salle' => $salle
            ], 201);
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Erreur lors de la création de la salle',
                'error' => $e->getMessage()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Une erreur inattendue est survenue',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Salle $salle)
    {
        try {
            if ($request->has('with')) {
                $relations = explode(',', $request->with);
                $allowedRelations = ['cours', 'edts'];
                $validRelations = array_intersect($allowedRelations, $relations);
                
                if (!empty($validRelations)) {
                    $salle->load($validRelations);
                }
            }
            
            return response()->json($salle);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la récupération de la salle',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SalleRequest $request, Salle $salle)
    {
        try {
            $salle->update($request->validated());
            
            return response()->json([
                'message' => 'La salle a été mise à jour avec succès',
                'salle' => $salle
            ]);
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Erreur lors de la mise à jour de la salle',
                'error' => $e->getMessage()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Une erreur inattendue est survenue',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Salle $salle)
    {
        try {
            $salleNom = $salle->nom;
            $salleId = $salle->id;
            
            $result = $salle->delete();
            
            if ($result) {
                return response()->json([
                    'message' => "La salle {$salleNom} a été supprimée avec succès",
                    'success' => true,
                    'id' => $salleId
                ], 200);
            } else {
                return response()->json([
                    'message' => 'Échec de la suppression de la salle',
                    'success' => false,
                    'id' => $salleId
                ], 500);
            }
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Erreur lors de la suppression de la salle',
                'error' => $e->getMessage()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Une erreur inattendue est survenue',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Récupérer les salles disponibles pour une période spécifique
     */
    public function getDisponibles(Request $request)
    {
        try {
            if (!$request->has('date_debut') || !$request->has('date_fin') || !$request->has('creneau_id')) {
                return response()->json([
                    'message' => 'Les paramètres date_debut, date_fin et creneau_id sont requis'
                ], 400);
            }
            
            $dateDebut = $request->date_debut;
            $dateFin = $request->date_fin;
            $creneauId = $request->creneau_id;
            
            $query = Salle::whereDoesntHave('edts', function ($q) use ($dateDebut, $dateFin, $creneauId) {
                $q->where('idCreneau', $creneauId)
                  ->where(function ($q2) use ($dateDebut, $dateFin) {
                      $q2->whereBetween('DateDebut', [$dateDebut, $dateFin])
                        ->orWhereBetween('DateFin', [$dateDebut, $dateFin])
                        ->orWhere(function ($q3) use ($dateDebut, $dateFin) {
                            $q3->where('DateDebut', '<=', $dateDebut)
                               ->where('DateFin', '>=', $dateFin);
                        });
                  });
            });
            
            // Filtres additionnels
            if ($request->has('capacite_min')) {
                $query->where('capacite', '>=', $request->capacite_min);
            }
            
            if ($request->has('type')) {
                $query->where('type', $request->type);
            }
            
            return $query->paginate($request->get('per_page', 15));
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la récupération des salles disponibles',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Récupérer le planning d'occupation d'une salle
     */
    public function getPlanning(Request $request, Salle $salle)
    {
        try {
            if (!$request->has('date_debut') || !$request->has('date_fin')) {
                return response()->json([
                    'message' => 'Les paramètres date_debut et date_fin sont requis'
                ], 400);
            }
            
            $dateDebut = $request->date_debut;
            $dateFin = $request->date_fin;
            
            $planning = $salle->edts()
                ->where(function ($q) use ($dateDebut, $dateFin) {
                    $q->whereBetween('DateDebut', [$dateDebut, $dateFin])
                      ->orWhereBetween('DateFin', [$dateDebut, $dateFin])
                      ->orWhere(function ($q2) use ($dateDebut, $dateFin) {
                          $q2->where('DateDebut', '<=', $dateDebut)
                             ->where('DateFin', '>=', $dateFin);
                      });
                })
                ->with(['cours.matiere', 'creneau'])
                ->orderBy('DateDebut')
                ->orderBy('idCreneau')
                ->get();
                
            return response()->json([
                'salle' => $salle->nom,
                'planning' => $planning
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la récupération du planning de la salle',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}