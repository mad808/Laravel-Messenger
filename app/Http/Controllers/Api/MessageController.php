<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\MessageAttachment;
use App\Models\Conversation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MessageController extends Controller
{
    public function index(Conversation $conversation)
    {
        if (!$conversation->participants->contains(auth()->id())) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $messages = $conversation->messages()
            ->with(['user:id,name,avatar', 'attachments', 'replyTo'])
            ->latest()
            ->paginate(50);

        return response()->json($messages);
    }

    public function store(Request $request, Conversation $conversation)
    {
        if (!$conversation->participants->contains(auth()->id())) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'body' => 'nullable|string|max:5000',
            'type' => 'required|in:text,photo,video,file,audio',
            'reply_to_id' => 'nullable|exists:messages,id',
            'file' => 'nullable|file|max:20480',
        ]);

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'user_id' => auth()->id(),
            'body' => $request->body,
            'type' => $request->type,
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

        return response()->json($message->load('user', 'attachments'), 201);
    }

    public function destroy(Message $message)
    {
        if ($message->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $message->update(['is_deleted' => true, 'body' => null]);
        $message->attachments()->delete();

        return response()->json(['message' => 'Message deleted.']);
    }
}