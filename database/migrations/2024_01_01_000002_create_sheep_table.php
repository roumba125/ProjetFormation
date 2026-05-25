<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sheep', function (Blueprint $table) {
            $table->id();

            // --- Identité ---
            $table->string('name');                        // Ex: "Alpha"
            $table->string('reference')->unique();         // Ex: "BP-2024-001"
            $table->foreignId('breed_id')->constrained()->restrictOnDelete();
            $table->enum('gender', ['male', 'female']);
            $table->date('birth_date');
            $table->string('ear_tag')->nullable()->unique(); // Boucle auriculaire officielle

            // --- Caractéristiques physiques ---
            $table->decimal('current_weight', 6, 2);       // kg (mis à jour régulièrement)
            $table->decimal('height', 5, 2)->nullable();   // cm
            $table->string('coat_color')->nullable();       // couleur de la robe
            $table->text('physical_description')->nullable();

            // --- Santé & état ---
            $table->enum('health_condition', ['excellent', 'good', 'fair', 'poor'])->default('good');
            $table->date('last_vet_checkup')->nullable();
            $table->text('notes')->nullable();

            // --- Alimentation ---
            $table->string('current_diet')->nullable();    // Ex: "Foin + Concentré + Sel"

            // --- Vente ---
            $table->decimal('price', 10, 2);               // FCFA
            $table->decimal('negotiable_price', 10, 2)->nullable();
            $table->enum('status', ['available', 'reserved', 'sold'])->default('available');
            $table->boolean('is_featured')->default(false); // Mise en avant sur le site
            $table->boolean('is_active')->default(true);    // Visible ou pas

            // --- Relations parent (optionnel) ---
            $table->foreignId('mother_id')->nullable()->constrained('sheep')->nullOnDelete();
            $table->foreignId('father_id')->nullable()->constrained('sheep')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // Index pour les filtres fréquents
            $table->index(['status', 'is_active']);
            $table->index(['breed_id', 'gender']);
            $table->index('birth_date');
            $table->index('price');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sheep');
    }
};
