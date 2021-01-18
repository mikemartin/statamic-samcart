<?php

namespace Mikemartin\Samcart\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'title', 'product', 'customer', 'order', 'updated_at', 'created_at',
    ];

    protected $casts = [
        'product' => 'json',
        'customer' => 'json',
        'order' => 'json',
    ];
}
