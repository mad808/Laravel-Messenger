@extends('admin.layouts.master')

@section('title', __('Conversations'))
@section('header', __('All Conversations'))

@push('styles')
<style>
    .conv-stat-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        flex-shrink: 0;
    }

    .conv-table thead th {
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

    .conv-avatar {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        object-fit: cover;
        flex-shrink: 0;
    }

    .conv-avatar-fallback {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1rem;
        flex-shrink: 0;
    }

    .conv-desc {
        max-width: 280px;
    }

    .conv-empty-icon {
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
    $typeCounts = \App\Models\Conversation::selectRaw('type, COUNT(*) as total')
        ->groupBy('type')
        ->pluck('total', 'type');

    $totalCount   = $typeCounts->sum();
    $privateCount = $typeCounts['private'] ?? 0;
    $groupCount   = $typeCounts['group'] ?? 0;
    $channelCount = $typeCounts->except(['private', 'group'])->sum();
@endphp

{{-- Summary Cards --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="card shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="conv-stat-icon bg-primary-subtle text-primary-emphasis">
                    <i class="bi bi-chat-square-text"></i>
                </div>
                <div class="min-w-0">
                    <div class="small text-muted text-truncate">{{ __('Conversations') }}</div>
                    <div class="fs-4 fw-bold lh-sm">{{ number_format($totalCount) }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-xl-3">
        <div class="card shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="conv-stat-icon bg-secondary-subtle text-secondary-emphasis">
                    <i class="bi bi-person"></i>
                </div>
                <div class="min-w-0">
                    <div class="small text-muted text-truncate">{{ __('Private') }}</div>
                    <div class="fs-4 fw-bold lh-sm">{{ number_format($privateCount) }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-xl-3">
        <div class="card shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="conv-stat-icon bg-success-subtle text-success-emphasis">
                    <i class="bi bi-people"></i>
                </div>
                <div class="min-w-0">
                    <div class="small text-muted text-truncate">{{ __('Group') }}</div>
                    <div class="fs-4 fw-bold lh-sm">{{ number_format($groupCount) }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-xl-3">
        <div class="card shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="conv-stat-icon bg-info-subtle text-info-emphasis">
                    <i class="bi bi-megaphone"></i>
                </div>
                <div class="min-w-0">
                    <div class="small text-muted text-truncate">{{ __('Channel') }}</div>
                    <div class="fs-4 fw-bold lh-sm">{{ number_format($channelCount) }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Conversations Table Card --}}
<div class="card shadow-sm overflow-hidden">
    <div class="card-header bg-transparent py-3 px-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h5 class="mb-0 fw-bold d-flex align-items-center gap-2">
            <i class="bi bi-chat-square-text text-primary"></i>
            <span>{{ __('Chats, Groups & Channels') }}</span>
        </h5>
        <span class="badge bg-body-tertiary text-body border rounded-pill px-3 py-2 fw-semibold">
            {{ number_format($conversations->total()) }}
        </span>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 conv-table">
                <thead>
                    <tr>
                        <th class="ps-4 text-nowrap">ID</th>
                        <th>{{ __('Conversation') }}</th>
                        <th class="text-nowrap">{{ __('Type') }}</th>
                        <th class="text-nowrap">{{ __('Owner') }}</th>
                        <th class="text-nowrap">{{ __('Messages') }}</th>
                        <th class="text-nowrap">{{ __('Created At') }}</th>
                        <th class="text-end pe-4 text-nowrap">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($conversations as $conversation)
                    @php
                        $title   = $conversation->title ?? __('Private Chat');
                        $initial = mb_strtoupper(mb_substr($conversation->title ?? $conversation->type, 0, 1));
                        $avatarTone = match($conversation->type) {
                            'private' => 'bg-secondary-subtle text-secondary-emphasis',
                            'group'   => 'bg-primary-subtle text-primary-emphasis',
                            default   => 'bg-info-subtle text-info-emphasis',
                        };
                    @endphp
                    <tr>
                        <td class="ps-4 text-muted fw-medium text-nowrap">#{{ $conversation->id }}</td>

                        <td>
                            <div class="d-flex align-items-center gap-3">
                                @if($conversation->avatar)
                                    <img src="{{ asset('storage/'.$conversation->avatar) }}"
                                         class="conv-avatar"
                                         alt="{{ $title }}"
                                         onerror="this.classList.add('d-none'); this.nextElementSibling.classList.remove('d-none');">
                                    <div class="conv-avatar-fallback {{ $avatarTone }} d-none">{{ $initial }}</div>
                                @else
                                    <div class="conv-avatar-fallback {{ $avatarTone }}">{{ $initial }}</div>
                                @endif

                                <div class="min-w-0">
                                    <div class="fw-semibold text-truncate conv-desc">{{ $title }}</div>
                                    <small class="text-muted d-block text-truncate conv-desc" title="{{ $conversation->description }}">
                                        {{ $conversation->description ?: __('No description') }}
                                    </small>
                                </div>
                            </div>
                        </td>

                        <td class="text-nowrap">
                            @if($conversation->type == 'private')
                                <span class="badge rounded-pill bg-secondary-subtle text-secondary-emphasis px-2.5 py-1">
                                    <i class="bi bi-person me-1"></i>{{ __('Private') }}
                                </span>
                            @elseif($conversation->type == 'group')
                                <span class="badge rounded-pill bg-primary-subtle text-primary-emphasis px-2.5 py-1">
                                    <i class="bi bi-people me-1"></i>{{ __('Group') }}
                                </span>
                            @else
                                <span class="badge rounded-pill bg-info-subtle text-info-emphasis px-2.5 py-1">
                                    <i class="bi bi-megaphone me-1"></i>{{ __('Channel') }}
                                </span>
                            @endif
                        </td>

                        <td class="text-nowrap">
                            @if($conversation->owner)
                                <span class="fw-medium">
                                    <i class="bi bi-person-circle text-muted me-1"></i>{{ $conversation->owner->name }}
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>

                        <td class="text-nowrap">
                            <span class="badge rounded-pill bg-body-tertiary text-body border px-2.5 py-1 fw-semibold">
                                <i class="bi bi-chat-dots text-muted me-1"></i>{{ number_format($conversation->messages_count) }}
                            </span>
                        </td>

                        <td class="text-nowrap">
                            <div class="fw-medium">{{ $conversation->created_at->format('d M, Y') }}</div>
                            <small class="text-muted">{{ $conversation->created_at->format('H:i') }}</small>
                        </td>

                        <td class="text-end pe-4 text-nowrap">
                            <div class="d-inline-flex align-items-center gap-1">
                                <a href="{{ route('admin.conversations.show', $conversation) }}" class="btn btn-sm btn-outline-primary rounded-3" title="{{ __('View Messages') }}">
                                    <i class="bi bi-eye"></i>
                                </a>

                                <form action="{{ route('admin.conversations.destroy', $conversation) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Are you sure you want to delete this entire conversation?') }}');">
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
                        <td colspan="7" class="text-center py-5">
                            <div class="conv-empty-icon bg-body-tertiary text-muted mx-auto mb-3">
                                <i class="bi bi-chat-square"></i>
                            </div>
                            <div class="text-muted fw-medium">{{ __('No conversations found.') }}</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($conversations->hasPages())
    <div class="card-footer bg-transparent py-3 px-4">
        {{ $conversations->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
@endsection