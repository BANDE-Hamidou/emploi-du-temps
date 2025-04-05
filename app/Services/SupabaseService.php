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
    }
    
    /**
     * Recherche dans les matières
     */
    private function searchMatieres($query, $perPage)
    {
        // Utiliser PostgreSQL pour la recherche full-text
        $results = DB::connection('pgsql')
            ->table('matieres')
            ->whereRaw("nom ILIKE ?", ["%{$query}%"])
            ->paginate($perPage);
            
        return response()->json($results);
    }
    
    /**
     * Recherche dans les salles
     */
    private function searchSalles($query, $perPage)
    {
        $results = DB::connection('pgsql')
            ->table('salles')
            ->whereRaw("nom ILIKE ?", ["%{$query}%"])
            ->paginate($perPage);
            
        return response()->json($results);
    }
    
    /**
     * Recherche dans les filières
     */
    private function searchFilieres($query, $perPage)
    {
        $results = DB::connection('pgsql')
            ->table('filieres')
            ->whereRaw("nom ILIKE ?", ["%{$query}%"])
            ->paginate($perPage);
            
        return response()->json($results);
    }
    
    /**
     * Recherche dans les personnes
     */
    private function searchPersonnes($query, $perPage)
    {
        $results = DB::connection('pgsql')
            ->table('personnes')
            ->whereRaw("nom ILIKE ? OR prenom ILIKE ? OR email ILIKE ?", ["%{$query}%", "%{$query}%", "%{$query}%"])
            ->paginate($perPage);
            
        return response()->json($results);
    }
    
    /**
     * Recherche dans les emplois du temps
     */
    private function searchEdts($query, $perPage)
    {
        // Une recherche complexe impliquant plusieurs tables
        $results = DB::connection('pgsql')
            ->table('edt')
            ->join('cours', 'edt.idCours', '=', 'cours.id')
            ->join('matieres', 'cours.idMatiere', '=', 'matieres.id')
            ->join('salles', 'edt.idSalle', '=', 'salles.id')
            ->whereRaw("matieres.nom ILIKE ? OR salles.nom ILIKE ?", ["%{$query}%", "%{$query}%"])
            ->select('edt.*', 'matieres.nom as matiere', 'salles.nom as salle')
            ->paginate($perPage);
            
        return response()->json($results);
    }
    
    /**
     * Recherche dans tous les modèles
     */
    private function searchAll($query, $perPage)
    {
        // Recherche avec fonction PostgreSQL personnalisée via Supabase RPC
        // Cette fonction doit être créée dans la base de données PostgreSQL
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
    }
    
    /**
     * Recherche avec Supabase
     */
    public function searchWithSupabase(Request $request)
    {
        $query = $request->get('q');
        $table = $request->get('table', '');
        $columns = explode(',', $request->get('columns', ''));
        $limit = $request->get('limit', 10);
        
        if (empty($table) || empty($columns) || empty($query)) {
            return response()->json([
                'error' => 'Les paramètres table, columns et q sont requis'
            ], 400);
        }
        
        $results = $this->supabase->search($table, $query, $columns, $limit);
        
        return response()->json($results);
    }
}