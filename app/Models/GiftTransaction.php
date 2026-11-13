<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GiftTransaction extends Model
{
    protected $fillable = [
        'sender_id',
        'receiver_id',
        'conversation_id',
        'message_id',
        'gift_id',
        'stars_spent',
    ];

    public function gift()
    {
        return $this->belongsTo(Gift::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
}