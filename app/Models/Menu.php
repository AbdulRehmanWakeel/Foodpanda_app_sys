<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Menu extends Model
{
    use HasFactory;

    protected $fillable = [
        'restaurant_id', 'name', 'price', 'description',
        'image', 'category', 'is_available'
    ];

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'menu_id');
    }
}
