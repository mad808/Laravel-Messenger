<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Story;
use App\Models\StoryView;
use Illuminate\Http\Request;

class StoryController extends Controller
{
    public function index()
    {
        $stories = Story::where('expires_at', '>', now())
            ->with('user:id,name,avatar')
            ->latest()
            ->get()
            ->groupBy('user_id');

        return response()->json($stories);
    }

    public function store(Request $request)
    {
        $request->validate([
            'media' => 'required|file|mimes:jpeg,png,jpg,mp4|max:20480',
            'type' => 'required|in:image,video',
        ]);

        $path = $request->file('media')->store('stories', 'public');

        $story = Story::create([
            'user_id' => auth()->id(),
            'media_path' => $path,
            'type' => $request->type,
            'expires_at' => now()->addHours(24),
        ]);

        return response()->json($story, 201);
    }

    public function view(Story $story)
    {
        if ($story->user_id !== auth()->id()) {
            StoryView::firstOrCreate([
                'story_id' => $story->id,
                'user_id' => auth()->id(),
            ], ['viewed_at' => now()]);
        }

        return response()->json(['message' => 'Story viewed.']);
    }
}