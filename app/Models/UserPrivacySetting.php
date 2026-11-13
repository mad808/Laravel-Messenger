<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPrivacySetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'hide_profile_photo',
        'hide_last_seen',
        'hide_online_status',
        'block_screenshots',
        'hide_read_receipts',
        'anonymous_mode',
        'profile_music_url',
        'show_profile_music',
        'hide_phone_number',
        'hide_stories_from_non_contacts',
    ];

    protected $casts = [
        'hide_profile_photo' => 'boolean',
        'hide_last_seen' => 'boolean',
        'hide_online_status' => 'boolean',
        'block_screenshots' => 'boolean',
        'hide_read_receipts' => 'boolean',
        'anonymous_mode' => 'boolean',
        'show_profile_music' => 'boolean',
        'hide_phone_number' => 'boolean',
        'hide_stories_from_non_contacts' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}