@extends('auth.layouts.auth')

@section('title', __('Forgot Password?') . ' - Messenger')

@section('header')
    <div class="text-center mb-4">
        <div class="auth-icon-badge bg-warning bg-opacity-10 text-warning mb-3">
            <i class="bi bi-key-fill"></i>
        </div>
        <h4 class="fw-bold mb-1">{{ __('Forgot Password?') }}</h4>
    </div>
@endsection

@section('content')
    <form action="{{ route('password.send-otp') }}" method="POST">
        @csrf
        <div class="mb-4">
            <label class="form-label small fw-medium">{{ __('Phone Number') }}</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                <input type="tel" name="phone" class="form-control" placeholder="+993 XX XXXXXX" value="{{ old('phone') }}" autocomplete="tel" required autofocus>
            </div>
        </div>

        <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold rounded-3">
            {{ __('Send Reset Code') }}
        </button>
    </form>
@endsection

@section('footer')
    <div class="text-center mt-4 small">
        <a href="{{ route('login') }}" class="text-decoration-none text-muted">
            <i class="bi bi-arrow-left me-1"></i>{{ __('Login') }}
        </a>
    </div>
@endsection