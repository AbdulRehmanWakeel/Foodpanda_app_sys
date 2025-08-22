<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'restaurant_id',
        'user_id',
        'rider_id',
        'total_price',
        'status',
        'rider_fee',
    ];
    
    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

     
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
    public function rider()
    {
        return $this->belongsTo(User::class, 'rider_id');
    }
}


