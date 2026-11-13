@extends('admin.layouts.master')

@section('title', __('User Details'))
@section('header', __('User Details') . ': ' . $user->name)

@push('styles')
<style>
    .profile-banner {
        height: 112px;
        background: linear-gradient(135deg, var(--bs-primary) 0%, #0b5ed7 100%);
        border-radius: 13px 13px 0 0;
    }

    .profile-avatar-wrap {
        width: 96px;
        height: 96px;
        margin: -48px auto 0;
        position: relative;
    }

    .profile-avatar {
        width: 96px;
        height: 96px;
        border-radius: 50%;
        border: 4px solid var(--bs-body-bg);
        object-fit: cover;
        background: var(--bs-body-bg);
    }

    .profile-avatar-fallback {
        width: 96px;
        height: 96px;
        border-radius: 50%;
        border: 4px solid var(--bs-body-bg);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 2.2rem;
        font-weight: 700;
    }

    .user-meta-list {
        background: var(--bs-tertiary-bg);
        border: 1px solid var(--bs-border-color-translucent);
        border-radius: 12px;
        padding: .25rem 1rem;
    }

    .user-meta-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: .75rem;
        padding: .65rem 0;
        border-bottom: 1px solid var(--bs-border-color-translucent);
        font-size: .88rem;
    }

    .user-meta-row:last-child {
        border-bottom: 0;
    }

    .user-tabs .nav-link {
        color: var(--bs-secondary-color);
        border: 0;
        border-bottom: 2px solid transparent;
        padding: .85rem 1rem;
        font-weight: 600;
        font-size: .92rem;
        transition: color .15s ease, border-color .15s ease;
    }

    .user-tabs .nav-link:hover {
        color: var(--bs-body-color);
    }

    .user-tabs .nav-link.active {
        background: transparent;
        border-bottom-color: var(--bs-primary);
        color: var(--bs-primary);
    }

    .stat-tile {
        background: var(--bs-tertiary-bg);
        border: 1px solid var(--bs-border-color-translucent);
        border-radius: 12px;
        padding: 1rem 1.15rem;
    }

    .stat-tile-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        flex-shrink: 0;
    }

    .user-pay-table thead th {
        font-size: .74rem;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
        color: var(--bs-secondary-color);
        background: var(--bs-tertiary-bg);
        border-bottom-width: 1px;
        padding-top: .75rem;
        padding-bottom: .75rem;
    }

    .tx-hash-pill {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-size: .8rem;
        background: var(--bs-tertiary-bg);
        border: 1px solid var(--bs-border-color-translucent);
        padding: .2rem .5rem;
        border-radius: 6px;
    }

    .tx-copy-btn {
        border: 0;
        background: transparent;
        padding: 0;
        color: var(--bs-secondary-color);
        line-height: 1;
        cursor: pointer;
    }

    .tx-copy-btn:hover {
        color: var(--bs-primary);
    }

    .privacy-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        padding: .85rem 1rem;
        background: var(--bs-tertiary-bg);
        border: 1px solid var(--bs-border-color-translucent);
        border-radius: 10px;
    }
</style>
@endpush

@section('content')
@php
    $isMe     = (int) $user->id === (int) auth()->id();
    $initial  = mb_strtoupper(mb_substr($user->name ?: 'U', 0, 1));
    $roleTone = match($user->role) {
        'admin'     => 'bg-danger-subtle text-danger-emphasis',
        'moderator' => 'bg-warning-subtle text-warning-emphasis',
        default     => 'bg-secondary-subtle text-secondary-emphasis',
    };
    $roleLabel = match($user->role) {
        'admin'     => __('Admin'),
        'moderator' => __('Moderator'),
        default     => __('User'),
    };
@endphp

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-secondary rounded-3 px-3">
        <i class="bi bi-arrow-left me-1"></i>{{ __('Back to List') }}
    </a>

    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-primary rounded-3 px-3 fw-semibold">
        <i class="bi bi-pencil me-1"></i>{{ __('Edit') }}
    </a>
</div>

<div class="row g-4 align-items-start">
    {{-- Left Column: Profile Card --}}
    <div class="col-lg-4">
        <div class="card shadow-sm overflow-hidden">
            <div class="profile-banner"></div>

            <div class="card-body text-center pt-0 p-4">
                <div class="profile-avatar-wrap">
                    @if($user->avatar)
                        <img src="{{ asset('storage/'.$user->avatar) }}"
                             alt="{{ $user->name }}"
                             class="profile-avatar shadow-sm"
                             onerror="this.classList.add('d-none'); this.nextElementSibling.classList.remove('d-none');">
                        <div class="profile-avatar-fallback bg-primary-subtle text-primary-emphasis shadow-sm d-none">{{ $initial }}</div>
                    @else
                        <div class="profile-avatar-fallback bg-primary-subtle text-primary-emphasis shadow-sm">{{ $initial }}</div>
                    @endif
                </div>

                <h5 class="mt-3 mb-1 fw-bold">{{ $user->name }}</h5>
                <p class="text-muted small mb-3">{{ $user->username ? '@'.$user->username : __('No username') }}</p>

                <div class="d-flex justify-content-center flex-wrap gap-2 mb-4">
                    <span class="badge rounded-pill {{ $roleTone }} px-3 py-1">{{ $roleLabel }}</span>

                    @if($user->is_blocked)
                        <span class="badge rounded-pill bg-danger-subtle text-danger-emphasis px-3 py-1">
                            <i class="bi bi-slash-circle me-1"></i>{{ __('Blocked') }}
                        </span>
                    @else
                        <span class="badge rounded-pill bg-success-subtle text-success-emphasis px-3 py-1">
                            <i class="bi bi-check-circle me-1"></i>{{ __('Active') }}
                        </span>
                    @endif
                </div>

                <div class="user-meta-list text-start">
                    <div class="user-meta-row">
                        <span class="text-muted">{{ __('Phone') }}</span>
                        <span class="fw-semibold font-monospace">{{ $user->phone }}</span>
                    </div>
                    <div class="user-meta-row">
                        <span class="text-muted">{{ __('Email') }}</span>
                        <span class="fw-medium text-truncate">{{ $user->email ?? '-' }}</span>
                    </div>
                    <div class="user-meta-row">
                        <span class="text-muted">{{ __('Joined') }}</span>
                        <span class="fw-medium">{{ $user->created_at->format('d M, Y') }}</span>
                    </div>
                    <div class="user-meta-row">
                        <span class="text-muted">{{ __('Last Seen') }}</span>
                        <span class="fw-medium">{{ $user->last_seen_at ? $user->last_seen_at->diffForHumans() : '-' }}</span>
                    </div>
                </div>

                @unless($isMe)
                <div class="mt-4 d-grid gap-2">
                    <form action="{{ route('admin.users.toggleBlock', $user) }}" method="POST"
                          onsubmit="return confirm('{{ $user->is_blocked ? __('Unblock :name?', ['name' => $user->name]) : __('Block :name?', ['name' => $user->name]) }}');">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn w-100 rounded-3 fw-semibold {{ $user->is_blocked ? 'btn-success' : 'btn-outline-warning' }}">
                            <i class="bi {{ $user->is_blocked ? 'bi-unlock' : 'bi-lock' }} me-2"></i>
                            {{ $user->is_blocked ? __('Unblock User') : __('Block User') }}
                        </button>
                    </form>

                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST"
                          onsubmit="return confirm('{{ __('Are you sure you want to delete this user?') }}');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger rounded-3 w-100 fw-semibold">
                            <i class="bi bi-trash me-2"></i>{{ __('Delete User') }}
                        </button>
                    </form>
                </div>
                @endunless
            </div>
        </div>
    </div>

    {{-- Right Column: Details & Tabs --}}
    <div class="col-lg-8">
        <div class="card shadow-sm overflow-hidden">
            <div class="card-header bg-transparent p-0 px-3 border-bottom">
                <ul class="nav nav-tabs user-tabs border-0" id="userTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview" type="button" role="tab">
                            <i class="bi bi-info-circle me-1"></i>{{ __('Overview') }}
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link d-inline-flex align-items-center gap-1" id="payments-tab" data-bs-toggle="tab" data-bs-target="#payments" type="button" role="tab">
                            <i class="bi bi-currency-bitcoin"></i>
                            <span>{{ __('Payments') }}</span>
                            @if($user->cryptoPayments->count() > 0)
                                <span class="badge rounded-pill bg-body-tertiary text-body border ms-1">{{ $user->cryptoPayments->count() }}</span>
                            @endif
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="privacy-tab" data-bs-toggle="tab" data-bs-target="#privacy" type="button" role="tab">
                            <i class="bi bi-shield-lock me-1"></i>{{ __('Privacy Settings') }}
                        </button>
                    </li>
                </ul>
            </div>

            <div class="card-body p-4">
                <div class="tab-content" id="userTabsContent">
                    {{-- Tab 1: Overview --}}
                    <div class="tab-pane fade show active" id="overview" role="tabpanel">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="stat-tile d-flex align-items-center gap-3 h-100">
                                    <div class="stat-tile-icon bg-primary-subtle text-primary-emphasis">
                                        <i class="bi bi-gem"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="small text-muted">{{ __('Current Plan') }}</div>
                                        @if($user->activeSubscription && $user->activeSubscription->plan)
                                            <div class="fs-5 fw-bold text-primary text-truncate">{{ $user->activeSubscription->plan->name }}</div>
                                            <small class="text-muted">
                                                {{ __('Expires') }}: {{ $user->activeSubscription->ends_at?->format('d M, Y') ?? '-' }}
                                            </small>
                                        @else
                                            <div class="fs-5 fw-bold text-secondary">{{ __('Free Plan') }}</div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="stat-tile d-flex align-items-center gap-3 h-100">
                                    <div class="stat-tile-icon bg-warning-subtle text-warning-emphasis">
                                        <i class="bi bi-star-fill"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="small text-muted">{{ __('Star Balance') }}</div>
                                        <div class="fs-4 fw-bold lh-sm">{{ number_format($user->stars ? $user->stars->balance : 0) }}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 mt-4">
                                <h6 class="fw-bold mb-2 pb-2 border-bottom">{{ __('Recent Activity') }}</h6>
                                <p class="text-muted small mb-0">{{ __('No recent activity to show.') }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Tab 2: Payments --}}
                    <div class="tab-pane fade" id="payments" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 user-pay-table">
                                <thead>
                                    <tr>
                                        <th>{{ __('Plan') }}</th>
                                        <th>{{ __('Amount') }}</th>
                                        <th>{{ __('TX Hash') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th class="text-end">{{ __('Date') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($user->cryptoPayments as $payment)
                                    <tr>
                                        <td>
                                            <span class="badge rounded-pill bg-primary-subtle text-primary-emphasis px-2.5 py-1">
                                                {{ $payment->plan->name ?? '-' }}
                                            </span>
                                        </td>
                                        <td class="fw-bold text-success">${{ number_format($payment->amount, 2) }}</td>
                                        <td>
                                            <span class="tx-hash-pill" title="{{ $payment->tx_hash }}">
                                                <span>{{ Str::limit($payment->tx_hash, 12) }}</span>
                                                <button type="button" class="tx-copy-btn js-copy-tx" data-tx="{{ $payment->tx_hash }}">
                                                    <i class="bi bi-copy"></i>
                                                </button>
                                            </span>
                                        </td>
                                        <td>
                                            @if($payment->status == 'pending')
                                                <span class="badge rounded-pill bg-warning-subtle text-warning-emphasis px-2.5 py-1">{{ __('Pending') }}</span>
                                            @elseif($payment->status == 'completed')
                                                <span class="badge rounded-pill bg-success-subtle text-success-emphasis px-2.5 py-1">{{ __('Completed') }}</span>
                                            @else
                                                <span class="badge rounded-pill bg-danger-subtle text-danger-emphasis px-2.5 py-1">{{ __('Failed') }}</span>
                                            @endif
                                        </td>
                                        <td class="text-end text-nowrap">{{ $payment->created_at->format('d M, Y') }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-5">
                                            <i class="bi bi-currency-bitcoin fs-2 d-block mb-2 opacity-50"></i>
                                            {{ __('No payments found.') }}
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Tab 3: Privacy Settings --}}
                    <div class="tab-pane fade" id="privacy" role="tabpanel">
                        @if($user->privacySetting)
                        <div class="d-grid gap-2">
                            <div class="privacy-row">
                                <span class="d-flex align-items-center gap-2 fw-medium">
                                    <i class="bi bi-eye-slash text-muted"></i>
                                    <span>{{ __('Hide Profile Photo') }}</span>
                                </span>
                                <i class="bi {{ $user->privacySetting->hide_profile_photo ? 'bi-check-circle-fill text-success' : 'bi-dash-circle text-muted opacity-50' }} fs-5"></i>
                            </div>

                            <div class="privacy-row">
                                <span class="d-flex align-items-center gap-2 fw-medium">
                                    <i class="bi bi-clock-history text-muted"></i>
                                    <span>{{ __('Hide Last Seen') }}</span>
                                </span>
                                <i class="bi {{ $user->privacySetting->hide_last_seen ? 'bi-check-circle-fill text-success' : 'bi-dash-circle text-muted opacity-50' }} fs-5"></i>
                            </div>

                            <div class="privacy-row">
                                <span class="d-flex align-items-center gap-2 fw-medium">
                                    <i class="bi bi-incognito text-muted"></i>
                                    <span>{{ __('Anonymous Mode') }}</span>
                                </span>
                                <i class="bi {{ $user->privacySetting->anonymous_mode ? 'bi-check-circle-fill text-success' : 'bi-dash-circle text-muted opacity-50' }} fs-5"></i>
                            </div>

                            <div class="privacy-row">
                                <span class="d-flex align-items-center gap-2 fw-medium">
                                    <i class="bi bi-shield-lock text-muted"></i>
                                    <span>{{ __('Block Screenshots') }}</span>
                                </span>
                                <i class="bi {{ $user->privacySetting->block_screenshots ? 'bi-check-circle-fill text-success' : 'bi-dash-circle text-muted opacity-50' }} fs-5"></i>
                            </div>
                        </div>
                        @else
                        <div class="alert alert-info border-0 rounded-3 py-2 px-3 mb-0 small">
                            <i class="bi bi-info-circle me-1"></i>{{ __('No privacy settings configured yet.') }}
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.js-copy-tx').forEach((btn) => {
    btn.addEventListener('click', () => {
        const tx = btn.dataset.tx;
        if (!tx || !navigator.clipboard) return;

        navigator.clipboard.writeText(tx).then(() => {
            const icon = btn.querySelector('i');
            icon.className = 'bi bi-check2 text-success';
            setTimeout(() => {
                icon.className = 'bi bi-copy';
            }, 1500);
        });
    });
});
</script>
@endpush