<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    protected $fillable = ['cart_id', 'menu_id', 'quantity', 'price', 'addons'];

    protected $casts = [
        'addons' => 'array',
    ];

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    public function menu()
    {
        return $this->belongsTo(Menu::class, 'menu_id');
    }
}
