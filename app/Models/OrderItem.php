<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id', 'sheep_id', 'sheep_name', 'sheep_reference',
        'breed_name', 'weight_at_order', 'unit_price',
    ];

    protected $casts = [
        'weight_at_order' => 'decimal:2',
        'unit_price'      => 'decimal:2',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function sheep(): BelongsTo
    {
        return $this->belongsTo(Sheep::class);
    }
}
