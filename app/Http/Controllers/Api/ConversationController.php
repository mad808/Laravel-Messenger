<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConversationController extends Controller
{
    public function index()
    {
        $conversations = auth()->user()->conversations()
            ->with(['messages' => function($query) {
                $query->latest()->limit(1); // Sadece son mesajı göster
            }])
            ->latest('updated_at')
            ->get();

        return response()->json($conversations);
    }

    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:private,group,channel',
            'title' => 'required_if:type,group,channel|string|max:255',
            'user_ids' => 'required_if:type,private,group|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        return DB::transaction(function () use ($request) {
            if ($request->type === 'private') {
                if (count($request->user_ids) !== 1) {
                    return response()->json(['message' => 'Private chat needs exactly 1 other user.'], 422);
                }

                $existing = Conversation::where('type', 'private')
                    ->whereHas('participants', function($q) use ($request) {
                        $q->whereIn('user_id', array_merge($request->user_ids, [auth()->id()]));
                    }, '=', 2)
                    ->first();

                if ($existing) {
                    return response()->json($existing->load('participants'));
                }
            }

            $conversation = Conversation::create([
                'type' => $request->type,
                'title' => $request->title,
                'owner_id' => auth()->id(),
            ]);

            $participants = array_merge($request->user_ids ?? [], [auth()->id()]);
            foreach ($participants as $userId) {
                ConversationParticipant::create([
                    'conversation_id' => $conversation->id,
                    'user_id' => $userId,
                    'role' => $userId === auth()->id() ? 'owner' : 'member',
                    'joined_at' => now(),
                ]);
            }

            return response()->json($conversation->load('participants'), 201);
        });
    }

    public function show(Conversation $conversation)
    {
        if (!$conversation->participants->contains(auth()->id())) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($conversation->load('participants', 'owner'));
    }
}