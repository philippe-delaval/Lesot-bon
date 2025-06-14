<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

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
            $table->jsonb('competences'); // ['plomberie', 'electricite', 'climatisation']
            $table->jsonb('certifications')->nullable(); // ['QUALIPAC', 'QUALIFELEC']
            $table->string('niveau_experience')->default('junior'); // junior, senior, expert
            $table->decimal('tarif_horaire', 8, 2);
            $table->jsonb('zone_intervention'); // Polygon ou coordinates
            $table->string('statut')->default('disponible'); // disponible, en_mission, indisponible
            $table->jsonb('planning')->nullable(); // Planning hebdomadaire
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->timestamp('derniere_position')->nullable();
            $table->string('vehicule_type')->nullable();
            $table->string('vehicule_immatriculation')->nullable();
            $table->integer('charge_travail_actuelle')->default(0); // Pourcentage
            $table->jsonb('equipements_mobiles')->nullable(); // Outils et équipements portés
            $table->boolean('actif')->default(true);
            $table->timestamps();
            
            $table->index(['statut', 'actif']);
            $table->index(['latitude', 'longitude']);
        });
        
        // Créer les index GIN pour les colonnes JSONB
        DB::statement('CREATE INDEX techniciens_competences_gin_idx ON techniciens USING GIN (competences)');
        DB::statement('CREATE INDEX techniciens_zone_intervention_gin_idx ON techniciens USING GIN (zone_intervention)');
        DB::statement('CREATE INDEX techniciens_equipements_mobiles_gin_idx ON techniciens USING GIN (equipements_mobiles)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS techniciens_competences_gin_idx');
        DB::statement('DROP INDEX IF EXISTS techniciens_zone_intervention_gin_idx');
        DB::statement('DROP INDEX IF EXISTS techniciens_equipements_mobiles_gin_idx');
        Schema::dropIfExists('techniciens');
    }
};
