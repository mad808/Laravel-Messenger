<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\BlockedUser;
use App\Models\Conversation;
use App\Models\Gift;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ConversationController extends Controller
{
    public function index()
    {
        $conversations = auth()->user()->conversations()->latest()->get();
        return view('front.conversations.index', compact('conversations'));
    }

    public function show(Conversation $conversation)
    {
        abort_unless(
            $conversation->participants()->where('users.id', auth()->id())->exists(),
            403
        );

        $conversation->load([
            'participants',
            'messages' => fn ($q) => $q->latest()->limit(100),
            'messages.user',
            'messages.attachments',
            'messages.replyTo.user',
            'messages.reactions.user:id,name',
            'messages.giftTransaction.gift',
        ]);

        $gifts = Gift::where('is_active', true)
            ->orderBy('star_cost')
            ->orderBy('id')
            ->get();

        $maxUploadMb = auth()->user()->activeSubscription?->plan?->max_file_size_mb ?? 15;

        return view('front.conversations.show', compact('conversation', 'gifts', 'maxUploadMb'));
    }

    public function store(Request $request)
    {
        $myId = (int) auth()->id();

        $request->validate([
            'user_id' => ['required', 'exists:users,id', Rule::notIn([$myId])],
        ]);

        $otherId = (int) $request->user_id;

        $isBlocked = BlockedUser::where(function ($q) use ($myId, $otherId) {
            $q->where('blocker_id', $myId)->where('blocked_id', $otherId);
        })->orWhere(function ($q) use ($myId, $otherId) {
            $q->where('blocker_id', $otherId)->where('blocked_id', $myId);
        })->exists();

        if ($isBlocked) {
            return back()->with('error', __('You cannot start a chat with this user.'));
        }

        $existingConversation = Conversation::where('type', 'private')
            ->whereHas('participants', fn ($q) => $q->where('user_id', $myId))
            ->whereHas('participants', fn ($q) => $q->where('user_id', $otherId))
            ->first();

        if ($existingConversation) {
            return redirect()->route('conversations.show', $existingConversation->id);
        }

        $conversation = Conversation::create([
            'type' => 'private',
            'owner_id' => $myId,
        ]);

        $conversation->participants()->attach([
            $myId => ['role' => 'owner', 'joined_at' => now()],
            $otherId => ['role' => 'member', 'joined_at' => now()],
        ]);

        return redirect()->route('conversations.show', $conversation->id);
    }
}