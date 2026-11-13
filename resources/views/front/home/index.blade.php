@extends('front.layouts.master')

@section('title', __('Chats'))
@section('header_title', __('Chats'))

@php
    use Illuminate\Support\Carbon;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;

    $meId = (int) auth()->id();

    $routeConv = request()->route('conversation');
    $activeId  = is_object($routeConv) ? (int) $routeConv->id : (int) $routeConv;

    $fmtTime = function ($date) {
        if (! $date) return '';
        $d = Carbon::parse($date);
        if ($d->isToday())       return $d->format('H:i');
        if ($d->isYesterday())   return __('Yesterday');
        if ($d->isCurrentYear()) return $d->translatedFormat('d M');
        return $d->format('d.m.y');
    };

    $maskPhone = function ($p) {
        $p = (string) $p;
        return strlen($p) > 6 ? Str::mask($p, '*', 3, -2) : $p;
    };

    $chatUsers = $chatUsers ?? auth()->user()->contacts()
        ->with('contactUser')
        ->get()
        ->pluck('contactUser')
        ->filter()
        ->values();

    $existing = [];
    foreach ($conversations as $c) {
        if ($c->type === 'private') {
            $o = $c->participants->where('id', '!=', $meId)->first();
            if ($o) $existing[$o->id] = route('conversations.show', $c->id);
        }
    }

    $storiesUrl = Route::has('stories.index') ? route('stories.index') : null;
@endphp

@section('sidebar_content')
    <div class="conv-head">
        <span>{{ __('Chats') }} <span class="badge text-bg-secondary rounded-pill ms-1">{{ $conversations->count() }}</span></span>
        <button type="button" class="conv-new" data-bs-toggle="modal" data-bs-target="#newChatModal">
            <i class="bi bi-plus-lg me-1"></i>{{ __('New Chat') }}
        </button>
    </div>

    @forelse($conversations as $conversation)
        @php
            $isGroup = $conversation->type !== 'private';

            if (! $isGroup) {
                $otherUser  = $conversation->participants->where('id', '!=', $meId)->first();
                $title      = $otherUser ? $otherUser->name : __('Unknown User');
                $avatarPath = $otherUser?->avatar;
            } else {
                $title      = $conversation->title ?? __('Group Chat');
                $avatarPath = $conversation->avatar;
            }

            $initial = mb_strtoupper(mb_substr($title ?: '?', 0, 1));
            $last    = $conversation->messages->first();
            $mine    = $last && (int) $last->user_id === $meId;
            $active  = (int) $conversation->id === $activeId;
            $isFile  = $last && (($last->type ?? 'text') !== 'text' || blank($last->body));
        @endphp

        <a href="{{ route('conversations.show', $conversation->id) }}"
           class="conv-item {{ $active ? 'active' : '' }}"
           @if($active) aria-current="page" @endif
           data-search-item>

            <div class="conv-av">
                @if($avatarPath)
                    <img src="{{ asset('storage/' . $avatarPath) }}" alt="{{ $title }}" loading="lazy"
                         onerror="this.classList.add('d-none'); this.nextElementSibling.classList.remove('d-none');">
                    <div class="conv-av-fallback bg-primary-subtle text-primary-emphasis d-none">{{ $initial }}</div>
                @else
                    <div class="conv-av-fallback bg-primary-subtle text-primary-emphasis">{{ $initial }}</div>
                @endif
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
                        @if($isFile)<i class="bi bi-paperclip"></i>@endif
                        {{ filled($last->body) ? Str::limit($last->body, 40) : __('Attachment') }}
                    @endif
                </div>
            </div>
        </a>
    @empty
        <div class="sb-empty">
            <div class="sb-empty-icon"><i class="bi bi-chat-square-dots"></i></div>
            <h6 class="fw-bold mb-1">{{ __('No chats yet') }}</h6>
            <p class="small text-body-secondary mb-3">{{ __('Start a new conversation to see it here.') }}</p>
            <button class="btn btn-primary btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#newChatModal">
                <i class="bi bi-plus-lg me-1"></i>{{ __('New Chat') }}
            </button>
        </div>
    @endforelse
@endsection

@push('styles')
<style>
    .conv-head {
        display: flex; align-items: center; justify-content: space-between; gap: .5rem;
        padding: .25rem 1rem .6rem; font-size: .75rem; font-weight: 700;
        letter-spacing: .05em; text-transform: uppercase; color: var(--bs-secondary-color);
    }
    .conv-new {
        border: 0; border-radius: 999px; padding: .3rem .8rem; font-size: .75rem; font-weight: 700;
        letter-spacing: 0; text-transform: none; background: var(--bs-primary); color: #fff;
    }
    .conv-new:hover { filter: brightness(1.08); }

    .conv-item {
        display: flex; align-items: center; gap: .8rem; padding: .75rem 1rem; position: relative;
        color: var(--bs-body-color); text-decoration: none; border-bottom: 1px solid var(--bs-border-color);
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
    .conv-av-fallback {
        width: 100%; height: 100%; border-radius: 50%;
        display: inline-flex; align-items: center; justify-content: center; font-weight: 700; font-size: 1.15rem;
    }
    .conv-grp {
        position: absolute; right: -3px; bottom: -3px; width: 20px; height: 20px; display: grid; place-items: center;
        border-radius: 50%; background: var(--bs-primary); color: #fff; font-size: .6rem; border: 2px solid var(--bs-body-bg);
    }
    .conv-title { font-weight: 700; font-size: .95rem; }
    .conv-time { flex: none; font-size: .72rem; color: var(--bs-secondary-color); }
    .conv-preview { margin-top: 2px; font-size: .84rem; color: var(--bs-secondary-color); }
    .conv-you { color: var(--bs-primary); font-weight: 600; margin-right: .2rem; }

    /* Mobile direct chat list */
    .mob-chats { background: var(--bs-body-bg); min-height: 100%; }
    .mob-chats-bar {
        display: flex; align-items: center; justify-content: space-between; gap: .5rem;
        padding: .75rem 1rem; border-bottom: 1px solid var(--bs-border-color);
        position: sticky; top: 0; z-index: 3; background: var(--bs-body-bg);
    }

    /* Desktop welcome */
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
    .welcome h2 { font-weight: 800; margin: 0; }
    .welcome p  { max-width: 420px; margin: 0 0 .75rem; color: var(--bs-secondary-color); }
    .welcome-tips { display: flex; flex-wrap: wrap; justify-content: center; gap: .5rem; margin-top: 1rem; }
    .welcome-tips span {
        padding: .3rem .8rem; border-radius: 999px; font-size: .8rem; color: var(--bs-secondary-color);
        background: var(--bs-body-bg); border: 1px solid var(--bs-border-color);
    }
    kbd { padding: .1rem .4rem; border-radius: 5px; font-size: .75rem; }

    /* New chat modal */
    .nc-search { position: relative; }
    .nc-search .bi-search { position: absolute; left: 1rem; top: 50%; translate: 0 -50%; color: var(--bs-secondary-color); pointer-events: none; }
    .nc-search input { padding-left: 2.5rem; border-radius: 999px; }
    .nc-list { max-height: 340px; overflow-y: auto; margin: .75rem -.25rem 0; padding: 0 .25rem; }
    .nc-user {
        display: flex; align-items: center; gap: .75rem; padding: .55rem .6rem; border-radius: 12px; cursor: pointer;
        border: 1.5px solid transparent; transition: background-color .12s, border-color .12s;
    }
    .nc-user:hover { background: var(--bs-tertiary-bg); }
    .nc-user input { position: absolute; opacity: 0; pointer-events: none; }
    .nc-user:has(input:checked) { background: rgba(var(--bs-primary-rgb), .1); border-color: var(--bs-primary); }
    .nc-user img, .nc-av-fallback { width: 42px; height: 42px; flex: none; border-radius: 50%; object-fit: cover; }
    .nc-av-fallback { display: inline-flex; align-items: center; justify-content: center; font-weight: 700; }
    .nc-user .tick { margin-left: auto; color: var(--bs-primary); font-size: 1.2rem; opacity: 0; }
    .nc-user:has(input:checked) .tick { opacity: 1; }
    .nc-empty { text-align: center; padding: 2rem 1rem; color: var(--bs-secondary-color); }
</style>
@endpush

@section('content')
{{-- Mobile: show chat list directly (like Telegram / WhatsApp) --}}
<div class="mob-chats d-md-none">
    <div class="mob-chats-bar">
        <div class="d-flex align-items-center gap-2">
            <span class="fw-bold">{{ __('Chats') }}</span>
            <span class="badge bg-body-tertiary text-body border rounded-pill">{{ $conversations->count() }}</span>
        </div>
        <div class="d-flex align-items-center gap-2">
            @if($storiesUrl)
                <a href="{{ $storiesUrl }}" class="btn btn-sm btn-outline-secondary rounded-pill">
                    <i class="bi bi-camera-reels"></i>
                </a>
            @endif
            <button type="button" class="btn btn-sm btn-primary rounded-pill fw-semibold px-3" data-bs-toggle="modal" data-bs-target="#newChatModal">
                <i class="bi bi-plus-lg me-1"></i>{{ __('New Chat') }}
            </button>
        </div>
    </div>

    @forelse($conversations as $conversation)
        @php
            $isGroup = $conversation->type !== 'private';
            if (! $isGroup) {
                $otherUser  = $conversation->participants->where('id', '!=', $meId)->first();
                $title      = $otherUser ? $otherUser->name : __('Unknown User');
                $avatarPath = $otherUser?->avatar;
            } else {
                $title      = $conversation->title ?? __('Group Chat');
                $avatarPath = $conversation->avatar;
            }
            $initial = mb_strtoupper(mb_substr($title ?: '?', 0, 1));
            $last    = $conversation->messages->first();
            $mine    = $last && (int) $last->user_id === $meId;
            $isFile  = $last && (($last->type ?? 'text') !== 'text' || blank($last->body));
        @endphp

        <a href="{{ route('conversations.show', $conversation->id) }}" class="conv-item">
            <div class="conv-av">
                @if($avatarPath)
                    <img src="{{ asset('storage/' . $avatarPath) }}" alt="{{ $title }}" loading="lazy"
                         onerror="this.classList.add('d-none'); this.nextElementSibling.classList.remove('d-none');">
                    <div class="conv-av-fallback bg-primary-subtle text-primary-emphasis d-none">{{ $initial }}</div>
                @else
                    <div class="conv-av-fallback bg-primary-subtle text-primary-emphasis">{{ $initial }}</div>
                @endif
                @if($isGroup)
                    <span class="conv-grp"><i class="bi bi-people-fill"></i></span>
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
                        @if($isFile)<i class="bi bi-paperclip"></i>@endif
                        {{ filled($last->body) ? Str::limit($last->body, 40) : __('Attachment') }}
                    @endif
                </div>
            </div>
        </a>
    @empty
        <div class="sb-empty py-5">
            <div class="sb-empty-icon"><i class="bi bi-chat-square-dots"></i></div>
            <h6 class="fw-bold mb-1">{{ __('No chats yet') }}</h6>
            <p class="small text-body-secondary mb-3">{{ __('Start a new conversation to see it here.') }}</p>
            <button class="btn btn-primary btn-sm rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#newChatModal">
                <i class="bi bi-plus-lg me-1"></i>{{ __('New Chat') }}
            </button>
        </div>
    @endforelse
</div>

{{-- Desktop: welcome panel --}}
<div class="welcome d-none d-md-flex">
    <div class="welcome-ico"><i class="bi bi-chat-dots-fill"></i></div>

    <h2>Messenger</h2>
    <p>{{ __('Select a chat from the left menu to start messaging, or create a new one to connect with your family and friends.') }}</p>

    <div class="d-flex flex-wrap justify-content-center gap-2">
        <button class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#newChatModal">
            <i class="bi bi-plus-lg me-2"></i>{{ __('New Chat') }}
        </button>

        @if($storiesUrl)
            <a href="{{ $storiesUrl }}" class="btn btn-outline-secondary rounded-pill px-4 py-2 fw-bold">
                <i class="bi bi-camera-reels me-2"></i>{{ __('View Stories') }}
            </a>
        @endif
    </div>

    <div class="welcome-tips">
        <span><i class="bi bi-search me-1"></i>{{ __('Search chats...') }} <kbd class="bg-body-secondary text-body">/</kbd></span>
        <span><i class="bi bi-plus-circle me-1"></i>{{ __('New Chat') }} <kbd class="bg-body-secondary text-body">N</kbd></span>
    </div>
</div>

{{-- New chat modal --}}
<div class="modal fade" id="newChatModal" tabindex="-1" aria-labelledby="newChatTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold" id="newChatTitle"><i class="bi bi-chat-dots text-primary me-2"></i>{{ __('Start New Chat') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
            </div>

            <form action="{{ route('conversations.store') }}" method="POST" id="newChatForm">
                @csrf
                <div class="modal-body pt-2">
                    @error('user_id')
                        <div class="alert alert-danger py-2 small">{{ $message }}</div>
                    @enderror

                    <div class="nc-search">
                        <i class="bi bi-search"></i>
                        <input type="search" id="ncSearch" class="form-control" autocomplete="off"
                               placeholder="{{ __('Choose a user to chat with...') }}">
                    </div>

                    <div class="nc-list" id="ncList" role="radiogroup" aria-label="{{ __('Select User') }}">
                        @forelse($chatUsers as $u)
                            @php $uInit = mb_strtoupper(mb_substr($u->name ?: '?', 0, 1)); @endphp
                            <label class="nc-user" data-nc-item
                                data-existing="{{ $existing[$u->id] ?? '' }}"
                                data-text="{{ Str::lower($u->name . ' ' . $u->username . ' ' . $u->phone) }}">
                                <input type="radio" name="user_id" value="{{ $u->id }}" required>
                                @if($u->avatar)
                                    <img src="{{ asset('storage/' . $u->avatar) }}" alt="" loading="lazy"
                                         onerror="this.classList.add('d-none'); this.nextElementSibling.classList.remove('d-none');">
                                    <span class="nc-av-fallback bg-primary-subtle text-primary-emphasis d-none">{{ $uInit }}</span>
                                @else
                                    <span class="nc-av-fallback bg-primary-subtle text-primary-emphasis">{{ $uInit }}</span>
                                @endif
                                <div class="min-w-0">
                                    <div class="fw-semibold text-truncate">{{ $u->name }}</div>
                                    <small class="text-body-secondary d-block text-truncate">
                                        {{ $u->username ? '@' . $u->username . ' · ' : '' }}{{ $maskPhone($u->phone) }}
                                    </small>
                                </div>
                                @if(isset($existing[$u->id]))
                                    <span class="badge text-bg-secondary rounded-pill ms-auto">{{ __('Chat exists') }}</span>
                                @else
                                    <i class="bi bi-check-circle-fill tick"></i>
                                @endif
                            </label>
                        @empty
                            <div class="nc-empty">
                                <i class="bi bi-person-x d-block fs-3 mb-1 opacity-50"></i>
                                <p class="small mb-2">{{ __('Your contact list is empty. Add someone first.') }}</p>
                                @if(\Illuminate\Support\Facades\Route::has('contacts.index'))
                                    <a href="{{ route('contacts.index') }}" class="btn btn-primary btn-sm rounded-pill">
                                        <i class="bi bi-person-plus me-1"></i>{{ __('Add Contact') }}
                                    </a>
                                @endif
                            </div>
                        @endforelse

                        <div class="nc-empty d-none" id="ncNone"><i class="bi bi-search d-block fs-3 mb-1 opacity-50"></i>{{ __('Nothing found') }}</div>
                    </div>
                </div>

                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary rounded-pill fw-bold px-4" id="ncSubmit" disabled>{{ __('Start Chat') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var modalEl = document.getElementById('newChatModal');
    var modal   = new bootstrap.Modal(modalEl);
    var form    = document.getElementById('newChatForm');
    var search  = document.getElementById('ncSearch');
    var submit  = document.getElementById('ncSubmit');
    var none    = document.getElementById('ncNone');
    var items   = Array.prototype.slice.call(document.querySelectorAll('[data-nc-item]'));

    search.addEventListener('input', function () {
        var q = search.value.trim().toLowerCase(), visible = 0;
        items.forEach(function (el) {
            var show = !q || el.dataset.text.indexOf(q) !== -1;
            el.classList.toggle('d-none', !show);
            if (show) visible++;
        });
        none.classList.toggle('d-none', !(items.length && visible === 0));
    });

    form.addEventListener('change', function () {
        submit.disabled = !form.querySelector('input[name="user_id"]:checked');
    });

    form.addEventListener('submit', function (e) {
        var checked = form.querySelector('input[name="user_id"]:checked');
        if (!checked) { e.preventDefault(); return; }
        var url = checked.closest('[data-nc-item]').dataset.existing;
        if (url) { e.preventDefault(); window.location.href = url; return; }
        submit.disabled = true;
        submit.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
    });

    modalEl.addEventListener('shown.bs.modal', function () { search.focus(); });
    modalEl.addEventListener('hidden.bs.modal', function () {
        form.reset(); search.value = ''; submit.disabled = true;
        items.forEach(function (el) { el.classList.remove('d-none'); });
        none.classList.add('d-none');
    });

    document.addEventListener('keydown', function (e) {
        if (e.ctrlKey || e.metaKey || e.altKey) return;
        var t = e.target, tag = t && t.tagName;
        if (tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT' || (t && t.isContentEditable)) return;
        if (document.querySelector('.modal.show')) return;

        if (e.key === '/') {
            var input = document.getElementById('sidebarSearch');
            if (input) { e.preventDefault(); input.focus(); input.select(); }
        } else if (e.key === 'n' || e.key === 'N') {
            e.preventDefault(); modal.show();
        }
    });

    @if($errors->has('user_id'))
        modal.show();
    @endif
})();
</script>
@endpush