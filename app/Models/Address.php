<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'label',
        'address_line',
        'city',
        'lat',
        'lng',
    ];

    /**
     * Relationship: Address belongs to a User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
