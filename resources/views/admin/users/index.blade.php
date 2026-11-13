@extends('admin.layouts.master')

@section('title', __('Users'))
@section('header', __('Users Management'))

@php
    $meId   = (int) auth()->id();
    $search = trim((string) request('search'));

    $totalUsers   = \App\Models\User::count();
    $blockedUsers = \App\Models\User::where('is_blocked', true)->count();
    $activeUsers  = max(0, $totalUsers - $blockedUsers);
    $staffUsers   = \App\Models\User::whereIn('role', ['admin', 'moderator'])->count();

    $avTones = [
        'bg-primary-subtle text-primary-emphasis',
        'bg-success-subtle text-success-emphasis',
        'bg-info-subtle text-info-emphasis',
        'bg-warning-subtle text-warning-emphasis',
        'bg-danger-subtle text-danger-emphasis',
    ];
@endphp

@push('styles')
<style>
    .u-stat-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        flex-shrink: 0;
    }

    .u-search-form {
        width: min(100%, 380px);
    }

    .u-search-group {
        border: 1px solid var(--bs-border-color);
        border-radius: 999px;
        background: var(--bs-tertiary-bg);
        padding: 3px;
        display: flex;
        align-items: center;
        gap: .25rem;
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    .u-search-group:focus-within {
        border-color: var(--bs-primary);
        box-shadow: 0 0 0 .2rem rgba(var(--bs-primary-rgb), .15);
    }

    .u-search-group .form-control {
        border: 0;
        background: transparent;
        box-shadow: none !important;
        font-size: .88rem;
        padding-left: .25rem;
    }

    .u-search-btn {
        border-radius: 999px;
        padding: .35rem .95rem;
        font-size: .82rem;
        font-weight: 600;
        flex-shrink: 0;
    }

    .filter-bar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: .45rem;
        padding: .75rem 1.25rem;
        border-bottom: 1px solid var(--bs-border-color);
        background: var(--bs-tertiary-bg);
    }

    .chip {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        border: 1px solid var(--bs-border-color);
        background: var(--bs-body-bg);
        color: var(--bs-body-color);
        border-radius: 999px;
        padding: .28rem .75rem;
        font-size: .8rem;
        font-weight: 600;
        cursor: pointer;
        transition: background-color .15s, color .15s, border-color .15s;
    }

    .chip:hover {
        border-color: var(--bs-primary);
    }

    .chip.active {
        background: var(--bs-primary);
        border-color: var(--bs-primary);
        color: #fff;
    }

    .chip .n {
        font-size: .72rem;
        padding: .08rem .42rem;
        border-radius: 999px;
        background: var(--bs-tertiary-bg);
        color: var(--bs-secondary-color);
    }

    .chip.active .n {
        background: rgba(255, 255, 255, .22);
        color: #fff;
    }

    .filter-note {
        margin-left: auto;
        font-size: .78rem;
        color: var(--bs-secondary-color);
    }

    .users-table {
        margin: 0;
    }

    .users-table thead th {
        background: var(--bs-tertiary-bg);
        color: var(--bs-secondary-color);
        border-bottom-width: 1px;
        white-space: nowrap;
        font-size: .74rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .04em;
        padding-top: .85rem;
        padding-bottom: .85rem;
    }

    .user-av-wrap {
        position: relative;
        width: 42px;
        height: 42px;
        flex-shrink: 0;
    }

    .user-av {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        object-fit: cover;
        display: block;
    }

    .user-av-fallback {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: .95rem;
    }

    .user-status-dot {
        position: absolute;
        right: 0;
        bottom: 0;
        width: 11px;
        height: 11px;
        border-radius: 50%;
        border: 2px solid var(--bs-body-bg);
    }

    .user-name-link {
        color: var(--bs-body-color);
        text-decoration: none;
        font-weight: 600;
        transition: color .15s ease;
    }

    .user-name-link:hover {
        color: var(--bs-primary);
    }

    .role-select {
        width: auto;
        min-width: 122px;
        border-radius: 999px;
        font-size: .8rem;
        font-weight: 600;
        padding: .28rem 1.85rem .28rem .8rem;
        cursor: pointer;
    }

    .role-select.r-admin {
        background-color: var(--bs-danger-bg-subtle);
        border-color: var(--bs-danger-border-subtle);
        color: var(--bs-danger-text-emphasis);
    }

    .role-select.r-moderator {
        background-color: var(--bs-warning-bg-subtle);
        border-color: var(--bs-warning-border-subtle);
        color: var(--bs-warning-text-emphasis);
    }

    .act {
        display: inline-flex;
        gap: .3rem;
    }

    .act .btn {
        width: 32px;
        height: 32px;
        padding: 0;
        display: inline-grid;
        place-items: center;
        border-radius: 8px;
    }

    .users-empty-icon {
        width: 64px;
        height: 64px;
        border-radius: 16px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.75rem;
    }

    .panel-foot {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
        padding: .85rem 1.25rem;
        border-top: 1px solid var(--bs-border-color);
    }

    .panel-foot .pagination {
        margin: 0;
    }
</style>
@endpush

@section('content')
{{-- Summary Cards --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="card shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="u-stat-icon bg-primary-subtle text-primary-emphasis">
                    <i class="bi bi-people"></i>
                </div>
                <div class="min-w-0">
                    <div class="small text-muted text-truncate">{{ __('All Users') }}</div>
                    <div class="fs-4 fw-bold lh-sm">{{ number_format($totalUsers) }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-xl-3">
        <div class="card shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="u-stat-icon bg-success-subtle text-success-emphasis">
                    <i class="bi bi-person-check"></i>
                </div>
                <div class="min-w-0">
                    <div class="small text-muted text-truncate">{{ __('Active') }}</div>
                    <div class="fs-4 fw-bold lh-sm">{{ number_format($activeUsers) }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-xl-3">
        <div class="card shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="u-stat-icon bg-danger-subtle text-danger-emphasis">
                    <i class="bi bi-slash-circle"></i>
                </div>
                <div class="min-w-0">
                    <div class="small text-muted text-truncate">{{ __('Blocked') }}</div>
                    <div class="fs-4 fw-bold lh-sm">{{ number_format($blockedUsers) }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-xl-3">
        <div class="card shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="u-stat-icon bg-warning-subtle text-warning-emphasis">
                    <i class="bi bi-shield-check"></i>
                </div>
                <div class="min-w-0">
                    <div class="small text-muted text-truncate">{{ __('Admin') }} / {{ __('Moderator') }}</div>
                    <div class="fs-4 fw-bold lh-sm">{{ number_format($staffUsers) }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Main Panel --}}
<div class="card shadow-sm overflow-hidden">
    <div class="card-header bg-transparent py-3 px-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
        <h5 class="mb-0 fw-bold d-flex align-items-center gap-2">
            <i class="bi bi-people text-primary"></i>
            <span>{{ __('All Users') }}</span>
            <span class="badge bg-body-tertiary text-body border rounded-pill px-2.5 py-1 fw-semibold">
                {{ number_format($users->total()) }}
            </span>
        </h5>

        <form action="{{ route('admin.users.index') }}" method="GET" class="u-search-form">
            <div class="u-search-group">
                <i class="bi bi-search text-muted ms-2 ps-1"></i>
                <input type="search" name="search" class="form-control form-control-sm" value="{{ $search }}"
                       placeholder="{{ __('Search by name or phone...') }}" autocomplete="off">
                @if($search !== '')
                    <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-link text-muted p-1" title="{{ __('Clear search') }}">
                        <i class="bi bi-x-lg"></i>
                    </a>
                @endif
                <button class="btn btn-primary u-search-btn" type="submit">{{ __('Search') }}</button>
            </div>
        </form>
    </div>

    {{-- Quick Filters --}}
    <div class="filter-bar" id="filterBar">
        <button type="button" class="chip active" data-filter="all">
            <span>{{ __('All') }}</span><span class="n" data-n="all">0</span>
        </button>
        <button type="button" class="chip" data-filter="role:admin">
            <span>{{ __('Admin') }}</span><span class="n" data-n="role:admin">0</span>
        </button>
        <button type="button" class="chip" data-filter="role:moderator">
            <span>{{ __('Moderator') }}</span><span class="n" data-n="role:moderator">0</span>
        </button>
        <button type="button" class="chip" data-filter="role:user">
            <span>{{ __('User') }}</span><span class="n" data-n="role:user">0</span>
        </button>
        <button type="button" class="chip" data-filter="status:active">
            <span>{{ __('Active') }}</span><span class="n" data-n="status:active">0</span>
        </button>
        <button type="button" class="chip" data-filter="status:blocked">
            <span>{{ __('Blocked') }}</span><span class="n" data-n="status:blocked">0</span>
        </button>
        <button type="button" class="chip" data-filter="plan:paid">
            <span>{{ __('Paid') }}</span><span class="n" data-n="plan:paid">0</span>
        </button>

        @if($search !== '')
            <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill ms-1">
                <i class="bi bi-x-lg me-1"></i>{{ __('Clear search') }}: "{{ \Illuminate\Support\Str::limit($search, 18) }}"
            </a>
        @endif

        <span class="filter-note d-none d-md-inline">{{ __('Filters apply to this page') }}</span>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle users-table">
            <thead>
                <tr>
                    <th class="ps-4 d-none d-md-table-cell">ID</th>
                    <th>{{ __('User') }}</th>
                    <th class="d-none d-lg-table-cell">{{ __('Phone') }}</th>
                    <th>{{ __('Role') }}</th>
                    <th class="d-none d-md-table-cell">{{ __('Plan') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th class="text-end pe-4">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody id="userRows">
                @forelse($users as $user)
                    @php
                        $isMe    = (int) $user->id === $meId;
                        $planObj = $user->activeSubscription?->plan;
                        $role    = in_array($user->role, ['admin', 'moderator']) ? $user->role : 'user';
                        $initial = mb_strtoupper(mb_substr($user->name ?: 'U', 0, 1));
                        $tone    = $avTones[$user->id % count($avTones)];
                    @endphp
                    <tr data-row
                        data-role="{{ $role }}"
                        data-status="{{ $user->is_blocked ? 'blocked' : 'active' }}"
                        data-plan="{{ $planObj ? 'paid' : 'free' }}">

                        <td class="ps-4 text-muted fw-medium d-none d-md-table-cell">#{{ $user->id }}</td>

                        <td>
                            <div class="d-flex align-items-center gap-3">
                                <a href="{{ route('admin.users.show', $user) }}" class="user-av-wrap text-decoration-none">
                                    @if($user->avatar)
                                        <img src="{{ asset('storage/' . $user->avatar) }}"
                                             class="user-av"
                                             alt="{{ $user->name }}"
                                             loading="lazy"
                                             onerror="this.classList.add('d-none'); this.nextElementSibling.classList.remove('d-none');">
                                        <div class="user-av-fallback {{ $tone }} d-none">{{ $initial }}</div>
                                    @else
                                        <div class="user-av-fallback {{ $tone }}">{{ $initial }}</div>
                                    @endif
                                    <span class="user-status-dot {{ $user->is_blocked ? 'bg-danger' : 'bg-success' }}"></span>
                                </a>

                                <div class="min-w-0">
                                    <div class="d-flex align-items-center gap-1" style="max-width: 220px;">
                                        <a href="{{ route('admin.users.show', $user) }}" class="user-name-link text-truncate">
                                            {{ $user->name }}
                                        </a>
                                        @if($isMe)
                                            <span class="badge rounded-pill bg-primary-subtle text-primary-emphasis flex-shrink-0">{{ __('You') }}</span>
                                        @endif
                                    </div>
                                    <small class="text-muted d-block text-truncate">
                                        {{ $user->username ? '@' . $user->username : __('No username') }}
                                    </small>
                                    <div class="d-flex flex-wrap align-items-center gap-2 mt-1 d-lg-none">
                                        <small class="font-monospace text-muted">{{ $user->phone }}</small>
                                        @if($planObj)
                                            <span class="badge rounded-pill bg-primary-subtle text-primary-emphasis d-md-none">{{ $planObj->name }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </td>

                        <td class="d-none d-lg-table-cell">
                            <span class="font-monospace">{{ $user->phone }}</span>
                        </td>

                        <td>
                            <form action="{{ route('admin.users.updateRole', $user) }}" method="POST" class="role-form"
                                  data-confirm="{{ __('Change role of :name?', ['name' => $user->name]) }}">
                                @csrf
                                @method('PATCH')
                                <select name="role" class="form-select form-select-sm role-select r-{{ $role }}"
                                        data-original="{{ $role }}" @disabled($isMe)
                                        @if($isMe) title="{{ __('You cannot change your own role') }}" @endif>
                                    <option value="user"      @selected($role === 'user')>{{ __('User') }}</option>
                                    <option value="moderator" @selected($role === 'moderator')>{{ __('Moderator') }}</option>
                                    <option value="admin"     @selected($role === 'admin')>{{ __('Admin') }}</option>
                                </select>
                            </form>
                        </td>

                        <td class="d-none d-md-table-cell">
                            @if($planObj)
                                <span class="badge rounded-pill bg-primary-subtle text-primary-emphasis px-2.5 py-1">
                                    <i class="bi bi-star-fill me-1"></i>{{ $planObj->name }}
                                </span>
                            @else
                                <span class="badge rounded-pill bg-secondary-subtle text-secondary-emphasis px-2.5 py-1">
                                    {{ __('Free') }}
                                </span>
                            @endif
                        </td>

                        <td>
                            @if($user->is_blocked)
                                <span class="badge rounded-pill bg-danger-subtle text-danger-emphasis px-2.5 py-1">
                                    <i class="bi bi-slash-circle me-1"></i>{{ __('Blocked') }}
                                </span>
                            @else
                                <span class="badge rounded-pill bg-success-subtle text-success-emphasis px-2.5 py-1">
                                    <i class="bi bi-check-circle me-1"></i>{{ __('Active') }}
                                </span>
                            @endif
                        </td>

                        <td class="text-end pe-4">
                            <div class="act">
                                <a href="{{ route('admin.users.show', $user) }}" class="btn btn-sm btn-outline-info" title="{{ __('View') }}">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline-primary" title="{{ __('Edit') }}">
                                    <i class="bi bi-pencil"></i>
                                </a>

                                @unless($isMe)
                                    <form action="{{ route('admin.users.toggleBlock', $user) }}" method="POST"
                                          data-confirm="{{ $user->is_blocked
                                              ? __('Unblock :name?', ['name' => $user->name])
                                              : __('Block :name?', ['name' => $user->name]) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm {{ $user->is_blocked ? 'btn-outline-success' : 'btn-outline-warning' }}"
                                                title="{{ $user->is_blocked ? __('Unblock') : __('Block') }}">
                                            <i class="bi {{ $user->is_blocked ? 'bi-unlock' : 'bi-lock' }}"></i>
                                        </button>
                                    </form>

                                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST"
                                          data-confirm="{{ __('Are you sure you want to delete this user?') }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="{{ __('Delete') }}">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                @endunless
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <div class="users-empty-icon bg-body-tertiary text-muted mx-auto mb-3">
                                <i class="bi bi-people"></i>
                            </div>
                            <div class="text-muted fw-medium">
                                {{ $search !== '' ? __('No users match your search.') : __('No users found.') }}
                            </div>
                        </td>
                    </tr>
                @endforelse

                <tr id="noMatch" class="d-none">
                    <td colspan="7" class="text-center py-5">
                        <div class="users-empty-icon bg-body-tertiary text-muted mx-auto mb-3">
                            <i class="bi bi-funnel"></i>
                        </div>
                        <div class="text-muted fw-medium">{{ __('No users match this filter on this page.') }}</div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    @if($users->count())
        <div class="panel-foot">
            <small class="text-body-secondary">
                {{ __('Showing') }} {{ $users->firstItem() }}–{{ $users->lastItem() }} {{ __('of') }} {{ number_format($users->total()) }}
            </small>
            @if($users->hasPages())
                {{ $users->withQueryString()->links('pagination::bootstrap-5') }}
            @endif
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
(function () {
    document.addEventListener('submit', function (e) {
        var msg = e.target.getAttribute && e.target.getAttribute('data-confirm');
        if (!msg) return;
        if (!window.confirm(msg)) {
            e.preventDefault();
            var sel = e.target.querySelector('select[data-original]');
            if (sel) {
                sel.value = sel.dataset.original;
                sel.className = sel.className.replace(/\br-\w+/g, '') + ' r-' + sel.dataset.original;
            }
        }
    });

    document.querySelectorAll('.role-select').forEach(function (sel) {
        sel.addEventListener('change', function () {
            sel.className = sel.className.replace(/\br-\w+/g, '') + ' r-' + sel.value;
            if (sel.form.requestSubmit) sel.form.requestSubmit(); else sel.form.submit();
        });
    });

    var rows    = Array.prototype.slice.call(document.querySelectorAll('[data-row]'));
    var chips   = document.querySelectorAll('#filterBar .chip');
    var noMatch = document.getElementById('noMatch');

    function matches(row, f) {
        if (f === 'all') return true;
        var parts = f.split(':');
        return row.dataset[parts[0]] === parts[1];
    }

    document.querySelectorAll('[data-n]').forEach(function (el) {
        var f = el.dataset.n;
        el.textContent = rows.filter(function (r) { return matches(r, f); }).length;
    });

    chips.forEach(function (chip) {
        chip.addEventListener('click', function () {
            chips.forEach(function (c) { c.classList.remove('active'); });
            chip.classList.add('active');
            var f = chip.dataset.filter, visible = 0;
            rows.forEach(function (row) {
                var show = matches(row, f);
                row.classList.toggle('d-none', !show);
                if (show) visible++;
            });
            if (noMatch) noMatch.classList.toggle('d-none', !(rows.length && visible === 0));
        });
    });
})();
</script>
@endpush