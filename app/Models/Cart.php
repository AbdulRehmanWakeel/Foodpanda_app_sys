<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $fillable = ['user_id', 'restaurant_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function items()
    {
        return $this->hasMany(CartItem::class);
    }

    public function totalPrice()
    {
        return $this->items->sum(fn($item) => $item->quantity * $item->price);
    }
}
