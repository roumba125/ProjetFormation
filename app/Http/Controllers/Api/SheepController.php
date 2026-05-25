<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SheepCollection;
use App\Http\Resources\SheepResource;
use App\Models\Sheep;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SheepController extends Controller
{
    /**
     * GET /api/v1/sheep
     * Catalogue avec filtres, tri et pagination
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'breed_id'   => 'nullable|integer|exists:breeds,id',
            'gender'     => 'nullable|in:male,female',
            'status'     => 'nullable|in:available,reserved,sold',
            'min_price'  => 'nullable|numeric|min:0',
            'max_price'  => 'nullable|numeric|min:0',
            'min_weight' => 'nullable|numeric|min:0',
            'max_weight' => 'nullable|numeric|min:0',
            'featured'   => 'nullable|boolean',
            'sort_by'    => 'nullable|in:price_asc,price_desc,weight_asc,weight_desc,newest,oldest',
            'per_page'   => 'nullable|integer|min:6|max:48',
            'search'     => 'nullable|string|max:100',
        ]);

        $query = Sheep::with(['breed', 'photos'])
            ->where('is_active', true);

        // --- Filtres ---
        if ($request->filled('breed_id')) {
            $query->byBreed($request->breed_id);
        }

        if ($request->filled('gender')) {
            $query->byGender($request->gender);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            // Par défaut : seulement disponibles et réservés (pas vendus)
            $query->whereIn('status', ['available', 'reserved']);
        }

        if ($request->filled('min_price') || $request->filled('max_price')) {
            $min = $request->get('min_price', 0);
            $max = $request->get('max_price', 9999999);
            $query->priceBetween($min, $max);
        }

        if ($request->filled('min_weight') || $request->filled('max_weight')) {
            $min = $request->get('min_weight', 0);
            $max = $request->get('max_weight', 999);
            $query->weightBetween($min, $max);
        }

        if ($request->boolean('featured')) {
            $query->where('is_featured', true);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('reference', 'like', "%{$search}%")
                  ->orWhereHas('breed', fn($b) => $b->where('name', 'like', "%{$search}%"));
            });
        }

        // --- Tri ---
        match ($request->get('sort_by', 'newest')) {
            'price_asc'    => $query->orderBy('price'),
            'price_desc'   => $query->orderByDesc('price'),
            'weight_asc'   => $query->orderBy('current_weight'),
            'weight_desc'  => $query->orderByDesc('current_weight'),
            'oldest'       => $query->orderBy('created_at'),
            default        => $query->orderByDesc('is_featured')->orderByDesc('created_at'),
        };

        $perPage = $request->get('per_page', 12);
        $sheep   = $query->paginate($perPage);

        return response()->json([
            'data' => SheepResource::collection($sheep->items()),
            'meta' => [
                'total'        => $sheep->total(),
                'per_page'     => $sheep->perPage(),
                'current_page' => $sheep->currentPage(),
                'last_page'    => $sheep->lastPage(),
                'from'         => $sheep->firstItem(),
                'to'           => $sheep->lastItem(),
            ],
            'filters_applied' => $request->only(['breed_id', 'gender', 'status', 'min_price', 'max_price']),
        ]);
    }

    /**
     * GET /api/v1/sheep/{sheep}
     * Fiche complète d'un mouton
     */
    public function show(Sheep $sheep): JsonResponse
    {
        abort_unless($sheep->is_active, 404, 'Ce mouton n\'est pas disponible.');

        $sheep->load([
            'breed',
            'photos',
            'vaccinations',
            'weightRecords',
            'mother:id,name,reference',
            'father:id,name,reference',
        ]);

        return response()->json([
            'data' => new SheepResource($sheep),
        ]);
    }

    /**
     * GET /api/v1/sheep/{sheep}/weight-history
     * Graphique évolution du poids
     */
    public function weightHistory(Sheep $sheep): JsonResponse
    {
        $records = $sheep->weightRecords()
            ->select(['recorded_at', 'weight', 'daily_gain', 'diet'])
            ->orderBy('recorded_at')
            ->get();

        return response()->json([
            'data' => $records,
            'stats' => [
                'initial_weight' => $records->first()?->weight,
                'current_weight' => $records->last()?->weight,
                'total_gain'     => round(($records->last()?->weight ?? 0) - ($records->first()?->weight ?? 0), 2),
                'avg_daily_gain' => round($records->avg('daily_gain'), 2),
                'measures_count' => $records->count(),
            ],
        ]);
    }

    /**
     * GET /api/v1/sheep/{sheep}/vaccinations
     * Historique des vaccinations
     */
    public function vaccinations(Sheep $sheep): JsonResponse
    {
        $vaccinations = $sheep->vaccinations()
            ->orderByDesc('administered_at')
            ->get();

        return response()->json([
            'data' => $vaccinations,
            'summary' => [
                'total'    => $vaccinations->count(),
                'valid'    => $vaccinations->where('status', 'valid')->count(),
                'expired'  => $vaccinations->where('status', 'expired')->count(),
                'due_soon' => $vaccinations->where('status', 'due_soon')->count(),
            ],
        ]);
    }
}
