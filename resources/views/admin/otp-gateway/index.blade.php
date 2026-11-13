@extends('admin.layouts.master')

@section('title', __('OTP Gateway'))
@section('header', __('OTP Gateway Logs'))

@push('styles')
<style>
    .otp-stat-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        flex-shrink: 0;
    }

    .otp-message-col {
        min-width: 240px;
        max-width: 380px;
    }

    .otp-message-preview {
        display: -webkit-box;
        -webkit-line-clamp: 1;
        -webkit-box-orient: vertical;
        overflow: hidden;
        word-break: break-word;
    }

    .otp-table thead th {
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

    .otp-empty-icon {
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
    $statusCounts = \App\Models\OtpGatewayMessage::selectRaw('status, COUNT(*) as total')
        ->groupBy('status')
        ->pluck('total', 'status');

    $pendingCount = $statusCounts[0] ?? 0;
    $processingCount = $statusCounts[1] ?? 0;
    $sentCount = $statusCounts[2] ?? 0;
    $failedCount = $statusCounts->except([0, 1, 2])->sum();
@endphp

{{-- Summary Cards --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="card shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="otp-stat-icon bg-warning-subtle text-warning-emphasis">
                    <i class="bi bi-hourglass-split"></i>
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
                <div class="otp-stat-icon bg-info-subtle text-info-emphasis">
                    <i class="bi bi-arrow-repeat"></i>
                </div>
                <div class="min-w-0">
                    <div class="small text-muted text-truncate">{{ __('Processing') }}</div>
                    <div class="fs-4 fw-bold lh-sm">{{ number_format($processingCount) }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-xl-3">
        <div class="card shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="otp-stat-icon bg-success-subtle text-success-emphasis">
                    <i class="bi bi-check-circle"></i>
                </div>
                <div class="min-w-0">
                    <div class="small text-muted text-truncate">{{ __('Sent') }}</div>
                    <div class="fs-4 fw-bold lh-sm">{{ number_format($sentCount) }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-xl-3">
        <div class="card shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="otp-stat-icon bg-danger-subtle text-danger-emphasis">
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

{{-- Logs Table Card --}}
<div class="card shadow-sm overflow-hidden">
    <div class="card-header bg-transparent py-3 px-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h5 class="mb-0 fw-bold d-flex align-items-center gap-2">
            <span class="d-inline-grid place-items-center text-primary">
                <i class="bi bi-shield-lock"></i>
            </span>
            <span>{{ __('SMS Gateway Messages') }}</span>
        </h5>
        <span class="badge bg-body-tertiary text-body border rounded-pill px-3 py-2 fw-semibold">
            {{ number_format($messages->total()) }}
        </span>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 otp-table">
                <thead>
                    <tr>
                        <th class="ps-4 text-nowrap">ID</th>
                        <th class="text-nowrap">{{ __('Phone') }}</th>
                        <th>{{ __('Message') }}</th>
                        <th class="text-nowrap">{{ __('Status') }}</th>
                        <th class="text-nowrap">{{ __('Created At') }}</th>
                        <th class="text-end pe-4 text-nowrap">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($messages as $message)
                    <tr>
                        <td class="ps-4 text-muted fw-medium text-nowrap">#{{ $message->id }}</td>

                        <td class="text-nowrap">
                            <span class="font-monospace fw-medium">
                                <i class="bi bi-telephone text-muted me-1"></i>{{ $message->phone }}
                            </span>
                        </td>

                        <td class="otp-message-col">
                            <div class="otp-message-preview" title="{{ $message->message }}">
                                {{ $message->message }}
                            </div>
                            @if($message->error_message)
                                <small class="text-danger d-flex align-items-center gap-1 mt-1">
                                    <i class="bi bi-exclamation-triangle-fill flex-shrink-0"></i>
                                    <span class="text-truncate">{{ Str::limit($message->error_message, 50) }}</span>
                                </small>
                            @endif
                        </td>

                        <td class="text-nowrap">
                            @if($message->status == 0)
                                <span class="badge rounded-pill bg-warning-subtle text-warning-emphasis px-2.5 py-1">
                                    <i class="bi bi-hourglass-split me-1"></i>{{ __('Pending') }}
                                </span>
                            @elseif($message->status == 1)
                                <span class="badge rounded-pill bg-info-subtle text-info-emphasis px-2.5 py-1">
                                    <i class="bi bi-arrow-repeat me-1"></i>{{ __('Processing') }}
                                </span>
                            @elseif($message->status == 2)
                                <span class="badge rounded-pill bg-success-subtle text-success-emphasis px-2.5 py-1">
                                    <i class="bi bi-check-circle me-1"></i>{{ __('Sent') }}
                                </span>
                            @else
                                <span class="badge rounded-pill bg-danger-subtle text-danger-emphasis px-2.5 py-1">
                                    <i class="bi bi-x-circle me-1"></i>{{ __('Failed') }}
                                </span>
                            @endif
                        </td>

                        <td class="text-nowrap">
                            <div class="fw-medium">{{ $message->created_at->format('d M, Y') }}</div>
                            <small class="text-muted">{{ $message->created_at->format('H:i') }}</small>
                        </td>

                        <td class="text-end pe-4 text-nowrap">
                            <div class="d-inline-flex align-items-center gap-1">
                                <button type="button"
                                        class="btn btn-sm btn-outline-secondary rounded-3 js-view-otp"
                                        data-bs-toggle="modal"
                                        data-bs-target="#otpLogModal"
                                        data-id="#{{ $message->id }}"
                                        data-phone="{{ $message->phone }}"
                                        data-message="{{ $message->message }}"
                                        data-error="{{ $message->error_message }}"
                                        data-date="{{ $message->created_at->format('d M, Y H:i') }}">
                                    <i class="bi bi-eye"></i>
                                </button>

                                @if($message->status != 2)
                                <form action="{{ route('admin.otp.retry', $message) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm btn-outline-primary rounded-3" title="{{ __('Retry') }}">
                                        <i class="bi bi-arrow-clockwise"></i>
                                    </button>
                                </form>
                                @endif

                                <form action="{{ route('admin.otp.destroy', $message) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Are you sure you want to delete this log?') }}');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-3" title="{{ __('Delete') }}">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <div class="otp-empty-icon bg-body-tertiary text-muted mx-auto mb-3">
                                <i class="bi bi-shield-lock"></i>
                            </div>
                            <div class="text-muted fw-medium">{{ __('No SMS logs found.') }}</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($messages->hasPages())
    <div class="card-footer bg-transparent py-3 px-4">
        {{ $messages->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>

{{-- Detail Modal --}}
<div class="modal fade" id="otpLogModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4">
            <div class="modal-header border-bottom-0 pb-0">
                <h6 class="modal-title fw-bold d-flex align-items-center gap-2">
                    <i class="bi bi-shield-lock text-primary"></i>
                    <span id="modalOtpId"></span>
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-3">
                <div class="d-flex justify-content-between align-items-center mb-3 small text-muted">
                    <span class="font-monospace fw-semibold text-body" id="modalOtpPhone"></span>
                    <span id="modalOtpDate"></span>
                </div>

                <div class="mb-2 small fw-semibold text-muted">{{ __('Message') }}</div>
                <div class="p-3 rounded-3 bg-body-tertiary border small text-break" id="modalOtpMessage"></div>

                <div class="alert alert-danger mt-3 mb-0 py-2 px-3 small d-none text-break" id="modalOtpErrorWrap">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i>
                    <span id="modalOtpError"></span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.js-view-otp').forEach((btn) => {
    btn.addEventListener('click', () => {
        document.getElementById('modalOtpId').textContent = btn.dataset.id;
        document.getElementById('modalOtpPhone').textContent = btn.dataset.phone;
        document.getElementById('modalOtpDate').textContent = btn.dataset.date;
        document.getElementById('modalOtpMessage').textContent = btn.dataset.message;

        const errWrap = document.getElementById('modalOtpErrorWrap');
        const errText = document.getElementById('modalOtpError');

        if (btn.dataset.error) {
            errText.textContent = btn.dataset.error;
            errWrap.classList.remove('d-none');
        } else {
            errText.textContent = '';
            errWrap.classList.add('d-none');
        }
    });
});
</script>
@endpush