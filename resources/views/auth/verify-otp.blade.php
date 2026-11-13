@extends('auth.layouts.auth')

@section('title', __('Verify OTP') . ' - Messenger')

@section('header')
    <div class="text-center mb-4">
        <div class="auth-icon-badge bg-success bg-opacity-10 text-success mb-3">
            <i class="bi bi-shield-check"></i>
        </div>
        <h4 class="fw-bold mb-1">{{ __('Verify Code') }}</h4>
        <p class="text-muted small mb-0">
            {{ __('Enter the 6-digit code sent to') }}<br>
            <strong class="text-body">{{ session('otp_phone') }}</strong>
        </p>
    </div>
@endsection

@section('content')
    <form action="{{ route('register.verify-otp') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label class="form-label small fw-medium">{{ __('Verification Code') }}</label>
            <input type="text" name="code" class="form-control otp-input" maxlength="6" inputmode="numeric" autocomplete="one-time-code" placeholder="000000" required autofocus>
        </div>

        <div class="mb-3">
            <label class="form-label small fw-medium">{{ __('Create Password') }}</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                <input type="password" name="password" class="form-control" placeholder="••••••••" minlength="6" autocomplete="new-password" required>
                <button type="button" class="btn btn-outline-secondary border-start-0 border-secondary-subtle" data-toggle-password tabindex="-1">
                    <i class="bi bi-eye"></i>
                </button>
            </div>
        </div>

        <div class="mb-4">
            <label class="form-label small fw-medium">{{ __('Confirm Password') }}</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                <input type="password" name="password_confirmation" class="form-control" placeholder="••••••••" minlength="6" autocomplete="new-password" required>
                <button type="button" class="btn btn-outline-secondary border-start-0 border-secondary-subtle" data-toggle-password tabindex="-1">
                    <i class="bi bi-eye"></i>
                </button>
            </div>
        </div>

        <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold rounded-3">
            {{ __('Verify & Register') }}
        </button>
    </form>
@endsection

@section('footer')
    <div class="text-center mt-4 small">
        <a href="{{ route('register') }}" class="text-decoration-none text-muted">
            <i class="bi bi-arrow-left me-1"></i>{{ __('Register') }}
        </a>
    </div>
@endsection