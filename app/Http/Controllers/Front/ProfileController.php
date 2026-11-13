<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function index()
    {
        return view('front.profile.index');
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'name' => 'required|string|max:50',
            'username' => 'nullable|string|max:30|unique:users,username,' . $user->id,
            'avatar' => 'nullable|image|max:2048',
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $path;
        }

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->name = $request->name;
        $user->username = $request->username;
        $user->save();

        return back()->with('success', __('Profile updated successfully.'));
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

        return back()->with('success', __('Privacy settings updated.'));
    }
}