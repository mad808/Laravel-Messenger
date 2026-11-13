@extends('admin.layouts.master')

@section('title', __('Dashboard'))
@section('header', __('Dashboard'))

@php
    use Illuminate\Support\Facades\Route;

    $cards = [
        ['label' => __('Total Users'),    'value' => $stats['total_users'],    'icon' => 'bi-people-fill',       'tone' => 'primary', 'decimals' => 0, 'prefix' => ''],
        ['label' => __('Active (24h)'),   'value' => $stats['active_users'],   'icon' => 'bi-person-check-fill', 'tone' => 'success', 'decimals' => 0, 'prefix' => ''],
        ['label' => __('Total Messages'), 'value' => $stats['total_messages'], 'icon' => 'bi-chat-dots-fill',    'tone' => 'info',    'decimals' => 0, 'prefix' => ''],
        ['label' => __('Revenue (USDT)'), 'value' => $stats['total_revenue'],  'icon' => 'bi-currency-dollar',   'tone' => 'warning', 'decimals' => 2, 'prefix' => '$'],
    ];

    $activeRate = $stats['total_users'] > 0
        ? round($stats['active_users'] / $stats['total_users'] * 100)
        : 0;

    $statusCounts = collect($recentPayments)->countBy('status');
    $statusTotal  = max($recentPayments->count(), 1);

    $quick = [
        ['admin.users.index',         'bi-people',           __('Users')],
        ['admin.conversations.index', 'bi-chat-square-text', __('Conversations')],
        ['admin.payments.index',      'bi-currency-bitcoin', __('Payments')],
        ['admin.plans.index',         'bi-star',             __('Plans')],
        ['admin.notifications.index', 'bi-bell',             __('Notifications')],
    ];
@endphp

@push('styles')
<style>
    .dash-hero {
        display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem;
        padding: 1.25rem 1.5rem; margin-bottom: 1.5rem; border-radius: 16px;
        background: linear-gradient(135deg, rgba(var(--bs-primary-rgb), .14), rgba(var(--bs-info-rgb), .08));
        border: 1px solid rgba(var(--bs-primary-rgb), .18);
    }
    .dash-hero h2 { font-size: 1.25rem; font-weight: 700; margin: 0 0 .15rem; }
    .dash-hero p  { margin: 0; color: var(--bs-secondary-color); font-size: .9rem; }
    .dash-quick   { display: flex; flex-wrap: wrap; gap: .5rem; }
    .dash-quick .btn { border-radius: 999px; font-weight: 600; font-size: .85rem; }

    .stat-card {
        position: relative; overflow: hidden; height: 100%;
        border: 1px solid var(--bs-border-color); border-radius: 16px; background: var(--bs-body-bg);
        transition: transform .2s, box-shadow .2s, border-color .2s;
    }
    .stat-card:hover { transform: translateY(-4px); box-shadow: 0 .6rem 1.2rem rgba(0, 0, 0, .12); }
    .stat-card::after {
        content: ""; position: absolute; right: -28px; top: -28px; width: 96px; height: 96px; border-radius: 50%;
        background: rgba(var(--tone), .10);
    }
    .stat-card .card-body { position: relative; z-index: 1; padding: 1.25rem; }
    .stat-label { color: var(--bs-secondary-color); font-size: .85rem; font-weight: 500; margin-bottom: .25rem; }
    .stat-value { font-size: 1.75rem; font-weight: 800; line-height: 1.1; margin: 0; font-variant-numeric: tabular-nums; }
    .stat-icon {
        width: 50px; height: 50px; flex: none; display: grid; place-items: center;
        border-radius: 14px; font-size: 1.5rem; color: rgb(var(--tone)); background: rgba(var(--tone), .14);
    }
    .tone-primary { --tone: var(--bs-primary-rgb); }
    .tone-success { --tone: var(--bs-success-rgb); }
    .tone-info    { --tone: var(--bs-info-rgb); }
    .tone-warning { --tone: var(--bs-warning-rgb); }
    .stat-meter { height: 6px; margin-top: .9rem; border-radius: 99px; background: rgba(var(--tone), .15); overflow: hidden; }
    .stat-meter > span { display: block; height: 100%; border-radius: inherit; background: rgb(var(--tone)); width: 0; transition: width 1s ease; }

    .panel { border: 1px solid var(--bs-border-color); border-radius: 16px; background: var(--bs-body-bg); height: 100%; overflow: hidden; }
    .panel-head {
        display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: .75rem;
        padding: 1rem 1.25rem; border-bottom: 1px solid var(--bs-border-color);
    }
    .panel-head h5 { margin: 0; font-weight: 700; font-size: 1rem; }
    .panel .table { margin: 0; }
    .panel .table thead th {
        background: var(--bs-tertiary-bg); color: var(--bs-secondary-color);
        font-size: .75rem; font-weight: 600; text-transform: uppercase; letter-spacing: .04em; border-bottom: 0;
    }
    .user-filter { max-width: 220px; }
    .user-av { width: 38px; height: 38px; border-radius: 50%; object-fit: cover; flex-shrink: 0; }
    .user-av-fallback {
        width: 38px; height: 38px; border-radius: 50%; flex-shrink: 0;
        display: inline-flex; align-items: center; justify-content: center; font-weight: 700; font-size: .9rem;
    }

    .pay-row { display: flex; align-items: center; justify-content: space-between; gap: .75rem; padding: .85rem 1.25rem; border-bottom: 1px solid var(--bs-border-color); }
    .pay-row:last-child { border-bottom: 0; }
    .pay-ico { width: 40px; height: 40px; flex: none; display: grid; place-items: center; border-radius: 12px; font-size: 1.15rem; }
    .pay-ico.completed { background: rgba(var(--bs-success-rgb), .14); color: var(--bs-success); }
    .pay-ico.pending   { background: rgba(var(--bs-warning-rgb), .18); color: var(--bs-warning-text-emphasis); }
    .pay-ico.failed    { background: rgba(var(--bs-danger-rgb), .14);  color: var(--bs-danger); }
    .status-bar { display: flex; height: 8px; border-radius: 99px; overflow: hidden; background: var(--bs-tertiary-bg); margin: 1rem 1.25rem .35rem; }
    .status-bar span { display: block; height: 100%; }
    .status-legend { display: flex; flex-wrap: wrap; gap: .25rem 1rem; padding: 0 1.25rem 1rem; font-size: .8rem; color: var(--bs-secondary-color); }
    .status-legend i { display: inline-block; width: 8px; height: 8px; border-radius: 50%; margin-right: .35rem; }

    .empty-state { text-align: center; padding: 2.5rem 1rem; color: var(--bs-secondary-color); }
    .empty-state i { font-size: 2rem; display: block; margin-bottom: .5rem; opacity: .6; }
</style>
@endpush

@section('content')

{{-- Hero --}}
<div class="dash-hero">
    <div>
        <h2>{{ __('Welcome back to ASTORE Messenger') }}, {{ auth()->user()->name }} 👋</h2>
        <p><i class="bi bi-calendar3 me-1"></i><span id="dashDate">{{ now()->format('d.m.Y') }}</span></p>
    </div>
    <div class="dash-quick">
        @foreach($quick as [$route, $icon, $label])
            @if(Route::has($route))
                <a href="{{ route($route) }}" class="btn btn-sm btn-outline-primary bg-body">
                    <i class="bi {{ $icon }} me-1"></i>{{ $label }}
                </a>
            @endif
        @endforeach
    </div>
</div>

{{-- Stats --}}
<div class="row g-3 g-xl-4 mb-4">
    @foreach($cards as $i => $c)
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card tone-{{ $c['tone'] }}">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between gap-2">
                        <div class="min-w-0">
                            <p class="stat-label">{{ $c['label'] }}</p>
                            <h3 class="stat-value"
                                data-count="{{ $c['value'] }}"
                                data-decimals="{{ $c['decimals'] }}"
                                data-prefix="{{ $c['prefix'] }}">{{ $c['prefix'] }}{{ number_format($c['value'], $c['decimals']) }}</h3>
                        </div>
                        <div class="stat-icon"><i class="bi {{ $c['icon'] }}"></i></div>
                    </div>

                    @if($i === 1)
                        <div class="stat-meter" title="{{ $activeRate }}%"><span data-width="{{ $activeRate }}"></span></div>
                        <small class="text-body-secondary">{{ $activeRate }}% {{ __('of all users') }}</small>
                    @elseif($i === 2)
                        <small class="text-body-secondary d-block mt-2">
                            <i class="bi bi-chat-square-text me-1"></i>{{ number_format($stats['total_conversations']) }} {{ __('Conversations') }}
                        </small>
                    @elseif($i === 3 && $stats['pending_payments'] > 0)
                        <small class="text-warning-emphasis d-block mt-2 fw-semibold">
                            <i class="bi bi-hourglass-split me-1"></i>{{ number_format($stats['pending_payments']) }} {{ __('Pending') }}
                        </small>
                    @endif
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="row g-3 g-xl-4">

    {{-- Newest users --}}
    <div class="col-lg-7">
        <div class="panel">
            <div class="panel-head">
                <h5><i class="bi bi-person-plus text-primary me-2"></i>{{ __('Newest Users') }}</h5>
                <div class="d-flex align-items-center gap-2">
                    <input type="search" id="userFilter" class="form-control form-control-sm user-filter rounded-pill"
                           placeholder="{{ __('Filter...') }}" autocomplete="off">
                    @if(Route::has('admin.users.index'))
                        <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-primary rounded-pill">{{ __('View All') }}</a>
                    @endif
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th class="ps-3">{{ __('User') }}</th>
                            <th>{{ __('Phone') }}</th>
                            <th>{{ __('Role') }}</th>
                            <th class="text-end pe-3">{{ __('Joined') }}</th>
                        </tr>
                    </thead>
                    <tbody id="userRows">
                        @forelse($recentUsers as $user)
                            @php $initial = mb_strtoupper(mb_substr($user->name ?: 'U', 0, 1)); @endphp
                            <tr data-user-row>
                                <td class="ps-3">
                                    <a href="{{ route('admin.users.show', $user) }}" class="d-flex align-items-center gap-2 text-decoration-none text-reset">
                                        @if($user->avatar)
                                            <img src="{{ asset('storage/' . $user->avatar) }}"
                                                 class="user-av"
                                                 alt="{{ $user->name }}"
                                                 loading="lazy"
                                                 onerror="this.classList.add('d-none'); this.nextElementSibling.classList.remove('d-none');">
                                            <span class="user-av-fallback bg-primary-subtle text-primary-emphasis d-none">{{ $initial }}</span>
                                        @else
                                            <span class="user-av-fallback bg-primary-subtle text-primary-emphasis">{{ $initial }}</span>
                                        @endif
                                        <div class="min-w-0">
                                            <div class="fw-semibold text-truncate">{{ $user->name }}</div>
                                            <small class="text-body-secondary">{{ $user->username ? '@' . $user->username : '-' }}</small>
                                        </div>
                                    </a>
                                </td>
                                <td class="text-nowrap font-monospace small">{{ $user->phone }}</td>
                                <td>
                                    @switch($user->role)
                                        @case('admin')     <span class="badge rounded-pill bg-danger-subtle text-danger-emphasis">{{ __('Admin') }}</span> @break
                                        @case('moderator') <span class="badge rounded-pill bg-warning-subtle text-warning-emphasis">{{ __('Moderator') }}</span> @break
                                        @default           <span class="badge rounded-pill bg-secondary-subtle text-secondary-emphasis">{{ __('User') }}</span>
                                    @endswitch
                                </td>
                                <td class="text-end pe-3 text-body-secondary text-nowrap" title="{{ $user->created_at }}">
                                    {{ $user->created_at->diffForHumans() }}
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4"><div class="empty-state"><i class="bi bi-people"></i>{{ __('No users found') }}</div></td></tr>
                        @endforelse
                        <tr id="userNoMatch" class="d-none">
                            <td colspan="4"><div class="empty-state"><i class="bi bi-search"></i>{{ __('Nothing found') }}</div></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Recent payments --}}
    <div class="col-lg-5">
        <div class="panel">
            <div class="panel-head">
                <h5>
                    <i class="bi bi-currency-bitcoin text-warning me-2"></i>{{ __('Recent Payments') }}
                    @if($stats['pending_payments'] > 0)
                        <span class="badge rounded-pill bg-warning text-dark ms-1">{{ $stats['pending_payments'] }}</span>
                    @endif
                </h5>
                @if(Route::has('admin.payments.index'))
                    <a href="{{ route('admin.payments.index') }}" class="btn btn-sm btn-outline-primary rounded-pill">{{ __('View All') }}</a>
                @endif
            </div>

            @if($recentPayments->count())
                @php
                    $done = $statusCounts->get('completed', 0);
                    $wait = $statusCounts->get('pending', 0);
                    $fail = $recentPayments->count() - $done - $wait;
                @endphp
                <div class="status-bar" title="{{ __('Based on the payments shown below') }}">
                    <span style="width: {{ $done / $statusTotal * 100 }}%; background: var(--bs-success)"></span>
                    <span style="width: {{ $wait / $statusTotal * 100 }}%; background: var(--bs-warning)"></span>
                    <span style="width: {{ $fail / $statusTotal * 100 }}%; background: var(--bs-danger)"></span>
                </div>
                <div class="status-legend">
                    <span><i style="background: var(--bs-success)"></i>{{ __('Completed') }} {{ $done }}</span>
                    <span><i style="background: var(--bs-warning)"></i>{{ __('Pending') }} {{ $wait }}</span>
                    <span><i style="background: var(--bs-danger)"></i>{{ __('Failed') }} {{ $fail }}</span>
                </div>
            @endif

            @forelse($recentPayments as $payment)
                @php
                    $st = in_array($payment->status, ['completed', 'pending']) ? $payment->status : 'failed';
                    $stIcon  = ['completed' => 'bi-check2-circle', 'pending' => 'bi-hourglass-split', 'failed' => 'bi-x-circle'][$st];
                    $stBadge = ['completed' => 'bg-success-subtle text-success-emphasis', 'pending' => 'bg-warning-subtle text-warning-emphasis', 'failed' => 'bg-danger-subtle text-danger-emphasis'][$st];
                    $stLabel = ['completed' => __('Completed'), 'pending' => __('Pending'), 'failed' => __('Failed')][$st];
                @endphp
                <div class="pay-row">
                    <div class="d-flex align-items-center gap-3 min-w-0">
                        <div class="pay-ico {{ $st }}"><i class="bi {{ $stIcon }}"></i></div>
                        <div class="min-w-0">
                            <div class="fw-semibold text-truncate">{{ $payment->user->name ?? __('Unknown') }}</div>
                            <small class="text-body-secondary text-truncate d-block">
                                {{ $payment->plan->name ?? '-' }} • {{ $payment->created_at->diffForHumans() }}
                            </small>
                        </div>
                    </div>
                    <div class="text-end flex-none">
                        <div class="fw-bold">${{ number_format($payment->amount, 2) }}</div>
                        <span class="badge rounded-pill {{ $stBadge }}">{{ $stLabel }}</span>
                    </div>
                </div>
            @empty
                <div class="empty-state"><i class="bi bi-wallet2"></i>{{ __('No payments yet') }}</div>
            @endforelse
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    document.querySelectorAll('[data-count]').forEach(function (el) {
        var target   = parseFloat(el.dataset.count) || 0;
        var decimals = parseInt(el.dataset.decimals, 10) || 0;
        var prefix   = el.dataset.prefix || '';
        var fmt = function (n) {
            return prefix + n.toLocaleString(undefined, { minimumFractionDigits: decimals, maximumFractionDigits: decimals });
        };
        if (reduce || target === 0) { el.textContent = fmt(target); return; }

        var start = null, duration = 900;
        function tick(ts) {
            if (start === null) start = ts;
            var p = Math.min((ts - start) / duration, 1);
            var eased = 1 - Math.pow(1 - p, 3);
            el.textContent = fmt(target * eased);
            if (p < 1) requestAnimationFrame(tick); else el.textContent = fmt(target);
        }
        el.textContent = fmt(0);
        requestAnimationFrame(tick);
    });

    requestAnimationFrame(function () {
        document.querySelectorAll('[data-width]').forEach(function (el) {
            el.style.width = el.dataset.width + '%';
        });
    });

    var input   = document.getElementById('userFilter');
    var rows    = document.querySelectorAll('[data-user-row]');
    var noMatch = document.getElementById('userNoMatch');
    if (input && rows.length) {
        input.addEventListener('input', function () {
            var q = input.value.trim().toLowerCase(), visible = 0;
            rows.forEach(function (row) {
                var show = !q || row.textContent.toLowerCase().indexOf(q) !== -1;
                row.classList.toggle('d-none', !show);
                if (show) visible++;
            });
            if (noMatch) noMatch.classList.toggle('d-none', !(q && visible === 0));
        });
    }

    var dateEl = document.getElementById('dashDate');
    if (dateEl) {
        var lang = document.documentElement.lang || undefined;
        try {
            dateEl.textContent = new Date().toLocaleDateString(lang, { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
        } catch (e) {}
    }
})();
</script>
@endpush