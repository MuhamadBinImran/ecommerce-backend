<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'seller_id',
        'order_number',
        'subtotal',
        'shipping',
        'tax',
        'total',
        'status',
        'shipping_address',
        'meta',
    ];

    protected $casts = [
        'shipping_address' => 'array',
        'meta' => 'array',
        'subtotal' => 'decimal:2',
        'shipping' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    // relations
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function seller()
    {
        return $this->belongsTo(\App\Models\Seller::class);
    }

    public function scopeForSeller($q, int $sellerId)
    {
        return $q->whereHas('items', fn($iq) => $iq->where('seller_id', $sellerId));
    }

}
