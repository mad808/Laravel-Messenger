@extends('front.layouts.master')

@section('title', __('My Profile'))
@section('header_title', __('My Profile'))
@section('search_placeholder', __('Search settings...'))

@php
    use Illuminate\Support\Facades\Route;

    $user     = auth()->user();
    $privacy  = $user->privacySetting ?? new \App\Models\UserPrivacySetting();
    $planName = $user->activeSubscription?->plan?->name;
    $planKey  = strtolower((string) ($planName ?: ($user->plan ?? 'free')));
    $isPro    = str_contains($planKey, 'pro');
    $isPaid   = $planKey !== '' && $planKey !== 'free';
    $avatar   = $user->avatar
        ? asset('storage/' . $user->avatar)
        : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=random&size=200';
    $upgrade  = Route::has('subscriptions.index') ? route('subscriptions.index') : null;

    // tab to open on load when the server sent errors
    $startTab = $errors->has('password') ? 'security'
              : ($errors->hasAny(['name', 'username', 'avatar']) ? 'general' : 'general');

    // [field, label, hint, needs: null|plus|pro]
    $basic = [
        ['hide_profile_photo',  __('Hide Profile Photo'),  __('Only your contacts can see your photo.'),                 null],
        ['hide_last_seen',      __('Hide Last Seen'),      __('Hide your last seen time from others.'),                  null],
        ['hide_online_status',  __('Hide Online Status'),  __('Hide your online status from others.'),                   null],
        ['hide_read_receipts',  __('Hide Read Receipts'),  __("Don't send read receipts in chats."),                     null],
        ['hide_phone_number',   __('Hide Phone Number'),   __('Hide your phone number from your profile.'),              null],
    ];
    $premium = [
        ['block_screenshots',   __('Block Screenshots'),   __('Prevent others from taking screenshots of your chats.'), 'pro'],
        ['anonymous_mode',      __('Anonymous Mode'),      __('Send messages without revealing your identity.'),         'pro'],
        ['show_profile_music',  __('Show Profile Music'),  __('Display the music you are listening to on your profile.'),'plus'],
    ];
    $allowed = fn($need) => $need === null || ($need === 'pro' ? $isPro : $isPaid);
@endphp

@section('sidebar_content')
    <div class="pf-side-user">
        <img src="{{ $avatar }}" alt="">
        <div class="min-w-0">
            <div class="fw-bold text-truncate">{{ $user->name }}</div>
            <small class="text-body-secondary d-block text-truncate">{{ $user->username ? '@' . $user->username : $user->phone }}</small>
        </div>
        <span class="badge rounded-pill ms-auto {{ $isPaid ? 'text-bg-warning' : 'text-bg-secondary' }}">{{ $planName ?: __('Free') }}</span>
    </div>

    <nav class="pf-tabs" id="pfTabs" role="tablist" aria-label="{{ __('Settings') }}">
        <a href="#general"  data-tab="general"  role="tab"><i class="bi bi-person"></i><span>{{ __('General Info') }}</span></a>
        <a href="#privacy"  data-tab="privacy"  role="tab"><i class="bi bi-shield-lock"></i><span>{{ __('Privacy Settings') }}</span></a>
        <a href="#security" data-tab="security" role="tab"><i class="bi bi-key"></i><span>{{ __('Security') }}</span></a>
    </nav>
@endsection

@push('styles')
<style>
    /* ---------- sidebar ---------- */
    .pf-side-user { display: flex; align-items: center; gap: .75rem; margin: 0 1rem .75rem; padding: .75rem; border-radius: 14px; background: var(--bs-tertiary-bg); }
    .pf-side-user img { width: 44px; height: 44px; flex: none; border-radius: 50%; object-fit: cover; }
    .pf-tabs { display: grid; gap: 2px; padding: 0 .75rem; }
    .pf-tabs a { display: flex; align-items: center; gap: .75rem; padding: .7rem .85rem; border-radius: 12px; color: var(--bs-body-color);
        text-decoration: none; font-weight: 500; transition: background-color .12s, color .12s; }
    .pf-tabs a i { font-size: 1.1rem; width: 1.25rem; text-align: center; }
    .pf-tabs a:hover { background: var(--bs-tertiary-bg); }
    .pf-tabs a.active { background: rgba(var(--bs-primary-rgb), .12); color: var(--bs-primary); font-weight: 700; }

    /* ---------- page ---------- */
    .pf-page { max-width: 760px; margin: 0 auto; padding: 1.5rem 1rem 2.5rem; }
    .pf-card { border: 1px solid var(--bs-border-color); border-radius: 18px; background: var(--bs-body-bg); overflow: hidden; }
    .pf-head { padding: 1.1rem 1.5rem; border-bottom: 1px solid var(--bs-border-color); }
    .pf-head h5 { margin: 0; font-weight: 800; font-size: 1.1rem; }
    .pf-head p { margin: .15rem 0 0; font-size: .85rem; color: var(--bs-secondary-color); }
    .pf-body { padding: 1.5rem; }
    .pf-foot { display: flex; justify-content: flex-end; gap: .5rem; padding: 1rem 1.5rem; border-top: 1px solid var(--bs-border-color); background: var(--bs-tertiary-bg); }
    .pf-pane[hidden] { display: none; }
    .pf-pane:not([hidden]) { animation: pfIn .25s ease; }
    @keyframes pfIn { from { opacity: 0; transform: translateY(6px); } }
    @media (prefers-reduced-motion: reduce) { .pf-pane:not([hidden]) { animation: none; } }

    /* ---------- avatar ---------- */
    .pf-avatar { position: relative; display: inline-block; }
    .pf-avatar img { width: 116px; height: 116px; border-radius: 50%; object-fit: cover; border: 4px solid var(--bs-body-bg); box-shadow: 0 4px 14px rgba(0, 0, 0, .2); }
    .pf-avatar label { position: absolute; right: 2px; bottom: 2px; width: 38px; height: 38px; display: grid; place-items: center; border-radius: 50%;
        background: var(--bs-primary); color: #fff; cursor: pointer; box-shadow: 0 2px 8px rgba(0, 0, 0, .3); transition: transform .15s; }
    .pf-avatar label:hover { transform: scale(1.08); }
    .pf-avatar input:focus-visible + label, .pf-avatar label:focus-within { outline: 3px solid rgba(var(--bs-primary-rgb), .4); }

    /* ---------- privacy rows ---------- */
    .pf-row { display: flex; align-items: center; justify-content: space-between; gap: 1rem; padding: .9rem 0; border-bottom: 1px solid var(--bs-border-color); }
    .pf-row:last-child { border-bottom: 0; }
    .pf-row h6 { margin: 0; font-weight: 600; font-size: .95rem; }
    .pf-row small { color: var(--bs-secondary-color); }
    .pf-row.locked { opacity: .6; }
    .pf-row .form-switch .form-check-input { width: 2.6em; height: 1.4em; cursor: pointer; }
    .pf-row.locked .form-check-input { cursor: not-allowed; }
    .pf-group-title { margin: 1.5rem 0 .25rem; font-size: .75rem; font-weight: 700; letter-spacing: .05em; text-transform: uppercase; color: var(--bs-secondary-color); }
    .pf-plan { display: flex; align-items: center; gap: .75rem; margin-bottom: 1rem; padding: .75rem 1rem; border-radius: 12px;
        background: linear-gradient(135deg, rgba(var(--bs-warning-rgb), .18), rgba(var(--bs-primary-rgb), .1)); font-size: .88rem; }

    /* ---------- password ---------- */
    .pf-pw { position: relative; }
    .pf-pw .form-control { padding-right: 3rem; }
    .pf-pw button { position: absolute; right: .35rem; top: 50%; translate: 0 -50%; width: 36px; height: 36px; border: 0; border-radius: 50%;
        background: transparent; color: var(--bs-secondary-color); }
    .pf-pw button:hover { background: var(--bs-tertiary-bg); color: var(--bs-body-color); }
    .pf-meter { display: flex; gap: 4px; margin-top: .5rem; }
    .pf-meter span { flex: 1; height: 5px; border-radius: 4px; background: var(--bs-border-color); transition: background-color .2s; }
    .pf-meter[data-s="1"] span:nth-child(-n+1) { background: var(--bs-danger); }
    .pf-meter[data-s="2"] span:nth-child(-n+2) { background: var(--bs-warning); }
    .pf-meter[data-s="3"] span:nth-child(-n+3) { background: var(--bs-info); }
    .pf-meter[data-s="4"] span:nth-child(-n+4) { background: var(--bs-success); }
    .pf-match { font-size: .8rem; margin-top: .35rem; min-height: 1.2em; }
</style>
@endpush

@section('content')
<div class="pf-page">

    {{-- 1. GENERAL --}}
    <section class="pf-pane" id="pane-general" hidden>
        <div class="pf-card">
            <div class="pf-head">
                <h5><i class="bi bi-person-circle text-primary me-2"></i>{{ __('General Info') }}</h5>
            </div>

            <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" id="formGeneral" novalidate>
                @csrf
                <div class="pf-body">
                    <div class="text-center mb-4">
                        <div class="pf-avatar">
                            <img src="{{ $avatar }}" alt="{{ $user->name }}" id="avatarPreview">
                            <input type="file" id="avatarInput" name="avatar" class="d-none" accept="image/jpeg,image/png,image/webp">
                            <label for="avatarInput" tabindex="0" aria-label="{{ __('Change photo') }}"><i class="bi bi-camera-fill"></i></label>
                        </div>
                        <p class="text-body-secondary small mt-2 mb-0">{{ __('Click the camera icon to change your profile photo.') }}</p>
                        <div class="text-danger small mt-1 {{ $errors->has('avatar') ? '' : 'd-none' }}" id="avatarError">{{ $errors->first('avatar') }}</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="pfName">{{ __('Full Name') }}</label>
                        <input type="text" id="pfName" name="name" value="{{ old('name', $user->name) }}" maxlength="100" required
                               class="form-control @error('name') is-invalid @enderror">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="pfUser">{{ __('Username') }}</label>
                        <div class="input-group @error('username') has-validation @enderror">
                            <span class="input-group-text">@</span>
                            <input type="text" id="pfUser" name="username" value="{{ old('username', $user->username) }}" maxlength="30"
                                   placeholder="username" autocapitalize="off" autocomplete="off" spellcheck="false"
                                   class="form-control @error('username') is-invalid @enderror">
                            @error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <small class="text-body-secondary">{{ __('Letters, numbers and underscore only.') }}</small>
                    </div>

                    <div>
                        <label class="form-label fw-semibold" for="pfPhone">{{ __('Phone Number') }}</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                            <input type="text" id="pfPhone" class="form-control" value="{{ $user->phone }}" disabled readonly>
                        </div>
                        <small class="text-body-secondary">{{ __('Phone number cannot be changed.') }}</small>
                    </div>
                </div>
                <div class="pf-foot">
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold" data-save disabled>{{ __('Save Changes') }}</button>
                </div>
            </form>
        </div>
    </section>

    {{-- 2. PRIVACY --}}
    <section class="pf-pane" id="pane-privacy" hidden>
        <div class="pf-card">
            <div class="pf-head">
                <h5><i class="bi bi-shield-lock text-primary me-2"></i>{{ __('Privacy Settings') }}</h5>
            </div>

            <form action="{{ route('profile.privacy.update') }}" method="POST" id="formPrivacy">
                @csrf
                <div class="pf-body">
                    @foreach([['', $basic], [__('Premium Features'), $premium]] as [$groupTitle, $rows])
                        @if($groupTitle)
                            <div class="pf-group-title">{{ $groupTitle }}</div>
                            @unless($isPaid)
                                <div class="pf-plan mt-2">
                                    <i class="bi bi-stars fs-4 text-warning"></i>
                                    <div class="flex-grow-1">{{ __('Upgrade your plan to unlock these features.') }}</div>
                                    @if($upgrade)<a href="{{ $upgrade }}" class="btn btn-sm btn-warning rounded-pill fw-bold">{{ __('Upgrade to Pro') }}</a>@endif
                                </div>
                            @endunless
                        @endif

                        @foreach($rows as [$field, $label, $hint, $need])
                            @php $ok = $allowed($need); @endphp
                            <div class="pf-row {{ $ok ? '' : 'locked' }}">
                                <div class="min-w-0">
                                    <h6>
                                        {{ $label }}
                                        @if($need === 'pro')<span class="badge text-bg-warning ms-1">PRO</span>
                                        @elseif($need === 'plus')<span class="badge text-bg-primary ms-1">PLUS</span>@endif
                                        @unless($ok)<i class="bi bi-lock-fill text-body-secondary ms-1"></i>@endunless
                                    </h6>
                                    <small>{{ $hint }}</small>
                                </div>
                                <div class="form-check form-switch m-0">
                                    @if($ok)<input type="hidden" name="{{ $field }}" value="0">@endif
                                    <input class="form-check-input" type="checkbox" role="switch" name="{{ $field }}" value="1"
                                           id="sw-{{ $field }}" aria-label="{{ $label }}"
                                           @checked(old($field, (bool) $privacy->{$field})) @disabled(! $ok)>
                                </div>
                            </div>
                        @endforeach
                    @endforeach
                </div>
                <div class="pf-foot">
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold" data-save disabled>{{ __('Save Privacy Settings') }}</button>
                </div>
            </form>
        </div>
    </section>

    {{-- 3. SECURITY --}}
    <section class="pf-pane" id="pane-security" hidden>
        <div class="pf-card">
            <div class="pf-head">
                <h5><i class="bi bi-key text-danger me-2"></i>{{ __('Security') }}</h5>
            </div>

            <form action="{{ route('profile.update') }}" method="POST" id="formSecurity" novalidate>
                @csrf
                {{-- the update route also validates these, so send the current values --}}
                <input type="hidden" name="name" value="{{ $user->name }}">
                <input type="hidden" name="username" value="{{ $user->username }}">

                <div class="pf-body">
                    <div class="alert alert-info py-2 small">
                        <i class="bi bi-info-circle me-1"></i>{{ __('If you change your password, you will be logged out of other devices.') }}
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="pw1">{{ __('New Password') }}</label>
                        <div class="pf-pw">
                            <input type="password" id="pw1" name="password" autocomplete="new-password"
                                   class="form-control @error('password') is-invalid @enderror"
                                   placeholder="{{ __('Leave empty to keep current password') }}">
                            <button type="button" data-eye="pw1" aria-label="{{ __('Show password') }}"><i class="bi bi-eye"></i></button>
                            @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="pf-meter" id="pwMeter" data-s="0"><span></span><span></span><span></span><span></span></div>
                        <small class="text-body-secondary" id="pwHint">{{ __('Use 8+ characters with letters, numbers and symbols.') }}</small>
                    </div>

                    <div>
                        <label class="form-label fw-semibold" for="pw2">{{ __('Confirm New Password') }}</label>
                        <div class="pf-pw">
                            <input type="password" id="pw2" name="password_confirmation" autocomplete="new-password" class="form-control">
                            <button type="button" data-eye="pw2" aria-label="{{ __('Show password') }}"><i class="bi bi-eye"></i></button>
                        </div>
                        <div class="pf-match" id="pwMatch"></div>
                    </div>
                </div>
                <div class="pf-foot">
                    <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold" data-save disabled>{{ __('Update Password') }}</button>
                </div>
            </form>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var START = @json($startTab);
    var TXT = {
        weak: @json(__('Weak')), fair: @json(__('Fair')), good: @json(__('Good')), strong: @json(__('Strong')),
        same: @json(__('Passwords match')), diff: @json(__('Passwords do not match')),
        type: @json(__('Only JPG, PNG or WEBP images.')), big: @json(__('Image is too large (max 5MB).')),
        hint: @json(__('Use 8+ characters with letters, numbers and symbols.'))
    };

    /* ---------- tabs (kept in the URL hash) ---------- */
    var links = document.querySelectorAll('#pfTabs [data-tab]');
    var panes = { general: 'pane-general', privacy: 'pane-privacy', security: 'pane-security' };
    function show(name, push) {
        if (!panes[name]) name = 'general';
        Object.keys(panes).forEach(function (k) { document.getElementById(panes[k]).hidden = (k !== name); });
        links.forEach(function (a) {
            var on = a.dataset.tab === name;
            a.classList.toggle('active', on);
            a.setAttribute('aria-selected', on ? 'true' : 'false');
        });
        if (push && history.replaceState) history.replaceState(null, '', '#' + name);
    }
    links.forEach(function (a) {
        a.addEventListener('click', function (e) {
            e.preventDefault(); show(a.dataset.tab, true);
            if (window.closeSidebar) window.closeSidebar();
        });
    });
    window.addEventListener('hashchange', function () { show(location.hash.slice(1), false); });
    var hash = location.hash.slice(1);
    show(START !== 'general' ? START : (panes[hash] ? hash : 'general'), false);

    /* ---------- save buttons: enabled only after a change ---------- */
    function watch(form, isDirty) {
        var btn = form.querySelector('[data-save]');
        function check() { btn.disabled = !isDirty(); }
        form.addEventListener('input', check);
        form.addEventListener('change', check);
        form.addEventListener('submit', function (e) {
            if (btn.disabled) { e.preventDefault(); return; }
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
        });
        return check;
    }

    // general
    var fg = document.getElementById('formGeneral'), avatarChanged = false;
    var name0 = fg.elements.name.value, user0 = fg.elements.username.value;
    watch(fg, function () {
        return avatarChanged || fg.elements.name.value !== name0 || fg.elements.username.value !== user0;
    });
    // old() values after a failed save count as changes
    if (fg.elements.name.classList.contains('is-invalid') || fg.elements.username.classList.contains('is-invalid')) {
        fg.querySelector('[data-save]').disabled = false;
    }

    // username: lowercase, no "@", only a-z 0-9 _
    fg.elements.username.addEventListener('input', function () {
        var v = this.value.replace(/^@+/, '').toLowerCase().replace(/[^a-z0-9_]/g, '');
        if (v !== this.value) this.value = v;
    });

    // avatar preview with checks
    var aIn = document.getElementById('avatarInput'), aErr = document.getElementById('avatarError'), aImg = document.getElementById('avatarPreview');
    var aUrl = null, aOld = aImg.src;
    aIn.addEventListener('change', function () {
        aErr.classList.add('d-none');
        var f = aIn.files[0];
        if (!f) { aImg.src = aOld; avatarChanged = false; return; }
        var msg = ['image/jpeg', 'image/png', 'image/webp'].indexOf(f.type) === -1 ? TXT.type : (f.size > 5 * 1048576 ? TXT.big : '');
        if (msg) { aIn.value = ''; aErr.textContent = msg; aErr.classList.remove('d-none'); aImg.src = aOld; avatarChanged = false; return; }
        if (aUrl) URL.revokeObjectURL(aUrl);
        aUrl = URL.createObjectURL(f); aImg.src = aUrl; avatarChanged = true;
    });
    document.querySelector('.pf-avatar label').addEventListener('keydown', function (e) {
        if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); aIn.click(); }
    });

    // privacy
    var fp = document.getElementById('formPrivacy');
    function snap() { return Array.prototype.map.call(fp.querySelectorAll('input[type=checkbox]'), function (c) { return c.checked ? 1 : 0; }).join(''); }
    var p0 = snap();
    watch(fp, function () { return snap() !== p0; });

    /* ---------- security ---------- */
    var fs = document.getElementById('formSecurity');
    var pw1 = document.getElementById('pw1'), pw2 = document.getElementById('pw2');
    var meter = document.getElementById('pwMeter'), hint = document.getElementById('pwHint'), match = document.getElementById('pwMatch');

    function score(p) {
        if (!p) return 0;
        var s = 0;
        if (p.length >= 8) s++;
        if (/[a-z]/.test(p) && /[A-Z]/.test(p)) s++;
        if (/\d/.test(p)) s++;
        if (/[^A-Za-z0-9]/.test(p) || p.length >= 14) s++;
        return Math.max(1, s);
    }
    function ok() { return pw1.value.length >= 8 && pw1.value === pw2.value; }
    var checkSec = watch(fs, ok);

    function paint() {
        var s = score(pw1.value);
        meter.dataset.s = s;
        hint.textContent = pw1.value ? [TXT.hint, TXT.weak, TXT.fair, TXT.good, TXT.strong][s] : TXT.hint;
        if (pw2.value) {
            var same = pw1.value === pw2.value;
            match.textContent = same ? TXT.same : TXT.diff;
            match.className = 'pf-match ' + (same ? 'text-success' : 'text-danger');
        } else { match.textContent = ''; }
        checkSec();
    }
    pw1.addEventListener('input', paint);
    pw2.addEventListener('input', paint);

    document.querySelectorAll('[data-eye]').forEach(function (b) {
        b.addEventListener('click', function () {
            var f = document.getElementById(b.dataset.eye), show = f.type === 'password';
            f.type = show ? 'text' : 'password';
            b.firstElementChild.className = 'bi ' + (show ? 'bi-eye-slash' : 'bi-eye');
        });
    });
})();
</script>
@endpush