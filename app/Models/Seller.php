<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Seller extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'company_name',
        'phone',
        'address',
        'city',
        'state',
        'postal_code',
        'country',
        'is_approved',
        'is_blocked',
    ];

    /**
     * Relationship: Seller belongs to a User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function products()
    {
        return $this->hasMany(\App\Models\Product::class);
    }

}
