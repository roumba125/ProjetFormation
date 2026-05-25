<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // -------------------------------------------------------
        // PHOTOS
        // -------------------------------------------------------
        Schema::create('sheep_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sheep_id')->constrained()->cascadeOnDelete();
            $table->string('path');                        // chemin stockage (storage/app/public/...)
            $table->string('url');                         // URL publique
            $table->string('caption')->nullable();         // légende
            $table->boolean('is_primary')->default(false); // photo principale
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['sheep_id', 'is_primary']);
        });

        // -------------------------------------------------------
        // VACCINATIONS
        // -------------------------------------------------------
        Schema::create('vaccinations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sheep_id')->constrained()->cascadeOnDelete();
            $table->string('vaccine_name');                // Ex: "PPR", "Fièvre aphteuse"
            $table->string('vaccine_brand')->nullable();   // Ex: "Pestivax"
            $table->string('batch_number')->nullable();    // Numéro de lot
            $table->date('administered_at');               // Date d'injection
            $table->date('next_due_at')->nullable();       // Prochain rappel
            $table->string('administered_by')->nullable(); // Nom du vétérinaire
            $table->decimal('dose_ml', 5, 2)->nullable(); // Dose en ml
            $table->enum('route', ['subcutaneous', 'intramuscular', 'oral', 'other'])->default('subcutaneous');
            $table->text('notes')->nullable();
            $table->enum('status', ['valid', 'expired', 'due_soon'])->default('valid');
            $table->timestamps();

            $table->index(['sheep_id', 'administered_at']);
            $table->index('next_due_at');
        });

        // -------------------------------------------------------
        // HISTORIQUE DES POIDS
        // -------------------------------------------------------
        Schema::create('weight_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sheep_id')->constrained()->cascadeOnDelete();
            $table->decimal('weight', 6, 2);               // kg
            $table->date('recorded_at');                   // date de la pesée
            $table->string('diet')->nullable();            // alimentation à ce moment
            $table->decimal('daily_gain', 6, 2)->nullable(); // GMQ en g/jour (calculé)
            $table->string('measured_by')->nullable();     // qui a pesé
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['sheep_id', 'recorded_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weight_records');
        Schema::dropIfExists('vaccinations');
        Schema::dropIfExists('sheep_photos');
    }
};
