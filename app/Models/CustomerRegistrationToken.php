<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerRegistrationToken extends Model
{
    protected $fillable = [
        'email', 'name', 'password_hash', 'payload',
        'otp_hash', 'expires_at', 'attempts', 'last_sent_at', 'used_at',
    ];

    protected $casts = [
        'payload'     => 'array',
        'expires_at'  => 'datetime',
        'last_sent_at'=> 'datetime',
        'used_at'     => 'datetime',
    ];
}
