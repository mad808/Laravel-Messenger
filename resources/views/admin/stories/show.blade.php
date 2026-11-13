@extends('admin.layouts.master')

@section('title', __('Story Details'))
@section('header', __('Story Details'))

@push('styles')
<style>
    .story-stage {
        background: #0d0f14;
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 320px;
        max-height: 70vh;
        overflow: hidden;
    }

    .story-stage img,
    .story-stage video {
        max-height: 70vh;
        max-width: 100%;
        width: auto;
        object-fit: contain;
        display: block;
    }

    .story-detail-avatar {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        object-fit: cover;
        flex-shrink: 0;
    }

    .story-detail-avatar-fallback {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        flex-shrink: 0;
    }
</style>
@endpush

@section('content')
@php
    $isExpired = $story->expires_at && $story->expires_at < now();
    $userName  = $story->user->name ?? __('Deleted User');
    $initial   = mb_strtoupper(mb_substr($userName, 0, 1));
@endphp

<div class="card shadow-sm overflow-hidden mx-auto" style="max-width: 680px;">
    <div class="card-header bg-transparent py-3 px-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div class="d-flex align-items-center gap-3 min-w-0">
            @if($story->user && $story->user->avatar)
                <img src="{{ asset('storage/'.$story->user->avatar) }}" class="story-detail-avatar" alt="{{ $userName }}">
            @else
                <div class="story-detail-avatar-fallback bg-primary-subtle text-primary-emphasis">{{ $initial }}</div>
            @endif
            <div class="min-w-0">
                <h6 class="mb-0 fw-bold text-truncate">{{ __('Story of') }}: {{ $userName }}</h6>
                <small class="text-muted">{{ $story->created_at->diffForHumans() }}</small>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2">
            @if($isExpired)
                <span class="badge rounded-pill bg-secondary-subtle text-secondary-emphasis px-2.5 py-1">{{ __('Expired') }}</span>
            @else
                <span class="badge rounded-pill bg-success-subtle text-success-emphasis px-2.5 py-1">
                    <i class="bi bi-broadcast me-1"></i>{{ __('Active') }}
                </span>
            @endif

            <a href="{{ route('admin.stories.index') }}" class="btn btn-sm btn-outline-secondary rounded-3 px-3">
                <i class="bi bi-arrow-left me-1"></i>{{ __('Back') }}
            </a>
        </div>
    </div>

    <div class="story-stage">
        @if($story->type == 'image')
            <img src="{{ asset('storage/'.$story->media_path) }}" alt="Story">
        @else
            <video src="{{ asset('storage/'.$story->media_path) }}" controls playsinline></video>
        @endif
    </div>

    <div class="card-footer bg-transparent py-3 px-4 d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div class="d-flex flex-wrap gap-3 small">
            <span>
                <i class="bi bi-calendar-plus text-muted me-1"></i>
                <strong>{{ __('Created') }}:</strong> {{ $story->created_at->format('d M, Y H:i') }}
            </span>
            <span>
                <i class="bi bi-clock text-muted me-1"></i>
                <strong>{{ __('Expires') }}:</strong> {{ $story->expires_at ? $story->expires_at->format('d M, Y H:i') : '-' }}
            </span>
        </div>

        <form action="{{ route('admin.stories.destroy', $story) }}" method="POST" onsubmit="return confirm('{{ __('Are you sure you want to delete this story?') }}');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-sm btn-outline-danger rounded-3">
                <i class="bi bi-trash me-1"></i>{{ __('Delete') }}
            </button>
        </form>
    </div>
</div>
@endsection