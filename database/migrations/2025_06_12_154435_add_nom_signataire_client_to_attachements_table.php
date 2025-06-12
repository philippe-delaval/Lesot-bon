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
        Schema::table('attachements', function (Blueprint $table) {
            // Ajouter le champ nom_signataire_client après client_nom
            $table->string('nom_signataire_client')->nullable()->after('client_nom')
                  ->comment('Nom de la personne qui signe pour le client (peut être différent du nom du client)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attachements', function (Blueprint $table) {
            $table->dropColumn('nom_signataire_client');
        });
    }
};
