<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Story;
use Illuminate\Support\Facades\Storage;

class StoryController extends Controller
{
    public function index()
    {
        $stories = Story::with('user')->latest()->paginate(20);
        return view('admin.stories.index', compact('stories'));
    }

    public function destroy(Story $story)
    {
        if ($story->media_path) {
            Storage::disk('public')->delete($story->media_path);
        }

        $story->delete();

        return back()->with('success', 'Story deleted.');
    }
}