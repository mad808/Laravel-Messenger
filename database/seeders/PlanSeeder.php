<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Plan;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        Plan::create([
            'name' => 'Free',
            'slug' => 'free',
            'price' => 0,
            'duration_days' => 0,
            'features' => ['Basic messaging', 'Max 15MB files'],
            'accepts_crypto' => false,
            'bep20_wallet_address' => null,
            'max_file_size_mb' => 15,
            'max_story_duration_sec' => 30,
            'can_hide_profile' => false,
            'can_block_screenshots' => false,
            'has_anonymous_mode' => false,
            'can_set_profile_music' => false,
        ]);

        Plan::create([
            'name' => 'Plus',
            'slug' => 'plus',
            'price' => 5.00,
            'duration_days' => 30,
            'features' => ['Max 100MB files', 'Profile Music', 'Hide Profile Photo'],
            'accepts_crypto' => true,
            'bep20_wallet_address' => '0x1234567890abcdef1234567890abcdef12345678',
            'max_file_size_mb' => 100,
            'max_story_duration_sec' => 60,
            'can_hide_profile' => true,
            'can_block_screenshots' => false,
            'has_anonymous_mode' => false,
            'can_set_profile_music' => true,
        ]);

        Plan::create([
            'name' => 'Pro',
            'slug' => 'pro',
            'price' => 10.00,
            'duration_days' => 30,
            'features' => ['Max 2GB files', 'Anonymous Mode', 'Block Screenshots', 'Everything in Plus'],
            'accepts_crypto' => true,
            'bep20_wallet_address' => '0x1234567890abcdef1234567890abcdef12345678',
            'max_file_size_mb' => 2048,
            'max_story_duration_sec' => 120,
            'can_hide_profile' => true,
            'can_block_screenshots' => true,
            'has_anonymous_mode' => true,
            'can_set_profile_music' => true,
        ]);
    }
}