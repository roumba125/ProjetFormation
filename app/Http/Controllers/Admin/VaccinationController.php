<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sheep;
use App\Models\Vaccination;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VaccinationController extends Controller
{
    public function store(Request $request, Sheep $sheep): JsonResponse
    {
        $validated = $request->validate([
            'vaccine_name'    => 'required|string|max:150',
            'vaccine_brand'   => 'nullable|string|max:100',
            'batch_number'    => 'nullable|string|max:60',
            'administered_at' => 'required|date|before_or_equal:today',
            'next_due_at'     => 'nullable|date|after:administered_at',
            'administered_by' => 'nullable|string|max:100',
            'dose_ml'         => 'nullable|numeric|min:0',
            'route'           => 'nullable|in:subcutaneous,intramuscular,oral,other',
            'notes'           => 'nullable|string|max:500',
        ]);

        $validated['sheep_id'] = $sheep->id;
        $vaccination = Vaccination::create($validated);
        $vaccination->refreshStatus();

        return response()->json([
            'message' => 'Vaccination ajoutée.',
            'data'    => $vaccination,
        ], 201);
    }
}
