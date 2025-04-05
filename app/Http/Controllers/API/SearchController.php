<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\SupabaseService;
use App\Models\Matiere;
use App\Models\Salle;
use App\Models\Filiere;
use App\Models\Personne;
use App\Models\Edt;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    protected $supabase;
    
    public function __construct(SupabaseService $supabase)
    {
        $this->supabase = $supabase;
    }
    
    /**
     * Recherche globale dans plusieurs modèles
     */
    public function search(Request $request)
    {
        try {
            $query = $request->get('q');
            
            if (empty($query)) {
                return response()->json([
                    'message' => 'Le terme de recherche est requis'
                ], 400);
            }
            
            $type = $request->get('type', 'all');
            $perPage = $request->get('per_page', 10);
            
            switch ($type) {
                case 'matieres':
                    return $this->searchMatieres($query, $perPage);
                case 'salles':
                    return $this->searchSalles($query, $perPage);
                case 'filieres':
                    return $this->searchFilieres($query, $perPage);
                case 'personnes':
                    return $this->searchPersonnes($query, $perPage);
                case 'edts':
                    return $this->searchEdts($query, $perPage);
                default:
                    return $this->searchAll($query, $perPage);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Une erreur est survenue lors de la recherche',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Recherche dans les matières
     */
    private function searchMatieres($query, $perPage)
    {
        try {
            // Utiliser PostgreSQL pour la recherche
            $results = Matiere::where('nom', 'ILIKE', "%{$query}%")
                ->paginate($perPage);
                
            return response()->json($results);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la recherche des matières',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Recherche dans les salles
     */
    private function searchSalles($query, $perPage)
    {
        try {
            $results = Salle::where('nom', 'ILIKE', "%{$query}%")
                ->paginate($perPage);
                
            return response()->json($results);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la recherche des salles',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Recherche dans les filières
     */
    private function searchFilieres($query, $perPage)
    {
        try {
            $results = Filiere::where('nom', 'ILIKE', "%{$query}%")
                ->paginate($perPage);
                
            return response()->json($results);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la recherche des filières',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Recherche dans les personnes
     */
    private function searchPersonnes($query, $perPage)
    {
        try {
            $results = Personne::where('nom', 'ILIKE', "%{$query}%")
                ->orWhere('prenom', 'ILIKE', "%{$query}%")
                ->orWhere('email', 'ILIKE', "%{$query}%")
                ->paginate($perPage);
                
            return response()->json($results);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la recherche des personnes',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Recherche dans les emplois du temps
     */
    private function searchEdts($query, $perPage)
    {
        try {
            // Une recherche complexe impliquant plusieurs tables
            $results = Edt::whereHas('cours.matiere', function ($q) use ($query) {
                $q->where('nom', 'ILIKE', "%{$query}%");
            })
            ->orWhereHas('salle', function ($q) use ($query) {
                $q->where('nom', 'ILIKE', "%{$query}%");
            })
            ->with(['cours.matiere', 'salle', 'creneau'])
            ->paginate($perPage);
                
            return response()->json($results);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la recherche des emplois du temps',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Recherche dans tous les modèles
     */
    private function searchAll($query, $perPage)
    {
        try {
            // Recherche avec fonction PostgreSQL personnalisée via Supabase RPC
            $params = [
                'search_term' => $query,
                'result_limit' => $perPage
            ];
            
            $results = $this->supabase->rpc('global_search', $params);
            
            if ($results === null) {
                // Fallback si l'appel RPC échoue
                return response()->json([
                    'matieres' => $this->searchMatieres($query, $perPage)->original->items(),
                    'salles' => $this->searchSalles($query, $perPage)->original->items(),
                    'filieres' => $this->searchFilieres($query, $perPage)->original->items(),
                    'personnes' => $this->searchPersonnes($query, $perPage)->original->items(),
                    'edts' => $this->searchEdts($query, $perPage)->original->items(),
                ]);
            }
            
            return response()->json($results);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la recherche globale',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Recherche avec Supabase
     */
    public function searchWithSupabase(Request $request)
    {
        try {
            $query = $request->get('q');
            $table = $request->get('table', '');
            $columns = explode(',', $request->get('columns', ''));
            $limit = $request->get('limit', 10);
            
            if (empty($table) || empty($columns) || empty($query)) {
                return response()->json([
                    'message' => 'Les paramètres table, columns et q sont requis'
                ], 400);
            }
            
            $results = $this->supabase->search($table, $query, $columns, $limit);
            
            return response()->json($results);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la recherche avec Supabase',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Recherche avancée avec filtres multiples
     */
    public function advancedSearch(Request $request)
    {
        try {
            $type = $request->get('type', 'all');
            $filters = $request->get('filters', []);
            $perPage = $request->get('per_page', 15);
            
            switch ($type) {
                case 'matieres':
                    $query = Matiere::query();
                    
                    if (isset($filters['nom'])) {
                        $query->where('nom', 'ILIKE', "%{$filters['nom']}%");
                    }
                    
                    if (isset($filters['code'])) {
                        $query->where('code', 'ILIKE', "%{$filters['code']}%");
                    }
                    
                    if (isset($filters['filiere_id'])) {
                        $query->where('filiere_id', $filters['filiere_id']);
                    }
                    
                    if ($request->has('with')) {
                        $relations = explode(',', $request->with);
                        $allowedRelations = ['filiere', 'cours'];
                        $validRelations = array_intersect($allowedRelations, $relations);
                        
                        if (!empty($validRelations)) {
                            $query->with($validRelations);
                        }
                    }
                    
                    $results = $query->paginate($perPage);
                    break;
                    
                case 'personnes':
                    $query = Personne::query();
                    
                    if (isset($filters['nom'])) {
                        $query->where('nom', 'ILIKE', "%{$filters['nom']}%");
                    }
                    
                    if (isset($filters['prenom'])) {
                        $query->where('prenom', 'ILIKE', "%{$filters['prenom']}%");
                    }
                    
                    if (isset($filters['email'])) {
                        $query->where('email', 'ILIKE', "%{$filters['email']}%");
                    }
                    
                    if (isset($filters['profil'])) {
                        $query->where('profil', $filters['profil']);
                    }
                    
                    if (isset($filters['filiere_id'])) {
                        $query->where('filiere_id', $filters['filiere_id']);
                    }
                    
                    if (isset($filters['matiere_id'])) {
                        $query->whereHas('matieres', function ($q) use ($filters) {
                            $q->where('matieres.id', $filters['matiere_id']);
                        });
                    }
                    
                    if ($request->has('with')) {
                        $relations = explode(',', $request->with);
                        $allowedRelations = ['filiere', 'matieres'];
                        $validRelations = array_intersect($allowedRelations, $relations);
                        
                        if (!empty($validRelations)) {
                            $query->with($validRelations);
                        }
                    }
                    
                    $results = $query->paginate($perPage);
                    break;
                    
                case 'edts':
                    $query = Edt::query();
                    
                    if (isset($filters['semestre'])) {
                        $query->where('Semestre', 'ILIKE', "%{$filters['semestre']}%");
                    }
                    
                    if (isset($filters['date_debut']) && isset($filters['date_fin'])) {
                        $query->where(function ($q) use ($filters) {
                            $q->whereBetween('DateDebut', [$filters['date_debut'], $filters['date_fin']])
                              ->orWhereBetween('DateFin', [$filters['date_debut'], $filters['date_fin']])
                              ->orWhere(function ($q2) use ($filters) {
                                  $q2->where('DateDebut', '<=', $filters['date_debut'])
                                     ->where('DateFin', '>=', $filters['date_fin']);
                              });
                        });
                    }
                    
                    if (isset($filters['cours_id'])) {
                        $query->where('idCours', $filters['cours_id']);
                    }
                    
                    if (isset($filters['matiere_nom'])) {
                        $query->whereHas('cours.matiere', function ($q) use ($filters) {
                            $q->where('nom', 'ILIKE', "%{$filters['matiere_nom']}%");
                        });
                    }
                    
                    if (isset($filters['salle_id'])) {
                        $query->where('idSalle', $filters['salle_id']);
                    }
                    
                    if (isset($filters['creneau_id'])) {
                        $query->where('idCreneau', $filters['creneau_id']);
                    }
                    
                    if (isset($filters['filiere_id'])) {
                        $query->whereHas('filieres', function ($q) use ($filters) {
                            $q->where('filieres.id', $filters['filiere_id']);
                        });
                    }
                    
                    if ($request->has('with')) {
                        $relations = explode(',', $request->with);
                        $allowedRelations = ['cours', 'cours.matiere', 'salle', 'creneau', 'filieres'];
                        $validRelations = array_intersect($allowedRelations, $relations);
                        
                        if (!empty($validRelations)) {
                            $query->with($validRelations);
                        }
                    }
                    
                    $results = $query->paginate($perPage);
                    break;
                    
                default:
                    return response()->json([
                        'message' => 'Type de recherche non pris en charge'
                    ], 400);
            }
            
            return response()->json($results);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la recherche avancée',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}