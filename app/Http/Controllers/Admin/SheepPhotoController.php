<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sheep;
use App\Models\SheepPhoto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SheepPhotoController extends Controller
{
    public function store(Request $request, Sheep $sheep): JsonResponse
    {
        $request->validate([
            'photos'   => 'required|array|min:1|max:8',
            'photos.*' => 'image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        $uploaded   = [];
        $hasPrimary = $sheep->photos()->where('is_primary', true)->exists();

        foreach ($request->file('photos') as $index => $file) {
            $isPrimary = !$hasPrimary && $index === 0;
            $filename  = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $path      = "sheep/{$sheep->id}/{$filename}";

            Storage::disk('public')->put($path, file_get_contents($file));

            $photo = SheepPhoto::create([
                'sheep_id'   => $sheep->id,
                'path'       => $path,
                'url'        => Storage::disk('public')->url($path),
                'caption'    => $request->captions[$index] ?? null,
                'is_primary' => $isPrimary,
                'sort_order' => $sheep->photos()->count() + $index,
            ]);

            $uploaded[] = $photo;
        }

        return response()->json([
            'message' => count($uploaded) . ' photo(s) uploadée(s).',
            'data'    => $uploaded,
        ], 201);
    }

    public function destroy(Sheep $sheep, SheepPhoto $photo): JsonResponse
    {
        abort_unless($photo->sheep_id === $sheep->id, 403);
        $wasPrimary = $photo->is_primary;
        $photo->delete();

        if ($wasPrimary) {
            $sheep->photos()->first()?->setAsPrimary();
        }

        return response()->json(['message' => 'Photo supprimée.']);
    }
}
