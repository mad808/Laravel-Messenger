<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'price',
        'duration_days',
        'features',
        'accepts_crypto',
        'bep20_wallet_address',
        'max_file_size_mb',
        'max_story_duration_sec',
        'can_hide_profile',
        'can_block_screenshots',
        'has_anonymous_mode',
        'can_set_profile_music',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'duration_days' => 'integer',
        'features' => 'array',
        'accepts_crypto' => 'boolean',
        'max_file_size_mb' => 'integer',
        'max_story_duration_sec' => 'integer',
        'can_hide_profile' => 'boolean',
        'can_block_screenshots' => 'boolean',
        'has_anonymous_mode' => 'boolean',
        'can_set_profile_music' => 'boolean',
    ];
}