<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OtpGatewayMessage;

class OtpGatewayController extends Controller
{
    public function index()
    {
        $messages = OtpGatewayMessage::latest()->paginate(30);
        return view('admin.otp-gateway.index', compact('messages'));
    }

    public function retry(OtpGatewayMessage $message)
    {
        if ($message->status == 2) {
            return back()->with('error', 'This message is already sent.');
        }

        $message->update([
            'status' => 0,
            'retry_count' => 0,
            'locked_at' => null,
            'error_message' => null,
        ]);

        return back()->with('success', 'Message queued for retry.');
    }

    public function destroy(OtpGatewayMessage $message)
    {
        $message->delete();
        return back()->with('success', 'Message deleted.');
    }
}