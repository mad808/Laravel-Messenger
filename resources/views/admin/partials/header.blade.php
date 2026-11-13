@php
    $locales = ['en' => ['🇬🇧', 'English'], 'tk' => ['🇹🇲', 'Türkmençe'], 'ru' => ['🇷🇺', 'Русский']];
    $current = app()->getLocale();
    $user    = auth()->user();
    $initial = mb_strtoupper(mb_substr($user->name ?: 'A', 0, 1));
@endphp

<header class="adm-topbar">
    <div class="d-flex align-items-center gap-2 min-w-0">
        <button type="button" class="adm-icon-btn d-lg-none" data-adm-open aria-label="{{ __('Open menu') }}">
            <i class="bi bi-list fs-4"></i>
        </button>
        <h1 class="adm-title text-truncate">@yield('header', __('Dashboard'))</h1>
    </div>

    <div class="d-flex align-items-center gap-1">
        <div class="dropdown">
            <button class="adm-icon-btn" type="button" data-bs-toggle="dropdown" aria-label="{{ __('Language') }}">
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

        <button class="adm-icon-btn" type="button" id="theme-toggle" aria-label="{{ __('Toggle theme') }}">
            <i class="bi bi-moon-fill" id="theme-icon"></i>
        </button>

        <div class="dropdown ms-1">
            <button class="adm-user" type="button" data-bs-toggle="dropdown">
                @if($user->avatar)
                    <img src="{{ asset('storage/' . $user->avatar) }}"
                         alt="{{ $user->name }}"
                         onerror="this.classList.add('d-none'); this.nextElementSibling.classList.remove('d-none');">
                    <span class="rounded-circle d-none align-items-center justify-content-center bg-primary-subtle text-primary-emphasis fw-bold flex-shrink-0"
                          style="width: 32px; height: 32px; font-size: .85rem;">{{ $initial }}</span>
                @else
                    <span class="rounded-circle d-inline-flex align-items-center justify-content-center bg-primary-subtle text-primary-emphasis fw-bold flex-shrink-0"
                          style="width: 32px; height: 32px; font-size: .85rem;">{{ $initial }}</span>
                @endif
                <span class="d-none d-md-inline text-truncate">{{ $user->name }}</span>
                <i class="bi bi-chevron-down small d-none d-md-inline"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                @if(\Illuminate\Support\Facades\Route::has('profile.index'))
                    <li><a class="dropdown-item" href="{{ route('profile.index') }}"><i class="bi bi-person me-2"></i>{{ __('Profile') }}</a></li>
                @endif
                <li><a class="dropdown-item" href="{{ route('home') }}"><i class="bi bi-chat-dots me-2"></i>{{ __('Back to site') }}</a></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="dropdown-item text-danger">
                            <i class="bi bi-box-arrow-right me-2"></i>{{ __('Logout') }}
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</header>