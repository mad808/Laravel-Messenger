@extends('admin.layouts.master')

@section('title', __('Stories'))
@section('header', __('Stories'))

@push('styles')
<style>
    .story-stat-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        flex-shrink: 0;
    }

    .story-card {
        overflow: hidden;
        transition: transform .2s ease, box-shadow .2s ease;
    }

    .story-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 26px rgba(0, 0, 0, .09) !important;
    }

    [data-bs-theme="dark"] .story-card:hover {
        box-shadow: 0 10px 26px rgba(0, 0, 0, .38) !important;
    }

    .story-media-wrap {
        position: relative;
        height: 230px;
        background: #111318;
        overflow: hidden;
        cursor: pointer;
    }

    .story-media {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
        transition: transform .25s ease;
    }

    .story-media-wrap:hover .story-media {
        transform: scale(1.03);
    }

    .story-media-fallback {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--bs-tertiary-bg);
        color: var(--bs-secondary-color);
    }

    .story-top-badges {
        position: absolute;
        top: .75rem;
        left: .75rem;
        right: .75rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: .5rem;
        z-index: 2;
    }

    .story-glass-pill {
        background: rgba(0, 0, 0, .58);
        backdrop-filter: blur(6px);
        color: #fff;
        font-size: .72rem;
        font-weight: 600;
        padding: .3rem .6rem;
        border-radius: 999px;
    }

    .story-play-btn {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 52px;
        height: 52px;
        border-radius: 50%;
        background: rgba(0, 0, 0, .55);
        backdrop-filter: blur(6px);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        border: 1px solid rgba(255, 255, 255, .25);
        pointer-events: none;
        z-index: 2;
    }

    .story-user {
        position: absolute;
        inset: auto 0 0 0;
        padding: 2rem .85rem .75rem;
        background: linear-gradient(to top, rgba(0, 0, 0, .82), rgba(0, 0, 0, .35) 60%, transparent);
        color: #fff;
        z-index: 2;
    }

    .story-avatar {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid rgba(255, 255, 255, .85);
        flex-shrink: 0;
    }

    .story-avatar-fallback {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: .85rem;
        background: var(--bs-primary);
        color: #fff;
        border: 2px solid rgba(255, 255, 255, .85);
        flex-shrink: 0;
    }

    .story-empty-icon {
        width: 64px;
        height: 64px;
        border-radius: 16px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.75rem;
    }

    .story-modal-stage {
        background: #0b0d12;
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 360px;
        max-height: 78vh;
        overflow: hidden;
    }

    .story-modal-stage img,
    .story-modal-stage video {
        max-height: 78vh;
        width: 100%;
        object-fit: contain;
        display: block;
    }
</style>
@endpush

@section('content')
@php
    $storyStats = \App\Models\Story::selectRaw('
        COUNT(*) as total,
        SUM(CASE WHEN expires_at IS NOT NULL AND expires_at <= ? THEN 1 ELSE 0 END) as expired
    ', [now()])->first();

    $totalStories   = (int) ($storyStats->total ?? 0);
    $expiredStories = (int) ($storyStats->expired ?? 0);
    $activeStories  = max(0, $totalStories - $expiredStories);
@endphp

{{-- Summary Cards --}}
<div class="row g-3 mb-4">
    <div class="col-4">
        <div class="card shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="story-stat-icon bg-primary-subtle text-primary-emphasis">
                    <i class="bi bi-camera-reels"></i>
                </div>
                <div class="min-w-0">
                    <div class="small text-muted text-truncate">{{ __('Stories') }}</div>
                    <div class="fs-4 fw-bold lh-sm">{{ number_format($totalStories) }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-4">
        <div class="card shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="story-stat-icon bg-success-subtle text-success-emphasis">
                    <i class="bi bi-broadcast"></i>
                </div>
                <div class="min-w-0">
                    <div class="small text-muted text-truncate">{{ __('Active') }}</div>
                    <div class="fs-4 fw-bold lh-sm">{{ number_format($activeStories) }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-4">
        <div class="card shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="story-stat-icon bg-secondary-subtle text-secondary-emphasis">
                    <i class="bi bi-clock-history"></i>
                </div>
                <div class="min-w-0">
                    <div class="small text-muted text-truncate">{{ __('Expired') }}</div>
                    <div class="fs-4 fw-bold lh-sm">{{ number_format($expiredStories) }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Section Title --}}
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0 fw-bold d-flex align-items-center gap-2">
        <i class="bi bi-camera-reels text-primary"></i>
        <span>{{ __('Active & Archived Stories') }}</span>
    </h5>
</div>

{{-- Stories Grid --}}
<div class="row g-4">
    @forelse($stories as $story)
    @php
        $isExpired = $story->expires_at && $story->expires_at < now();
        $userName  = $story->user->name ?? __('Deleted User');
        $initial   = mb_strtoupper(mb_substr($userName, 0, 1));

        $rawPath   = (string) $story->media_path;
        $mediaSrc  = \Illuminate\Support\Str::startsWith($rawPath, ['http://', 'https://', '/'])
            ? $rawPath
            : asset(\Illuminate\Support\Str::startsWith($rawPath, 'storage/') ? $rawPath : 'storage/' . ltrim($rawPath, '/'));

        $ext       = strtolower(pathinfo($rawPath, PATHINFO_EXTENSION));
        $isImage   = \Illuminate\Support\Str::startsWith((string) $story->type, 'image')
                     || $story->type === 'photo'
                     || in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'], true);
    @endphp

    <div class="col-sm-6 col-lg-4 col-xl-3">
        <div class="card story-card shadow-sm h-100">
            {{-- Clickable Media Wrap (Opens Larger Preview Modal) --}}
            <div class="story-media-wrap js-story-preview"
                 data-bs-toggle="modal"
                 data-bs-target="#storyPreviewModal"
                 data-type="{{ $isImage ? 'image' : 'video' }}"
                 data-src="{{ $mediaSrc }}"
                 data-user="{{ $userName }}"
                 data-date="{{ $story->created_at->format('d M, Y H:i') }}">

                <div class="story-top-badges">
                    <span class="story-glass-pill">
                        @if($isImage)
                            <i class="bi bi-image me-1"></i>IMG
                        @else
                            <i class="bi bi-camera-video-fill me-1"></i>MP4
                        @endif
                    </span>

                    @if($isExpired)
                        <span class="badge rounded-pill bg-secondary px-2.5 py-1">{{ __('Expired') }}</span>
                    @else
                        <span class="badge rounded-pill bg-success px-2.5 py-1">
                            <i class="bi bi-broadcast me-1"></i>{{ __('Active') }}
                        </span>
                    @endif
                </div>

                @if($isImage)
                    <img src="{{ $mediaSrc }}"
                         class="story-media"
                         alt="Story"
                         onerror="this.classList.add('d-none'); this.nextElementSibling.classList.remove('d-none');">
                    <div class="story-media-fallback d-none">
                        <i class="bi bi-image fs-1 opacity-50"></i>
                    </div>
                @else
                    <video src="{{ $mediaSrc }}#t=0.5"
                           class="story-media"
                           preload="metadata"
                           muted
                           playsinline></video>
                    <div class="story-play-btn">
                        <i class="bi bi-play-fill ms-1"></i>
                    </div>
                @endif

                <div class="story-user">
                    <div class="d-flex align-items-center gap-2">
                        @if($story->user && $story->user->avatar)
                            <img src="{{ asset('storage/'.$story->user->avatar) }}" class="story-avatar" alt="{{ $userName }}">
                        @else
                            <div class="story-avatar-fallback">{{ $initial }}</div>
                        @endif
                        <div class="min-w-0">
                            <div class="fw-semibold text-truncate lh-sm" style="font-size: .9rem;">{{ $userName }}</div>
                            <small class="d-block opacity-75" style="font-size: .75rem;">{{ $story->created_at->diffForHumans() }}</small>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="card-footer bg-transparent py-2 px-3 d-flex justify-content-between align-items-center gap-2">
                <small class="text-muted text-truncate">
                    <i class="bi bi-clock me-1"></i>
                    @if($story->expires_at)
                        {{ __('Expires') }}: {{ $story->expires_at->format('d M, H:i') }}
                    @else
                        {{ __('No expiry') }}
                    @endif
                </small>

                <div class="d-inline-flex align-items-center gap-1 flex-shrink-0">
                    <button type="button"
                            class="btn btn-sm btn-outline-secondary rounded-3 js-story-preview"
                            data-bs-toggle="modal"
                            data-bs-target="#storyPreviewModal"
                            data-type="{{ $isImage ? 'image' : 'video' }}"
                            data-src="{{ $mediaSrc }}"
                            data-user="{{ $userName }}"
                            data-date="{{ $story->created_at->format('d M, Y H:i') }}">
                        <i class="bi bi-eye"></i>
                    </button>

                    <form action="{{ route('admin.stories.destroy', $story) }}" method="POST" onsubmit="return confirm('{{ __('Are you sure you want to delete this story?') }}');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-3" title="{{ __('Delete') }}">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-body text-center py-5">
                <div class="story-empty-icon bg-body-tertiary text-muted mx-auto mb-3">
                    <i class="bi bi-camera-reels"></i>
                </div>
                <h6 class="text-muted mb-0">{{ __('No stories found.') }}</h6>
            </div>
        </div>
    </div>
    @endforelse
</div>

@if($stories->hasPages())
<div class="mt-4 d-flex justify-content-center">
    {{ $stories->links('pagination::bootstrap-5') }}
</div>
@endif

{{-- Larger Story Preview Modal --}}
<div class="modal fade" id="storyPreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4 overflow-hidden">
            <div class="modal-header py-2 px-3">
                <div class="min-w-0">
                    <h6 class="modal-title fw-bold mb-0 text-truncate" id="previewStoryUser"></h6>
                    <small class="text-muted" id="previewStoryDate"></small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="story-modal-stage">
                <img id="previewStoryImg" src="" alt="Story" class="d-none">
                <video id="previewStoryVideo" src="" controls playsinline class="d-none"></video>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const storyModalEl = document.getElementById('storyPreviewModal');
const previewImg   = document.getElementById('previewStoryImg');
const previewVideo = document.getElementById('previewStoryVideo');

document.querySelectorAll('.js-story-preview').forEach((el) => {
    el.addEventListener('click', () => {
        document.getElementById('previewStoryUser').textContent = el.dataset.user;
        document.getElementById('previewStoryDate').textContent = el.dataset.date;

        if (el.dataset.type === 'image') {
            previewVideo.pause();
            previewVideo.removeAttribute('src');
            previewVideo.classList.add('d-none');

            previewImg.src = el.dataset.src;
            previewImg.classList.remove('d-none');
        } else {
            previewImg.removeAttribute('src');
            previewImg.classList.add('d-none');

            previewVideo.src = el.dataset.src;
            previewVideo.classList.remove('d-none');
            previewVideo.play().catch(() => {});
        }
    });
});

storyModalEl.addEventListener('hidden.bs.modal', () => {
    previewVideo.pause();
    previewVideo.removeAttribute('src');
    previewImg.removeAttribute('src');
});
</script>
@endpush