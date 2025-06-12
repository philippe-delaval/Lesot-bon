<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nom');
            $table->string('email')->unique();
            $table->string('adresse');
            $table->string('complement_adresse')->nullable();
            $table->string('code_postal');
            $table->string('ville');
            $table->string('telephone')->nullable();
            $table->text('notes')->nullable();
            $table->timestampsTz(precision: 6);
            
            $table->index(['nom', 'email']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};