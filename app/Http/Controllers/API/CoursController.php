<?php
namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cours;
use App\Http\Requests\CoursRequest;
use Illuminate\Database\QueryException;

class CoursController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Cours::query();
            
            // Recherche via la matière
            if ($request->has('search')) {
                $search = $request->search;
                $query->whereHas('matiere', function ($q) use ($search) {
                    $q->where('nom', 'ILIKE', "%{$search}%");
                });
            }
            
            // Filtres
            if ($request->has('matiere_id')) {
                $query->where('idMatiere', $request->matiere_id);
            }
            
            // Relations
            if ($request->has('with')) {
                $relations = explode(',', $request->with);
                $allowedRelations = ['matiere', 'creneaux', 'salles', 'edts'];
                $validRelations = array_intersect($allowedRelations, $relations);
                
                if (!empty($validRelations)) {
                    $query->with($validRelations);
                }
            }
            
            $cours = $query->paginate($request->get('per_page', 15));
            
            return response()->json($cours);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Une erreur est survenue lors de la récupération des cours',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(CoursRequest $request)
    {
        try {
            $cours = Cours::create($request->validated());
            
            // Associer les créneaux
            if ($request->has('creneaux')) {
                $cours->creneaux()->attach($request->creneaux);
            }
            
            // Associer les salles
            if ($request->has('salles')) {
                $cours->salles()->attach($request->salles);
            }
            
            return response()->json([
                'message' => 'Le cours a été créé avec succès',
                'cours' => $cours->load('matiere', 'creneaux', 'salles')
            ], 201);
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Erreur lors de la création du cours',
                'error' => $e->getMessage()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Une erreur inattendue est survenue',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request, Cours $cours)
    {
        try {
            if ($request->has('with')) {
                $relations = explode(',', $request->with);
                $allowedRelations = ['matiere', 'creneaux', 'salles', 'edts'];
                $validRelations = array_intersect($allowedRelations, $relations);
                
                if (!empty($validRelations)) {
                    $cours->load($validRelations);
                }
            }
            
            return response()->json($cours);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la récupération du cours',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(CoursRequest $request, Cours $cours)
    {
        try {
            $cours->update($request->validated());
            
            // Synchroniser les créneaux
            if ($request->has('creneaux')) {
                $cours->creneaux()->sync($request->creneaux);
            }
            
            // Synchroniser les salles
            if ($request->has('salles')) {
                $cours->salles()->sync($request->salles);
            }
            
            return response()->json([
                'message' => 'Le cours a été mis à jour avec succès',
                'cours' => $cours->load('matiere', 'creneaux', 'salles')
            ]);
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Erreur lors de la mise à jour du cours',
                'error' => $e->getMessage()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Une erreur inattendue est survenue',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Cours $cours)
    {
        try {
            $coursId = $cours->id;
            $coursName = $cours->matiere ? $cours->matiere->nom : 'Cours #' . $coursId;
            
            $result = $cours->delete();
            
            if ($result) {
                return response()->json([
                    'message' => 'Le cours "' . $coursName . '" a été supprimé avec succès',
                    'success' => true,
                    'id' => $coursId
                ], 200);
            } else {
                return response()->json([
                    'message' => 'Échec de la suppression du cours',
                    'success' => false,
                    'id' => $coursId
                ], 500);
            }
        } catch (QueryException $e) {
            // Gérer les erreurs de contrainte (par exemple si le cours est utilisé ailleurs)
            $errorCode = $e->errorInfo[1] ?? 0;
            if ($errorCode == 23503) { // Code pour violation de contrainte de clé étrangère dans PostgreSQL
                return response()->json([
                    'message' => 'Impossible de supprimer ce cours car il est utilisé dans des emplois du temps',
                    'error' => $e->getMessage()
                ], 422);
            }
            
            return response()->json([
                'message' => 'Erreur lors de la suppression du cours',
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
     * Mise à jour du statut d'un cours (fait ou non fait)
     *
     * @param Request $request
     * @param Cours $cours
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStatus(Request $request, Cours $cours)
    {
        try {
            // Validation de la requête
            $request->validate([
                'estFait' => 'required|boolean',
                'commentaire' => 'nullable|string|max:500',
            ]);
            
            // Vérification du rôle (délégué)
            if (!auth()->user()->hasRole('delegue')) {
                return response()->json([
                    'message' => 'Permission refusée. Seul un délégué peut valider un cours.'
                ], 403);
            }
            
            // Mise à jour du statut du cours
            $cours->update([
                'estFait' => $request->estFait,
                'commentaire' => $request->commentaire,
                'validePar' => auth()->id(),
                'dateValidation' => now(),
            ]);
            
            $statusMessage = $request->estFait ? 
                'Le cours a été marqué comme effectué avec succès.' : 
                'Le cours a été marqué comme non effectué avec succès.';
            
            return response()->json([
                'message' => $statusMessage,
                'cours' => $cours->load('matiere')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Une erreur est survenue lors de la mise à jour du statut',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}