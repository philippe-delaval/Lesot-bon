<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('intervention_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('intervention_id')->constrained()->cascadeOnDelete();
            $table->foreignId('technicien_id')->constrained()->cascadeOnDelete();
            $table->string('action'); // planification, depart, arrivee, debut, pause, reprise, fin, incident
            $table->string('statut_avant')->nullable();
            $table->string('statut_apres')->nullable();
            $table->text('description')->nullable();
            $table->jsonb('donnees_supplementaires')->nullable(); // Contexte, coordonnées, etc.
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->timestamp('timestamp_action');
            $table->jsonb('photos')->nullable();
            $table->text('commentaire_technicien')->nullable();
            $table->timestamps();
            
            $table->index(['intervention_id', 'timestamp_action']);
            $table->index(['technicien_id', 'action']);
            $table->index('timestamp_action');
        });

        // Add GIN indexes for JSONB columns
        DB::statement('CREATE INDEX intervention_logs_donnees_supplementaires_gin_idx ON intervention_logs USING GIN (donnees_supplementaires)');
        DB::statement('CREATE INDEX intervention_logs_photos_gin_idx ON intervention_logs USING GIN (photos)');
    }

    public function down()
    {
        DB::statement('DROP INDEX IF EXISTS intervention_logs_donnees_supplementaires_gin_idx');
        DB::statement('DROP INDEX IF EXISTS intervention_logs_photos_gin_idx');
        Schema::dropIfExists('intervention_logs');
    }
};
