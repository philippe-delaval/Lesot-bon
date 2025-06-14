<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('interventions', function (Blueprint $table) {
            $table->id();
            $table->string('numero_intervention')->unique();
            $table->foreignId('demande_id')->constrained()->cascadeOnDelete();
            $table->foreignId('technicien_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            
            // Type et caractéristiques
            $table->string('type_intervention'); // preventive, corrective, urgente, installation
            $table->text('description_technique');
            $table->jsonb('competences_requises')->nullable(); // ['plomberie', 'electricite']
            $table->string('priorite')->default('normale'); // basse, normale, haute, urgente
            $table->string('statut')->default('planifiee'); // planifiee, en_route, sur_site, en_cours, terminee, annulee
            
            // Planning et timing
            $table->datetime('date_planifiee');
            $table->datetime('heure_debut_prevue');
            $table->datetime('heure_fin_prevue');
            $table->datetime('heure_arrivee')->nullable();
            $table->datetime('heure_debut_relle')->nullable();
            $table->datetime('heure_fin_relle')->nullable();
            $table->integer('duree_estimee_minutes');
            $table->integer('duree_relle_minutes')->nullable();
            
            // Localisation
            $table->text('adresse_intervention');
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->text('instructions_acces')->nullable();
            $table->decimal('distance_technicien_km')->nullable();
            $table->integer('temps_trajet_estime_min')->nullable();
            
            // Équipements et ressources
            $table->jsonb('equipements_necessaires')->nullable();
            $table->jsonb('pieces_detachees')->nullable();
            $table->jsonb('outils_speciaux')->nullable();
            $table->decimal('cout_estime', 10, 2)->nullable();
            $table->decimal('cout_reel', 10, 2)->nullable();
            
            // Résultats et suivi
            $table->text('diagnostic')->nullable();
            $table->text('actions_realisees')->nullable();
            $table->jsonb('pieces_utilisees')->nullable();
            $table->jsonb('photos_avant')->nullable();
            $table->jsonb('photos_apres')->nullable();
            $table->text('signature_client_path')->nullable();
            $table->text('rapport_technique')->nullable();
            $table->boolean('intervention_reussie')->nullable();
            $table->text('problemes_rencontres')->nullable();
            $table->text('recommandations')->nullable();
            
            // Satisfaction et suivi
            $table->integer('note_client')->nullable(); // 1-5
            $table->text('commentaire_client')->nullable();
            $table->boolean('facturation_automatique')->default(false);
            $table->datetime('date_facturation')->nullable();
            $table->string('numero_facture')->nullable();
            
            // Suivi qualité
            $table->boolean('first_time_fix')->nullable();
            $table->integer('temps_resolution_minutes')->nullable();
            $table->jsonb('kpis')->nullable(); // Métriques calculées
            
            $table->timestamps();
            
            // Indexes pour performance
            $table->index(['statut', 'date_planifiee']);
            $table->index(['technicien_id', 'statut']);
            $table->index(['client_id', 'date_planifiee']);
            $table->index(['type_intervention', 'priorite']);
            $table->index(['latitude', 'longitude']);
        });

        // Créer l'index GIN séparément
        DB::statement('CREATE INDEX interventions_competences_requises_index ON interventions USING gin (competences_requises)');
    }

    public function down()
    {
        DB::statement('DROP INDEX IF EXISTS interventions_competences_requises_index');
        Schema::dropIfExists('interventions');
    }
};
