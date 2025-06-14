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
            $table->json('competences_associees')->nullable();
            $table->boolean('transportable')->default(true);
            $table->decimal('poids_kg', 8, 2)->nullable();
            $table->text('instructions_utilisation')->nullable();
            
            // Traçabilité
            $table->json('historique_utilisation')->nullable();
            $table->timestamp('derniere_utilisation')->nullable();
            $table->integer('nombre_utilisations')->default(0);
            $table->boolean('actif')->default(true);
            
            $table->timestamps();
            
            $table->index(['type', 'categorie']);
            $table->index(['statut', 'actif']);
            $table->index(['stock_disponible', 'stock_minimum']);
            $table->index(['technicien_id', 'statut']);
            $table->index('competences_associees');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipements');
    }
};
