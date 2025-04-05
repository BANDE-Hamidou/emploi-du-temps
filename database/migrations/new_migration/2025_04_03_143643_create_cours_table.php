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
        Schema::create('plannings', function (Blueprint $table) {
            $table->id();
            // $table->string('titre');
            $table->date('date');
            $table->time('heure_debut');
            $table->time('heure_fin');
            $table->enum('statut', ['en attente', 'terminer', 'annuler']);
            $table->enum('type', ['cours', 'devoir', 'autre']);
            $table->foreignId('matiere_id')->constrained('matieres')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('classe_id')->constrained('classes')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('niveau_id')->constrained('niveaux')->onDelete('cascade')->onUpdate('cascade');

            $table->text('commentaire')->nullable();
            $table->string('valide-par')->nullable();
            $table->dateTime('date-validation')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cours');
    }
};
