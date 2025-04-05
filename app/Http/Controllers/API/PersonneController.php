<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Personne;
use App\Http\Requests\PersonneRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\QueryException;

class PersonneController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = Personne::query();
            
            // Recherche
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('nom', 'ILIKE', "%{$search}%")
                      ->orWhere('prenom', 'ILIKE', "%{$search}%")
                      ->orWhere('email', 'ILIKE', "%{$search}%");
                });
            }
            
            // Filtres
            if ($request->has('profil')) {
                $query->where('profil', $request->profil);
            }
            
            if ($request->has('filiere_id')) {
                $query->where('filiere_id', $request->filiere_id);
            }
            
            if ($request->has('matiere_id')) {
                $query->where('matiere_id', $request->matiere_id);
            }
            
            if ($request->has('sexe')) {
                $query->where('sexe', $request->sexe);
            }
            
            // Relations
            if ($request->has('with')) {
                $relations = explode(',', $request->with);
                $allowedRelations = ['filiere', 'matiere'];
                $validRelations = array_intersect($allowedRelations, $relations);
                
                if (!empty($validRelations)) {
                    $query->with($validRelations);
                }
            }
            
            return $query->paginate($request->get('per_page', 15));
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Une erreur est survenue lors de la récupération des personnes',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PersonneRequest $request)
    {
        try {
            $data = $request->validated();
            
            // Si un mot de passe est fourni, le hasher
            if (isset($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            }
            
            $personne = Personne::create($data);
            
            // Charger les relations pour la réponse
            $personne->load(['filiere', 'matiere']);
            
            return response()->json([
                'message' => 'La personne a été créée avec succès',
                'personne' => $personne
            ], 201);
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Erreur lors de la création de la personne',
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
    public function show(Request $request, Personne $personne)
    {
        try {
            if ($request->has('with')) {
                $relations = explode(',', $request->with);
                $allowedRelations = ['filiere', 'matiere'];
                $validRelations = array_intersect($allowedRelations, $relations);
                
                if (!empty($validRelations)) {
                    $personne->load($validRelations);
                }
            }
            
            return response()->json($personne);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la récupération de la personne',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PersonneRequest $request, Personne $personne)
    {
        try {
            $data = $request->validated();
            
            // Si un mot de passe est fourni, le hasher
            if (isset($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            }
            
            $personne->update($data);
            
            // Charger les relations pour la réponse
            $personne->load(['filiere', 'matiere']);
            
            return response()->json([
                'message' => 'La personne a été mise à jour avec succès',
                'personne' => $personne
            ]);
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Erreur lors de la mise à jour de la personne',
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
    public function destroy(Personne $personne)
    {
        try {
            $nomComplet = $personne->prenom . ' ' . $personne->nom;
            $id = $personne->id;
            
            $result = $personne->delete();
            
            if ($result) {
                return response()->json([
                    'message' => "La personne {$nomComplet} a été supprimée avec succès",
                    'success' => true,
                    'id' => $id
                ], 200);
            } else {
                return response()->json([
                    'message' => 'Échec de la suppression de la personne',
                    'success' => false,
                    'id' => $id
                ], 500);
            }
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Erreur lors de la suppression de la personne',
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
     * Récupérer les personnes par profil
     */
    public function getByProfil(Request $request, $profil)
    {
        try {
            $query = Personne::where('profil', $profil);
            
            // Relations
            if ($request->has('with')) {
                $relations = explode(',', $request->with);
                $allowedRelations = ['filiere', 'matiere'];
                $validRelations = array_intersect($allowedRelations, $relations);
                
                if (!empty($validRelations)) {
                    $query->with($validRelations);
                }
            }
            
            return $query->paginate($request->get('per_page', 15));
        } catch (\Exception $e) {
            return response()->json([
                'message' => "Erreur lors de la récupération des personnes avec le profil '{$profil}'",
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Rechercher des personnes par nom
     */
    public function searchByName(Request $request)
    {
        try {
            $query = $request->get('q');
            
            if (empty($query)) {
                return response()->json([
                    'message' => 'Le terme de recherche est requis'
                ], 400);
            }
            
            $limit = $request->get('limit', 10);
            
            $results = Personne::where('nom', 'ILIKE', "%{$query}%")
                ->orWhere('prenom', 'ILIKE', "%{$query}%")
                ->limit($limit)
                ->get();
                
            return response()->json($results);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la recherche de personnes',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}