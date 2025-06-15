<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipes', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('code')->unique(); // EQ001, EQ002, etc.
            $table->text('description')->nullable();
            
            // Responsabilité
            $table->unsignedBigInteger('charge_projet_id');
            $table->foreign('charge_projet_id')->references('id')->on('employes')->onDelete('cascade');
            
            // Spécialisation
            $table->enum('specialisation', [
                'installation_generale', 
                'maintenance', 
                'depannage_urgence', 
                'industriel', 
                'tertiaire', 
                'particulier',
                'eclairage_public'
            ])->default('installation_generale');
            
            // Capacités
            $table->integer('capacite_max')->default(4); // Nombre max d'employés
            $table->json('competences_requises')->nullable(); // Habilitations minimum
            $table->json('vehicules_attribues')->nullable(); // Véhicules de l'équipe
            
            // Planning
            $table->boolean('active')->default(true);
            $table->json('zones_intervention')->nullable(); // Secteurs géographiques
            $table->time('horaire_debut')->default('08:00');
            $table->time('horaire_fin')->default('17:00');
            
            $table->timestamps();
            
            // Index
            $table->index('charge_projet_id');
            $table->index('specialisation');
            $table->index('active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipes');
    }
};