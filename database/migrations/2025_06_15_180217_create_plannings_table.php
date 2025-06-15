<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plannings', function (Blueprint $table) {
            $table->id();
            
            // Relations
            $table->unsignedBigInteger('employe_id');
            $table->unsignedBigInteger('demande_id')->nullable(); // Peut être une affectation sans demande
            $table->unsignedBigInteger('equipe_id')->nullable();
            
            // Planification temporelle
            $table->datetime('date_debut');
            $table->datetime('date_fin');
            $table->time('heure_debut_prevue')->nullable();
            $table->time('heure_fin_prevue')->nullable();
            $table->time('heure_debut_reelle')->nullable();
            $table->time('heure_fin_reelle')->nullable();
            
            // Type d'affectation
            $table->enum('type_affectation', [
                'intervention',
                'maintenance',
                'formation',
                'conge',
                'arret_maladie',
                'deplacement',
                'administratif',
                'astreinte'
            ])->default('intervention');
            
            // Statut du planning
            $table->enum('statut', [
                'planifie',
                'en_cours',
                'termine',
                'annule',
                'reporte',
                'en_attente'
            ])->default('planifie');
            
            // Informations d'intervention
            $table->string('lieu_intervention')->nullable();
            $table->json('coordonnees_gps')->nullable(); // {lat, lng}
            $table->text('description_tache')->nullable();
            $table->json('materiels_requis')->nullable();
            $table->integer('duree_estimee_minutes')->nullable();
            $table->integer('duree_reelle_minutes')->nullable();
            
            // Déplacement et logistique
            $table->string('vehicule_utilise')->nullable();
            $table->decimal('kilometres_parcourus', 8, 2)->nullable();
            $table->decimal('frais_deplacement', 8, 2)->nullable();
            
            // Suivi et validation
            $table->unsignedBigInteger('cree_par_id');
            $table->unsignedBigInteger('valide_par_id')->nullable();
            $table->datetime('date_validation')->nullable();
            $table->text('commentaires')->nullable();
            $table->text('rapport_intervention')->nullable();
            
            // Evaluation
            $table->enum('difficulte', ['facile', 'moyenne', 'difficile', 'complexe'])->nullable();
            $table->integer('note_client')->nullable(); // 1-5
            $table->boolean('objectifs_atteints')->nullable();
            
            $table->timestamps();
            
            // Clés étrangères
            $table->foreign('employe_id')->references('id')->on('employes')->onDelete('cascade');
            $table->foreign('demande_id')->references('id')->on('demandes')->onDelete('cascade');
            $table->foreign('equipe_id')->references('id')->on('equipes')->onDelete('set null');
            $table->foreign('cree_par_id')->references('id')->on('employes')->onDelete('cascade');
            $table->foreign('valide_par_id')->references('id')->on('employes')->onDelete('set null');
            
            // Index pour optimiser les recherches
            $table->index(['employe_id', 'date_debut', 'date_fin']);
            $table->index(['demande_id', 'statut']);
            $table->index(['equipe_id', 'date_debut']);
            $table->index(['statut', 'date_debut']);
            $table->index(['type_affectation', 'date_debut']);
            $table->index('lieu_intervention');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plannings');
    }
};