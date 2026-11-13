@extends('admin.layouts.master')

@section('title', __('Edit Plan'))
@section('header', __('Edit Plan') . ': ' . $plan->name)

@push('styles')
<style>
    .edit-plan-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.15rem;
        flex-shrink: 0;
    }

    .plan-section-box {
        background: var(--bs-tertiary-bg);
        border: 1px solid var(--bs-border-color-translucent);
        border-radius: 12px;
        padding: 1.25rem;
    }

    .switch-tile {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
        padding: .85rem 1rem;
        border-radius: 10px;
        background: var(--bs-body-bg);
        border: 1px solid var(--bs-border-color);
        cursor: pointer;
        transition: border-color .15s ease, background-color .15s ease;
        user-select: none;
    }

    .switch-tile:hover {
        border-color: rgba(var(--bs-primary-rgb), .5);
    }

    .switch-tile .form-check-input {
        width: 2.5em;
        height: 1.3em;
        margin: 0;
        cursor: pointer;
        flex-shrink: 0;
    }
</style>
@endpush

@section('content')
<div class="row justify-content-center">
    <div class="col-xl-9 col-lg-10">
        <form action="{{ route('admin.plans.update', $plan) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="card shadow-sm overflow-hidden">
                {{-- Header --}}
                <div class="card-header bg-transparent py-3 px-4 d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="edit-plan-icon bg-primary-subtle text-primary-emphasis">
                            <i class="bi bi-pencil-square"></i>
                        </div>
                        <div>
                            <h5 class="mb-0 fw-bold">{{ __('Plan Details') }}</h5>
                            <div class="small text-muted font-monospace">{{ $plan->slug }}</div>
                        </div>
                    </div>

                    <a href="{{ route('admin.plans.index') }}" class="btn btn-sm btn-outline-secondary rounded-3 px-3">
                        <i class="bi bi-arrow-left me-1"></i>{{ __('Back to Plans') }}
                    </a>
                </div>

                <div class="card-body p-4">
                    {{-- Core Details & Limits --}}
                    <div class="plan-section-box mb-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold" for="planName">{{ __('Plan Name') }}</label>
                                <input type="text" id="planName" name="name" class="form-control" value="{{ old('name', $plan->name) }}" maxlength="255" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-semibold" for="planPrice">{{ __('Price ($)') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" id="planPrice" step="0.01" min="0" name="price" class="form-control" value="{{ old('price', $plan->price) }}" required>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small fw-semibold" for="planDuration">{{ __('Duration (Days)') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-calendar3"></i></span>
                                    <input type="number" id="planDuration" min="0" name="duration_days" class="form-control" value="{{ old('duration_days', $plan->duration_days) }}" required>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small fw-semibold" for="planMaxFile">{{ __('Max File Size (MB)') }}</label>
                                <div class="input-group">
                                    <input type="number" id="planMaxFile" min="1" name="max_file_size_mb" class="form-control" value="{{ old('max_file_size_mb', $plan->max_file_size_mb) }}" required>
                                    <span class="input-group-text">MB</span>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small fw-semibold" for="planMaxStory">{{ __('Max Story Duration (Seconds)') }}</label>
                                <div class="input-group">
                                    <input type="number" id="planMaxStory" min="1" name="max_story_duration_sec" class="form-control" value="{{ old('max_story_duration_sec', $plan->max_story_duration_sec) }}" required>
                                    <span class="input-group-text">s</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Crypto Payment --}}
                    <div class="plan-section-box mb-4">
                        <h6 class="fw-bold mb-3 d-flex align-items-center gap-2">
                            <i class="bi bi-currency-bitcoin text-warning"></i>
                            <span>{{ __('Crypto Payment') }}</span>
                        </h6>

                        <div class="row g-3 align-items-center">
                            <div class="col-md-5">
                                <label class="switch-tile mb-0" for="accepts_crypto">
                                    <span class="fw-semibold small">{{ __('Accept Crypto Payments') }}</span>
                                    <span class="form-check form-switch m-0 p-0">
                                        <input type="hidden" name="accepts_crypto" value="0">
                                        <input class="form-check-input" type="checkbox" name="accepts_crypto" id="accepts_crypto" value="1" {{ old('accepts_crypto', $plan->accepts_crypto) ? 'checked' : '' }}>
                                    </span>
                                </label>
                            </div>

                            <div class="col-md-7">
                                <label class="form-label small fw-semibold" for="bep20Wallet">{{ __('BEP-20 Wallet Address') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-wallet2"></i></span>
                                    <input type="text" id="bep20Wallet" name="bep20_wallet_address" class="form-control font-monospace" value="{{ old('bep20_wallet_address', $plan->bep20_wallet_address) }}" maxlength="255" placeholder="0x...">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Premium Features --}}
                    <div class="plan-section-box">
                        <h6 class="fw-bold mb-3 d-flex align-items-center gap-2">
                            <i class="bi bi-stars text-primary"></i>
                            <span>{{ __('Premium Features') }}</span>
                        </h6>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="switch-tile mb-0" for="can_hide_profile">
                                    <span class="d-flex align-items-center gap-2 small fw-medium">
                                        <i class="bi bi-eye-slash text-muted"></i>
                                        <span>{{ __('Hide Profile Photo') }}</span>
                                    </span>
                                    <span class="form-check form-switch m-0 p-0">
                                        <input type="hidden" name="can_hide_profile" value="0">
                                        <input class="form-check-input" type="checkbox" name="can_hide_profile" id="can_hide_profile" value="1" {{ old('can_hide_profile', $plan->can_hide_profile) ? 'checked' : '' }}>
                                    </span>
                                </label>
                            </div>

                            <div class="col-md-6">
                                <label class="switch-tile mb-0" for="can_set_profile_music">
                                    <span class="d-flex align-items-center gap-2 small fw-medium">
                                        <i class="bi bi-music-note-beamed text-muted"></i>
                                        <span>{{ __('Set Profile Music') }}</span>
                                    </span>
                                    <span class="form-check form-switch m-0 p-0">
                                        <input type="hidden" name="can_set_profile_music" value="0">
                                        <input class="form-check-input" type="checkbox" name="can_set_profile_music" id="can_set_profile_music" value="1" {{ old('can_set_profile_music', $plan->can_set_profile_music) ? 'checked' : '' }}>
                                    </span>
                                </label>
                            </div>

                            <div class="col-md-6">
                                <label class="switch-tile mb-0" for="can_block_screenshots">
                                    <span class="d-flex align-items-center gap-2 small fw-medium">
                                        <i class="bi bi-shield-lock text-muted"></i>
                                        <span>{{ __('Block Screenshots') }}</span>
                                    </span>
                                    <span class="form-check form-switch m-0 p-0">
                                        <input type="hidden" name="can_block_screenshots" value="0">
                                        <input class="form-check-input" type="checkbox" name="can_block_screenshots" id="can_block_screenshots" value="1" {{ old('can_block_screenshots', $plan->can_block_screenshots) ? 'checked' : '' }}>
                                    </span>
                                </label>
                            </div>

                            <div class="col-md-6">
                                <label class="switch-tile mb-0" for="has_anonymous_mode">
                                    <span class="d-flex align-items-center gap-2 small fw-medium">
                                        <i class="bi bi-incognito text-muted"></i>
                                        <span>{{ __('Anonymous Mode') }}</span>
                                    </span>
                                    <span class="form-check form-switch m-0 p-0">
                                        <input type="hidden" name="has_anonymous_mode" value="0">
                                        <input class="form-check-input" type="checkbox" name="has_anonymous_mode" id="has_anonymous_mode" value="1" {{ old('has_anonymous_mode', $plan->has_anonymous_mode) ? 'checked' : '' }}>
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="card-footer bg-transparent py-3 px-4 d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary px-4 py-2 fw-semibold rounded-3">
                        <i class="bi bi-save me-2"></i>{{ __('Save Changes') }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection