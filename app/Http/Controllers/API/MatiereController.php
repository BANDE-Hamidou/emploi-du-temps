<?php
namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Matiere;
use App\Http\Requests\MatiereRequest;
use Illuminate\Database\QueryException;

class MatiereController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Matiere::query();
            
            // Recherche
            if ($request->has('search')) {
                $search = $request->search;
                $query->where('nom', 'ILIKE', "%{$search}%");
            }
            
            // Filtres par filière
            if ($request->has('filiere_id')) {
                $query->whereHas('filieres', function ($q) use ($request) {
                    $q->where('filieres.id', $request->filiere_id);
                });
            }
            
            // Relations
            if ($request->has('with')) {
                $relations = explode(',', $request->with);
                $allowedRelations = ['cours', 'personnes', 'filieres'];
                $validRelations = array_intersect($allowedRelations, $relations);
                
                if (!empty($validRelations)) {
                    $query->with($validRelations);
                }
            }
            
            return $query->paginate($request->get('per_page', 15));
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Une erreur est survenue lors de la récupération des matières',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(MatiereRequest $request)
    {
        try {
            $matiere = Matiere::create($request->validated());
            
            // Attacher les filières si fournies
            if ($request->has('filieres')) {
                $matiere->filieres()->attach($request->filieres);
            }
            
            return response()->json([
                'message' => 'La matière a été créée avec succès',
                'matiere' => $matiere
            ], 201);
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Erreur lors de la création de la matière',
                'error' => $e->getMessage()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Une erreur inattendue est survenue',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request, Matiere $matiere)
    {
        try {
            if ($request->has('with')) {
                $relations = explode(',', $request->with);
                $allowedRelations = ['cours', 'personnes', 'filieres'];
                $validRelations = array_intersect($allowedRelations, $relations);
                
                if (!empty($validRelations)) {
                    $matiere->load($validRelations);
                }
            }
            
            return response()->json($matiere);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la récupération de la matière',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(MatiereRequest $request, Matiere $matiere)
    {
        try {
            $matiere->update($request->validated());
            
            // Synchroniser les filières si fournies
            if ($request->has('filieres')) {
                $matiere->filieres()->sync($request->filieres);
            }
            
            return response()->json([
                'message' => 'La matière a été mise à jour avec succès',
                'matiere' => $matiere
            ]);
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Erreur lors de la mise à jour de la matière',
                'error' => $e->getMessage()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Une erreur inattendue est survenue',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Matiere $matiere)
    {
        try {
            $matiereId = $matiere->id;
            $matiereName = $matiere->nom;
            
            $result = $matiere->delete();
            
            if ($result) {
                return response()->json([
                    'message' => 'La matière "' . $matiereName . '" a été supprimée avec succès',
                    'success' => true,
                    'id' => $matiereId
                ], 200);
            } else {
                return response()->json([
                    'message' => 'Échec de la suppression de la matière',
                    'success' => false,
                    'id' => $matiereId
                ], 500);
            }
        } catch (QueryException $e) {
            // Gérer les erreurs de contrainte (par exemple si la matière est utilisée ailleurs)
            $errorCode = $e->errorInfo[1] ?? 0;
            if ($errorCode == 23503) { // Code pour violation de contrainte de clé étrangère dans PostgreSQL
                return response()->json([
                    'message' => 'Impossible de supprimer cette matière car elle est utilisée dans des cours ou des emplois du temps',
                    'error' => $e->getMessage()
                ], 422);
            }
            
            return response()->json([
                'message' => 'Erreur lors de la suppression de la matière',
                'error' => $e->getMessage()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Une erreur inattendue est survenue',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}