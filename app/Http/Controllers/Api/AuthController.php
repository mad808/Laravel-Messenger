<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\OtpCode;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    private function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone);
        if (strlen($digits) === 8) return '+993' . $digits;
        if (strlen($digits) === 11 && str_starts_with($digits, '993')) return '+' . $digits;
        return '+' . $digits;
    }

    public function registerSendOtp(Request $request)
    {
        $phone = $this->normalizePhone((string) $request->input('phone'));
        $request->merge(['phone' => $phone]);

        $request->validate([
            'phone' => ['required', 'string', 'regex:/^\+993(6[0-5]|71)\d{6}$/'],
            'name' => ['required', 'string', 'max:50'],
        ]);

        if (User::where('phone', $phone)->exists()) {
            return response()->json(['message' => 'Bu telefon belgisi eýýäm ulanylýar.'], 422);
        }

        $key = 'register-otp:' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 3)) {
            return response()->json(['message' => 'Gaty köp synanyşyk. Garaşyň.'], 429);
        }
        RateLimiter::hit($key, 300);

        session(['pending_name' => strip_tags($request->name)]);

        if (app()->environment('local')) {
            $code = '111111';
            OtpCode::create([
                'phone' => $phone, 'code_hash' => Hash::make($code),
                'type' => 'register', 'request_ip' => $request->ip(),
                'expires_at' => now()->addMinutes(10),
            ]);
            return response()->json(['message' => 'OTP sent (Dev)', 'dev_code' => $code]);
        }

        OtpService::generateAndSend($phone, 'register');

        return response()->json(['message' => 'OTP üstünlikli ugradyldy.']);
    }

    public function registerVerifyOtp(Request $request)
    {
        $phone = $this->normalizePhone((string) $request->input('phone'));
        $request->merge(['phone' => $phone]);

        $request->validate([
            'phone' => ['required', 'string', 'regex:/^\+993(6[0-5]|71)\d{6}$/'],
            'code' => ['required', 'string', 'size:6'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        $otp = OtpCode::where('phone', $phone)->where('type', 'register')
            ->whereNull('used_at')->where('expires_at', '>', now())->latest()->first();

        $isDev = app()->environment('local') && $request->code === '111111';

        if ((!$otp || !Hash::check($request->code, $otp->code_hash)) && !$isDev) {
            return response()->json(['message' => 'Kod nädogry ýa-da möhleti geçen.'], 422);
        }

        if ($otp && !$isDev) $otp->update(['used_at' => now()]);

        $user = User::create([
            'name' => session()->pull('pending_name', 'User'),
            'phone' => $phone,
            'password' => Hash::make($request->password),
            'role' => 'user',
            'plan' => 'free',
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Hasap döredildi.',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ], 201);
    }

    public function login(Request $request)
    {
        $phone = $this->normalizePhone((string) $request->input('phone'));
        $request->merge(['phone' => $phone]);

        $request->validate([
            'phone' => ['required', 'string', 'regex:/^\+993(6[0-5]|71)\d{6}$/'],
            'password' => ['required', 'string'],
        ]);

        $key = 'login:' . $phone . ':' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            return response()->json(['message' => 'Gaty köp nädogry synanyşyk.'], 429);
        }

        $user = User::where('phone', $phone)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            RateLimiter::hit($key, 60);
            return response()->json(['message' => 'Telefon belgisi ýa-da parol nädogry.'], 401);
        }

        RateLimiter::clear($key);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ]);
    }

    public function resetPasswordSendOtp(Request $request)
    {
        $phone = $this->normalizePhone((string) $request->input('phone'));
        $request->merge(['phone' => $phone]);

        $request->validate(['phone' => ['required', 'string', 'regex:/^\+993(6[0-5]|71)\d{6}$/']]);

        if (!User::where('phone', $phone)->exists()) {
            return response()->json(['message' => 'Bu belgi bilen hasap tapylmady.'], 404);
        }

        $key = 'reset-otp:' . $phone;
        if (RateLimiter::tooManyAttempts($key, 3)) {
            return response()->json(['message' => 'Gaty köp synanyşyk.'], 429);
        }
        RateLimiter::hit($key, 300);

        OtpService::generateAndSend($phone, 'reset_password');

        return response()->json(['message' => 'Paroly täzelemek üçin kod ugradyldy.']);
    }

    public function resetPasswordVerify(Request $request)
    {
        $phone = $this->normalizePhone((string) $request->input('phone'));
        $request->merge(['phone' => $phone]);

        $request->validate([
            'phone' => ['required', 'string', 'regex:/^\+993(6[0-5]|71)\d{6}$/'],
            'code' => ['required', 'string', 'size:6'],
            'new_password' => ['required', 'string', 'min:6'],
        ]);

        $otp = OtpCode::where('phone', $phone)->where('type', 'reset_password')
            ->whereNull('used_at')->where('expires_at', '>', now())->latest()->first();

        if (!$otp || !Hash::check($request->code, $otp->code_hash)) {
            return response()->json(['message' => 'Kod nädogry.'], 422);
        }

        $otp->update(['used_at' => now()]);
        User::where('phone', $phone)->update(['password' => Hash::make($request->new_password)]);

        return response()->json(['message' => 'Parol üstünlikli täzelendi.']);
    }
}