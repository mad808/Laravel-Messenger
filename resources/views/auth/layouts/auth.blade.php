<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Messenger')</title>

    @php
        $siteFavicon = \App\Models\Setting::where('key', 'site_favicon')->value('value');
        $faviconUrl  = $siteFavicon ? asset('storage/' . ltrim($siteFavicon, '/')) : asset('favicon.ico');
    @endphp
    <link rel="icon" href="{{ $faviconUrl }}">
    <link rel="apple-touch-icon" href="{{ $faviconUrl }}">

    <script>
        document.documentElement.setAttribute('data-bs-theme', localStorage.getItem('theme') || 'light');
    </script>

    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap-icons.min.css') }}">

    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: var(--bs-tertiary-bg);
            background-image:
                radial-gradient(circle at 15% 15%, rgba(var(--bs-primary-rgb), 0.08), transparent 45%),
                radial-gradient(circle at 85% 85%, rgba(var(--bs-primary-rgb), 0.06), transparent 45%);
            padding: 1rem;
        }

        .auth-wrapper {
            width: 100%;
            max-width: 420px;
        }

        .auth-card {
            border-radius: 1.25rem;
            border: 1px solid var(--bs-border-color-translucent);
            background-color: var(--bs-body-bg);
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.08);
        }

        [data-bs-theme="dark"] .auth-card {
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.35);
        }

        .auth-icon-badge {
            width: 56px;
            height: 56px;
            border-radius: 1rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .theme-toggle-btn {
            position: fixed;
            top: 1rem;
            right: 1rem;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            z-index: 10;
        }

        .input-group-text {
            background-color: var(--bs-tertiary-bg);
        }

        .form-control:focus {
            box-shadow: 0 0 0 0.2rem rgba(var(--bs-primary-rgb), 0.15);
        }

        .otp-input {
            letter-spacing: 0.45em;
            font-size: 1.35rem;
            font-weight: 700;
            text-align: center;
            padding-left: 1.25rem;
        }

        .lang-btn {
            border-radius: 0.5rem;
            text-decoration: none;
            padding: 0.25rem 0.6rem;
            font-size: 0.95rem;
            opacity: 0.65;
            transition: opacity 0.15s ease, background-color 0.15s ease;
        }

        .lang-btn:hover,
        .lang-btn.active {
            opacity: 1;
            background-color: var(--bs-tertiary-bg);
        }
    </style>
</head>
<body>

<button type="button" class="btn btn-outline-secondary bg-body theme-toggle-btn shadow-sm" id="theme-toggle" aria-label="Toggle theme">
    <i class="bi bi-moon-fill" id="theme-icon"></i>
</button>

<div class="auth-wrapper">
    <div class="card auth-card">
        <div class="card-body p-4 p-sm-4">
            @yield('header')

            @if($errors->any())
                <div class="alert alert-danger d-flex align-items-center gap-2 py-2 px-3 small rounded-3 border-0" role="alert">
                    <i class="bi bi-exclamation-triangle-fill flex-shrink-0"></i>
                    <div>{{ $errors->first() }}</div>
                </div>
            @endif

            @if(session('success'))
                <div class="alert alert-success d-flex align-items-center gap-2 py-2 px-3 small rounded-3 border-0" role="alert">
                    <i class="bi bi-check-circle-fill flex-shrink-0"></i>
                    <div>{{ session('success') }}</div>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger d-flex align-items-center gap-2 py-2 px-3 small rounded-3 border-0" role="alert">
                    <i class="bi bi-x-circle-fill flex-shrink-0"></i>
                    <div>{{ session('error') }}</div>
                </div>
            @endif

            @yield('content')

            @yield('footer')

            <div class="d-flex justify-content-center gap-2 mt-4 pt-3 border-top">
                <a href="{{ route('locale.switch', 'en') }}" class="lang-btn {{ app()->getLocale() === 'en' ? 'active' : '' }}">🇬🇧</a>
                <a href="{{ route('locale.switch', 'tk') }}" class="lang-btn {{ app()->getLocale() === 'tk' ? 'active' : '' }}">🇹🇲</a>
                <a href="{{ route('locale.switch', 'ru') }}" class="lang-btn {{ app()->getLocale() === 'ru' ? 'active' : '' }}">🇷🇺</a>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
<script>
    const htmlEl = document.documentElement;
    const themeIcon = document.getElementById('theme-icon');

    function syncThemeIcon(theme) {
        themeIcon.className = theme === 'dark' ? 'bi bi-sun-fill' : 'bi bi-moon-fill';
    }

    syncThemeIcon(htmlEl.getAttribute('data-bs-theme') || 'light');

    document.getElementById('theme-toggle').addEventListener('click', () => {
        const nextTheme = htmlEl.getAttribute('data-bs-theme') === 'light' ? 'dark' : 'light';
        htmlEl.setAttribute('data-bs-theme', nextTheme);
        localStorage.setItem('theme', nextTheme);
        syncThemeIcon(nextTheme);
    });

    document.querySelectorAll('[data-toggle-password]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const input = btn.closest('.input-group').querySelector('input');
            const icon = btn.querySelector('i');
            const isPassword = input.type === 'password';
            input.type = isPassword ? 'text' : 'password';
            icon.className = isPassword ? 'bi bi-eye-slash' : 'bi bi-eye';
        });
    });
</script>
</body>
</html>