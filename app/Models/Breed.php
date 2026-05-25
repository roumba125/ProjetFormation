<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Breed extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'origin',
        'description',
        'image',
        'avg_adult_weight_male',
        'avg_adult_weight_female',
        'is_active',
    ];

    protected $casts = [
        'is_active'               => 'boolean',
        'avg_adult_weight_male'   => 'decimal:2',
        'avg_adult_weight_female' => 'decimal:2',
    ];

    // -------------------------------------------------------
    // Relations
    // -------------------------------------------------------

    public function sheep(): HasMany
    {
        return $this->hasMany(Sheep::class);
    }

    public function availableSheep(): HasMany
    {
        return $this->hasMany(Sheep::class)->where('status', 'available')->where('is_active', true);
    }

    // -------------------------------------------------------
    // Scopes
    // -------------------------------------------------------

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // -------------------------------------------------------
    // Accessors
    // -------------------------------------------------------

    public function getAvailableCountAttribute(): int
    {
        return $this->availableSheep()->count();
    }
}
