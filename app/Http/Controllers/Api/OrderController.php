<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Sheep;
use App\Notifications\OrderConfirmedNotification;
use App\Notifications\NewOrderAdminNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    /**
     * POST /api/v1/orders
     * Passer une commande
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            // Moutons commandés
            'sheep_ids'              => 'required|array|min:1|max:10',
            'sheep_ids.*'            => 'integer|exists:sheep,id',

            // Infos client
            'client_name'            => 'required|string|max:100',
            'client_email'           => 'required|email|max:150',
            'client_phone'           => 'required|string|max:20',
            'client_city'            => 'nullable|string|max:100',
            'client_address'         => 'nullable|string|max:255',

            // Livraison
            'delivery_method'        => 'required|in:delivery,pickup',
            'delivery_address'       => 'required_if:delivery_method,delivery|nullable|string|max:255',
            'delivery_city'          => 'required_if:delivery_method,delivery|nullable|string|max:100',
            'desired_delivery_date'  => 'nullable|date|after:today',

            // Paiement
            'payment_method'         => ['required', Rule::in(['orange_money', 'wave', 'moov_money', 'bank_transfer', 'cash'])],
            'deposit_amount'         => 'nullable|numeric|min:0',

            'client_notes'           => 'nullable|string|max:1000',
        ]);

        return DB::transaction(function () use ($validated, $request) {

            // 1. Vérifier que tous les moutons sont disponibles
            $sheepIds = collect($validated['sheep_ids'])->unique();
            $sheep    = Sheep::whereIn('id', $sheepIds)
                ->where('status', 'available')
                ->where('is_active', true)
                ->get();

            if ($sheep->count() !== $sheepIds->count()) {
                return response()->json([
                    'message' => 'Un ou plusieurs moutons ne sont plus disponibles. Veuillez actualiser le catalogue.',
                    'errors'  => ['sheep_ids' => ['Certains moutons sont déjà réservés ou vendus.']],
                ], 422);
            }

            // 2. Calculer les montants
            $subtotal    = $sheep->sum('price');
            $deliveryFee = $validated['delivery_method'] === 'delivery' ? $this->calculateDeliveryFee($validated['delivery_city'] ?? '') : 0;
            $totalAmount = $subtotal + $deliveryFee;
            $deposit     = min($validated['deposit_amount'] ?? 0, $totalAmount);

            // 3. Créer la commande
            $order = Order::create([
                'user_id'               => auth()->id(),
                'client_name'           => $validated['client_name'],
                'client_email'          => $validated['client_email'],
                'client_phone'          => $validated['client_phone'],
                'client_city'           => $validated['client_city'] ?? null,
                'client_address'        => $validated['client_address'] ?? null,
                'status'                => 'pending',
                'delivery_method'       => $validated['delivery_method'],
                'delivery_address'      => $validated['delivery_address'] ?? null,
                'delivery_city'         => $validated['delivery_city'] ?? null,
                'delivery_fee'          => $deliveryFee,
                'desired_delivery_date' => $validated['desired_delivery_date'] ?? null,
                'subtotal'              => $subtotal,
                'total_amount'          => $totalAmount,
                'deposit_amount'        => $deposit,
                'remaining_amount'      => $totalAmount - $deposit,
                'payment_method'        => $validated['payment_method'],
                'payment_status'        => $deposit > 0 ? 'partial' : 'unpaid',
                'client_notes'          => $validated['client_notes'] ?? null,
            ]);

            // 4. Créer les lignes de commande (snapshot)
            foreach ($sheep as $s) {
                OrderItem::create([
                    'order_id'        => $order->id,
                    'sheep_id'        => $s->id,
                    'sheep_name'      => $s->name,
                    'sheep_reference' => $s->reference,
                    'breed_name'      => $s->breed->name,
                    'weight_at_order' => $s->current_weight,
                    'unit_price'      => $s->price,
                ]);

                // Réserver le mouton immédiatement
                $s->reserve();
            }

            // 5. Notifications
            // Au client
            Notification::route('mail', $order->client_email)
                ->notify(new OrderConfirmedNotification($order));

            // À l'admin
            Notification::route('mail', config('bergerpro.admin_email'))
                ->notify(new NewOrderAdminNotification($order));

            return response()->json([
                'message' => 'Commande passée avec succès ! Nous vous contacterons sous 24h.',
                'data'    => [
                    'order_number'   => $order->order_number,
                    'status'         => $order->status_label,
                    'total_amount'   => $order->total_amount,
                    'deposit_amount' => $order->deposit_amount,
                    'remaining'      => $order->remaining_amount,
                    'sheep_count'    => $sheep->count(),
                ],
            ], 201);
        });
    }

    /**
     * GET /api/v1/orders/{orderNumber}
     * Suivi de commande (par numéro + email)
     */
    public function track(Request $request, string $orderNumber): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $order = Order::where('order_number', $orderNumber)
            ->where('client_email', $request->email)
            ->with(['items.sheep.photos'])
            ->first();

        abort_unless($order, 404, 'Commande introuvable. Vérifiez le numéro et l\'email.');

        return response()->json([
            'data' => [
                'order_number'         => $order->order_number,
                'status'               => $order->status,
                'status_label'         => $order->status_label,
                'payment_status'       => $order->payment_status_label,
                'delivery_method'      => $order->delivery_method_label,
                'desired_delivery'     => $order->desired_delivery_date?->format('d/m/Y'),
                'actual_delivery'      => $order->actual_delivery_date?->format('d/m/Y'),
                'total_amount'         => $order->total_amount,
                'deposit_paid'         => $order->deposit_amount,
                'remaining'            => $order->remaining_amount,
                'items'                => $order->items->map(fn($item) => [
                    'sheep_name'      => $item->sheep_name,
                    'sheep_reference' => $item->sheep_reference,
                    'breed'           => $item->breed_name,
                    'weight'          => $item->weight_at_order . ' kg',
                    'price'           => number_format($item->unit_price, 0, ',', ' ') . ' FCFA',
                    'photo'           => $item->sheep?->primary_photo_url,
                ]),
                'created_at'           => $order->created_at->format('d/m/Y à H:i'),
            ],
        ]);
    }

    private function calculateDeliveryFee(string $city): float
    {
        $fees = config('bergerpro.delivery_fees', []);

        return $fees[strtolower(trim($city))] ?? 10000;
    }
}
