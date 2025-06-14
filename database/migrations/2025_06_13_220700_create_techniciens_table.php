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
        Schema::create('techniciens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('matricule')->unique();
            $table->json('competences'); // ['plomberie', 'electricite', 'climatisation']
            $table->json('certifications')->nullable(); // ['QUALIPAC', 'QUALIFELEC']
            $table->string('niveau_experience')->default('junior'); // junior, senior, expert
            $table->decimal('tarif_horaire', 8, 2);
            $table->json('zone_intervention'); // Polygon ou coordinates
            $table->string('statut')->default('disponible'); // disponible, en_mission, indisponible
            $table->json('planning')->nullable(); // Planning hebdomadaire
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->timestamp('derniere_position')->nullable();
            $table->string('vehicule_type')->nullable();
            $table->string('vehicule_immatriculation')->nullable();
            $table->integer('charge_travail_actuelle')->default(0); // Pourcentage
            $table->json('equipements_mobiles')->nullable(); // Outils et équipements portés
            $table->boolean('actif')->default(true);
            $table->timestamps();
            
            $table->index(['statut', 'actif']);
            $table->index(['latitude', 'longitude']);
            $table->index('zone_intervention');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('techniciens');
    }
};
