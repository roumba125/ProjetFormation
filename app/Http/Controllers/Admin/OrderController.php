<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $orders = Order::with(['items'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->search, fn($q, $s) =>
                $q->where('order_number', 'like', "%{$s}%")
                  ->orWhere('client_name', 'like', "%{$s}%")
            )
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json([
            'data' => $orders->items(),
            'meta' => [
                'total'        => $orders->total(),
                'current_page' => $orders->currentPage(),
                'last_page'    => $orders->lastPage(),
            ],
            'counts' => [
                'pending'   => Order::where('status', 'pending')->count(),
                'confirmed' => Order::where('status', 'confirmed')->count(),
                'delivered' => Order::where('status', 'delivered')->count(),
            ],
        ]);
    }

    public function show(Order $order): JsonResponse
    {
        $order->load(['items.sheep.photos', 'user']);
        return response()->json(['data' => $order]);
    }

    public function confirm(Order $order): JsonResponse
    {
        return DB::transaction(function () use ($order) {
            $fresh = Order::lockForUpdate()->findOrFail($order->id);

            if ($fresh->status !== 'pending') {
                return response()->json(['message' => 'Cette commande ne peut pas être confirmée.'], 422);
            }

            $fresh->confirm();

            return response()->json(['message' => 'Commande confirmée.']);
        });
    }

    public function cancel(Request $request, Order $order): JsonResponse
    {
        if (in_array($order->status, ['delivered', 'cancelled'])) {
            return response()->json(['message' => 'Impossible d\'annuler.'], 422);
        }
        if ($request->filled('reason')) {
            $order->update(['admin_notes' => 'Annulation : ' . $request->reason]);
        }
        $order->cancel();
        return response()->json(['message' => 'Commande annulée.']);
    }

    public function deliver(Order $order): JsonResponse
    {
        if (!in_array($order->status, ['confirmed', 'paid'])) {
            return response()->json(['message' => 'La commande doit être confirmée avant livraison.'], 422);
        }
        $order->markAsDelivered();
        return response()->json(['message' => 'Livraison enregistrée.']);
    }
}
