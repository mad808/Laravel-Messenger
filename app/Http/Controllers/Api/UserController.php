<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function profile()
    {
        return response()->json(
            auth()->user()->load('privacySetting', 'activeSubscription.plan')
        );
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'name' => 'sometimes|string|max:50',
            'username' => 'sometimes|string|max:30|unique:users,username,' . $user->id,
            'avatar' => 'sometimes|image|max:2048',
            'password' => 'sometimes|string|min:6|confirmed',
        ]);

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $path;
        }

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->fill($request->only('name', 'username'))->save();

        return response()->json(['message' => 'Profile updated.', 'user' => $user]);
    }

    public function updatePrivacy(Request $request)
    {
        $settings = auth()->user()->privacySetting()->firstOrCreate();
        
        $settings->update($request->only([
            'hide_profile_photo', 'hide_last_seen', 'hide_online_status',
            'block_screenshots', 'hide_read_receipts', 'anonymous_mode',
            'profile_music_url', 'show_profile_music', 'hide_phone_number',
            'hide_stories_from_non_contacts'
        ]));

        return response()->json(['message' => 'Privacy updated.', 'settings' => $settings]);
    }

    public function getStarBalance()
    {
        $stars = auth()->user()->stars ?? \App\Models\UserStar::create(['user_id' => auth()->id(), 'balance' => 0]);
        return response()->json(['balance' => $stars->balance]);
    }
}