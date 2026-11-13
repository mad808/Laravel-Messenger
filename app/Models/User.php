<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'username',
        'phone',
        'email',
        'password',
        'avatar',
        'role',
        'plan',
        'is_blocked',
        'block_reason',
        'last_seen_at',
        'name_ru',
        'name_tk',
        'ai_enabled',
        'notifications_enabled',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_seen_at' => 'datetime',
            'password' => 'hashed',
            'is_blocked' => 'boolean',
        ];
    }

    public function privacySetting()
    {
        return $this->hasOne(UserPrivacySetting::class);
    }

    public function conversations()
    {
        return $this->belongsToMany(Conversation::class, 'conversation_participants')
                    ->withPivot('role', 'joined_at', 'left_at')
                    ->withTimestamps();
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function stories()
    {
        return $this->hasMany(Story::class);
    }

    public function blockedUsers()
    {
        return $this->hasMany(BlockedUser::class, 'blocker_id');
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function activeSubscription()
    {
        return $this->hasOne(Subscription::class)->where('status', 'active')->latestOfMany();
    }

    public function cryptoPayments()
    {
        return $this->hasMany(CryptoPayment::class);
    }

    public function savedMessages()
    {
        return $this->belongsToMany(Message::class, 'saved_messages')->withTimestamps();
    }

    public function stars()
    {
        return $this->hasOne(UserStar::class);
    }

    public function notifications()
    {
        return $this->morphMany(\Illuminate\Notifications\DatabaseNotification::class, 'notifiable')->latest();
    }

        public function contacts()
    {
        return $this->hasMany(Contact::class);
    }

    public function contactOf()
    {
        return $this->hasMany(Contact::class, 'contact_user_id');
    }
}