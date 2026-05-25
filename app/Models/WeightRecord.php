<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeightRecord extends Model
{
    protected $fillable = [
        'sheep_id',
        'weight',
        'recorded_at',
        'diet',
        'daily_gain',
        'measured_by',
        'notes',
    ];

    protected $casts = [
        'recorded_at' => 'date',
        'weight'      => 'decimal:2',
        'daily_gain'  => 'decimal:2',
    ];

    public function sheep(): BelongsTo
    {
        return $this->belongsTo(Sheep::class);
    }

    /**
     * Calcul automatique du GMQ lors de la sauvegarde
     */
    protected static function booted(): void
    {
        static::creating(function (WeightRecord $record) {
            // Récupérer le dernier enregistrement
            $previous = static::where('sheep_id', $record->sheep_id)
                ->where('recorded_at', '<', $record->recorded_at)
                ->orderByDesc('recorded_at')
                ->first();

            if ($previous) {
                $days = $previous->recorded_at->diffInDays($record->recorded_at);
                if ($days > 0) {
                    $gainKg          = $record->weight - $previous->weight;
                    $gainGrams       = $gainKg * 1000;
                    $record->daily_gain = round($gainGrams / $days, 2);
                }
            }
        });

        // Mettre à jour le poids courant du mouton
        static::created(function (WeightRecord $record) {
            $record->sheep->update(['current_weight' => $record->weight]);
        });
    }
}
