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
        Schema::create('attachements', function (Blueprint $table) {
            $table->id();
            $table->string('numero_dossier')->unique();
            $table->string('client_nom');
            $table->string('client_email');
            $table->text('client_adresse_facturation');
            $table->string('lieu_intervention');
            $table->date('date_intervention');
            $table->text('designation_travaux');
            $table->json('fournitures_travaux');
            $table->decimal('temps_total_passe', 8, 2);
            $table->string('signature_entreprise_path');
            $table->string('signature_client_path');
            $table->string('pdf_path');
            $table->json('geolocation')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            
            // Index pour améliorer les performances de recherche
            $table->index('numero_dossier');
            $table->index('client_nom');
            $table->index('date_intervention');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attachements');
    }
};
