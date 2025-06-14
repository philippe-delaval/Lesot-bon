<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('equipements', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->string('nom');
            $table->text('description')->nullable();
            $table->string('type'); // piece_detachee, outil, equipement_mesure, vehicule
            $table->string('categorie'); // plomberie, electricite, climatisation, general
            $table->string('marque')->nullable();
            $table->string('modele')->nullable();
            $table->string('numero_serie')->nullable();
            
            // Stock et localisation
            $table->integer('stock_total');
            $table->integer('stock_disponible');
            $table->integer('stock_minimum');
            $table->string('localisation_depot');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            
            // Coûts et valeurs
            $table->decimal('prix_unitaire', 10, 2);
            $table->decimal('cout_maintenance', 10, 2)->nullable();
            $table->string('fournisseur')->nullable();
            $table->string('contact_fournisseur')->nullable();
            
            // Maintenance et cycle de vie
            $table->date('date_achat')->nullable();
            $table->date('date_mise_service')->nullable();
            $table->integer('duree_vie_mois')->nullable();
            $table->date('prochaine_maintenance')->nullable();
            $table->text('historique_maintenance')->nullable();
            $table->string('etat')->default('neuf'); // neuf, bon, usage, defaillant, reforme
            
            // Affectation et utilisation
            $table->foreignId('technicien_id')->nullable()->constrained()->nullOnDelete();
            $table->string('statut')->default('disponible'); // disponible, reserve, en_utilisation, maintenance
            $table->jsonb('competences_associees')->nullable();
            $table->boolean('transportable')->default(true);
            $table->decimal('poids_kg', 8, 2)->nullable();
            $table->text('instructions_utilisation')->nullable();
            
            // Traçabilité
            $table->jsonb('historique_utilisation')->nullable();
            $table->timestamp('derniere_utilisation')->nullable();
            $table->integer('nombre_utilisations')->default(0);
            $table->boolean('actif')->default(true);
            
            $table->timestamps();
            
            $table->index(['type', 'categorie']);
            $table->index(['statut', 'actif']);
            $table->index(['stock_disponible', 'stock_minimum']);
            $table->index(['technicien_id', 'statut']);
        });

        // Créer les index GIN pour les colonnes JSONB
        DB::statement('CREATE INDEX equipements_competences_associees_gin_idx ON equipements USING GIN (competences_associees)');
        DB::statement('CREATE INDEX equipements_historique_utilisation_gin_idx ON equipements USING GIN (historique_utilisation)');
    }

    public function down()
    {
        DB::statement('DROP INDEX IF EXISTS equipements_competences_associees_gin_idx');
        DB::statement('DROP INDEX IF EXISTS equipements_historique_utilisation_gin_idx');
        Schema::dropIfExists('equipements');
    }
};
