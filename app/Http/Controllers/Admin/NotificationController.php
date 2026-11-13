<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\AdminBroadcastNotification;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Notification;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = DatabaseNotification::with('notifiable')
            ->latest()
            ->paginate(20);

        return view('admin.notifications.index', compact('notifications'));
    }

    public function send(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:100',
            'body' => 'required|string|max:255',
            'target' => 'required|in:all,single',
            'user_id' => 'nullable|required_if:target,single|exists:users,id',
        ]);

        $title = trim((string) $request->title);
        $body  = trim((string) $request->body);

        if ($request->target === 'all') {
            User::where('is_blocked', false)
                ->select('id')
                ->chunkById(500, function ($users) use ($title, $body) {
                    Notification::send($users, new AdminBroadcastNotification($title, $body));
                });
        } else {
            $user = User::findOrFail($request->user_id);
            $user->notify(new AdminBroadcastNotification($title, $body));
        }

        return back()->with('success', __('Notification sent successfully.'));
    }
}