<?php
// routes/api.php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\CreneauController;
use App\Http\Controllers\API\MatiereController;
use App\Http\Controllers\API\SalleController;
use App\Http\Controllers\API\CoursController;
use App\Http\Controllers\API\EdtController;
use App\Http\Controllers\API\FiliereController;
use App\Http\Controllers\API\PersonneController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Route par défaut
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Préfixe de l'API
Route::prefix('v1')->group(function () {
    // Créneaux
    Route::apiResource('creneaux', CreneauController::class);
    
    // Matières
    Route::apiResource('matieres', MatiereController::class);
    
    // Salles
    Route::apiResource('salles', SalleController::class);
    
    // Cours
    Route::apiResource('cours', CoursController::class);
    
    // Emplois du temps
    Route::apiResource('edts', EdtController::class);
    Route::get('filieres/{filiere}/edts', [EdtController::class, 'getByFiliere']);
    
    // Filières
    Route::apiResource('filieres', FiliereController::class);
    Route::get('filieres/{filiere}/etudiants', [FiliereController::class, 'getStudents']);
    Route::get('filieres/{filiere}/matieres', [FiliereController::class, 'getMatieres']);
    
    // Personnes
    Route::apiResource('personnes', PersonneController::class);
    Route::get('personnes/profil/{profil}', [PersonneController::class, 'getByProfil']);
    Route::get('personnes/search', [PersonneController::class, 'searchByName']);
    
    // Recherche globale
    Route::get('search', 'App\Http\Controllers\API\SearchController@search');
});

// Cours - Validation par délégué
Route::patch('cours/{cours}/status', [CoursController::class, 'updateStatus'])
    ->middleware(['auth:sanctum']);