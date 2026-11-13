@extends('front.layouts.master')

@section('title', __('Stories'))
@section('header_title', __('Stories'))
@section('search_placeholder', __('Search stories...'))

@php
    $meId = (int) auth()->id();

    $avatarOf = fn($u) => $u && $u->avatar
        ? asset('storage/' . $u->avatar)
        : 'https://ui-avatars.com/api/?name=' . urlencode($u->name ?? '?') . '&background=random';

    // one group per user, my group first, oldest story first inside a group
    $groups = $stories->groupBy('user_id')
        ->map(fn($g) => $g->sortBy('created_at')->values())
        ->sortBy(fn($g) => (int) $g->first()->user_id === $meId ? 0 : 1)
        ->values();

    $myGroupIndex = $groups->search(fn($g) => (int) $g->first()->user_id === $meId);
    $myStories    = $myGroupIndex === false ? collect() : $groups[$myGroupIndex];

    $left = fn($s) => $s->expires_at ? $s->expires_at->diffForHumans(null, true) : '';

    // data for the viewer (JSON, no HTML injection)
    $payload = $groups->map(fn($g) => [
        'mine'   => (int) $g->first()->user_id === $meId,
        'name'   => (int) $g->first()->user_id === $meId ? __('You') : ($g->first()->user->name ?? __('Unknown User')),
        'avatar' => $avatarOf($g->first()->user),
        'items'  => $g->map(fn($s) => [
            'id'   => $s->id,
            'type' => $s->type === 'video' ? 'video' : 'image',
            'src'  => asset('storage/' . $s->media_path),
            'time' => $s->created_at->diffForHumans(),
        ])->values(),
    ])->values();
@endphp

@section('sidebar_content')
    <div class="st-side-title">{{ __('My Stories') }}</div>

    @forelse($myStories as $n => $my)
        <button type="button" class="st-side-row" data-open-group="{{ $myGroupIndex }}" data-open-index="{{ $n }}">
            <span class="st-side-ico"><i class="bi {{ $my->type === 'video' ? 'bi-camera-reels-fill' : 'bi-image-fill' }}"></i></span>
            <span class="min-w-0 text-start">
                <span class="d-block fw-semibold small text-truncate">{{ $my->created_at->diffForHumans() }}</span>
                @if($left($my))<small class="text-body-secondary"><i class="bi bi-hourglass-split me-1"></i>{{ $left($my) }}</small>@endif
            </span>
        </button>
    @empty
        <p class="small text-body-secondary fst-italic px-3">{{ __('No active stories.') }}</p>
    @endforelse
@endsection

@push('styles')
<style>
    /* ---------- sidebar ---------- */
    .st-side-title { padding: .5rem 1rem .35rem; font-size: .75rem; font-weight: 700; letter-spacing: .05em; text-transform: uppercase; color: var(--bs-secondary-color); }
    .st-side-row { display: flex; align-items: center; gap: .75rem; width: 100%; padding: .6rem 1rem; border: 0; background: transparent; color: var(--bs-body-color);
        border-bottom: 1px solid var(--bs-border-color); transition: background-color .12s; }
    .st-side-row:hover { background: var(--bs-tertiary-bg); }
    .st-side-ico { width: 38px; height: 38px; flex: none; display: grid; place-items: center; border-radius: 12px; background: rgba(var(--bs-primary-rgb), .12); color: var(--bs-primary); }

    /* ---------- page ---------- */
    .st-page { max-width: 1100px; margin: 0 auto; padding: 1.25rem 1rem 2rem; }

    .st-tray { display: flex; gap: 1rem; overflow-x: auto; padding: .25rem .25rem 1rem; scrollbar-width: none; }
    .st-tray::-webkit-scrollbar { display: none; }
    .st-user { flex: none; width: 76px; border: 0; background: transparent; padding: 0; text-align: center; color: var(--bs-body-color); }
    .st-ring { width: 68px; height: 68px; margin: 0 auto .35rem; padding: 3px; border-radius: 50%;
        background: conic-gradient(var(--bs-primary), var(--bs-info), var(--bs-primary)); transition: transform .15s; }
    .st-user:hover .st-ring { transform: scale(1.06); }
    .st-ring.seen { background: var(--bs-border-color); }
    .st-ring img { width: 100%; height: 100%; border-radius: 50%; object-fit: cover; border: 3px solid var(--bs-body-bg); background: var(--bs-body-bg); }
    .st-add .st-ring { background: transparent; border: 2px dashed var(--bs-primary); display: grid; place-items: center; color: var(--bs-primary); font-size: 1.6rem; }
    .st-user small { display: block; font-size: .74rem; line-height: 1.2; }

    .st-bar { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: .5rem; margin: .5rem 0 1rem; }
    .st-bar h2 { margin: 0; font-size: 1.15rem; font-weight: 800; }

    .st-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: .85rem; }
    .st-card { position: relative; aspect-ratio: 3 / 4; overflow: hidden; border: 0; padding: 0; border-radius: 16px; background: #000;
        transition: transform .2s, box-shadow .2s; }
    .st-card:hover { transform: translateY(-3px); box-shadow: 0 .6rem 1.2rem rgba(0, 0, 0, .25); }
    .st-card > img, .st-card > video { width: 100%; height: 100%; object-fit: cover; display: block; pointer-events: none; }
    .st-shade { position: absolute; inset: auto 0 0 0; padding: 2rem .6rem .55rem; text-align: left; color: #fff;
        background: linear-gradient(to top, rgba(0, 0, 0, .8), transparent); }
    .st-shade .nm { display: flex; align-items: center; gap: .45rem; font-weight: 700; font-size: .8rem; }
    .st-shade .nm img { width: 26px; height: 26px; border-radius: 50%; object-fit: cover; border: 2px solid #fff; flex: none; }
    .st-shade small { display: block; margin-top: 2px; font-size: .68rem; opacity: .85; }
    .st-play { position: absolute; top: 50%; left: 50%; translate: -50% -50%; font-size: 2.4rem; color: #fff; text-shadow: 0 2px 10px rgba(0, 0, 0, .5); }
    .st-badge { position: absolute; top: .5rem; right: .5rem; }
    .st-left { position: absolute; top: .5rem; left: .5rem; padding: .15rem .5rem; border-radius: 999px; font-size: .66rem; font-weight: 600;
        color: #fff; background: rgba(0, 0, 0, .55); backdrop-filter: blur(4px); }

    .st-empty { text-align: center; padding: 3.5rem 1rem; color: var(--bs-secondary-color); border: 1px dashed var(--bs-border-color); border-radius: 16px; }
    .st-empty i { font-size: 3rem; display: block; margin-bottom: .5rem; opacity: .5; }

    /* ---------- upload modal ---------- */
    .st-drop { display: grid; place-items: center; gap: .25rem; padding: 1.75rem 1rem; text-align: center; cursor: pointer;
        border: 2px dashed var(--bs-border-color); border-radius: 14px; background: var(--bs-tertiary-bg); transition: border-color .15s, background-color .15s; }
    .st-drop:hover, .st-drop.over { border-color: var(--bs-primary); background: rgba(var(--bs-primary-rgb), .06); }
    .st-drop i { font-size: 2rem; color: var(--bs-primary); }
    .st-drop input { display: none; }
    #storyPreview img, #storyPreview video { max-width: 100%; max-height: 260px; border-radius: 12px; }

    /* ---------- viewer ---------- */
    .sv { position: fixed; inset: 0; z-index: 2000; display: none; align-items: center; justify-content: center; background: rgba(0, 0, 0, .92); }
    .sv.show { display: flex; }
    .sv-box { position: relative; width: min(100vw, 420px); height: min(100dvh, 760px); background: #000; overflow: hidden; border-radius: 18px; }
    @media (max-width: 575.98px) { .sv-box { width: 100vw; height: 100dvh; border-radius: 0; } }
    .sv-stage { position: absolute; inset: 0; display: grid; place-items: center; }
    .sv-stage img, .sv-stage video { width: 100%; height: 100%; object-fit: contain; }
    .sv-top { position: absolute; inset: 0 0 auto 0; z-index: 3; padding: .75rem .85rem 2.5rem; color: #fff;
        background: linear-gradient(to bottom, rgba(0, 0, 0, .7), transparent); }
    .sv-bars { display: flex; gap: 4px; margin-bottom: .65rem; }
    .sv-bars span { flex: 1; height: 3px; border-radius: 3px; background: rgba(255, 255, 255, .35); overflow: hidden; }
    .sv-bars span i { display: block; height: 100%; width: 0; background: #fff; }
    .sv-who { display: flex; align-items: center; gap: .6rem; }
    .sv-who img { width: 38px; height: 38px; border-radius: 50%; object-fit: cover; }
    .sv-who .nm { font-weight: 700; line-height: 1.1; }
    .sv-who small { opacity: .8; font-size: .74rem; }
    .sv-close { margin-left: auto; width: 38px; height: 38px; border: 0; border-radius: 50%; background: rgba(255, 255, 255, .12); color: #fff; font-size: 1.1rem; }
    .sv-zone { position: absolute; top: 4.5rem; bottom: 0; z-index: 2; width: 38%; border: 0; background: transparent; }
    .sv-zone.l { left: 0; } .sv-zone.r { right: 0; width: 62%; }
    .sv-arrow { position: absolute; top: 50%; translate: 0 -50%; z-index: 5; width: 44px; height: 44px; border: 0; border-radius: 50%;
        background: rgba(255, 255, 255, .15); color: #fff; font-size: 1.2rem; display: none; }
    @media (min-width: 576px) { .sv-arrow { display: grid; place-items: center; } }
    .sv-arrow.l { right: calc(50% + 210px + 16px); } .sv-arrow.r { left: calc(50% + 210px + 16px); }
</style>
@endpush

@section('content')
<div class="st-page">

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Story tray --}}
    <div class="st-tray">
        <button type="button" class="st-user st-add" data-bs-toggle="modal" data-bs-target="#addStoryModal">
            <div class="st-ring"><i class="bi bi-plus-lg"></i></div>
            <small class="fw-semibold">{{ __('Add Story') }}</small>
        </button>

        @foreach($groups as $gi => $g)
            @php $mineG = (int) $g->first()->user_id === $meId; @endphp
            <button type="button" class="st-user" data-open-group="{{ $gi }}" data-open-index="0" data-ring="{{ $gi }}">
                <div class="st-ring"><img src="{{ $avatarOf($g->first()->user) }}" alt="" loading="lazy"></div>
                <small class="text-truncate fw-semibold">{{ $mineG ? __('You') : \Illuminate\Support\Str::limit($g->first()->user->name ?? '?', 11) }}</small>
            </button>
        @endforeach
    </div>

    <div class="st-bar">
        <h2><i class="bi bi-camera-reels-fill text-primary me-2"></i>{{ __('Active Stories') }}</h2>
        <span class="badge text-bg-primary rounded-pill">{{ $stories->count() }} {{ __('Stories') }}</span>
    </div>

    @if($stories->isEmpty())
        <div class="st-empty">
            <i class="bi bi-camera-reels"></i>
            <h5 class="fw-bold text-body">{{ __('No active stories right now.') }}</h5>
            <p class="mb-3">{{ __('Be the first to share a moment!') }}</p>
            <button class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#addStoryModal">
                <i class="bi bi-plus-circle me-1"></i>{{ __('Add Story') }}
            </button>
        </div>
    @else
        <div class="st-grid">
            @foreach($groups as $gi => $g)
                @foreach($g as $si => $story)
                    @php
                        $mineS = (int) $story->user_id === $meId;
                        $url   = asset('storage/' . $story->media_path);
                    @endphp
                    <button type="button" class="st-card" data-open-group="{{ $gi }}" data-open-index="{{ $si }}"
                            aria-label="{{ $mineS ? __('You') : ($story->user->name ?? '') }}">
                        @if($story->type === 'video')
                            <video src="{{ $url }}#t=0.5" preload="metadata" muted playsinline></video>
                            <i class="bi bi-play-circle-fill st-play"></i>
                        @else
                            <img src="{{ $url }}" alt="" loading="lazy">
                        @endif

                        @if($left($story))<span class="st-left"><i class="bi bi-hourglass-split me-1"></i>{{ $left($story) }}</span>@endif
                        @if($mineS)<span class="badge text-bg-primary st-badge">{{ __('You') }}</span>@endif

                        <span class="st-shade">
                            <span class="nm">
                                <img src="{{ $avatarOf($story->user) }}" alt="">
                                <span class="text-truncate">{{ $mineS ? __('You') : ($story->user->name ?? __('Unknown User')) }}</span>
                            </span>
                            <small>{{ $story->created_at->diffForHumans() }}</small>
                        </span>
                    </button>
                @endforeach
            @endforeach
        </div>
    @endif
</div>

{{-- Add story modal --}}
<div class="modal fade" id="addStoryModal" tabindex="-1" aria-labelledby="addStoryTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold" id="addStoryTitle"><i class="bi bi-plus-circle text-primary me-2"></i>{{ __('Add New Story') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
            </div>
            <form action="{{ route('stories.store') }}" method="POST" enctype="multipart/form-data" id="storyForm">
                @csrf
                <input type="hidden" name="type" id="storyType" value="image">
                <div class="modal-body">
                    <label class="st-drop" id="storyDrop" for="storyMedia">
                        <i class="bi bi-cloud-arrow-up"></i>
                        <span class="fw-semibold">{{ __('Select Image or Video') }}</span>
                        <small class="text-body-secondary">{{ __('Max size: 20MB. (JPG, PNG, MP4, WEBM)') }}</small>
                        <input type="file" name="media" id="storyMedia" accept="image/jpeg,image/png,video/mp4,video/webm" required>
                    </label>
                    <div id="storyError" class="text-danger small mt-2 d-none"></div>
                    <div id="storyPreview" class="text-center mt-3 d-none"></div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary rounded-pill fw-bold px-4" id="storySubmit" disabled>{{ __('Upload Story') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Story viewer --}}
<div class="sv" id="sv" role="dialog" aria-modal="true" aria-label="{{ __('Stories') }}">
    <button type="button" class="sv-arrow l" id="svPrev" aria-label="{{ __('Previous') }}"><i class="bi bi-chevron-left"></i></button>
    <div class="sv-box">
        <div class="sv-top">
            <div class="sv-bars" id="svBars"></div>
            <div class="sv-who">
                <img id="svAvatar" src="" alt="">
                <div>
                    <div class="nm" id="svName"></div>
                    <small id="svTime"></small>
                </div>
                <button type="button" class="sv-close" id="svClose" aria-label="{{ __('Close') }}"><i class="bi bi-x-lg"></i></button>
            </div>
        </div>
        <div class="sv-stage" id="svStage"></div>
        <button type="button" class="sv-zone l" id="svZoneL" aria-label="{{ __('Previous') }}"></button>
        <button type="button" class="sv-zone r" id="svZoneR" aria-label="{{ __('Next') }}"></button>
    </div>
    <button type="button" class="sv-arrow r" id="svNext" aria-label="{{ __('Next') }}"><i class="bi bi-chevron-right"></i></button>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var groups = @json($payload);
    var IMG_MS = 5000, MAX_MB = 20;

    /* ---------- seen state (browser only) ---------- */
    var seen = {};
    try { seen = JSON.parse(localStorage.getItem('seenStories') || '{}'); } catch (e) {}
    function saveSeen() { try { localStorage.setItem('seenStories', JSON.stringify(seen)); } catch (e) {} }
    function paintRings() {
        document.querySelectorAll('[data-ring]').forEach(function (b) {
            var g = groups[+b.dataset.ring];
            var all = g && g.items.every(function (s) { return seen[s.id]; });
            b.querySelector('.st-ring').classList.toggle('seen', !!all && !g.mine);
        });
    }
    paintRings();

    /* ---------- viewer ---------- */
    var sv = document.getElementById('sv'), stage = document.getElementById('svStage'), bars = document.getElementById('svBars');
    var gi = 0, ii = 0, raf = null, t0 = 0;

    function stop() { if (raf) cancelAnimationFrame(raf); raf = null; stage.innerHTML = ''; }

    function render() {
        stop();
        var g = groups[gi], s = g.items[ii];
        document.getElementById('svAvatar').src = g.avatar;
        document.getElementById('svName').textContent = g.name;
        document.getElementById('svTime').textContent = s.time;

        bars.innerHTML = '';
        g.items.forEach(function (_, k) {
            var seg = document.createElement('span'), fill = document.createElement('i');
            if (k < ii) fill.style.width = '100%';
            seg.appendChild(fill); bars.appendChild(seg);
        });
        var fill = bars.children[ii].firstChild;

        seen[s.id] = 1; saveSeen(); paintRings();

        if (s.type === 'video') {
            var v = document.createElement('video');
            v.src = s.src; v.autoplay = true; v.playsInline = true;
            v.addEventListener('timeupdate', function () { if (v.duration) fill.style.width = (v.currentTime / v.duration * 100) + '%'; });
            v.addEventListener('ended', next);
            stage.appendChild(v);
        } else {
            var im = document.createElement('img');
            im.src = s.src; im.alt = '';
            stage.appendChild(im);
            t0 = 0;
            (function tick(ts) {
                if (!t0) t0 = ts;
                var p = Math.min((ts - t0) / IMG_MS, 1);
                fill.style.width = (p * 100) + '%';
                if (p >= 1) next(); else raf = requestAnimationFrame(tick);
            })(performance.now());
        }
    }

    function open(g, i) { gi = g; ii = i; sv.classList.add('show'); render(); }
    function close() { stop(); sv.classList.remove('show'); }
    function next() {
        if (ii < groups[gi].items.length - 1) { ii++; }
        else if (gi < groups.length - 1) { gi++; ii = 0; }
        else { close(); return; }
        render();
    }
    function prev() {
        if (ii > 0) { ii--; }
        else if (gi > 0) { gi--; ii = groups[gi].items.length - 1; }
        render();
    }

    document.querySelectorAll('[data-open-group]').forEach(function (el) {
        el.addEventListener('click', function () {
            if (window.closeSidebar) window.closeSidebar();
            open(+el.dataset.openGroup, +el.dataset.openIndex || 0);
        });
    });
    document.getElementById('svClose').addEventListener('click', close);
    document.getElementById('svNext').addEventListener('click', next);
    document.getElementById('svPrev').addEventListener('click', prev);
    document.getElementById('svZoneR').addEventListener('click', next);
    document.getElementById('svZoneL').addEventListener('click', prev);
    sv.addEventListener('click', function (e) { if (e.target === sv) close(); });
    document.addEventListener('keydown', function (e) {
        if (!sv.classList.contains('show')) return;
        if (e.key === 'Escape') close();
        else if (e.key === 'ArrowRight') next();
        else if (e.key === 'ArrowLeft') prev();
    });

    /* ---------- upload ---------- */
    var input = document.getElementById('storyMedia'), typeIn = document.getElementById('storyType');
    var preview = document.getElementById('storyPreview'), err = document.getElementById('storyError');
    var submit = document.getElementById('storySubmit'), drop = document.getElementById('storyDrop');
    var url = null;
    var okTypes = ['image/jpeg', 'image/png', 'video/mp4', 'video/webm'];

    function reset() {
        if (url) URL.revokeObjectURL(url); url = null;
        preview.innerHTML = ''; preview.classList.add('d-none'); err.classList.add('d-none');
        submit.disabled = true;
    }
    function fail(msg) { input.value = ''; reset(); err.textContent = msg; err.classList.remove('d-none'); }

    input.addEventListener('change', function () {
        reset();
        var f = input.files[0];
        if (!f) return;
        if (okTypes.indexOf(f.type) === -1) return fail(@json(__('Unsupported file type.')));
        if (f.size > MAX_MB * 1048576) return fail(@json(__('File is too large (max 20MB).')));

        var video = f.type.indexOf('video/') === 0;
        typeIn.value = video ? 'video' : 'image';
        url = URL.createObjectURL(f);
        var el = document.createElement(video ? 'video' : 'img');
        el.src = url;
        if (video) { el.controls = true; el.muted = true; }
        preview.appendChild(el); preview.classList.remove('d-none');
        submit.disabled = false;
    });

    ['dragenter', 'dragover'].forEach(function (ev) { drop.addEventListener(ev, function (e) { e.preventDefault(); drop.classList.add('over'); }); });
    ['dragleave', 'drop'].forEach(function (ev) { drop.addEventListener(ev, function (e) { e.preventDefault(); drop.classList.remove('over'); }); });
    drop.addEventListener('drop', function (e) {
        if (e.dataTransfer.files.length) { input.files = e.dataTransfer.files; input.dispatchEvent(new Event('change')); }
    });

    document.getElementById('storyForm').addEventListener('submit', function (e) {
        if (submit.disabled) { e.preventDefault(); return; }
        submit.disabled = true;
        submit.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>' + @json(__('Uploading...'));
    });
    document.getElementById('addStoryModal').addEventListener('hidden.bs.modal', function () { input.value = ''; reset(); });

    @if($errors->any())
        new bootstrap.Modal(document.getElementById('addStoryModal')).show();
    @endif
})();
</script>
@endpush