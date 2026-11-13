<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Story;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StoryController extends Controller
{
    public function index()
    {
        $stories = Story::where('expires_at', '>', now())
            ->with('user')
            ->latest()
            ->get();
            
        return view('front.stories.index', compact('stories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'media' => 'required|file|mimes:jpeg,png,jpg,mp4,webm|max:20480',
            'type'  => 'required|in:image,video',
        ]);

        $path = $request->file('media')->store('stories', 'public');

        Story::create([
            'user_id'    => auth()->id(),
            'media_path' => $path,
            'type'       => $request->type,
            'expires_at' => now()->addHours(24),
        ]);

        return back()->with('success', __('Story uploaded successfully.'));
    }
}