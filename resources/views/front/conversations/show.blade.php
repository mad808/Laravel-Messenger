@extends('front.layouts.master')

@php
    use Illuminate\Support\Carbon;
    use Illuminate\Support\Str;

    $meId = (int) auth()->id();

    if ($conversation->type === 'private') {
        $otherUser = $conversation->participants->where('id', '!=', $meId)->first();
        $title     = $otherUser ? $otherUser->name : __('Unknown User');
        $avatar    = $otherUser && $otherUser->avatar
            ? asset('storage/' . $otherUser->avatar)
            : 'https://ui-avatars.com/api/?name=' . urlencode($title) . '&background=random';

        $seen     = data_get($otherUser, 'last_seen_at');
        $subtitle = $seen ? __('Last seen') . ' ' . Carbon::parse($seen)->diffForHumans() : '';
    } else {
        $otherUser = null;
        $title     = $conversation->title ?? __('Group Chat');
        $avatar    = $conversation->avatar
            ? asset('storage/' . $conversation->avatar)
            : 'https://ui-avatars.com/api/?name=' . urlencode($title) . '&background=random';
        $subtitle  = $conversation->participants->count() . ' ' . __('members');
    }

    $userAvatar = fn($u) => $u && $u->avatar
        ? asset('storage/' . $u->avatar)
        : 'https://ui-avatars.com/api/?name=' . urlencode($u->name ?? '?') . '&background=random';

    // escape first, then turn URLs into links (safe against XSS)
    $linkify = fn($text) => preg_replace_callback('~https?://[^\s<]+~i', function ($m) {
        $u = $m[0]; $trail = '';
        while ($u !== '' && str_contains('.,;:!?)', substr($u, -1))) { $trail = substr($u, -1) . $trail; $u = substr($u, 0, -1); }
        return '<a href="' . $u . '" target="_blank" rel="noopener noreferrer nofollow">' . $u . '</a>' . $trail;
    }, e($text));

    // always oldest -> newest, so the newest message is at the bottom
    $messages = $conversation->messages
        ->sortBy([['created_at', 'asc'], ['id', 'asc']])
        ->values();
    $isGroup  = $conversation->type !== 'private';
@endphp

@section('title', $title)
@section('search_placeholder', $isGroup ? __('Search members...') : __('Search...'))

@section('header_title')
    <span class="chat-head">
        <img src="{{ $avatar }}" class="chat-head-avatar" alt="{{ $title }}">
        <span class="chat-head-text">
            <span class="chat-head-name text-truncate">{{ $title }}</span>
            @if($subtitle)<small class="text-truncate">{{ $subtitle }}</small>@endif
        </span>
    </span>
@endsection

@section('sidebar_content')
    <div class="member-head">
        <span>{{ $isGroup ? __('Members') : __('Contact') }}</span>
        <span class="badge text-bg-secondary rounded-pill">{{ $isGroup ? $conversation->participants->count() : 1 }}</span>
    </div>

    @foreach($conversation->participants as $participant)
        @if($isGroup || $participant->id !== $meId)
            <div class="member-row" data-search-item>
                <img src="{{ $userAvatar($participant) }}" alt="{{ $participant->name }}" loading="lazy">
                <div class="min-w-0">
                    <div class="fw-semibold text-truncate">
                        {{ $participant->name }}
                        @if($participant->id === $meId)<span class="badge text-bg-primary rounded-pill ms-1">{{ __('You') }}</span>@endif
                    </div>
                    <small class="text-body-secondary d-block text-truncate">
                        {{ $participant->username ? '@' . $participant->username : $participant->phone }}
                    </small>
                </div>
                @if(in_array($participant->role, ['admin', 'moderator']))
                    <span class="badge {{ $participant->role === 'admin' ? 'text-bg-danger' : 'text-bg-warning' }} ms-auto">
                        {{ $participant->role === 'admin' ? __('Admin') : __('Moderator') }}
                    </span>
                @endif
            </div>
        @endif
    @endforeach
@endsection

@push('styles')
<style>
    /* ---------- header ---------- */
    .chat-head { display: flex; align-items: center; gap: .7rem; min-width: 0; }
    .chat-head-avatar { width: 40px; height: 40px; flex: none; border-radius: 50%; object-fit: cover; }
    .chat-head-text { display: flex; flex-direction: column; min-width: 0; line-height: 1.2; }
    .chat-head-name { font-size: 1rem; font-weight: 700; }
    .chat-head-text small { font-size: .75rem; font-weight: 400; color: var(--bs-secondary-color); }

    /* ---------- sidebar members ---------- */
    .member-head { display: flex; align-items: center; justify-content: space-between; padding: .25rem 1rem .5rem;
        font-size: .75rem; font-weight: 700; letter-spacing: .05em; text-transform: uppercase; color: var(--bs-secondary-color); }
    .member-row { display: flex; align-items: center; gap: .75rem; padding: .7rem 1rem; border-bottom: 1px solid var(--bs-border-color); }
    .member-row img { width: 42px; height: 42px; flex: none; border-radius: 50%; object-fit: cover; }

    /* ---------- chat layout ---------- */
    .chat {
        position: absolute; inset: 0; display: flex; flex-direction: column;
        --chat-out: #d9fdd3; --chat-out-ink: #111b21; --chat-dot: rgba(0, 0, 0, .06);
    }
    [data-bs-theme="dark"] .chat { --chat-out: #005c4b; --chat-out-ink: #e9edef; --chat-dot: rgba(255, 255, 255, .05); }

    .chat-scroll {
        flex: 1 1 auto; min-height: 0; overflow-y: auto; padding: 1rem 1rem .5rem;
        background-color: var(--bs-tertiary-bg);
        background-image: radial-gradient(var(--chat-dot) 1.2px, transparent 1.2px);
        background-size: 22px 22px;
    }
    .chat-inner { display: flex; flex-direction: column; max-width: 900px; margin: 0 auto; min-height: 100%; }
    /* few messages / new chat: keep them at the bottom, next to the input */
    .chat-inner::before { content: ""; margin-top: auto; }
    .chat-inner > .chat-empty { margin: 0 auto auto; }

    .day-sep { align-self: center; margin: 1rem 0 .6rem; padding: .2rem .8rem; border-radius: 999px;
        background: var(--bs-body-bg); color: var(--bs-secondary-color); font-size: .75rem; font-weight: 600;
        box-shadow: 0 1px 2px rgba(0, 0, 0, .08); }

    /* ---------- bubbles ---------- */
    .msg { display: flex; flex-direction: column; margin-top: 2px; scroll-margin: 5rem; }
    .msg.out { align-items: flex-end; }
    .msg.in  { align-items: flex-start; }
    .msg.first { margin-top: 10px; }
    .msg.hl .bubble { outline: 2px solid var(--bs-primary); outline-offset: 2px; }

    .bubble-row { display: flex; align-items: center; gap: .35rem; max-width: 100%; }
    .out .bubble-row { flex-direction: row-reverse; }

    .bubble {
        max-width: min(75vw, 560px); padding: .4rem .65rem .3rem; border-radius: 14px;
        box-shadow: 0 1px 1px rgba(0, 0, 0, .1); overflow-wrap: anywhere; transition: outline-color .3s;
    }
    .in  .bubble { background: var(--bs-body-bg); }
    .out .bubble { background: var(--chat-out); color: var(--chat-out-ink); }
    .in.first  .bubble { border-top-left-radius: 4px; }
    .out.first .bubble { border-top-right-radius: 4px; }
    .bubble a { color: inherit; text-decoration: underline; }

    .bubble-name { font-size: .78rem; font-weight: 700; color: var(--bs-primary); margin-bottom: 1px; }
    .bubble-text { white-space: pre-wrap; }
    .bubble-gone { font-style: italic; opacity: .65; }
    .bubble-meta { display: flex; justify-content: flex-end; margin-top: 1px; font-size: .68rem; opacity: .65; }

    .reply-btn { flex: none; width: 30px; height: 30px; display: grid; place-items: center; border: 0; border-radius: 50%;
        background: var(--bs-body-bg); color: var(--bs-secondary-color); box-shadow: 0 1px 3px rgba(0, 0, 0, .2);
        opacity: 0; transition: opacity .15s; }
    .msg:hover .reply-btn, .reply-btn:focus-visible { opacity: 1; }
    @media (hover: none) { .reply-btn { opacity: .85; } }

    .reply-quote { display: block; width: 100%; text-align: left; padding: .3rem .55rem; margin-bottom: .35rem;
        border: 0; border-left: 3px solid var(--bs-primary); border-radius: 6px;
        background: rgba(var(--bs-primary-rgb), .1); color: inherit; font-size: .8rem; }
    .reply-quote .who { font-weight: 700; color: var(--bs-primary); }

    .att-img { display: block; max-width: 100%; max-height: 260px; border-radius: 10px; margin-top: .25rem; cursor: zoom-in; object-fit: cover; }
    .att-file { display: flex; align-items: center; gap: .6rem; margin-top: .25rem; padding: .5rem .65rem;
        border-radius: 10px; background: rgba(0, 0, 0, .06); color: inherit; text-decoration: none !important; }
    [data-bs-theme="dark"] .att-file { background: rgba(255, 255, 255, .08); }
    .att-file i { font-size: 1.5rem; color: var(--bs-primary); }
    .att-file .nm { max-width: 220px; }

    /* ---------- scroll-to-bottom ---------- */
    .to-bottom { position: absolute; right: 1.25rem; bottom: 6.5rem; width: 42px; height: 42px; border: 0; border-radius: 50%;
        display: none; place-items: center; background: var(--bs-body-bg); color: var(--bs-body-color);
        box-shadow: 0 4px 14px rgba(0, 0, 0, .25); z-index: 5; }
    .to-bottom.show { display: grid; }
    .to-bottom .cnt { position: absolute; top: -6px; right: -6px; min-width: 20px; height: 20px; padding: 0 5px; border-radius: 99px;
        background: var(--bs-primary); color: #fff; font-size: .7rem; font-weight: 700; display: none; place-items: center; }
    .to-bottom .cnt.show { display: grid; }

    /* ---------- drop overlay ---------- */
    .drop { position: absolute; inset: 0; z-index: 20; display: none; place-items: center; text-align: center;
        background: rgba(var(--bs-primary-rgb), .15); backdrop-filter: blur(2px); border: 3px dashed var(--bs-primary); pointer-events: none; }
    .drop.show { display: grid; }
    .drop i { font-size: 3rem; color: var(--bs-primary); display: block; }

    /* ---------- composer ---------- */
    .composer { flex: none; padding: .6rem 1rem calc(.6rem + env(safe-area-inset-bottom)); background: var(--bs-body-bg); border-top: 1px solid var(--bs-border-color); }
    .composer-in { max-width: 900px; margin: 0 auto; }
    .composer-row { display: flex; align-items: flex-end; gap: .5rem; }
    .composer textarea {
        flex: 1; resize: none; max-height: 140px; min-height: 42px; padding: .6rem 1rem;
        border: 1px solid transparent; border-radius: 22px; background: var(--bs-tertiary-bg); line-height: 1.35;
    }
    .composer textarea:focus { outline: none; background: var(--bs-body-bg); border-color: var(--bs-primary); box-shadow: 0 0 0 .2rem rgba(var(--bs-primary-rgb), .15); }
    .c-btn { flex: none; width: 42px; height: 42px; display: grid; place-items: center; border: 0; border-radius: 50%; }
    .c-attach { background: transparent; color: var(--bs-secondary-color); font-size: 1.3rem; }
    .c-attach:hover { background: var(--bs-tertiary-bg); color: var(--bs-body-color); }
    .c-send { background: var(--bs-primary); color: #fff; }
    .c-send:disabled { opacity: .45; }

    .strip { display: none; align-items: center; gap: .6rem; margin-bottom: .5rem; padding: .4rem .5rem .4rem .6rem;
        border: 1px solid var(--bs-border-color); border-radius: 12px; background: var(--bs-tertiary-bg); }
    .strip.show { display: flex; }
    .strip.reply { border-left: 3px solid var(--bs-primary); }
    .strip .thumb { width: 40px; height: 40px; border-radius: 8px; object-fit: cover; flex: none; }
    .strip .ico { width: 40px; height: 40px; display: grid; place-items: center; border-radius: 8px; background: rgba(var(--bs-primary-rgb), .12); color: var(--bs-primary); font-size: 1.3rem; flex: none; }

    @media (max-width: 575.98px) { .bubble { max-width: 80vw; } .chat-scroll { padding: .75rem .6rem .4rem; } }
</style>
@endpush

@section('content')
<div class="chat" id="chat">

    {{-- Messages (oldest at the top, newest at the bottom) --}}
    <div class="chat-scroll" id="chatScroll">
        <div class="chat-inner" id="chatInner">
            @forelse($messages as $i => $message)
                @php
                    $prev    = $messages[$i - 1] ?? null;
                    $mine    = (int) $message->user_id === $meId;
                    $newDay  = ! $prev || ! $prev->created_at->isSameDay($message->created_at);
                    $first   = $newDay || ! $prev || $prev->user_id !== $message->user_id
                               || abs($prev->created_at->diffInMinutes($message->created_at)) > 5;
                    $day     = $message->created_at;
                    $dayText = $day->isToday() ? __('Today') : ($day->isYesterday() ? __('Yesterday') : $day->translatedFormat('d F Y'));
                    $gone    = (bool) ($message->is_deleted ?? false);
                    $who     = $mine ? __('You') : ($message->user->name ?? __('Deleted User'));
                @endphp

                @if($newDay)
                    <div class="day-sep">{{ $dayText }}</div>
                @endif

                <div class="msg {{ $mine ? 'out' : 'in' }} {{ $first ? 'first' : '' }}" id="m{{ $message->id }}" data-mid="{{ $message->id }}">
                    <div class="bubble-row">
                        <div class="bubble">

                            @if($isGroup && ! $mine && $first)
                                <div class="bubble-name">{{ $message->user->name ?? __('Deleted User') }}</div>
                            @endif

                            @if(! $gone && $message->replyTo)
                                <button type="button" class="reply-quote" data-goto="m{{ $message->replyTo->id }}">
                                    <div class="who">{{ $message->replyTo->user->name ?? __('User') }}</div>
                                    <div class="text-truncate">{{ $message->replyTo->body ?: __('Attachment') }}</div>
                                </button>
                            @endif

                            @if($gone)
                                <div class="bubble-gone"><i class="bi bi-slash-circle me-1"></i>{{ __('This message was deleted') }}</div>
                            @else
                                @if(filled($message->body))
                                    <div class="bubble-text">{!! $linkify($message->body) !!}</div>
                                @endif

                                @foreach($message->attachments as $attachment)
                                    @php $url = asset('storage/' . $attachment->file_path); @endphp
                                    @if(str_starts_with((string) $attachment->mime_type, 'image/'))
                                        <img src="{{ $url }}" class="att-img" alt="{{ $attachment->file_name }}" loading="lazy" data-lightbox="{{ $url }}">
                                    @else
                                        <a href="{{ $url }}" target="_blank" rel="noopener" class="att-file" download>
                                            <i class="bi bi-file-earmark-arrow-down"></i>
                                            <span class="nm text-truncate">{{ $attachment->file_name }}</span>
                                        </a>
                                    @endif
                                @endforeach
                            @endif

                            <div class="bubble-meta" title="{{ $message->created_at }}">{{ $message->created_at->format('H:i') }}</div>
                        </div>

                        @unless($gone)
                            <button type="button" class="reply-btn" aria-label="{{ __('Reply') }}" title="{{ __('Reply') }}"
                                    data-reply="{{ $message->id }}"
                                    data-who="{{ $who }}"
                                    data-snippet="{{ filled($message->body) ? Str::limit($message->body, 80) : __('Attachment') }}">
                                <i class="bi bi-reply-fill"></i>
                            </button>
                        @endunless
                    </div>
                </div>
            @empty
                <div class="chat-empty text-center text-body-secondary">
                    <i class="bi bi-chat-dots fs-1 d-block mb-2 opacity-50"></i>
                    <p class="mb-0">{{ __('No messages here yet. Say hi!') }}</p>
                </div>
            @endforelse
        </div>
    </div>

    <button type="button" class="to-bottom" id="toBottom" aria-label="{{ __('Scroll to latest') }}">
        <i class="bi bi-chevron-double-down"></i><span class="cnt" id="newCnt">0</span>
    </button>

    <div class="drop" id="dropZone"><div><i class="bi bi-cloud-arrow-up"></i><strong>{{ __('Drop file to attach') }}</strong></div></div>

    {{-- Composer --}}
    <div class="composer">
        <form action="{{ route('messages.store', $conversation->id) }}" method="POST" enctype="multipart/form-data"
              id="messageForm" class="composer-in">
            @csrf
            <input type="hidden" name="reply_to_id" id="replyTo" value="">

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show py-2 mb-2" role="alert">
                    @foreach($errors->all() as $error)<div class="small">{{ $error }}</div>@endforeach
                    <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
                </div>
            @endif
            <div class="alert alert-warning py-2 mb-2 small d-none" id="fileError"></div>

            {{-- replying to --}}
            <div class="strip reply" id="replyBar">
                <i class="bi bi-reply-fill text-primary fs-5"></i>
                <div class="min-w-0 flex-grow-1">
                    <div class="fw-semibold small text-primary text-truncate" id="replyWho"></div>
                    <div class="small text-body-secondary text-truncate" id="replyText"></div>
                </div>
                <button type="button" class="btn-close" id="replyClose" aria-label="{{ __('Cancel') }}"></button>
            </div>

            {{-- file preview --}}
            <div class="strip" id="fileChip">
                <img class="thumb d-none" id="fileThumb" alt="">
                <span class="ico" id="fileIcon"><i class="bi bi-file-earmark"></i></span>
                <div class="min-w-0 flex-grow-1">
                    <div class="fw-semibold small text-truncate" id="fileName"></div>
                    <div class="text-body-secondary" style="font-size: .75rem;" id="fileSize"></div>
                </div>
                <button type="button" class="btn-close" id="fileRemove" aria-label="{{ __('Remove') }}"></button>
            </div>

            <div class="composer-row">
                <button type="button" class="c-btn c-attach" id="attachBtn" aria-label="{{ __('Attach file') }}">
                    <i class="bi bi-paperclip"></i>
                </button>
                <input type="file" id="fileInput" name="file" class="d-none">

                <textarea name="body" id="bodyInput" rows="1" maxlength="5000"
                          placeholder="{{ __('Type a message...') }}" autocomplete="off">{{ old('body') }}</textarea>

                <button type="submit" class="c-btn c-send" id="sendBtn" aria-label="{{ __('Send') }}" disabled>
                    <i class="bi bi-send-fill"></i>
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Image viewer --}}
<div class="modal fade" id="lightbox" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content bg-transparent border-0">
            <button type="button" class="btn-close btn-close-white ms-auto mb-2" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
            <img id="lightboxImg" src="" alt="" class="img-fluid rounded mx-auto" style="max-height: 85vh; object-fit: contain;">
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var MAX_MB = 20, POLL_MS = 6000;
    var DRAFT_KEY = 'draft:{{ $conversation->id }}';

    var chat     = document.getElementById('chat');
    var scroller = document.getElementById('chatScroll');
    var inner    = document.getElementById('chatInner');
    var toBottom = document.getElementById('toBottom');
    var newCnt   = document.getElementById('newCnt');
    var form     = document.getElementById('messageForm');
    var body     = document.getElementById('bodyInput');
    var sendBtn  = document.getElementById('sendBtn');
    var fileIn   = document.getElementById('fileInput');
    var chip     = document.getElementById('fileChip');
    var thumb    = document.getElementById('fileThumb');
    var icon     = document.getElementById('fileIcon');
    var fileErr  = document.getElementById('fileError');
    var replyBar = document.getElementById('replyBar');
    var replyIn  = document.getElementById('replyTo');
    var thumbUrl = null, sending = false, unread = 0;

    /* ---------- scroll: open at the newest message and stay there ---------- */
    var pinned = true;     // true until the user scrolls up on purpose

    function nearBottom() { return scroller.scrollHeight - scroller.scrollTop - scroller.clientHeight < 160; }
    function down(smooth) { scroller.scrollTo({ top: scroller.scrollHeight, behavior: smooth ? 'smooth' : 'auto' }); }
    function setUnread(n) { unread = n; newCnt.textContent = n; newCnt.classList.toggle('show', n > 0); }

    down(false);
    window.addEventListener('load', function () { if (pinned) down(false); });

    // images / layout changes after load push the bottom further down: follow them while pinned
    inner.addEventListener('load', function () { if (pinned) down(false); }, true);
    if (window.ResizeObserver) {
        new ResizeObserver(function () { if (pinned) down(false); }).observe(inner);
    }
    setTimeout(function () { if (pinned) down(false); }, 300);

    scroller.addEventListener('scroll', function () {
        var far = scroller.scrollHeight - scroller.scrollTop - scroller.clientHeight > 300;
        pinned = nearBottom();
        toBottom.classList.toggle('show', far || unread > 0);
        if (pinned) setUnread(0);
    });
    toBottom.addEventListener('click', function () { pinned = true; down(true); setUnread(0); });

    /* ---------- composer ---------- */
    function refresh() { sendBtn.disabled = !(body.value.trim() || fileIn.files.length); }
    function grow() { body.style.height = 'auto'; body.style.height = Math.min(body.scrollHeight, 140) + 'px'; }

    try { if (!body.value) body.value = localStorage.getItem(DRAFT_KEY) || ''; } catch (e) {}
    body.addEventListener('input', function () {
        grow(); refresh();
        try { body.value ? localStorage.setItem(DRAFT_KEY, body.value) : localStorage.removeItem(DRAFT_KEY); } catch (e) {}
    });
    body.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && !e.shiftKey && !e.isComposing) {
            e.preventDefault();
            if (!sendBtn.disabled) form.requestSubmit();
        }
    });
    grow(); refresh();
    body.focus({ preventScroll: true });

    /* ---------- file attach ---------- */
    function fmtSize(b) { return b < 1024 ? b + ' B' : b < 1048576 ? (b / 1024).toFixed(1) + ' KB' : (b / 1048576).toFixed(1) + ' MB'; }
    function warn(msg) { fileErr.textContent = msg; fileErr.classList.toggle('d-none', !msg); }
    function clearFile() {
        fileIn.value = '';
        if (thumbUrl) { URL.revokeObjectURL(thumbUrl); thumbUrl = null; }
        chip.classList.remove('show'); thumb.classList.add('d-none'); icon.classList.remove('d-none');
        refresh();
    }
    function setFiles(list) {
        warn('');
        if (!list || !list.length) return;
        if (list[0].size > MAX_MB * 1048576) { clearFile(); warn(@json(__('File is too large (max 20MB).'))); return; }
        var dt = new DataTransfer(); dt.items.add(list[0]); fileIn.files = dt.files;
        fileIn.dispatchEvent(new Event('change'));
    }
    document.getElementById('attachBtn').addEventListener('click', function () { fileIn.click(); });
    document.getElementById('fileRemove').addEventListener('click', function () { clearFile(); warn(''); });
    fileIn.addEventListener('change', function () {
        var f = fileIn.files[0];
        if (!f) { clearFile(); return; }
        if (f.size > MAX_MB * 1048576) { clearFile(); warn(@json(__('File is too large (max 20MB).'))); return; }
        document.getElementById('fileName').textContent = f.name;
        document.getElementById('fileSize').textContent = fmtSize(f.size);
        if (thumbUrl) URL.revokeObjectURL(thumbUrl);
        if (f.type.indexOf('image/') === 0) {
            thumbUrl = URL.createObjectURL(f);
            thumb.src = thumbUrl; thumb.classList.remove('d-none'); icon.classList.add('d-none');
        } else { thumb.classList.add('d-none'); icon.classList.remove('d-none'); }
        chip.classList.add('show'); refresh();
    });

    // paste a screenshot / file
    body.addEventListener('paste', function (e) {
        var files = e.clipboardData && e.clipboardData.files;
        if (files && files.length) { e.preventDefault(); setFiles(files); }
    });

    // drag & drop onto the chat
    var drop = document.getElementById('dropZone'), depth = 0;
    function hasFiles(e) { return e.dataTransfer && Array.prototype.indexOf.call(e.dataTransfer.types || [], 'Files') !== -1; }
    chat.addEventListener('dragenter', function (e) { if (hasFiles(e)) { e.preventDefault(); depth++; drop.classList.add('show'); } });
    chat.addEventListener('dragover',  function (e) { if (hasFiles(e)) e.preventDefault(); });
    chat.addEventListener('dragleave', function () { depth = Math.max(0, depth - 1); if (!depth) drop.classList.remove('show'); });
    chat.addEventListener('drop', function (e) {
        if (!hasFiles(e)) return;
        e.preventDefault(); depth = 0; drop.classList.remove('show');
        setFiles(e.dataTransfer.files);
    });

    /* ---------- reply ---------- */
    function setReply(id, who, text) {
        replyIn.value = id;
        document.getElementById('replyWho').textContent = who;
        document.getElementById('replyText').textContent = text;
        replyBar.classList.add('show');
        body.focus();
    }
    function clearReply() { replyIn.value = ''; replyBar.classList.remove('show'); }
    document.getElementById('replyClose').addEventListener('click', clearReply);

    /* ---------- delegated clicks (still work after live updates) ---------- */
    var lbEl = document.getElementById('lightbox'), lb = new bootstrap.Modal(lbEl);
    document.addEventListener('click', function (e) {
        var r = e.target.closest('[data-reply]');
        if (r) { setReply(r.dataset.reply, r.dataset.who, r.dataset.snippet); return; }

        var g = e.target.closest('[data-goto]');
        if (g) {
            var el = document.getElementById(g.dataset.goto);
            if (el) {
                pinned = false;
                el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                el.classList.add('hl'); setTimeout(function () { el.classList.remove('hl'); }, 1500);
            }
            return;
        }
        var img = e.target.closest('[data-lightbox]');
        if (img) { document.getElementById('lightboxImg').src = img.dataset.lightbox; lb.show(); }
    });
    lbEl.addEventListener('hidden.bs.modal', function () { document.getElementById('lightboxImg').src = ''; });

    /* ---------- submit guard ---------- */
    form.addEventListener('submit', function (e) {
        if (sendBtn.disabled) { e.preventDefault(); return; }
        sending = true;
        try { localStorage.removeItem(DRAFT_KEY); } catch (err) {}
        sendBtn.disabled = true;
        sendBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
    });

    /* ---------- live updates (checks the page for newer messages) ---------- */
    function newestId() {
        var n = inner.querySelectorAll('[data-mid]');
        return n.length ? +n[n.length - 1].dataset.mid : 0;
    }
    var lastId = newestId(), busy = false;

    function poll() {
        if (document.hidden || sending || busy) return;
        busy = true;
        fetch(location.href, { credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { return r.ok ? r.text() : ''; })
            .then(function (html) {
                if (!html) return;
                var doc = new DOMParser().parseFromString(html, 'text/html');
                var fresh = doc.getElementById('chatInner');
                if (!fresh) return;

                var ids = fresh.querySelectorAll('[data-mid]');
                var top = ids.length ? +ids[ids.length - 1].dataset.mid : 0;
                if (top <= lastId) return;

                var added = 0;
                ids.forEach(function (n) { if (+n.dataset.mid > lastId) added++; });
                var stick = pinned || nearBottom();
                inner.innerHTML = fresh.innerHTML;
                lastId = top;

                if (stick) { pinned = true; down(true); } else { setUnread(unread + added); toBottom.classList.add('show'); }
            })
            .catch(function () {})
            .then(function () { busy = false; });
    }
    setInterval(poll, POLL_MS);
    document.addEventListener('visibilitychange', function () { if (!document.hidden) poll(); });
})();
</script>
@endpush