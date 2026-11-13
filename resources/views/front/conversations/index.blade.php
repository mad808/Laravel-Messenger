@extends('front.layouts.master')

@section('title', __('Conversations'))
@section('header_title', __('Conversations'))

@php
    use Illuminate\Support\Carbon;
    use Illuminate\Support\Str;

    $meId = (int) auth()->id();

    $routeConv = request()->route('conversation');
    $activeId  = is_object($routeConv) ? (int) $routeConv->id : (int) $routeConv;

    $fmtTime = function ($date) {
        if (! $date) return '';
        $d = Carbon::parse($date);
        if ($d->isToday())     return $d->format('H:i');
        if ($d->isYesterday()) return __('Yesterday');
        if ($d->isCurrentYear()) return $d->translatedFormat('d M');
        return $d->format('d.m.y');
    };
@endphp

@section('sidebar_content')
    <div class="conv-head">
        <span>{{ __('Conversations') }}</span>
        <span class="badge text-bg-secondary rounded-pill">{{ $conversations->count() }}</span>
    </div>

    @forelse($conversations as $conversation)
        @php
            $isGroup = $conversation->type !== 'private';

            if (! $isGroup) {
                $otherUser = $conversation->participants->where('id', '!=', $meId)->first();
                $title  = $otherUser ? $otherUser->name : __('Unknown User');
                $avatar = $otherUser && $otherUser->avatar
                    ? asset('storage/' . $otherUser->avatar)
                    : 'https://ui-avatars.com/api/?name=' . urlencode($title) . '&background=random';
            } else {
                $title  = $conversation->title ?? __('Group Chat');
                $avatar = $conversation->avatar
                    ? asset('storage/' . $conversation->avatar)
                    : 'https://ui-avatars.com/api/?name=' . urlencode($title) . '&background=random';
            }

            $last   = $conversation->messages->first();
            $mine   = $last && (int) $last->user_id === $meId;
            $active = (int) $conversation->id === $activeId;
        @endphp

        <a href="{{ route('conversations.show', $conversation->id) }}"
           class="conv-item {{ $active ? 'active' : '' }}"
           @if($active) aria-current="page" @endif
           data-search-item>

            <div class="conv-av">
                <img src="{{ $avatar }}" alt="{{ $title }}" loading="lazy">
                @if($isGroup)
                    <span class="conv-grp" title="{{ __('Group Chat') }}"><i class="bi bi-people-fill"></i></span>
                @endif
            </div>

            <div class="min-w-0 flex-grow-1">
                <div class="d-flex justify-content-between align-items-baseline gap-2">
                    <span class="conv-title text-truncate">{{ $title }}</span>
                    <small class="conv-time">{{ $fmtTime($last?->created_at ?? $conversation->updated_at) }}</small>
                </div>

                <div class="conv-preview text-truncate">
                    @if(! $last)
                        <span class="fst-italic">{{ __('No messages yet') }}</span>
                    @elseif($last->is_deleted)
                        <i class="bi bi-slash-circle me-1"></i><span class="fst-italic">{{ __('This message was deleted') }}</span>
                    @else
                        @if($mine)<span class="conv-you">{{ __('You') }}:</span>@endif
                        @if(filled($last->body))
                            {{ Str::limit($last->body, 40) }}
                        @else
                            <i class="bi bi-paperclip"></i> {{ __('Attachment') }}
                        @endif
                    @endif
                </div>
            </div>
        </a>
    @empty
        <div class="sb-empty">
            <div class="sb-empty-icon"><i class="bi bi-chat-square-dots"></i></div>
            <h6 class="fw-bold mb-1">{{ __('No chats yet') }}</h6>
            <p class="small text-body-secondary mb-0">{{ __('Start a new conversation to see it here.') }}</p>
        </div>
    @endforelse
@endsection

@push('styles')
<style>
    /* ---------- sidebar list ---------- */
    .conv-head {
        display: flex; align-items: center; justify-content: space-between;
        padding: .25rem 1rem .5rem; font-size: .75rem; font-weight: 700;
        letter-spacing: .05em; text-transform: uppercase; color: var(--bs-secondary-color);
    }
    .conv-item {
        display: flex; align-items: center; gap: .8rem; padding: .75rem 1rem;
        color: var(--bs-body-color); text-decoration: none; position: relative;
        border-bottom: 1px solid var(--bs-border-color);
        transition: background-color .12s;
    }
    .conv-item:hover { background: var(--bs-tertiary-bg); color: var(--bs-body-color); }
    .conv-item.active { background: rgba(var(--bs-primary-rgb), .1); }
    .conv-item.active::before {
        content: ""; position: absolute; left: 0; top: 10px; bottom: 10px; width: 3px;
        border-radius: 0 3px 3px 0; background: var(--bs-primary);
    }

    .conv-av { position: relative; flex: none; width: 50px; height: 50px; }
    .conv-av img { width: 100%; height: 100%; border-radius: 50%; object-fit: cover; }
    .conv-grp {
        position: absolute; right: -3px; bottom: -3px; width: 20px; height: 20px; display: grid; place-items: center;
        border-radius: 50%; background: var(--bs-primary); color: #fff; font-size: .6rem;
        border: 2px solid var(--bs-body-bg);
    }

    .conv-title { font-weight: 700; font-size: .95rem; }
    .conv-time { flex: none; font-size: .72rem; color: var(--bs-secondary-color); }
    .conv-preview { margin-top: 2px; font-size: .84rem; color: var(--bs-secondary-color); }
    .conv-you { color: var(--bs-body-color); font-weight: 600; margin-right: .2rem; }

    /* ---------- welcome panel ---------- */
    .welcome {
        height: 100%; min-height: 360px; display: flex; flex-direction: column; align-items: center; justify-content: center;
        text-align: center; padding: 2rem 1.25rem; gap: .5rem;
        background-image: radial-gradient(rgba(var(--bs-emphasis-color-rgb), .06) 1.2px, transparent 1.2px);
        background-size: 22px 22px;
    }
    .welcome-ico {
        width: 110px; height: 110px; display: grid; place-items: center; border-radius: 32px; margin-bottom: .75rem;
        background: linear-gradient(135deg, rgba(var(--bs-primary-rgb), .18), rgba(var(--bs-info-rgb), .12));
        color: var(--bs-primary); font-size: 3.2rem;
    }
    .welcome h4 { font-weight: 800; margin: 0; }
    .welcome p  { max-width: 420px; margin: 0; color: var(--bs-secondary-color); }
    .welcome-tips { display: flex; flex-wrap: wrap; justify-content: center; gap: .5rem; margin-top: 1rem; }
    .welcome-tips span {
        padding: .3rem .8rem; border-radius: 999px; font-size: .8rem; color: var(--bs-secondary-color);
        background: var(--bs-body-bg); border: 1px solid var(--bs-border-color);
    }
    kbd { padding: .1rem .4rem; border-radius: 5px; font-size: .75rem; }
</style>
@endpush

@section('content')
<div class="welcome">
    <div class="welcome-ico"><i class="bi bi-chat-dots"></i></div>

    <h4>{{ __('Select a conversation') }}</h4>
    <p>{{ __('Choose from your existing conversations or start a new one.') }}</p>

    {{-- Mobile: the sidebar is hidden, so give a direct way to it --}}
    <button type="button" class="btn btn-primary rounded-pill px-4 mt-3 d-md-none" onclick="openSidebar()">
        <i class="bi bi-chat-left-text me-2"></i>{{ __('Open chats') }}
        <span class="badge text-bg-light ms-1">{{ $conversations->count() }}</span>
    </button>

    <div class="welcome-tips d-none d-md-flex">
        <span><i class="bi bi-search me-1"></i>{{ __('Search chats...') }} <kbd class="bg-body-secondary text-body">/</kbd></span>
        <span><i class="bi bi-moon-stars me-1"></i>{{ __('Toggle theme') }}</span>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    // Press "/" to jump to the sidebar search (not while typing in a field)
    document.addEventListener('keydown', function (e) {
        if (e.key !== '/' || e.ctrlKey || e.metaKey || e.altKey) return;
        var t = e.target, tag = t && t.tagName;
        if (tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT' || (t && t.isContentEditable)) return;
        var input = document.getElementById('sidebarSearch');
        if (input) { e.preventDefault(); input.focus(); input.select(); }
    });
})();
</script>
@endpush