@php
    $user   = auth()->user();
    $sub    = $user->activeSubscription;
    $plan   = $sub?->plan?->name;
    $avatar = $user->avatar
        ? asset('storage/' . $user->avatar)
        : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=random';
    $storiesUrl = \Illuminate\Support\Facades\Route::has('stories.index') ? route('stories.index') : null;
@endphp

{{-- Profile --}}
<div class="sb-profile">
    <a href="{{ route('profile.index') }}" class="sb-user">
        <img src="{{ $avatar }}" class="sb-avatar" alt="{{ $user->name }}">
        <div class="min-w-0">
            <div class="sb-name text-truncate">{{ $user->name }}</div>
            <span class="badge rounded-pill {{ $plan ? 'text-bg-warning' : 'text-bg-secondary' }}">
                <i class="bi bi-star-fill me-1"></i>{{ $plan ?: __('Free') }}
            </span>
        </div>
    </a>

    <div class="dropdown">
        <button class="icon-btn" type="button" data-bs-toggle="dropdown" aria-label="{{ __('Menu') }}">
            <i class="bi bi-three-dots-vertical"></i>
        </button>
        <ul class="dropdown-menu dropdown-menu-end shadow border-0 sb-menu">
            <li><a class="dropdown-item" href="{{ route('profile.index') }}"><i class="bi bi-person me-2 text-primary"></i>{{ __('Profile') }}</a></li>
            <li><a class="dropdown-item" href="{{ route('subscriptions.index') }}"><i class="bi bi-star me-2 text-warning"></i>{{ __('Upgrade to Pro') }}</a></li>
            <li><hr class="dropdown-divider"></li>
            <li>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2"></i>{{ __('Logout') }}</button>
                </form>
            </li>
        </ul>
    </div>
</div>

@php
    use Illuminate\Support\Facades\Route;
    $contactsUrl = Route::has('contacts.index') ? route('contacts.index') : null;
@endphp

{{-- Chats / Contacts / Stories switch --}}
<nav class="sb-tabs" aria-label="{{ __('Sections') }}">
    <a href="{{ route('home') }}" class="{{ request()->routeIs('home', 'conversations.*') ? 'active' : '' }}">
        <i class="bi bi-chat-dots-fill"></i><span>{{ __('Chats') }}</span>
    </a>
    @if($contactsUrl)
        <a href="{{ $contactsUrl }}" class="{{ request()->routeIs('contacts.*') ? 'active' : '' }}">
            <i class="bi bi-person-lines-fill"></i><span>{{ __('Contacts') }}</span>
        </a>
    @endif
    @if($storiesUrl)
        <a href="{{ $storiesUrl }}" class="{{ request()->routeIs('stories.*') ? 'active' : '' }}">
            <i class="bi bi-camera-reels-fill"></i><span>{{ __('Stories') }}</span>
        </a>
    @endif
</nav>

{{-- Search (filters rows that have data-search-item) --}}
<div class="sb-search">
    <i class="bi bi-search"></i>
    <input type="search" id="sidebarSearch" class="form-control"
           placeholder="@yield('search_placeholder', __('Search chats...'))" autocomplete="off">
</div>

{{-- Page content (chat list, story menu, ...) --}}
<div class="sb-list" id="chat-list">
    @hasSection('sidebar_content')
        @yield('sidebar_content')
    @else
        <div class="sb-empty">
            <div class="sb-empty-icon"><i class="bi bi-chat-dots"></i></div>
            <h6 class="fw-bold mb-1">{{ __('No chats yet') }}</h6>
            <p class="small text-body-secondary mb-0">{{ __('Start a new conversation to see it here.') }}</p>
        </div>
    @endif
    <div class="sb-empty d-none" id="sidebarNoResults">
        <p class="small text-body-secondary mb-0">{{ __('Nothing found') }}</p>
    </div>
</div>