@extends('admin.layouts.master')

@section('title', __('Payments'))
@section('header', __('Crypto Payments'))

@push('styles')
<style>
    .pay-stat-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        flex-shrink: 0;
    }

    .pay-table thead th {
        font-size: .75rem;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
        color: var(--bs-secondary-color);
        background-color: var(--bs-tertiary-bg);
        border-bottom-width: 1px;
        padding-top: .85rem;
        padding-bottom: .85rem;
    }

    .pay-avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        object-fit: cover;
        flex-shrink: 0;
    }

    .pay-avatar-fallback {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: .85rem;
        flex-shrink: 0;
    }

    .tx-hash-pill {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-size: .82rem;
        background: var(--bs-tertiary-bg);
        border: 1px solid var(--bs-border-color-translucent);
        padding: .25rem .55rem;
        border-radius: 8px;
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

    .pay-empty-icon {
        width: 64px;
        height: 64px;
        border-radius: 16px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.75rem;
    }
</style>
@endpush

@section('content')
@php
    $statusCounts = \App\Models\CryptoPayment::selectRaw('status, COUNT(*) as total')
        ->groupBy('status')
        ->pluck('total', 'status');

    $totalCount     = $statusCounts->sum();
    $pendingCount   = $statusCounts['pending'] ?? 0;
    $completedCount = $statusCounts['completed'] ?? 0;
    $failedCount    = $statusCounts->except(['pending', 'completed'])->sum();
@endphp

{{-- Summary Cards --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="card shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="pay-stat-icon bg-primary-subtle text-primary-emphasis">
                    <i class="bi bi-currency-bitcoin"></i>
                </div>
                <div class="min-w-0">
                    <div class="small text-muted text-truncate">{{ __('All Payments') }}</div>
                    <div class="fs-4 fw-bold lh-sm">{{ number_format($totalCount) }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-xl-3">
        <div class="card shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="pay-stat-icon bg-warning-subtle text-warning-emphasis">
                    <i class="bi bi-clock-history"></i>
                </div>
                <div class="min-w-0">
                    <div class="small text-muted text-truncate">{{ __('Pending') }}</div>
                    <div class="fs-4 fw-bold lh-sm">{{ number_format($pendingCount) }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-xl-3">
        <div class="card shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="pay-stat-icon bg-success-subtle text-success-emphasis">
                    <i class="bi bi-check-circle"></i>
                </div>
                <div class="min-w-0">
                    <div class="small text-muted text-truncate">{{ __('Completed') }}</div>
                    <div class="fs-4 fw-bold lh-sm">{{ number_format($completedCount) }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-xl-3">
        <div class="card shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="pay-stat-icon bg-danger-subtle text-danger-emphasis">
                    <i class="bi bi-x-circle"></i>
                </div>
                <div class="min-w-0">
                    <div class="small text-muted text-truncate">{{ __('Failed') }}</div>
                    <div class="fs-4 fw-bold lh-sm">{{ number_format($failedCount) }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Payments Table Card --}}
<div class="card shadow-sm overflow-hidden">
    <div class="card-header bg-transparent py-3 px-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h5 class="mb-0 fw-bold d-flex align-items-center gap-2">
            <i class="bi bi-currency-bitcoin text-warning"></i>
            <span>{{ __('All Payments') }}</span>
        </h5>
        <span class="badge bg-body-tertiary text-body border rounded-pill px-3 py-2 fw-semibold">
            {{ number_format($payments->total()) }}
        </span>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 pay-table">
                <thead>
                    <tr>
                        <th class="ps-4 text-nowrap">ID</th>
                        <th>{{ __('User') }}</th>
                        <th class="text-nowrap">{{ __('Plan') }}</th>
                        <th class="text-nowrap">{{ __('Amount') }}</th>
                        <th class="text-nowrap">{{ __('TX Hash') }}</th>
                        <th class="text-nowrap">{{ __('Status') }}</th>
                        <th class="text-nowrap">{{ __('Date') }}</th>
                        <th class="text-end pe-4 text-nowrap">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $payment)
                    @php
                        $userName = $payment->user->name ?? __('Deleted User');
                        $initial  = mb_strtoupper(mb_substr($userName, 0, 1));
                    @endphp
                    <tr>
                        <td class="ps-4 text-muted fw-medium text-nowrap">#{{ $payment->id }}</td>

                        <td>
                            <div class="d-flex align-items-center gap-2">
                                @if($payment->user && $payment->user->avatar)
                                    <img src="{{ asset('storage/'.$payment->user->avatar) }}" class="pay-avatar" alt="{{ $userName }}">
                                @else
                                    <div class="pay-avatar-fallback bg-primary-subtle text-primary-emphasis">{{ $initial }}</div>
                                @endif
                                <div class="min-w-0">
                                    <div class="fw-semibold text-truncate">{{ $userName }}</div>
                                    <small class="text-muted font-monospace">{{ $payment->user->phone ?? '-' }}</small>
                                </div>
                            </div>
                        </td>

                        <td class="text-nowrap">
                            <span class="badge rounded-pill bg-primary-subtle text-primary-emphasis px-2.5 py-1">
                                {{ $payment->plan->name ?? '-' }}
                            </span>
                        </td>

                        <td class="fw-bold text-success text-nowrap">
                            ${{ number_format($payment->amount, 2) }}
                        </td>

                        <td class="text-nowrap">
                            <span class="tx-hash-pill" title="{{ $payment->tx_hash }}">
                                <span>{{ Str::limit($payment->tx_hash, 16) }}</span>
                                <button type="button" class="tx-copy-btn js-copy-tx" data-tx="{{ $payment->tx_hash }}">
                                    <i class="bi bi-copy"></i>
                                </button>
                            </span>
                        </td>

                        <td class="text-nowrap">
                            @if($payment->status == 'pending')
                                <span class="badge rounded-pill bg-warning-subtle text-warning-emphasis px-2.5 py-1">
                                    <i class="bi bi-clock-history me-1"></i>{{ __('Pending') }}
                                </span>
                            @elseif($payment->status == 'completed')
                                <span class="badge rounded-pill bg-success-subtle text-success-emphasis px-2.5 py-1">
                                    <i class="bi bi-check-circle me-1"></i>{{ __('Completed') }}
                                </span>
                            @else
                                <span class="badge rounded-pill bg-danger-subtle text-danger-emphasis px-2.5 py-1">
                                    <i class="bi bi-x-circle me-1"></i>{{ __('Failed') }}
                                </span>
                            @endif
                        </td>

                        <td class="text-nowrap">
                            <div class="fw-medium">{{ $payment->created_at->format('d M, Y') }}</div>
                            <small class="text-muted">{{ $payment->created_at->format('H:i') }}</small>
                        </td>

                        <td class="text-end pe-4 text-nowrap">
                            @if($payment->status == 'pending')
                                <div class="d-inline-flex align-items-center gap-1">
                                    <form action="{{ route('admin.payments.approve', $payment) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-outline-success rounded-3" title="{{ __('Approve') }}" onclick="return confirm('{{ __('Are you sure you want to approve this payment?') }}');">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                    </form>

                                    <form action="{{ route('admin.payments.reject', $payment) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-3" title="{{ __('Reject') }}" onclick="return confirm('{{ __('Are you sure you want to reject this payment?') }}');">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </form>
                                </div>
                            @else
                                <span class="btn btn-sm btn-outline-secondary rounded-3 disabled">
                                    <i class="bi bi-lock"></i>
                                </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <div class="pay-empty-icon bg-body-tertiary text-muted mx-auto mb-3">
                                <i class="bi bi-currency-bitcoin"></i>
                            </div>
                            <div class="text-muted fw-medium">{{ __('No payments found.') }}</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($payments->hasPages())
    <div class="card-footer bg-transparent py-3 px-4">
        {{ $payments->links('pagination::bootstrap-5') }}
    </div>
    @endif
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