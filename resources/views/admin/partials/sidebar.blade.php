@php
    use Illuminate\Support\Facades\Route;

    $pendingPaymentsCount = \App\Models\CryptoPayment::where('status', 'pending')->count();

    // [route name, active pattern, icon, label, badge]
    $groups = [
        __('Overview') => [
            ['admin.dashboard',         'admin.dashboard',         'bi-speedometer2',    __('Dashboard'),     0],
        ],
        __('Content') => [
            ['admin.users.index',         'admin.users.*',         'bi-people',          __('Users'),         0],
            ['admin.conversations.index', 'admin.conversations.*', 'bi-chat-square-text',__('Conversations'), 0],
            ['admin.stories.index',       'admin.stories.*',       'bi-camera-reels',    __('Stories'),       0],
            ['admin.notifications.index', 'admin.notifications.*', 'bi-bell',            __('Notifications'), 0],
            ['admin.faqs.index', 'admin.faqs.*', 'bi-question-circle', __('FAQs'), 0],
        ],
        __('Billing') => [
            ['admin.payments.index',    'admin.payments.*',        'bi-currency-bitcoin',__('Payments'),      $pendingPaymentsCount],
            ['admin.plans.index',       'admin.plans.*',           'bi-star',            __('Plans'),         0],
        ],
        __('System') => [
            ['admin.otp.index',         'admin.otp.*',             'bi-shield-lock',     __('OTP Gateway'),   0],
            ['admin.settings.index',    'admin.settings.*',        'bi-gear',            __('Settings'),      0],
        ],
    ];
@endphp

<aside class="adm-sidebar" id="admSidebar" aria-label="{{ __('Admin menu') }}">
    <div class="adm-brand">
        <a href="{{ route('admin.dashboard') }}" class="adm-brand-link">
            <span class="adm-logo"><i class="bi bi-chat-dots-fill"></i></span>
            <span>Admin</span>
        </a>
        <button type="button" class="adm-icon-btn d-lg-none" data-adm-close aria-label="{{ __('Close') }}">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>

    <nav class="adm-nav">
        @foreach($groups as $title => $items)
            @php $items = array_filter($items, fn($i) => Route::has($i[0])); @endphp
            @continue(empty($items))

            <div class="adm-nav-title">{{ $title }}</div>
            @foreach($items as [$route, $pattern, $icon, $label, $badge])
                <a href="{{ route($route) }}" class="adm-link {{ request()->routeIs($pattern) ? 'active' : '' }}"
                   @if(request()->routeIs($pattern)) aria-current="page" @endif>
                    <i class="bi {{ $icon }}"></i>
                    <span class="flex-grow-1">{{ $label }}</span>
                    @if($badge > 0)
                        <span class="badge rounded-pill bg-warning text-dark">{{ $badge }}</span>
                    @endif
                </a>
            @endforeach
        @endforeach
    </nav>

    <div class="adm-side-foot">
        <a href="{{ route('home') }}" class="adm-link">
            <i class="bi bi-box-arrow-up-right"></i><span>{{ __('Back to site') }}</span>
        </a>
    </div>
</aside>