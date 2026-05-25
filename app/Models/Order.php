<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_number', 'user_id', 'client_name', 'client_email', 'client_phone',
        'client_city', 'client_address', 'status', 'delivery_method',
        'delivery_address', 'delivery_city', 'delivery_fee',
        'desired_delivery_date', 'actual_delivery_date',
        'subtotal', 'total_amount', 'deposit_amount', 'remaining_amount',
        'payment_method', 'payment_status', 'transaction_id',
        'client_notes', 'admin_notes',
    ];

    protected $casts = [
        'desired_delivery_date' => 'date',
        'actual_delivery_date'  => 'date',
        'subtotal'              => 'decimal:2',
        'total_amount'          => 'decimal:2',
        'deposit_amount'        => 'decimal:2',
        'remaining_amount'      => 'decimal:2',
        'delivery_fee'          => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            if (empty($order->order_number)) {
                $order->order_number = static::generateOrderNumber();
            }
            $order->remaining_amount = $order->total_amount - $order->deposit_amount;
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public static function generateOrderNumber(): string
    {
        $year    = now()->year;
        $count   = static::withTrashed()->whereYear('created_at', $year)->count();
        $counter = str_pad($count + 1, 5, '0', STR_PAD_LEFT);
        return "CMD-{$year}-{$counter}";
    }

    public function confirm(): void
    {
        $this->update(['status' => 'confirmed']);
        foreach ($this->items as $item) {
            $item->sheep?->reserve();
        }
    }

    public function cancel(): void
    {
        $this->update(['status' => 'cancelled']);
        foreach ($this->items as $item) {
            if ($item->sheep?->status === 'reserved') {
                $item->sheep->update(['status' => 'available']);
            }
        }
    }

    public function markAsDelivered(): void
    {
        $this->update(['status' => 'delivered', 'actual_delivery_date' => now()->toDateString()]);
        foreach ($this->items as $item) {
            $item->sheep?->markAsSold();
        }
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending'   => 'En attente',
            'confirmed' => 'Confirmée',
            'paid'      => 'Payée',
            'ready'     => 'Prête',
            'delivered' => 'Livrée',
            'cancelled' => 'Annulée',
            'refunded'  => 'Remboursée',
            default     => $this->status,
        };
    }

    public function getPaymentStatusLabelAttribute(): string
    {
        return match ($this->payment_status) {
            'unpaid'  => 'Non payée',
            'partial' => 'Acompte versé',
            'paid'    => 'Payée',
            default   => $this->payment_status,
        };
    }

    public function getDeliveryMethodLabelAttribute(): string
    {
        return $this->delivery_method === 'delivery' ? 'Livraison à domicile' : 'Retrait à l\'élevage';
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        return match ($this->payment_method) {
            'orange_money'  => 'Orange Money',
            'wave'          => 'Wave',
            'moov_money'    => 'Moov Money',
            'bank_transfer' => 'Virement bancaire',
            'cash'          => 'Espèces',
            default         => '—',
        };
    }
}
