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
        Schema::table('employes', function (Blueprint $table) {
            // Index pour les recherches textuelles
            if (!$this->indexExists('employes', 'employes_nom_prenom_index')) {
                $table->index(['nom', 'prenom']);
            }
            if (!$this->indexExists('employes', 'employes_email_index')) {
                $table->index('email');
            }
            if (!$this->indexExists('employes', 'employes_matricule_index')) {
                $table->index('matricule');
            }
            
            // Index pour les filtres fréquents
            if (!$this->indexExists('employes', 'employes_statut_index')) {
                $table->index('statut');
            }
            if (!$this->indexExists('employes', 'employes_disponibilite_index')) {
                $table->index('disponibilite');
            }
            if (!$this->indexExists('employes', 'employes_role_hierarchique_index')) {
                $table->index('role_hierarchique');
            }
            
            // Index pour les relations
            if (!$this->indexExists('employes', 'employes_charge_projet_id_index')) {
                $table->index('charge_projet_id');
            }
            if (!$this->indexExists('employes', 'employes_gestionnaire_id_index')) {
                $table->index('gestionnaire_id');
            }
            
            // Index composite pour les requêtes de tri
            if (!$this->indexExists('employes', 'employes_nom_prenom_statut_index')) {
                $table->index(['nom', 'prenom', 'statut']);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employes', function (Blueprint $table) {
            $table->dropIndex(['nom', 'prenom']);
            $table->dropIndex(['email']);
            $table->dropIndex(['matricule']);
            $table->dropIndex(['statut']);
            $table->dropIndex(['disponibilite']);
            $table->dropIndex(['role_hierarchique']);
            $table->dropIndex(['charge_projet_id']);
            $table->dropIndex(['gestionnaire_id']);
            $table->dropIndex(['nom', 'prenom', 'statut']);
        });
    }

    /**
     * Vérifie si un index existe
     */
    private function indexExists($table, $indexName): bool
    {
        $indexes = DB::select("PRAGMA index_list({$table})");
        return collect($indexes)->contains('name', $indexName);
    }
};
