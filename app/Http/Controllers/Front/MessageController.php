<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\BlockedUser;
use App\Models\Conversation;
use App\Models\Gift;
use App\Models\GiftTransaction;
use App\Models\Message;
use App\Models\MessageAttachment;
use App\Models\MessageReaction;
use App\Models\UserStar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class MessageController extends Controller
{
    public function store(Request $request, Conversation $conversation)
    {
        $meId = (int) auth()->id();

        abort_unless(
            $conversation->participants()->where('users.id', $meId)->exists(),
            403
        );

        if ($conversation->type === 'private') {
            $otherId = $conversation->participants()->where('users.id', '!=', $meId)->value('users.id');
            if ($otherId && $this->isBlockedBetween($meId, (int) $otherId)) {
                return back()->with('error', __('You cannot send messages to this user.'));
            }
        }

        $maxMb = auth()->user()->activeSubscription?->plan?->max_file_size_mb ?? 15;

        $request->validate([
            'body' => 'nullable|string|max:5000',
            'file' => 'nullable|file|max:' . ($maxMb * 1024),
            'reply_to_id' => [
                'nullable',
                Rule::exists('messages', 'id')->where('conversation_id', $conversation->id),
            ],
        ]);

        if (!filled($request->body) && !$request->hasFile('file')) {
            return back()->with('error', __('Please write a message or attach a file.'));
        }

        $type = 'text';
        if ($request->hasFile('file')) {
            $mime = (string) $request->file('file')->getMimeType();
            if (str_contains($mime, 'image')) $type = 'photo';
            elseif (str_contains($mime, 'video')) $type = 'video';
            elseif (str_contains($mime, 'audio')) $type = 'audio';
            else $type = 'file';
        }

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'user_id' => $meId,
            'body' => $request->body,
            'type' => $type,
            'reply_to_id' => $request->reply_to_id,
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $path = $file->store('attachments', 'public');

            MessageAttachment::create([
                'message_id' => $message->id,
                'file_path' => $path,
                'file_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
            ]);
        }

        $conversation->touch();

        return back();
    }

    public function react(Request $request, Message $message)
    {
        $meId = (int) auth()->id();

        abort_unless(
            $message->conversation->participants()->where('users.id', $meId)->exists(),
            403
        );

        if ($message->is_deleted) {
            return response()->json(['error' => 'Cannot react to deleted message.'], 422);
        }

        $allowed = ['👍', '❤️', '🔥', '😂', '😮', '😢', '🎉', '🙏', '👏', '🥰', '💯', '🤩'];

        $request->validate([
            'emoji' => ['required', 'string', 'max:16', Rule::in($allowed)],
        ]);

        $existing = MessageReaction::where('message_id', $message->id)
            ->where('user_id', $meId)
            ->where('emoji', $request->emoji)
            ->first();

        if ($existing) {
            $existing->delete();
        } else {
            MessageReaction::create([
                'message_id' => $message->id,
                'user_id' => $meId,
                'emoji' => $request->emoji,
            ]);
        }

        $grouped = MessageReaction::where('message_id', $message->id)
            ->get()
            ->groupBy('emoji')
            ->map(fn ($items, $emoji) => [
                'emoji' => $emoji,
                'count' => $items->count(),
                'mine' => $items->contains('user_id', $meId),
            ])
            ->values();

        return response()->json(['reactions' => $grouped]);
    }

    public function sendGift(Request $request, Conversation $conversation)
    {
        $meId = (int) auth()->id();

        abort_unless(
            $conversation->participants()->where('users.id', $meId)->exists(),
            403
        );

        $request->validate([
            'gift_id' => ['required', 'exists:gifts,id'],
        ]);

        $gift = Gift::where('id', $request->gift_id)->where('is_active', true)->firstOrFail();

        $receiverId = $conversation->type === 'private'
            ? $conversation->participants()->where('users.id', '!=', $meId)->value('users.id')
            : null;

        if ($receiverId && $this->isBlockedBetween($meId, (int) $receiverId)) {
            return back()->with('error', __('You cannot send gifts to this user.'));
        }

        $error = DB::transaction(function () use ($gift, $meId, $receiverId, $conversation) {
            if ($gift->star_cost > 0) {
                $wallet = UserStar::firstOrCreate(['user_id' => $meId], ['balance' => 0]);
                $locked = UserStar::whereKey($wallet->id)->lockForUpdate()->first();

                if ($locked->balance < $gift->star_cost) {
                    return __('Not enough stars.');
                }

                $locked->decrement('balance', $gift->star_cost);

                DB::table('star_transactions')->insert([
                    'user_id' => $meId,
                    'amount' => -$gift->star_cost,
                    'reason' => 'Gift: ' . $gift->name,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                if ($receiverId) {
                    $rxWallet = UserStar::firstOrCreate(['user_id' => $receiverId], ['balance' => 0]);
                    $rxWallet->increment('balance', $gift->star_cost);

                    DB::table('star_transactions')->insert([
                        'user_id' => $receiverId,
                        'amount' => $gift->star_cost,
                        'reason' => 'Received Gift: ' . $gift->name,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            $message = Message::create([
                'conversation_id' => $conversation->id,
                'user_id' => $meId,
                'body' => $gift->emoji . ' ' . $gift->name,
                'type' => 'text',
            ]);

            GiftTransaction::create([
                'sender_id' => $meId,
                'receiver_id' => $receiverId,
                'conversation_id' => $conversation->id,
                'message_id' => $message->id,
                'gift_id' => $gift->id,
                'stars_spent' => $gift->star_cost,
            ]);

            $conversation->touch();

            return null;
        });

        if ($error) {
            return back()->with('error', $error);
        }

        return back();
    }

    public function destroy(Message $message)
    {
        if ((int) $message->user_id !== (int) auth()->id()) {
            abort(403);
        }

        $paths = $message->attachments()->pluck('file_path')->filter()->all();
        if (!empty($paths)) {
            Storage::disk('public')->delete($paths);
        }

        $message->attachments()->delete();
        $message->reactions()->delete();
        $message->update(['is_deleted' => true, 'body' => null]);

        return back();
    }

    private function isBlockedBetween(int $a, int $b): bool
    {
        return BlockedUser::where(function ($q) use ($a, $b) {
            $q->where('blocker_id', $a)->where('blocked_id', $b);
        })->orWhere(function ($q) use ($a, $b) {
            $q->where('blocker_id', $b)->where('blocked_id', $a);
        })->exists();
    }
}