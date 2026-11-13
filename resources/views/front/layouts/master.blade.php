<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'ASTORE Messenger')</title>

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
    <link rel="stylesheet" href="{{ asset('assets/css/toastr.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">
    @stack('styles')
</head>
<body>

<div class="app-shell">
    <aside class="sidebar" id="sidebar" aria-label="{{ __('Sidebar') }}">
        @include('front.partials.sidebar')
    </aside>

    <div class="sidebar-backdrop" id="sidebarBackdrop" onclick="closeSidebar()"></div>

    <main class="main-pane">
        @include('front.partials.header')

        <div class="main-scroll" id="main-content-area">
            @yield('content')
        </div>
    </main>
</div>

<script id="flash-data" type="application/json">
    @json(['success' => session('success'), 'error' => session('error'), 'status' => session('status')])
</script>

<script src="{{ asset('assets/js/jquery.min.js') }}"></script>
<script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('assets/js/toastr.min.js') }}"></script>
<script src="{{ asset('assets/js/app.js') }}"></script>
@stack('scripts')
</body>
</html>