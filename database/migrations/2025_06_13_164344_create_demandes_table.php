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
        Schema::create('demandes', function (Blueprint $table) {
            $table->id();
            $table->string('numero_demande')->unique();
            $table->string('titre');
            $table->text('description');
            $table->string('priorite')->default('normale');
            $table->string('statut')->default('en_attente');
            $table->foreignId('createur_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('receveur_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->string('lieu_intervention')->nullable();
            $table->datetime('date_demande');
            $table->datetime('date_souhaite_intervention')->nullable();
            $table->datetime('date_assignation')->nullable();
            $table->datetime('date_completion')->nullable();
            $table->text('notes_receveur')->nullable();
            $table->foreignId('attachement_id')->nullable()->constrained('attachements')->nullOnDelete();
            $table->timestamps();
            
            $table->index(['statut', 'priorite']);
            $table->index(['createur_id', 'statut']);
            $table->index(['receveur_id', 'statut']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('demandes');
    }
};
