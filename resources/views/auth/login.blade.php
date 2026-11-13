@extends('auth.layouts.auth')

@section('title', __('Login') . ' - Messenger')

@section('header')
    <div class="text-center mb-4">
        <div class="auth-icon-badge bg-primary bg-opacity-10 text-primary mb-3">
            <i class="bi bi-chat-dots-fill"></i>
        </div>
        <h4 class="fw-bold mb-1">{{ __('Login') }}</h4>
        <p class="text-muted small mb-0">{{ __('Welcome back to ASTORE Messenger') }}</p>
    </div>
@endsection

@section('content')
    <form action="{{ route('login.submit') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label class="form-label small fw-medium">{{ __('Phone Number') }}</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                <input type="tel" name="phone" class="form-control" placeholder="+993 XX XXXXXX" value="{{ old('phone') }}" autocomplete="tel" required autofocus>
            </div>
        </div>

        <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <label class="form-label small fw-medium mb-0">{{ __('Password') }}</label>
                <a href="{{ route('password.request') }}" class="text-decoration-none small">{{ __('Forgot Password?') }}</a>
            </div>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                <input type="password" name="password" class="form-control" placeholder="••••••••" autocomplete="current-password" required>
                <button type="button" class="btn btn-outline-secondary border-start-0 border-secondary-subtle" data-toggle-password tabindex="-1">
                    <i class="bi bi-eye"></i>
                </button>
            </div>
        </div>

        <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold rounded-3 mt-2">
            {{ __('Login') }}
        </button>
    </form>
@endsection

@section('footer')
    <div class="text-center mt-4 small">
        <span class="text-muted">{{ __("Don't have an account?") }}</span>
        <a href="{{ route('register') }}" class="text-decoration-none fw-semibold ms-1">{{ __('Register') }}</a>
    </div>
@endsection