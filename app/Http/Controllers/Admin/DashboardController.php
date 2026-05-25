<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Sheep;
use App\Models\Vaccination;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function index(): JsonResponse
    {
        $sheepStats = [
            'available' => Sheep::where('status', 'available')->count(),
            'reserved'  => Sheep::where('status', 'reserved')->count(),
            'sold'      => Sheep::where('status', 'sold')->count(),
            'total'     => Sheep::count(),
        ];

        $orderStats = [
            'pending'        => Order::where('status', 'pending')->count(),
            'confirmed'      => Order::where('status', 'confirmed')->count(),
            'delivered'      => Order::where('status', 'delivered')->count(),
            'total_revenue'  => Order::where('status', 'delivered')->sum('total_amount'),
            'pending_revenue'=> Order::whereIn('status', ['confirmed', 'paid'])->sum('remaining_amount'),
        ];

        $revenueByMonth = Order::where('status', 'delivered')
            ->where('created_at', '>=', now()->subMonths(12))
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, SUM(total_amount) as total, COUNT(*) as orders')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $upcomingVaccinations = Vaccination::with('sheep:id,name,reference')
            ->where('next_due_at', '<=', now()->addDays(30))
            ->where('next_due_at', '>=', now())
            ->orderBy('next_due_at')
            ->get()
            ->map(fn($v) => [
                'sheep_name' => $v->sheep->name,
                'sheep_ref'  => $v->sheep->reference,
                'vaccine'    => $v->vaccine_name,
                'due_date'   => $v->next_due_at->format('d/m/Y'),
                'days_left'  => now()->diffInDays($v->next_due_at),
            ]);

        $recentOrders = Order::with('items')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->map(fn($o) => [
                'order_number' => $o->order_number,
                'client_name'  => $o->client_name,
                'client_phone' => $o->client_phone,
                'total'        => $o->total_amount,
                'status'       => $o->status_label,
                'date'         => $o->created_at->diffForHumans(),
            ]);

        return response()->json([
            'sheep'                 => $sheepStats,
            'orders'                => $orderStats,
            'revenue_by_month'      => $revenueByMonth,
            'upcoming_vaccinations' => $upcomingVaccinations,
            'recent_orders'         => $recentOrders,
        ]);
    }
}
