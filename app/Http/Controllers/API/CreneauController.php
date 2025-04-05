<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\CreneauRequest;
use App\Models\Creneau;

class CreneauController extends Controller
{
    public function index(Request $request)
    {
        $query = Creneau::query();
        
        // Recherche
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('HeureDebut', 'ILIKE', "%{$search}%")
                  ->orWhere('HeureFin', 'ILIKE', "%{$search}%");
            });
        }
        
        // Filtres
        if ($request->has('heure_debut')) {
            $query->where('HeureDebut', '>=', $request->heure_debut);
        }
        
        if ($request->has('heure_fin')) {
            $query->where('HeureFin', '<=', $request->heure_fin);
        }
        
        // Pagination
        return $query->paginate($request->get('per_page', 15));
    }

    public function store(CreneauRequest $request)
    {
        $creneau = Creneau::create($request->validated());
        return response()->json($creneau, 201);
    }

    public function show(Creneau $creneau)
    {
        return response()->json($creneau);
    }

    public function update(CreneauRequest $request, Creneau $creneau)
    {
        $creneau->update($request->validated());
        return response()->json($creneau);
    }

    public function destroy($id)  // Utilisez l'ID directement au lieu de l'injection de modèle
    {
        try {
            $creneau = Creneau::find($id);
            
            if (!$creneau) {
                return response()->json([
                    'message' => 'Créneau non trouvé',
                    'id' => $id
                ], 404);
            }
            
            $result = $creneau->delete();
            
            return response()->json([
                'message' => 'Le créneau a été supprimé avec succès',
                'success' => $result,
                'id' => $creneau->id
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }
}
