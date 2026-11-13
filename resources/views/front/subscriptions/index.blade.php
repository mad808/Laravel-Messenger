@extends('front.layouts.master')

@section('title', __('Subscriptions'))
@section('header_title', __('Subscription Plans'))

@section('sidebar_content')
    <div class="p-3">
        <a href="{{ route('home') }}" class="btn btn-outline-secondary w-100 mb-2">
            <i class="bi bi-arrow-left me-2"></i>{{ __('Back to Chats') }}
        </a>
    </div>
    <div class="px-3 mt-3">
        <h6 class="text-uppercase text-muted fw-bold mb-3" style="font-size: 0.8rem;">{{ __('Your Current Plan') }}</h6>
        <div class="card border-0 shadow-sm bg-primary-subtle">
            <div class="card-body p-3 text-center">
                <i class="bi bi-star-fill text-warning fs-1 mb-2"></i>
                <h5 class="fw-bold text-primary mb-1">{{ auth()->user()->activeSubscription->plan->name ?? __('Free Plan') }}</h5>
                @if(auth()->user()->activeSubscription)
                    <small class="text-muted">{{ __('Expires') }}: {{ auth()->user()->activeSubscription->ends_at->format('d M, Y') }}</small>
                @else
                    <small class="text-muted">{{ __('Upgrade to unlock premium features.') }}</small>
                @endif
            </div>
        </div>
    </div>
@endsection

@section('content')
<div class="h-100 overflow-auto p-4" style="background: var(--bs-tertiary-bg);">
    
    <!-- Başarı / Hata Mesajları -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="text-center mb-5">
        <h2 class="fw-bold">{{ __('Choose Your Plan') }}</h2>
        <p class="text-muted">{{ __('Unlock exclusive features with our premium plans.') }}</p>
    </div>

    <div class="row g-4 justify-content-center">
        @foreach($plans as $plan)
        <div class="col-md-6 col-lg-4">
            <div class="card shadow-sm border-0 h-100 plan-card position-relative {{ $plan->slug === 'pro' ? 'border-primary border-2' : '' }}">
                @if($plan->slug === 'pro')
                    <span class="position-absolute top-0 start-50 translate-middle badge rounded-pill bg-primary shadow-sm">{{ __('Most Popular') }}</span>
                @endif
                
                <div class="card-body p-4 text-center">
                    <h4 class="fw-bold text-primary mb-2">{{ $plan->name }}</h4>
                    <div class="display-5 fw-bold mb-3">
                        ${{ number_format($plan->price, 2) }}
                        @if($plan->price > 0)
                            <span class="fs-6 text-muted fw-normal">/ {{ $plan->duration_days }} {{ __('Days') }}</span>
                        @endif
                    </div>

                    <ul class="list-unstyled text-start mb-4 feature-list">
                        <li><i class="bi bi-check-circle-fill text-success me-2"></i>{{ __('Max File Size') }}: <strong>{{ $plan->max_file_size_mb }} MB</strong></li>
                        <li><i class="bi bi-check-circle-fill text-success me-2"></i>{{ __('Max Story Duration') }}: <strong>{{ $plan->max_story_duration_sec }}s</strong></li>
                        
                        @if($plan->can_hide_profile)
                            <li><i class="bi bi-check-circle-fill text-success me-2"></i>{{ __('Hide Profile Photo') }}</li>
                        @endif
                        @if($plan->can_set_profile_music)
                            <li><i class="bi bi-check-circle-fill text-success me-2"></i>{{ __('Profile Music') }}</li>
                        @endif
                        @if($plan->can_block_screenshots)
                            <li><i class="bi bi-check-circle-fill text-success me-2"></i>{{ __('Block Screenshots') }}</li>
                        @endif
                        @if($plan->has_anonymous_mode)
                            <li><i class="bi bi-check-circle-fill text-success me-2"></i>{{ __('Anonymous Mode') }}</li>
                        @endif
                    </ul>

                    @if($plan->price == 0)
                        <button class="btn btn-outline-secondary w-100 fw-bold" disabled>{{ __('Current Plan') }}</button>
                    @else
                        <button class="btn btn-primary w-100 fw-bold py-2" data-bs-toggle="modal" data-bs-target="#paymentModal{{ $plan->id }}">
                            <i class="bi bi-currency-bitcoin me-2"></i>{{ __('Pay with Crypto') }}
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <!-- Ödeme Modalı -->
        @if($plan->price > 0)
        <div class="modal fade" id="paymentModal{{ $plan->id }}" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header border-bottom-0 pb-0">
                        <h5 class="modal-title fw-bold"><i class="bi bi-currency-bitcoin text-warning me-2"></i>{{ __('Crypto Payment') }} - {{ $plan->name }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="{{ route('subscriptions.pay', $plan) }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <div class="alert alert-info py-2 small">
                                <i class="bi bi-info-circle me-1"></i> {{ __('Please send exactly') }} <strong>${{ number_format($plan->price, 2) }} (USDT)</strong> {{ __('to the BEP-20 address below.') }}
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-semibold">{{ __('BEP-20 Wallet Address') }}</label>
                                <div class="input-group">
                                    <input type="text" class="form-control font-monospace" value="{{ $plan->bep20_wallet_address }}" readonly id="wallet-address-{{ $plan->id }}">
                                    <button class="btn btn-outline-secondary" type="button" onclick="copyAddress('{{ $plan->bep20_wallet_address }}')">
                                        <i class="bi bi-clipboard"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">{{ __('Transaction Hash (TX Hash)') }}</label>
                                <input type="text" name="tx_hash" class="form-control" placeholder="0x..." required>
                                <small class="text-muted">{{ __('Paste your transaction hash after sending the payment.') }}</small>
                            </div>
                        </div>
                        <div class="modal-footer border-top-0 pt-0">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                            <button type="submit" class="btn btn-primary fw-bold">{{ __('Submit Payment') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif
        @endforeach
    </div>
</div>
@endsection

@push('styles')
<style>
    .plan-card { transition: transform 0.2s, box-shadow 0.2s; border-radius: 16px; overflow: hidden; }
    .plan-card:hover { transform: translateY(-5px); box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important; }
    .feature-list li { margin-bottom: 10px; font-size: 0.9rem; }
</style>
@endpush

@push('scripts')
<script>
    function copyAddress(address) {
        navigator.clipboard.writeText(address).then(() => {
            toastr.success("{{ __('Wallet address copied to clipboard!') }}");
        });
    }
</script>
@endpush