@extends('admin.layouts.master')

@section('title', __('Notifications'))
@section('header', __('Notifications'))

@push('styles')
<style>
    .notif-composer-card {
        position: sticky;
        top: calc(var(--adm-header) + 1.25rem);
    }

    .notif-header-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.15rem;
        flex-shrink: 0;
    }

    .notif-target-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: .65rem;
    }

    .notif-target-option {
        position: relative;
        margin: 0;
        cursor: pointer;
    }

    .notif-target-option input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .notif-target-card {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: .5rem;
        padding: .65rem .75rem;
        border-radius: 10px;
        border: 1px solid var(--bs-border-color);
        background: var(--bs-tertiary-bg);
        font-weight: 600;
        font-size: .88rem;
        transition: border-color .15s ease, background-color .15s ease, color .15s ease;
    }

    .notif-target-option input:checked + .notif-target-card {
        border-color: var(--bs-primary);
        background: rgba(var(--bs-primary-rgb), .12);
        color: var(--bs-primary);
    }

    .notif-item {
        padding: 1rem 1.25rem;
        transition: background-color .15s ease;
    }

    .notif-item:hover {
        background-color: var(--bs-tertiary-bg);
    }

    .notif-item-icon {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        flex-shrink: 0;
    }

    .notif-empty-icon {
        width: 64px;
        height: 64px;
        border-radius: 16px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.75rem;
    }

    @media (max-width: 991.98px) {
        .notif-composer-card {
            position: static;
        }
    }
</style>
@endpush

@section('content')
@php
    $selectedTarget = old('target', 'all');
    $users = \App\Models\User::select('id', 'name', 'phone')->latest()->get();
@endphp

<div class="row g-4 align-items-start">
    {{-- Left Column: Send Notification --}}
    <div class="col-lg-5">
        <div class="card shadow-sm notif-composer-card">
            <div class="card-header bg-transparent py-3 px-4 d-flex align-items-center gap-3">
                <div class="notif-header-icon bg-primary-subtle text-primary-emphasis">
                    <i class="bi bi-bell-fill"></i>
                </div>
                <h5 class="fw-bold mb-0">{{ __('Send New Notification') }}</h5>
            </div>

            <div class="card-body p-4">
                <form action="{{ route('admin.notifications.send') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">{{ __('Target Audience') }}</label>
                        <div class="notif-target-grid">
                            <label class="notif-target-option">
                                <input type="radio" name="target" value="all" {{ $selectedTarget === 'all' ? 'checked' : '' }}>
                                <span class="notif-target-card">
                                    <i class="bi bi-people-fill"></i>
                                    <span>{{ __('All Users') }}</span>
                                </span>
                            </label>
                            <label class="notif-target-option">
                                <input type="radio" name="target" value="single" {{ $selectedTarget === 'single' ? 'checked' : '' }}>
                                <span class="notif-target-card">
                                    <i class="bi bi-person-fill"></i>
                                    <span>{{ __('Single User') }}</span>
                                </span>
                            </label>
                        </div>
                    </div>

                    <div class="mb-3 {{ $selectedTarget === 'single' ? '' : 'd-none' }}" id="userSelectDiv">
                        <label class="form-label small fw-semibold" for="userSelect">{{ __('Select User') }}</label>
                        <select name="user_id" id="userSelect" class="form-select" {{ $selectedTarget === 'single' ? 'required' : 'disabled' }}>
                            <option value="">{{ __('Choose a user...') }}</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}" {{ (string) old('user_id') === (string) $u->id ? 'selected' : '' }}>
                                    {{ $u->name }} ({{ $u->phone }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label small fw-semibold mb-0" for="notifTitle">{{ __('Title') }}</label>
                            <small class="text-muted"><span id="titleCount">0</span>/100</small>
                        </div>
                        <input type="text" id="notifTitle" name="title" class="form-control" maxlength="100" value="{{ old('title') }}" placeholder="{{ __('Notification Title') }}" required>
                    </div>

                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label small fw-semibold mb-0" for="notifBody">{{ __('Message Body') }}</label>
                            <small class="text-muted"><span id="bodyCount">0</span>/255</small>
                        </div>
                        <textarea id="notifBody" name="body" class="form-control" rows="4" maxlength="255" placeholder="{{ __('Write your message here...') }}" required>{{ old('body') }}</textarea>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary fw-semibold py-2 rounded-3">
                            <i class="bi bi-send-fill me-2"></i>{{ __('Send Notification') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Right Column: Notification History --}}
    <div class="col-lg-7">
        <div class="card shadow-sm overflow-hidden">
            <div class="card-header bg-transparent py-3 px-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
                <h5 class="fw-bold mb-0 d-flex align-items-center gap-2">
                    <i class="bi bi-clock-history text-secondary"></i>
                    <span>{{ __('Notification History') }}</span>
                </h5>
                <span class="badge bg-body-tertiary text-body border rounded-pill px-3 py-2 fw-semibold">
                    {{ number_format($notifications->total() ?? 0) }} {{ __('Total') }}
                </span>
            </div>

            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @forelse($notifications as $notification)
                        @php
                            $data = $notification->data ?? [];
                            $isUnread = is_null($notification->read_at);
                        @endphp
                        <div class="list-group-item notif-item d-flex align-items-start gap-3">
                            <div class="notif-item-icon {{ $isUnread ? 'bg-primary-subtle text-primary-emphasis' : 'bg-body-tertiary text-secondary border' }}">
                                <i class="bi {{ $isUnread ? 'bi-bell-fill' : 'bi-bell' }}"></i>
                            </div>

                            <div class="flex-grow-1 min-w-0">
                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-1">
                                    <h6 class="mb-0 fw-bold text-truncate">{{ $data['title'] ?? __('No Title') }}</h6>
                                    <small class="text-muted text-nowrap">
                                        {{ \Carbon\Carbon::parse($notification->created_at)->diffForHumans() }}
                                    </small>
                                </div>

                                <p class="mb-2 text-body-secondary small text-break">{{ $data['body'] ?? '-' }}</p>

                                <span class="badge rounded-pill bg-body-tertiary text-secondary border fw-normal px-2.5 py-1">
                                    <i class="bi bi-person me-1"></i>{{ __('Sent to') }}:
                                    @if($notification->notifiable_type === 'App\Models\User')
                                        <strong class="text-body">{{ $notification->notifiable->name ?? __('Deleted User') }}</strong>
                                    @else
                                        <strong class="text-body">{{ __('Multiple Users') }}</strong>
                                    @endif
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5">
                            <div class="notif-empty-icon bg-body-tertiary text-muted mx-auto mb-3">
                                <i class="bi bi-bell-slash"></i>
                            </div>
                            <div class="text-muted fw-medium">{{ __('No notifications sent yet.') }}</div>
                        </div>
                    @endforelse
                </div>
            </div>

            @if($notifications->hasPages())
                <div class="card-footer bg-transparent py-3 px-4">
                    {{ $notifications->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const userSelectDiv = document.getElementById('userSelectDiv');
const userSelect = document.getElementById('userSelect');

document.querySelectorAll('input[name="target"]').forEach((radio) => {
    radio.addEventListener('change', () => {
        const isSingle = radio.value === 'single' && radio.checked;
        userSelectDiv.classList.toggle('d-none', !isSingle);
        userSelect.disabled = !isSingle;
        userSelect.required = isSingle;
    });
});

function bindCounter(inputId, countId) {
    const input = document.getElementById(inputId);
    const counter = document.getElementById(countId);
    const update = () => { counter.textContent = input.value.length; };
    input.addEventListener('input', update);
    update();
}

bindCounter('notifTitle', 'titleCount');
bindCounter('notifBody', 'bodyCount');
</script>
@endpush