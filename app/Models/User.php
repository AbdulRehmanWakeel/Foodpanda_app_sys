<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, HasRoles;

    /**
     * Fillable fields
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'is_online',
        'vehicle_type',
        'rider_license',
        'verification_status', // ✅ added
        'verified_at',         // ✅ added
    ];

    /**
     * Hidden fields
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Casts
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_online' => 'boolean',
        'verified_at' => 'datetime', // ✅ added
    ];

    /**
     * JWT Identifier
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * JWT Custom Claims
     */
    public function getJWTCustomClaims()
    {
        return [
            'role' => $this->getRoleNames(),
        ];
    }

    /**
     * Orders assigned to rider
     */
    public function orders()
    {
        return $this->hasMany(Order::class, 'rider_id');
    }

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public static function getFilterMap(): array
    {
        return [
            'status' => \App\Filters\Admin\UserStatusFilter::class,
            'restaurant_approval' => \App\Filters\Admin\RestaurantApprovalFilter::class,
            'rider_verification' => \App\Filters\Admin\RiderVerificationFilter::class,
            'commission_range' => \App\Filters\Admin\CommissionRangeFilter::class,
        ];
    }
}
