@extends('admin.layouts.master')

@section('title', __('Edit User'))
@section('header', __('Edit User') . ': ' . $user->name)

@push('styles')
<style>
    .edit-user-av {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        object-fit: cover;
        flex-shrink: 0;
    }

    .edit-user-av-fallback {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1rem;
        flex-shrink: 0;
    }

    .user-section-box {
        background: var(--bs-tertiary-bg);
        border: 1px solid var(--bs-border-color-translucent);
        border-radius: 12px;
        padding: 1.25rem;
    }
</style>
@endpush

@section('content')
@php $initial = mb_strtoupper(mb_substr($user->name ?: 'U', 0, 1)); @endphp

<div class="row justify-content-center">
    <div class="col-xl-8 col-lg-9">
        <form action="{{ route('admin.users.update', $user) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="card shadow-sm overflow-hidden">
                {{-- Header --}}
                <div class="card-header bg-transparent py-3 px-4 d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div class="d-flex align-items-center gap-3 min-w-0">
                        @if($user->avatar)
                            <img src="{{ asset('storage/' . $user->avatar) }}"
                                 class="edit-user-av"
                                 alt="{{ $user->name }}"
                                 onerror="this.classList.add('d-none'); this.nextElementSibling.classList.remove('d-none');">
                            <div class="edit-user-av-fallback bg-primary-subtle text-primary-emphasis d-none">{{ $initial }}</div>
                        @else
                            <div class="edit-user-av-fallback bg-primary-subtle text-primary-emphasis">{{ $initial }}</div>
                        @endif
                        <div class="min-w-0">
                            <h5 class="mb-0 fw-bold text-truncate">{{ __('Edit User Details') }}</h5>
                            <small class="text-muted font-monospace">{{ $user->phone }}</small>
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <a href="{{ route('admin.users.show', $user) }}" class="btn btn-sm btn-outline-info rounded-3 px-3">
                            <i class="bi bi-eye me-1"></i>{{ __('View') }}
                        </a>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-secondary rounded-3 px-3">
                            <i class="bi bi-arrow-left me-1"></i>{{ __('Back to Users') }}
                        </a>
                    </div>
                </div>

                <div class="card-body p-4">
                    <div class="user-section-box mb-3">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold" for="userName">{{ __('Full Name') }}</label>
                                <input type="text" id="userName" name="name" class="form-control" value="{{ old('name', $user->name) }}" maxlength="255" required>
                                @error('name') <small class="text-danger d-block mt-1">{{ $message }}</small> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-semibold" for="userUsername">{{ __('Username') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text">@</span>
                                    <input type="text" id="userUsername" name="username" class="form-control" value="{{ old('username', $user->username) }}" maxlength="50">
                                </div>
                                @error('username') <small class="text-danger d-block mt-1">{{ $message }}</small> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-semibold" for="userPhone">{{ __('Phone Number') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                                    <input type="tel" id="userPhone" name="phone" class="form-control font-monospace" value="{{ old('phone', $user->phone) }}" maxlength="20" required>
                                </div>
                                @error('phone') <small class="text-danger d-block mt-1">{{ $message }}</small> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-semibold" for="userEmail">{{ __('Email Address') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                    <input type="email" id="userEmail" name="email" class="form-control" value="{{ old('email', $user->email) }}" maxlength="255">
                                </div>
                                @error('email') <small class="text-danger d-block mt-1">{{ $message }}</small> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="user-section-box">
                        <label class="form-label small fw-semibold" for="userPassword">{{ __('New Password') }}</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input type="password" id="userPassword" name="password" class="form-control" minlength="6" placeholder="{{ __('Leave empty to keep current password') }}" autocomplete="new-password">
                            <button type="button" class="btn btn-outline-secondary border-start-0 border-secondary-subtle" id="toggleEditPwd" tabindex="-1">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        <small class="text-muted d-block mt-1">{{ __('Minimum 6 characters.') }}</small>
                        @error('password') <small class="text-danger d-block mt-1">{{ $message }}</small> @enderror
                    </div>
                </div>

                <div class="card-footer bg-transparent py-3 px-4 d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary px-4 py-2 fw-semibold rounded-3">
                        <i class="bi bi-save me-2"></i>{{ __('Update User') }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('toggleEditPwd').addEventListener('click', function () {
    const input = document.getElementById('userPassword');
    const icon  = this.querySelector('i');
    const show  = input.type === 'password';
    input.type = show ? 'text' : 'password';
    icon.className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
});
</script>
@endpush