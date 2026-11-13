<?php

namespace App\Services;

use App\Models\OtpCode;
use App\Models\OtpGatewayMessage;
use Illuminate\Support\Facades\Hash;

class OtpService
{
    public static function generateAndSend(string $phone, string $type = 'login'): void
    {
        $code = (string) rand(100000, 999999);
        $messageText = "Messenger tassyklama kody: {$code}. Kod 5 minutdan soň gutarýar.";

        $gatewayMessage = OtpGatewayMessage::create([
            'phone' => $phone,
            'message' => $messageText,
            'status' => 0,
        ]);

        OtpCode::create([
            'phone' => $phone,
            'code_hash' => Hash::make($code),
            'type' => $type,
            'request_ip' => request()->ip(),
            'expires_at' => now()->addMinutes(5),
            'gateway_message_id' => $gatewayMessage->id,
        ]);
    }
}