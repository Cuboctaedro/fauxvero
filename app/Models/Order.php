<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'number',
    'status',
    'name',
    'email',
    'phone',
    'address',
    'city',
    'postal_code',
    'country',
    'notes',
    'subtotal',
    'total',
    'currency',
    'locale',
])]
class Order extends Model
{
    use HasFactory;

    protected $attributes = [
        'status' => 'pending',
    ];

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'subtotal' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        // The number derives from the id, so it can only be set once the row exists.
        static::created(function (Order $order) {
            $order->updateQuietly(['number' => 'FV-'.str_pad((string) $order->id, 6, '0', STR_PAD_LEFT)]);
        });
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
