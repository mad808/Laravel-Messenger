@extends('front.layouts.master')

@section('title', __('Contacts'))
@section('header_title', __('My Contacts'))
@section('search_placeholder', __('Search contacts...'))

@php
    use Illuminate\Support\Str;

    // drop contacts whose user no longer exists
    $contacts = $contacts->filter(fn($c) => $c->contactUser)->values();

    $avatarOf = fn($name, $path) => $path
        ? asset('storage/' . $path)
        : 'https://ui-avatars.com/api/?name=' . urlencode($name ?: '?') . '&background=random';
    $nameOf = fn($c) => $c->nickname ?: $c->contactUser->name;
@endphp

@section('sidebar_content')
    <div class="ct-head">
        <span>{{ __('Contacts') }} <span class="badge text-bg-secondary rounded-pill ms-1">{{ $contacts->count() }}</span></span>
        <button type="button" class="ct-add" data-bs-toggle="modal" data-bs-target="#addContactModal">
            <i class="bi bi-person-plus me-1"></i>{{ __('Add Contact') }}
        </button>
    </div>

    @forelse($contacts as $contact)
        @php $n = $nameOf($contact); @endphp
        <a href="#contact-{{ $contact->id }}" class="ct-row" data-search-item data-jump="contact-{{ $contact->id }}">
            <img src="{{ $avatarOf($n, $contact->contactUser->avatar) }}" alt="{{ $n }}" loading="lazy">
            <div class="min-w-0">
                <div class="fw-semibold text-truncate">{{ $n }}</div>
                <small class="text-body-secondary d-block text-truncate">{{ $contact->contactUser->phone }}</small>
            </div>
        </a>
    @empty
        <div class="sb-empty">
            <div class="sb-empty-icon"><i class="bi bi-person-x"></i></div>
            <h6 class="fw-bold mb-1">{{ __('No contacts yet') }}</h6>
            <p class="small text-body-secondary mb-0">{{ __('Add someone by their phone number to start.') }}</p>
        </div>
    @endforelse
@endsection

@push('styles')
<style>
    /* sidebar */
    .ct-head { display: flex; align-items: center; justify-content: space-between; gap: .5rem; padding: .25rem 1rem .6rem;
        font-size: .75rem; font-weight: 700; letter-spacing: .05em; text-transform: uppercase; color: var(--bs-secondary-color); }
    .ct-add { border: 0; border-radius: 999px; padding: .3rem .8rem; font-size: .75rem; font-weight: 700; letter-spacing: 0;
        text-transform: none; background: var(--bs-primary); color: #fff; }
    .ct-add:hover { filter: brightness(1.08); }
    .ct-row { display: flex; align-items: center; gap: .75rem; padding: .65rem 1rem; color: var(--bs-body-color);
        text-decoration: none; border-bottom: 1px solid var(--bs-border-color); transition: background-color .12s; }
    .ct-row:hover { background: var(--bs-tertiary-bg); color: var(--bs-body-color); }
    .ct-row img { width: 42px; height: 42px; flex: none; border-radius: 50%; object-fit: cover; }

    /* page */
    .ct-page { max-width: 960px; margin: 0 auto; padding: 1.5rem 1rem 2rem; }
    .ct-bar { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: .75rem; margin-bottom: 1.25rem; }
    .ct-bar h2 { margin: 0; font-size: 1.25rem; font-weight: 800; }
    .ct-find { position: relative; width: min(100%, 320px); }
    .ct-find .bi-search { position: absolute; left: 1rem; top: 50%; translate: 0 -50%; color: var(--bs-secondary-color); pointer-events: none; }
    .ct-find input { padding-left: 2.5rem; border-radius: 999px; }

    .ct-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1rem; }
    .ct-card { display: flex; flex-direction: column; gap: .9rem; padding: 1rem; border: 1px solid var(--bs-border-color);
        border-radius: 16px; background: var(--bs-body-bg); transition: box-shadow .2s, border-color .2s; scroll-margin-top: 1rem; }
    .ct-card:hover { box-shadow: 0 .4rem 1rem rgba(0, 0, 0, .1); }
    .ct-card.flash { border-color: var(--bs-primary); box-shadow: 0 0 0 .2rem rgba(var(--bs-primary-rgb), .2); }
    .ct-card img { width: 56px; height: 56px; flex: none; border-radius: 50%; object-fit: cover; }
    .ct-actions { display: flex; gap: .5rem; margin-top: auto; }
    .ct-actions form { margin: 0; }
    .ct-actions .btn-msg { flex: 1; }

    .ct-empty { text-align: center; padding: 3.5rem 1rem; color: var(--bs-secondary-color); border: 1px dashed var(--bs-border-color); border-radius: 16px; }
    .ct-empty i { font-size: 3rem; display: block; margin-bottom: .5rem; opacity: .5; }
</style>
@endpush

@section('content')
<div class="ct-page">

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="ct-bar">
        <h2><i class="bi bi-people-fill text-primary me-2"></i>{{ __('Contact List') }}</h2>
        <div class="d-flex flex-wrap align-items-center gap-2">
            @if($contacts->count() > 4)
                <div class="ct-find">
                    <i class="bi bi-search"></i>
                    <input type="search" id="ctFind" class="form-control" placeholder="{{ __('Search contacts...') }}" autocomplete="off">
                </div>
            @endif
            <button class="btn btn-primary rounded-pill px-3 fw-bold" data-bs-toggle="modal" data-bs-target="#addContactModal">
                <i class="bi bi-person-plus me-1"></i>{{ __('Add Contact') }}
            </button>
        </div>
    </div>

    @if($contacts->isEmpty())
        <div class="ct-empty">
            <i class="bi bi-person-x"></i>
            <h5 class="fw-bold text-body">{{ __('No contacts yet') }}</h5>
            <p class="mb-3">{{ __('Add someone by their phone number to start.') }}</p>
            <!-- <button class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#addContactModal">
                <i class="bi bi-person-plus me-1"></i>{{ __('Add Contact') }}
            </button> -->
        </div>
    @else
        <div class="ct-grid" id="ctGrid">
            @foreach($contacts as $contact)
                @php $n = $nameOf($contact); $u = $contact->contactUser; @endphp
                <div class="ct-card" id="contact-{{ $contact->id }}"
                     data-ct-item data-text="{{ Str::lower($n . ' ' . $u->name . ' ' . $u->phone . ' ' . $u->username) }}">
                    <div class="d-flex align-items-center gap-3">
                        <img src="{{ $avatarOf($n, $u->avatar) }}" alt="{{ $n }}" loading="lazy">
                        <div class="min-w-0">
                            <div class="fw-bold text-truncate">{{ $n }}</div>
                            @if($contact->nickname && $contact->nickname !== $u->name)
                                <small class="text-body-secondary d-block text-truncate">{{ $u->name }}</small>
                            @endif
                            <small class="font-monospace text-body-secondary">{{ $u->phone }}</small>
                        </div>
                    </div>

                    <div class="ct-actions">
                        <form action="{{ route('conversations.store') }}" method="POST" class="btn-msg d-flex">
                            @csrf
                            <input type="hidden" name="user_id" value="{{ $u->id }}">
                            <button type="submit" class="btn btn-outline-primary rounded-pill w-100 fw-semibold">
                                <i class="bi bi-chat-dots me-1"></i>{{ __('Message') }}
                            </button>
                        </form>

                        <form action="{{ route('contacts.destroy', $contact) }}" method="POST"
                              data-confirm="{{ __('Remove this contact?') }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger rounded-circle" style="width: 38px; height: 38px; padding: 0;"
                                    title="{{ __('Delete') }}" aria-label="{{ __('Delete') }}">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="ct-empty d-none" id="ctNone"><i class="bi bi-search"></i>{{ __('Nothing found') }}</div>
    @endif
</div>

{{-- Add contact modal --}}
<div class="modal fade" id="addContactModal" tabindex="-1" aria-labelledby="addContactTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold" id="addContactTitle"><i class="bi bi-person-plus text-primary me-2"></i>{{ __('Add New Contact') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
            </div>
            <form action="{{ route('contacts.store') }}" method="POST" id="addContactForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="ctPhone">{{ __('Phone Number') }}</label>
                        <input type="tel" id="ctPhone" name="phone" value="{{ old('phone') }}"
                               class="form-control @error('phone') is-invalid @enderror"
                               placeholder="+993 XX XXXXXX" inputmode="tel" autocomplete="off" required>
                        @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <small class="text-body-secondary">{{ __('Enter the phone number of the registered user.') }}</small>
                    </div>
                    <div class="mb-1">
                        <label class="form-label fw-semibold" for="ctNick">{{ __('Nickname (Optional)') }}</label>
                        <input type="text" id="ctNick" name="nickname" value="{{ old('nickname') }}" maxlength="50"
                               class="form-control @error('nickname') is-invalid @enderror"
                               placeholder="{{ __('e.g. Brother, Mom, Work') }}">
                        @error('nickname')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary rounded-pill fw-bold px-4" id="ctSubmit">{{ __('Add Contact') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    /* confirm before destructive forms */
    document.addEventListener('submit', function (e) {
        var msg = e.target.getAttribute && e.target.getAttribute('data-confirm');
        if (msg && !window.confirm(msg)) e.preventDefault();
    });

    /* page search (cards) */
    var find  = document.getElementById('ctFind');
    var items = Array.prototype.slice.call(document.querySelectorAll('[data-ct-item]'));
    var none  = document.getElementById('ctNone');
    if (find) find.addEventListener('input', function () {
        var q = find.value.trim().toLowerCase(), visible = 0;
        items.forEach(function (el) {
            var show = !q || el.dataset.text.indexOf(q) !== -1;
            el.classList.toggle('d-none', !show);
            if (show) visible++;
        });
        if (none) none.classList.toggle('d-none', visible !== 0);
    });

    /* sidebar row -> scroll to and highlight the card (and close the mobile sidebar) */
    document.querySelectorAll('[data-jump]').forEach(function (a) {
        a.addEventListener('click', function (e) {
            e.preventDefault();
            var card = document.getElementById(a.dataset.jump);
            if (!card) return;
            card.classList.remove('d-none');
            card.scrollIntoView({ behavior: 'smooth', block: 'center' });
            card.classList.add('flash');
            setTimeout(function () { card.classList.remove('flash'); }, 1600);
            if (window.closeSidebar) window.closeSidebar();
        });
    });

    /* add-contact modal */
    var modalEl = document.getElementById('addContactModal');
    var modal   = new bootstrap.Modal(modalEl);
    var submit  = document.getElementById('ctSubmit');
    modalEl.addEventListener('shown.bs.modal', function () { document.getElementById('ctPhone').focus(); });
    document.getElementById('addContactForm').addEventListener('submit', function () {
        submit.disabled = true;
        submit.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
    });

    @if($errors->has('phone') || $errors->has('nickname'))
        modal.show();   // reopen after a failed submit so the error is visible
    @endif
})();
</script>
@endpush