<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Breed;
use Illuminate\Http\JsonResponse;

class BreedController extends Controller
{
    public function index(): JsonResponse
    {
        $breeds = Breed::active()
            ->withCount(['sheep as available_count' => fn($q) => $q->where('status', 'available')])
            ->orderBy('name')
            ->get();

        return response()->json(['data' => $breeds]);
    }
}
