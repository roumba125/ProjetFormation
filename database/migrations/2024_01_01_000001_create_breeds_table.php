<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('breeds', function (Blueprint $table) {
            $table->id();
            $table->string('name');                        // Ex: "Race Peulh Soudanaise"
            $table->string('slug')->unique();              // Ex: "peulh-soudanaise"
            $table->string('origin')->nullable();          // Ex: "Afrique de l'Ouest"
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->decimal('avg_adult_weight_male', 5, 2)->nullable();   // kg
            $table->decimal('avg_adult_weight_female', 5, 2)->nullable(); // kg
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('breeds');
    }
};
