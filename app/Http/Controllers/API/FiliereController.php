<?php
namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Filiere;
use App\Http\Requests\FiliereRequest;
use Illuminate\Database\QueryException;

class FiliereController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Filiere::query();
            
            // Recherche
            if ($request->has('search')) {
                $search = $request->search;
                $query->where('nom', 'ILIKE', "%{$search}%");
            }
            
            // Relations
            if ($request->has('with')) {
                $relations = explode(',', $request->with);
                $allowedRelations = ['edt', 'personnes', 'matieres'];
                $validRelations = array_intersect($allowedRelations, $relations);
                
                if (!empty($validRelations)) {
                    $query->with($validRelations);
                }
            }
            
            return $query->paginate($request->get('per_page', 15));
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Une erreur est survenue lors de la récupération des filières',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(FiliereRequest $request)
    {
        try {
            $filiere = Filiere::create($request->validated());
            
            // Attacher les matières si fournies
            if ($request->has('matieres')) {
                $filiere->matieres()->attach($request->matieres);
            }
            
            return response()->json([
                'message' => 'La filière a été créée avec succès',
                'filiere' => $filiere
            ], 201);
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Erreur lors de la création de la filière',
                'error' => $e->getMessage()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Une erreur inattendue est survenue',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request, Filiere $filiere)
    {
        try {
            if ($request->has('with')) {
                $relations = explode(',', $request->with);
                $allowedRelations = ['edt', 'personnes', 'matieres'];
                $validRelations = array_intersect($allowedRelations, $relations);
                
                if (!empty($validRelations)) {
                    $filiere->load($validRelations);
                }
            }
            
            return response()->json($filiere);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la récupération de la filière',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(FiliereRequest $request, Filiere $filiere)
    {
        try {
            $filiere->update($request->validated());
            
            // Synchroniser les matières si fournies
            if ($request->has('matieres')) {
                $filiere->matieres()->sync($request->matieres);
            }
            
            return response()->json([
                'message' => 'La filière a été mise à jour avec succès',
                'filiere' => $filiere
            ]);
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Erreur lors de la mise à jour de la filière',
                'error' => $e->getMessage()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Une erreur inattendue est survenue',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Filiere $filiere)
    {
        try {
            $filiereId = $filiere->id;
            $filiereName = $filiere->nom;
            
            $result = $filiere->delete();
            
            if ($result) {
                return response()->json([
                    'message' => 'La filière "' . $filiereName . '" a été supprimée avec succès',
                    'success' => true,
                    'id' => $filiereId
                ], 200);
            } else {
                return response()->json([
                    'message' => 'Échec de la suppression de la filière',
                    'success' => false,
                    'id' => $filiereId
                ], 500);
            }
        } catch (QueryException $e) {
            // Gérer les erreurs de contrainte (par exemple si la filière est utilisée ailleurs)
            $errorCode = $e->errorInfo[1] ?? 0;
            if ($errorCode == 23503) { // Code pour violation de contrainte de clé étrangère dans PostgreSQL
                return response()->json([
                    'message' => 'Impossible de supprimer cette filière car elle est utilisée pour des étudiants ou dans des emplois du temps',
                    'error' => $e->getMessage()
                ], 422);
            }
            
            return response()->json([
                'message' => 'Erreur lors de la suppression de la filière',
                'error' => $e->getMessage()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Une erreur inattendue est survenue',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getStudents(Filiere $filiere)
    {
        try {
            $students = $filiere->personnes()
                ->where('profil', 'etudiant')
                ->get();
                
            return response()->json($students);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la récupération des étudiants de la filière',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getMatieres(Filiere $filiere)
    {
        try {
            return response()->json([
                'filiereNom' => $filiere->nom,
                'matieres' => $filiere->matieres
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la récupération des matières de la filière',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}