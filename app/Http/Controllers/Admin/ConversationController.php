<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Support\Facades\Storage;

class ConversationController extends Controller
{
    public function index()
    {
        $conversations = Conversation::with('owner')
            ->withCount('messages')
            ->latest()
            ->paginate(20);

        return view('admin.conversations.index', compact('conversations'));
    }

    public function show(Conversation $conversation)
    {
        $conversation->load(['owner', 'participants'])
            ->loadCount('messages');

        $messages = $conversation->messages()
            ->with(['user', 'attachments', 'replyTo.user'])
            ->latest()
            ->paginate(100);

        $conversation->setRelation('messages', $messages->getCollection());

        return view('admin.conversations.show', compact('conversation', 'messages'));
    }

    public function destroy(Conversation $conversation)
    {
        if ($conversation->avatar) {
            Storage::disk('public')->delete($conversation->avatar);
        }

        $conversation->messages()
            ->with('attachments:id,message_id,file_path')
            ->chunkById(200, function ($messages) {
                $paths = $messages->pluck('attachments.*.file_path')->flatten()->filter()->all();
                if (!empty($paths)) {
                    Storage::disk('public')->delete($paths);
                }
            });

        $conversation->delete();

        return redirect()->route('admin.conversations.index')
            ->with('success', 'Conversation and all its messages deleted.');
    }

    public function destroyMessage(Message $message)
    {
        $paths = $message->attachments()->pluck('file_path')->filter()->all();
        if (!empty($paths)) {
            Storage::disk('public')->delete($paths);
        }

        $message->attachments()->delete();
        $message->update(['is_deleted' => true, 'body' => null]);

        return back()->with('success', 'Message and attachments hidden by Admin.');
    }
}