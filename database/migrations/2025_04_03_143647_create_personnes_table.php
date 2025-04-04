<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('personnes', function (Blueprint $table) {
            $table->id();
            $table->string('nom');           // Corrigé: strings -> string
            $table->string('prenom');        // Corrigé: strings -> string
            $table->date('date');
            $table->enum('sexe', ['M', 'F']); // Utiliser enum pour limiter les valeurs possibles
            $table->string('tel');           // Corrigé: strings -> string
            $table->string('email');         // Corrigé: strings -> string
            $table->enum('profil', ['admin', 'enseignant', 'etudiant', 'parent', 'responsable']);
            $table->foreignId('filiere_id')->nullable()->constrained('filieres')->onDelete('set null');
            $table->foreignId('matiere_id')->nullable()->constrained('matieres')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personnes');
    }
};
