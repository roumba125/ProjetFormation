<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sheep;
use App\Models\WeightRecord;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WeightController extends Controller
{
    public function store(Request $request, Sheep $sheep): JsonResponse
    {
        $validated = $request->validate([
            'weight'      => 'required|numeric|min:1|max:500',
            'recorded_at' => 'required|date|before_or_equal:today',
            'diet'        => 'nullable|string|max:255',
            'measured_by' => 'nullable|string|max:100',
            'notes'       => 'nullable|string|max:500',
        ]);

        if ($sheep->weightRecords()->where('recorded_at', $validated['recorded_at'])->exists()) {
            return response()->json([
                'message' => 'Une pesée existe déjà pour cette date.',
                'errors'  => ['recorded_at' => ['Doublon détecté.']],
            ], 422);
        }

        $validated['sheep_id'] = $sheep->id;
        $record = WeightRecord::create($validated);

        return response()->json([
            'message'    => 'Pesée enregistrée.',
            'data'       => $record,
            'new_weight' => $sheep->fresh()->current_weight,
        ], 201);
    }
}
