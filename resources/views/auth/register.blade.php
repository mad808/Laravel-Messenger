@extends('auth.layouts.auth')

@section('title', __('Register') . ' - Messenger')

@section('header')
    <div class="text-center mb-4">
        <div class="auth-icon-badge bg-primary bg-opacity-10 text-primary mb-3">
            <i class="bi bi-person-plus-fill"></i>
        </div>
        <h4 class="fw-bold mb-1">{{ __('Create Account') }}</h4>
    </div>
@endsection

@section('content')
    <form action="{{ route('register.send-otp') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label class="form-label small fw-medium">{{ __('Your Name') }}</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-person"></i></span>
                <input type="text" name="name" class="form-control" value="{{ old('name') }}" maxlength="50" autocomplete="name" required autofocus>
            </div>
        </div>

        <div class="mb-4">
            <label class="form-label small fw-medium">{{ __('Phone Number') }}</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                <input type="tel" name="phone" class="form-control" placeholder="+993 XX XXXXXX" value="{{ old('phone') }}" autocomplete="tel" required>
            </div>
        </div>

        <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold rounded-3">
            {{ __('Send OTP Code') }}
        </button>
    </form>
@endsection

@section('footer')
    <div class="text-center mt-4 small">
        <span class="text-muted">{{ __('Already have an account?') }}</span>
        <a href="{{ route('login') }}" class="text-decoration-none fw-semibold ms-1">{{ __('Login') }}</a>
    </div>
@endsection