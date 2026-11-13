<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\CryptoPayment;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::where('last_seen_at', '>=', now()->subHours(24))->count(),
            'total_conversations' => Conversation::count(),
            'total_messages' => Message::count(),
            'pending_payments' => CryptoPayment::where('status', 'pending')->count(),
            'total_revenue' => CryptoPayment::where('status', 'completed')->sum('amount'),
        ];

        $recentUsers = User::latest()->take(5)->get();
        $recentPayments = CryptoPayment::with('user', 'plan')->latest()->take(5)->get();

        return view('admin.dashboard.index', compact('stats', 'recentUsers', 'recentPayments'));
    }
}