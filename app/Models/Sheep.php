<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sheep extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'sheep';

    protected $fillable = [
        'name',
        'reference',
        'breed_id',
        'gender',
        'birth_date',
        'ear_tag',
        'current_weight',
        'height',
        'coat_color',
        'physical_description',
        'health_condition',
        'last_vet_checkup',
        'notes',
        'current_diet',
        'price',
        'negotiable_price',
        'status',
        'is_featured',
        'is_active',
        'mother_id',
        'father_id',
    ];

    protected $casts = [
        'birth_date'       => 'date',
        'last_vet_checkup' => 'date',
        'current_weight'   => 'decimal:2',
        'height'           => 'decimal:2',
        'price'            => 'decimal:2',
        'negotiable_price' => 'decimal:2',
        'is_featured'      => 'boolean',
        'is_active'        => 'boolean',
    ];

    // -------------------------------------------------------
    // Relations
    // -------------------------------------------------------

    public function breed(): BelongsTo
    {
        return $this->belongsTo(Breed::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(SheepPhoto::class)->orderBy('sort_order');
    }

    public function primaryPhoto(): HasOne
    {
        return $this->hasOne(SheepPhoto::class)->where('is_primary', true);
    }

    public function vaccinations(): HasMany
    {
        return $this->hasMany(Vaccination::class)->orderByDesc('administered_at');
    }

    public function weightRecords(): HasMany
    {
        return $this->hasMany(WeightRecord::class)->orderBy('recorded_at');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function mother(): BelongsTo
    {
        return $this->belongsTo(Sheep::class, 'mother_id');
    }

    public function father(): BelongsTo
    {
        return $this->belongsTo(Sheep::class, 'father_id');
    }

    // -------------------------------------------------------
    // Scopes
    // -------------------------------------------------------

    public function scopeAvailable($query)
    {
        return $query->where('status', 'available')->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true)->available();
    }

    public function scopeByGender($query, string $gender)
    {
        return $query->where('gender', $gender);
    }

    public function scopeByBreed($query, int $breedId)
    {
        return $query->where('breed_id', $breedId);
    }

    public function scopePriceBetween($query, float $min, float $max)
    {
        return $query->whereBetween('price', [$min, $max]);
    }

    public function scopeWeightBetween($query, float $min, float $max)
    {
        return $query->whereBetween('current_weight', [$min, $max]);
    }

    // -------------------------------------------------------
    // Accessors (calculés automatiquement)
    // -------------------------------------------------------

    /**
     * Âge en mois
     */
    public function getAgeInMonthsAttribute(): int
    {
        return (int) $this->birth_date->diffInMonths(now());
    }

    /**
     * Âge formaté : "18 mois" ou "2 ans 3 mois"
     */
    public function getAgeFormattedAttribute(): string
    {
        $months = $this->age_in_months;
        if ($months < 12) {
            return "{$months} mois";
        }
        $years  = intdiv($months, 12);
        $remain = $months % 12;
        return $remain > 0 ? "{$years} an(s) {$remain} mois" : "{$years} an(s)";
    }

    /**
     * URL de la photo principale
     */
    public function getPrimaryPhotoUrlAttribute(): ?string
    {
        $photo = $this->photos->where('is_primary', true)->first()
            ?? $this->photos->first();
        return $photo?->url;
    }

    /**
     * Libellé genre en français
     */
    public function getGenderLabelAttribute(): string
    {
        return $this->gender === 'male' ? 'Bélier' : 'Brebis';
    }

    /**
     * Libellé condition sanitaire
     */
    public function getHealthLabelAttribute(): string
    {
        return match ($this->health_condition) {
            'excellent' => 'Excellent',
            'good'      => 'Bon',
            'fair'      => 'Passable',
            'poor'      => 'Mauvais',
            default     => '—',
        };
    }

    /**
     * Libellé statut
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'available' => 'Disponible',
            'reserved'  => 'Réservé',
            'sold'      => 'Vendu',
            default     => $this->status,
        };
    }

    /**
     * Dernier GMQ (Gain Moyen Quotidien) enregistré
     */
    public function getLastDailyGainAttribute(): ?float
    {
        return $this->weightRecords()->latest('recorded_at')->value('daily_gain');
    }

    /**
     * Vaccins valides
     */
    public function getValidVaccinationsAttribute()
    {
        return $this->vaccinations->where('status', 'valid');
    }

    // -------------------------------------------------------
    // Méthodes
    // -------------------------------------------------------

    /**
     * Générer une référence unique automatiquement
     */
    public static function generateReference(string $prefix = 'BP'): string
    {
        $year    = now()->year;
        $last    = static::withTrashed()
            ->where('reference', 'like', "{$prefix}-{$year}-%")
            ->count();
        $counter = str_pad($last + 1, 4, '0', STR_PAD_LEFT);

        return "{$prefix}-{$year}-{$counter}";
    }

    /**
     * Marquer comme réservé
     */
    public function reserve(): bool
    {
        return $this->update(['status' => 'reserved']);
    }

    /**
     * Marquer comme vendu
     */
    public function markAsSold(): bool
    {
        return $this->update(['status' => 'sold']);
    }
}
