<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\SheepResource;
use App\Models\Sheep;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SheepController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $sheep = Sheep::with(['breed', 'photos'])
            ->withTrashed()
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->search, fn($q, $s) =>
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('reference', 'like', "%{$s}%")
            )
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json([
            'data' => SheepResource::collection($sheep->items()),
            'meta' => [
                'total'        => $sheep->total(),
                'current_page' => $sheep->currentPage(),
                'last_page'    => $sheep->lastPage(),
            ],
            'counts' => [
                'available' => Sheep::where('status', 'available')->count(),
                'reserved'  => Sheep::where('status', 'reserved')->count(),
                'sold'      => Sheep::where('status', 'sold')->count(),
                'total'     => Sheep::count(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'                 => 'required|string|max:100',
            'breed_id'             => 'required|exists:breeds,id',
            'gender'               => 'required|in:male,female',
            'birth_date'           => 'required|date|before:today',
            'current_weight'       => 'required|numeric|min:1|max:500',
            'height'               => 'nullable|numeric',
            'coat_color'           => 'nullable|string|max:100',
            'physical_description' => 'nullable|string|max:1000',
            'health_condition'     => 'required|in:excellent,good,fair,poor',
            'last_vet_checkup'     => 'nullable|date',
            'current_diet'         => 'nullable|string|max:255',
            'price'                => 'required|numeric|min:0',
            'negotiable_price'     => 'nullable|numeric|min:0',
            'status'               => 'required|in:available,reserved,sold',
            'is_featured'          => 'boolean',
            'notes'                => 'nullable|string|max:2000',
        ]);

        $validated['reference'] = Sheep::generateReference();
        $sheep = Sheep::create($validated);

        return response()->json([
            'message' => 'Mouton créé avec succès.',
            'data'    => new SheepResource($sheep->load(['breed', 'photos'])),
        ], 201);
    }

    public function show(Sheep $sheep): JsonResponse
    {
        return response()->json([
            'data' => new SheepResource($sheep->load(['breed', 'photos', 'vaccinations', 'weightRecords'])),
        ]);
    }

    public function update(Request $request, Sheep $sheep): JsonResponse
    {
        $validated = $request->validate([
            'name'                 => 'sometimes|string|max:100',
            'breed_id'             => 'sometimes|exists:breeds,id',
            'gender'               => 'sometimes|in:male,female',
            'birth_date'           => 'sometimes|date',
            'current_weight'       => 'sometimes|numeric|min:1|max:500',
            'health_condition'     => 'sometimes|in:excellent,good,fair,poor',
            'price'                => 'sometimes|numeric|min:0',
            'status'               => 'sometimes|in:available,reserved,sold',
            'is_featured'          => 'boolean',
            'is_active'            => 'boolean',
            'notes'                => 'nullable|string|max:2000',
        ]);

        $sheep->update($validated);

        return response()->json([
            'message' => 'Mouton mis à jour.',
            'data'    => new SheepResource($sheep->fresh(['breed', 'photos'])),
        ]);
    }

    public function destroy(Sheep $sheep): JsonResponse
    {
        $sheep->delete();
        return response()->json(['message' => 'Mouton archivé.']);
    }
}
