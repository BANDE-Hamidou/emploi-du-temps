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
        Schema::create('edt', function (Blueprint $table) {
            $table->id();
            $table->date('Semestre');
            $table->date('DateDebut');
            $table->date('DateFin');
            $table->foreignId('idCours')->constrained('cours')->onDelete('cascade');
            $table->foreignId('idSalle')->constrained('salles')->onDelete('cascade');
            $table->foreignId('idCreneau')->constrained('creneaux')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('edt');
    }
};
