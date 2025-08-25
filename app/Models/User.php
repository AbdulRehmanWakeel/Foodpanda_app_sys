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


}
