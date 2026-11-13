<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OtpCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'phone',
        'code_hash',
        'type',
        'request_ip',
        'attempts',
        'expires_at',
        'used_at',
        'gateway_message_id',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    public function gatewayMessage()
    {
        return $this->belongsTo(OtpGatewayMessage::class, 'gateway_message_id');
    }
}