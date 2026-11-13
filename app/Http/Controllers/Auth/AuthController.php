<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\OtpCode;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

class AuthController extends Controller
{
    private function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone);
        if (strlen($digits) === 8) return '+993' . $digits;
        if (strlen($digits) === 11 && str_starts_with($digits, '993')) return '+' . $digits;
        return '+' . $digits;
    }

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $phone = $this->normalizePhone((string) $request->input('phone'));
        $request->merge(['phone' => $phone]);

        $request->validate([
            'phone' => ['required', 'string', 'regex:/^\+993(6[0-5]|71)\d{6}$/'],
            'password' => ['required', 'string'],
        ]);

        $key = 'web-login:' . $phone . ':' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            return back()->withErrors(['phone' => 'Gaty köp synanyşyk edildi. Garaşyň.']);
        }

        $user = User::where('phone', $phone)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            RateLimiter::hit($key, 60);
            return back()->withErrors(['phone' => 'Telefon belgisi ýa-da parol nädogry.']);
        }

        if ($user->is_blocked) {
            return back()->withErrors(['phone' => 'Hasabyňyz petiklenen.']);
        }

        RateLimiter::clear($key);
        Auth::login($user, true);
        $request->session()->regenerate();

        return redirect()->route('home');
    }

    public function showRegister()
    {
        return view('auth.register');
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
            return back()->withErrors(['phone' => 'Bu telefon belgisi eýýäm ulanylýar.']);
        }

        $key = 'web-register-otp:' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 3)) {
            return back()->withErrors(['phone' => 'Gaty köp synanyşyk. 5 minutdan soň synanyşyň.']);
        }
        RateLimiter::hit($key, 300);

        session(['pending_name' => strip_tags($request->name), 'otp_phone' => $phone]);

        if (app()->environment('local')) {
            OtpCode::create([
                'phone' => $phone, 'code_hash' => Hash::make('111111'),
                'type' => 'register', 'request_ip' => $request->ip(),
                'expires_at' => now()->addMinutes(10),
            ]);
            return redirect()->route('auth.verify-otp-form')->with('success', 'DEV KOD: 111111');
        }

        OtpService::generateAndSend($phone, 'register');
        return redirect()->route('auth.verify-otp-form')->with('success', 'Tassyklama kody ugradyldy.');
    }

    public function showVerifyOtpForm()
    {
        if (!session('otp_phone')) {
            return redirect()->route('register');
        }
        return view('auth.verify-otp');
    }

    public function registerVerifyOtp(Request $request)
    {
        $phone = session('otp_phone');
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $otp = OtpCode::where('phone', $phone)->where('type', 'register')
            ->whereNull('used_at')->where('expires_at', '>', now())->latest()->first();

        $isDev = app()->environment('local') && $request->code === '111111';

        if ((!$otp || !Hash::check($request->code, $otp->code_hash)) && !$isDev) {
            return back()->withErrors(['code' => 'Kod nädogry ýa-da möhleti geçen.']);
        }

        if ($otp && !$isDev) $otp->update(['used_at' => now()]);

        $user = User::create([
            'name' => session()->pull('pending_name', 'User'),
            'phone' => $phone,
            'password' => Hash::make($request->password),
            'role' => 'user',
            'plan' => 'free',
        ]);

        session()->forget('otp_phone');
        Auth::login($user);
        return redirect()->route('home');
    }

    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    public function resetPasswordSendOtp(Request $request)
    {
        $phone = $this->normalizePhone((string) $request->input('phone'));
        $request->merge(['phone' => $phone]);

        $request->validate(['phone' => ['required', 'string', 'regex:/^\+993(6[0-5]|71)\d{6}$/']]);

        if (!User::where('phone', $phone)->exists()) {
            return back()->withErrors(['phone' => 'Bu belgi bilen hasap tapylmady.']);
        }

        $key = 'web-reset-otp:' . $phone;
        if (RateLimiter::tooManyAttempts($key, 3)) {
            return back()->withErrors(['phone' => 'Gaty köp synanyşyk.']);
        }
        RateLimiter::hit($key, 300);

        session(['reset_phone' => $phone]);

        if (app()->environment('local')) {
            OtpCode::create([
                'phone' => $phone, 'code_hash' => Hash::make('111111'),
                'type' => 'reset_password', 'request_ip' => $request->ip(),
                'expires_at' => now()->addMinutes(10),
            ]);
            return redirect()->route('password.reset-form')->with('success', 'DEV KOD: 111111');
        }

        OtpService::generateAndSend($phone, 'reset_password');
        return redirect()->route('password.reset-form')->with('success', 'Kod ugradyldy.');
    }

    public function showResetPasswordForm()
    {
        if (!session('reset_phone')) {
            return redirect()->route('password.request');
        }
        return view('auth.reset-password');
    }

    public function resetPasswordVerify(Request $request)
    {
        $phone = session('reset_phone');
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $otp = OtpCode::where('phone', $phone)->where('type', 'reset_password')
            ->whereNull('used_at')->where('expires_at', '>', now())->latest()->first();

        $isDev = app()->environment('local') && $request->code === '111111';

        if ((!$otp || !Hash::check($request->code, $otp->code_hash)) && !$isDev) {
            return back()->withErrors(['code' => 'Kod nädogry ýa-da möhleti geçen.']);
        }

        if ($otp && !$isDev) $otp->update(['used_at' => now()]);

        User::where('phone', $phone)->update(['password' => Hash::make($request->password)]);

        session()->forget('reset_phone');
        return redirect()->route('login')->with('success', 'Parol üstünlikli täzelendi. Indi giriş edip bilersiňiz.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}