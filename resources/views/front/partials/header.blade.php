@php
    $locales = ['en' => ['🇬🇧', 'English'], 'tk' => ['🇹🇲', 'Türkmençe'], 'ru' => ['🇷🇺', 'Русский']];
    $current = app()->getLocale();
@endphp

<header class="topbar">
    <div class="d-flex align-items-center gap-2 min-w-0">
        <button class="icon-btn d-md-none" type="button" onclick="toggleSidebar()" aria-label="{{ __('Open menu') }}">
            <i class="bi bi-list fs-4"></i>
        </button>
        <h1 class="topbar-title text-truncate">@yield('header_title', __('Chats'))</h1>
    </div>

    <div class="d-flex align-items-center gap-1">
        @if(auth()->check() && in_array(auth()->user()->role, ['admin', 'moderator']))
            <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-danger btn-sm rounded-pill d-flex align-items-center" title="{{ __('Admin Panel') }}">
                <i class="bi bi-shield-lock-fill"></i>
                <span class="d-none d-md-inline ms-1">{{ __('Admin') }}</span>
            </a>
        @endif

        <!-- YENİ EKLENEN BİLDİRİM İKONU (Kodun geri kalanına dokunulmadı) -->
        @auth
        <div class="dropdown">
            <button class="icon-btn position-relative" type="button" data-bs-toggle="dropdown" aria-label="{{ __('Notifications') }}">
                <i class="bi bi-bell"></i>
                @if(auth()->user()->unreadNotifications->count() > 0)
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.55rem; padding: 0.25em 0.4em;">
                        {{ auth()->user()->unreadNotifications->count() }}
                    </span>
                @endif
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2" style="width: 300px; border-radius: 12px; padding: 0;">
                <li class="dropdown-header bg-body-tertiary rounded-top py-2 d-flex justify-content-between align-items-center">
                    <span class="fw-bold">{{ __('Notifications') }}</span>
                    <a href="{{ route('notifications.index') }}" class="text-decoration-none small">{{ __('View All') }}</a>
                </li>
                <div style="max-height: 300px; overflow-y: auto;">
                    @forelse(auth()->user()->unreadNotifications->take(5) as $notification)
                        <li>
                            <a class="dropdown-item py-2 border-bottom" href="#">
                                <div class="d-flex align-items-start gap-2">
                                    <div class="bg-primary-subtle text-primary rounded-circle p-2 mt-1 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                        <i class="bi bi-bell-fill" style="font-size: 0.8rem;"></i>
                                    </div>
                                    <div class="flex-grow-1 overflow-hidden">
                                        <div class="fw-semibold text-truncate" style="font-size: 0.85rem;">{{ $notification->data['title'] ?? __('Notification') }}</div>
                                        <p class="mb-0 text-muted text-truncate" style="font-size: 0.75rem;">{{ $notification->data['body'] ?? '' }}</p>
                                        <small class="text-muted" style="font-size: 0.65rem;">{{ $notification->created_at->diffForHumans() }}</small>
                                    </div>
                                </div>
                            </a>
                        </li>
                    @empty
                        <li class="text-center py-4 text-muted">
                            <i class="bi bi-bell-slash fs-4 mb-2 d-block opacity-50"></i>
                            <small>{{ __('No new notifications') }}</small>
                        </li>
                    @endforelse
                </div>
            </ul>
        </div>
        @endauth
        <!-- YENİ EKLENEN BİLDİRİM İKONU SONU -->

        <div class="dropdown">
            <button class="icon-btn" type="button" data-bs-toggle="dropdown" aria-label="{{ __('Language') }}">
                <i class="bi bi-globe2"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                @foreach($locales as $code => [$flag, $label])
                    <li>
                        <a class="dropdown-item {{ $current === $code ? 'active' : '' }}" href="{{ route('locale.switch', $code) }}">
                            {{ $flag }} {{ $label }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>

        <button class="icon-btn" type="button" onclick="toggleTheme()" aria-label="{{ __('Toggle theme') }}">
            <i class="bi bi-moon-fill theme-toggle-icon"></i>
        </button>
    </div>
</header>