@extends('admin.layouts.master')

@section('title', __('Plans'))
@section('header', __('Subscription Plans'))

@push('styles')
<style>
    .plan-card {
        transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
    }

    .plan-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 28px rgba(0, 0, 0, .08) !important;
    }

    [data-bs-theme="dark"] .plan-card:hover {
        box-shadow: 0 10px 28px rgba(0, 0, 0, .35) !important;
    }

    .plan-card.plan-featured {
        border-color: rgba(var(--bs-primary-rgb), .55) !important;
    }

    .plan-icon-badge {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        flex-shrink: 0;
    }

    .plan-price-box {
        background: var(--bs-tertiary-bg);
        border: 1px solid var(--bs-border-color-translucent);
        border-radius: 12px;
        padding: 1rem;
    }

    .plan-price {
        font-size: 2rem;
        font-weight: 800;
        letter-spacing: -.02em;
        line-height: 1.1;
    }

    .plan-limit-tile {
        background: var(--bs-tertiary-bg);
        border: 1px solid var(--bs-border-color-translucent);
        border-radius: 10px;
        padding: .65rem .75rem;
    }

    .plan-feature-item {
        display: flex;
        align-items: center;
        gap: .6rem;
        padding: .4rem 0;
        font-size: .9rem;
    }
</style>
@endpush

@section('content')
<div class="row g-4">
    @foreach($plans as $plan)
    @php
        $isPro = $plan->slug === 'pro';
        $features = [
            ['enabled' => $plan->can_hide_profile,      'label' => __('Hide Profile')],
            ['enabled' => $plan->can_set_profile_music, 'label' => __('Profile Music')],
            ['enabled' => $plan->can_block_screenshots, 'label' => __('Block Screenshots')],
            ['enabled' => $plan->has_anonymous_mode,    'label' => __('Anonymous Mode')],
        ];
    @endphp

    <div class="col-md-6 col-xl-4">
        <div class="card plan-card shadow-sm h-100 {{ $isPro ? 'plan-featured' : '' }}">
            <div class="card-body p-4 d-flex flex-column">
                {{-- Header --}}
                <div class="d-flex align-items-start justify-content-between gap-2 mb-3">
                    <div class="d-flex align-items-center gap-3 min-w-0">
                        <div class="plan-icon-badge {{ $isPro ? 'bg-primary-subtle text-primary-emphasis' : 'bg-body-tertiary text-secondary border' }}">
                            <i class="bi {{ $isPro ? 'bi-gem' : 'bi-box-seam' }}"></i>
                        </div>
                        <div class="min-w-0">
                            <h5 class="fw-bold mb-0 text-truncate {{ $isPro ? 'text-primary' : '' }}">{{ $plan->name }}</h5>
                            <div class="small text-muted">
                                {{ __('Duration') }}:
                                {{ $plan->duration_days > 0 ? $plan->duration_days . ' ' . __('Days') : __('Lifetime/Free') }}
                            </div>
                        </div>
                    </div>

                    @if($isPro)
                        <span class="badge rounded-pill bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2.5 py-1 flex-shrink-0">
                            <i class="bi bi-star-fill me-1"></i>{{ __('Most Popular') }}
                        </span>
                    @endif
                </div>

                {{-- Price --}}
                <div class="plan-price-box text-center mb-3">
                    <div class="plan-price">${{ number_format($plan->price, 2) }}</div>
                </div>

                {{-- Quotas --}}
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <div class="plan-limit-tile text-center h-100">
                            <div class="small text-muted text-truncate">{{ __('Max File Size') }}</div>
                            <div class="fw-bold mt-1">{{ $plan->max_file_size_mb }} MB</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="plan-limit-tile text-center h-100">
                            <div class="small text-muted text-truncate">{{ __('Max Story Duration') }}</div>
                            <div class="fw-bold mt-1">{{ $plan->max_story_duration_sec }}s</div>
                        </div>
                    </div>
                </div>

                {{-- Features --}}
                <ul class="list-unstyled mb-4">
                    @foreach($features as $feature)
                        <li class="plan-feature-item {{ $feature['enabled'] ? '' : 'text-muted opacity-50' }}">
                            <i class="bi {{ $feature['enabled'] ? 'bi-check-circle-fill text-success' : 'bi-dash-circle' }}"></i>
                            <span>{{ $feature['label'] }}</span>
                        </li>
                    @endforeach
                </ul>

                {{-- Action --}}
                <div class="mt-auto d-grid">
                    <a href="{{ route('admin.plans.edit', $plan) }}" class="btn {{ $isPro ? 'btn-primary' : 'btn-outline-primary' }} fw-semibold rounded-3 py-2">
                        <i class="bi bi-pencil-square me-1"></i>{{ __('Edit Plan') }}
                    </a>
                </div>
            </div>

            <div class="card-footer bg-transparent py-2 px-4 d-flex justify-content-center">
                @if($plan->accepts_crypto)
                    <span class="badge rounded-pill bg-success-subtle text-success-emphasis px-3 py-1">
                        <i class="bi bi-currency-bitcoin me-1"></i>{{ __('Crypto Accepted') }}
                    </span>
                @else
                    <span class="badge rounded-pill bg-secondary-subtle text-secondary-emphasis px-3 py-1">
                        <i class="bi bi-x-circle me-1"></i>{{ __('No Crypto') }}
                    </span>
                @endif
            </div>
        </div>
    </div>
    @endforeach
</div>
@endsection