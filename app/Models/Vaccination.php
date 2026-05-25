<?php


namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vaccination extends Model
{
    protected $fillable = [
        'sheep_id',
        'vaccine_name',
        'vaccine_brand',
        'batch_number',
        'administered_at',
        'next_due_at',
        'administered_by',
        'dose_ml',
        'route',
        'notes',
        'status',
    ];

    protected $casts = [
        'administered_at' => 'date',
        'next_due_at'     => 'date',
        'dose_ml'         => 'decimal:2',
    ];

    public function sheep(): BelongsTo
    {
        return $this->belongsTo(Sheep::class);
    }

    public function getRouteLabelAttribute(): string
    {
        return match ($this->route) {
            'subcutaneous'  => 'Sous-cutanée',
            'intramuscular' => 'Intramusculaire',
            'oral'          => 'Orale',
            default         => 'Autre',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'valid'    => 'Valide',
            'expired'  => 'Expiré',
            'due_soon' => 'Rappel proche',
            default    => $this->status,
        };
    }

    /**
     * Recalculer le statut selon la date de rappel
     */
    public function refreshStatus(): void
    {
        if (!$this->next_due_at) {
            $this->update(['status' => 'valid']);
            return;
        }

        $daysLeft = now()->diffInDays($this->next_due_at, false);

        $status = match (true) {
            $daysLeft < 0    => 'expired',
            $daysLeft <= 30  => 'due_soon',
            default          => 'valid',
        };

        $this->update(['status' => $status]);
    }
}

