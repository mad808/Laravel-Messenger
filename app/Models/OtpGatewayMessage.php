<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OtpGatewayMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'phone',
        'message',
        'status',
        'retry_count',
        'error_message',
        'locked_at',
        'sent_at',
    ];

    protected $casts = [
        'locked_at' => 'datetime',
        'sent_at' => 'datetime',
    ];
}