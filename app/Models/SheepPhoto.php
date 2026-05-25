<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class SheepPhoto extends Model
{
    protected $table = 'sheep_photos';

    protected $fillable = [
        'sheep_id',
        'path',
        'url',
        'caption',
        'is_primary',
        'sort_order',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function sheep(): BelongsTo
    {
        return $this->belongsTo(Sheep::class);
    }

    public function getUrlAttribute(): string
    {
        return '/storage/' . $this->path;
    }

    /**
     * Définir cette photo comme photo principale
     * (désactive les autres du même mouton)
     */
    public function setAsPrimary(): void
    {
        // Retirer le statut primaire des autres photos
        static::where('sheep_id', $this->sheep_id)
            ->where('id', '!=', $this->id)
            ->update(['is_primary' => false]);

        $this->update(['is_primary' => true]);
    }

    /**
     * Supprimer aussi le fichier physique
     */
    protected static function booted(): void
    {
        static::deleting(function (SheepPhoto $photo) {
            Storage::disk('public')->delete($photo->path);
        });
    }
}


