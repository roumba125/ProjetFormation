<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // -------------------------------------------------------
        // COMMANDES
        // -------------------------------------------------------
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique(); // Ex: "CMD-2024-00042"

            // --- Client (peut être un user enregistré ou un guest) ---
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('client_name');
            $table->string('client_email');
            $table->string('client_phone');
            $table->string('client_city')->nullable();
            $table->text('client_address')->nullable();

            // --- Statut de la commande ---
            $table->enum('status', [
                'pending',      // En attente de confirmation
                'confirmed',    // Confirmée par l'éleveur
                'paid',         // Acompte ou totalité payé
                'ready',        // Prêt pour livraison/retrait
                'delivered',    // Livré
                'cancelled',    // Annulée
                'refunded',     // Remboursée
            ])->default('pending');

            // --- Livraison ---
            $table->enum('delivery_method', ['delivery', 'pickup'])->default('pickup');
            $table->text('delivery_address')->nullable();
            $table->string('delivery_city')->nullable();
            $table->decimal('delivery_fee', 10, 2)->default(0);
            $table->date('desired_delivery_date')->nullable();
            $table->date('actual_delivery_date')->nullable();

            // --- Montants ---
            $table->decimal('subtotal', 12, 2);       // Sous-total moutons
            $table->decimal('total_amount', 12, 2);   // Total avec livraison
            $table->decimal('deposit_amount', 12, 2)->default(0); // Acompte versé
            $table->decimal('remaining_amount', 12, 2)->default(0); // Reste à payer

            // --- Paiement ---
            $table->enum('payment_method', [
                'orange_money',
                'wave',
                'moov_money',
                'bank_transfer',
                'cash',
            ])->nullable();
            $table->enum('payment_status', ['unpaid', 'partial', 'paid'])->default('unpaid');
            $table->string('transaction_id')->nullable(); // Référence paiement mobile

            // --- Notes ---
            $table->text('client_notes')->nullable();  // Note du client
            $table->text('admin_notes')->nullable();   // Note interne

            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'created_at']);
            $table->index('payment_status');
            $table->index('client_email');
        });

        // -------------------------------------------------------
        // LIGNES DE COMMANDE
        // -------------------------------------------------------
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sheep_id')->constrained()->restrictOnDelete();

            // Snapshot des infos au moment de la commande (historique)
            $table->string('sheep_name');
            $table->string('sheep_reference');
            $table->string('breed_name');
            $table->decimal('weight_at_order', 6, 2);
            $table->decimal('unit_price', 10, 2);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
