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
        Schema::create('intervention_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('intervention_id')->constrained()->cascadeOnDelete();
            $table->foreignId('technicien_id')->constrained()->cascadeOnDelete();
            $table->string('action'); // planification, depart, arrivee, debut, pause, reprise, fin, incident
            $table->string('statut_avant')->nullable();
            $table->string('statut_apres')->nullable();
            $table->text('description')->nullable();
            $table->json('donnees_supplementaires')->nullable(); // Contexte, coordonnées, etc.
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->timestamp('timestamp_action');
            $table->json('photos')->nullable();
            $table->text('commentaire_technicien')->nullable();
            $table->timestamps();
            
            $table->index(['intervention_id', 'timestamp_action']);
            $table->index(['technicien_id', 'action']);
            $table->index('timestamp_action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('intervention_logs');
    }
};
