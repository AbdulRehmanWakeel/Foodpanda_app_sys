<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Restaurant extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'email',
        'phone',
        'address',
        'cuisine_type',
        'delivery_radius',
        'opening_time',
        'closing_time',
        'approval_status',    
        'commission_rate',    
        'is_verified',        
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function menus()
    {
        return $this->hasMany(Menu::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    /**
     * 🔹 Restaurant-specific filters
     */
    public static function getFilterMap(): array
    {
        return [
            'cuisine'        => \App\Filters\Customer\RestaurantCuisineFilter::class,
            'delivery_time'  => \App\Filters\Customer\DeliveryTimeFilter::class,    
            'price_range'    => \App\Filters\Customer\PriceRangeFilter::class,      
            'rating'         => \App\Filters\Customer\RatingFilter::class,          
            'q'              => \App\Filters\Common\SearchFilter::class,             
            'location'       => \App\Filters\Customer\LocationFilter::class,        
        ];
    }
}
