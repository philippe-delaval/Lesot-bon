<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employes', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('prenom');
            $table->string('email')->unique();
            $table->string('telephone')->nullable();
            $table->string('matricule')->unique();
            
            // Informations contractuelles
            $table->enum('statut', ['permanent', 'interimaire'])->default('permanent');
            $table->enum('type_contrat', ['cdi', 'cdd', 'interim', 'apprenti', 'stagiaire'])->default('cdi');
            $table->date('date_debut');
            $table->date('date_fin')->nullable();
            $table->decimal('salaire_base', 8, 2)->nullable();
            
            // Hiérarchie
            $table->enum('role_hierarchique', ['gestionnaire', 'charge_projet', 'employe'])->default('employe');
            $table->unsignedBigInteger('charge_projet_id')->nullable();
            $table->unsignedBigInteger('gestionnaire_id')->nullable();
            
            // Compétences électriques
            $table->json('habilitations_electriques')->nullable(); // B0, B1V, B2V, BR, BC, H0, etc.
            $table->json('certifications')->nullable(); // CACES, travail en hauteur, etc.
            $table->json('competences')->nullable(); // Compétences techniques
            $table->date('date_derniere_formation')->nullable();
            
            // Informations pratiques
            $table->enum('disponibilite', ['disponible', 'occupe', 'conge', 'arret_maladie', 'formation'])->default('disponible');
            $table->string('vehicule_attribue')->nullable();
            $table->boolean('astreinte')->default(false);
            $table->text('notes')->nullable();
            
            // Photo et documents
            $table->string('photo_path')->nullable();
            $table->json('documents')->nullable(); // CV, diplômes, certifs
            
            $table->timestamps();
            
            // Index pour optimiser les recherches
            $table->index(['statut', 'disponibilite']);
            $table->index('charge_projet_id');
            $table->index('gestionnaire_id');
            $table->index('role_hierarchique');
            
            // Clés étrangères
            $table->foreign('charge_projet_id')->references('id')->on('employes')->onDelete('set null');
            $table->foreign('gestionnaire_id')->references('id')->on('employes')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employes');
    }
};