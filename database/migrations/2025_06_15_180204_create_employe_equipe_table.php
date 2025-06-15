<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employe_equipe', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employe_id');
            $table->unsignedBigInteger('equipe_id');
            
            // Informations sur l'affectation
            $table->enum('role_equipe', ['chef_equipe', 'membre', 'apprenti'])->default('membre');
            $table->date('date_debut_affectation');
            $table->date('date_fin_affectation')->nullable();
            $table->boolean('active')->default(true);
            
            $table->timestamps();
            
            // Contraintes et index
            $table->foreign('employe_id')->references('id')->on('employes')->onDelete('cascade');
            $table->foreign('equipe_id')->references('id')->on('equipes')->onDelete('cascade');
            $table->unique(['employe_id', 'equipe_id', 'active']); // Un employé ne peut être actif que dans une équipe
            $table->index(['employe_id', 'active']);
            $table->index(['equipe_id', 'active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employe_equipe');
    }
};