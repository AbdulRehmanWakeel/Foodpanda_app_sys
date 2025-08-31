<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ErrorLog extends Model
{
    protected $table = 'error_logs';

    protected $fillable = [
        'message',
        'trace',
        'file',
        'line',
        'url',
        'method',
        'request_data'
    ];

    protected $casts = [
        'request_data' => 'array',
    ];
}
