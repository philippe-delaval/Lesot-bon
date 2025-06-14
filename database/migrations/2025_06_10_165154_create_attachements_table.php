<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('attachements', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('numero_dossier')->unique();
            $table->string('client_nom');
            $table->string('client_email');
            $table->text('client_adresse_facturation');
            $table->string('lieu_intervention');
            $table->date('date_intervention');
            $table->text('designation_travaux');
            $table->jsonb('fournitures_travaux');
            $table->decimal('temps_total_passe', 8, 2);
            $table->string('signature_entreprise_path');
            $table->string('signature_client_path');
            $table->string('pdf_path');
            $table->jsonb('geolocation')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('restrict');
            $table->timestampsTz(precision: 6);
            
            // Index pour améliorer les performances de recherche
            $table->index('numero_dossier');
            $table->index('client_nom');
            $table->index('date_intervention');
            $table->index('created_at');
        });

        // Add GIN indexes for JSONB columns
        DB::statement('CREATE INDEX attachements_fournitures_travaux_gin_idx ON attachements USING GIN (fournitures_travaux)');
        DB::statement('CREATE INDEX attachements_geolocation_gin_idx ON attachements USING GIN (geolocation)');
    }

    public function down()
    {
        DB::statement('DROP INDEX IF EXISTS attachements_fournitures_travaux_gin_idx');
        DB::statement('DROP INDEX IF EXISTS attachements_geolocation_gin_idx');
        Schema::dropIfExists('attachements');
    }
};
