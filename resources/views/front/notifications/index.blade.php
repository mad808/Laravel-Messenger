@extends('front.layouts.master')

@section('title', __('Notifications'))
@section('header_title', __('Notifications'))

@section('sidebar_content')
    <div class="p-3 border-bottom">
        <a href="{{ route('home') }}" class="btn btn-light text-start w-100 fw-semibold">
            <i class="bi bi-arrow-left me-2"></i>{{ __('Back to Chats') }}
        </a>
    </div>
@endsection

@section('content')
<div class="h-100 overflow-auto p-4" style="background: var(--bs-tertiary-bg);">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-transparent border-0 pt-4">
                    <h5 class="fw-bold mb-0"><i class="bi bi-bell-fill text-primary me-2"></i>{{ __('All Notifications') }}</h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($notifications as $notification)
                            <div class="list-group-item p-3 d-flex align-items-start gap-3 border-bottom-0">
                                <div class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; flex-shrink: 0;">
                                    <i class="bi bi-bell-fill"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <h6 class="mb-0 fw-bold">{{ $notification->data['title'] ?? __('Notification') }}</h6>
                                        <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                                    </div>
                                    <p class="mb-0 text-muted">{{ $notification->data['body'] ?? '' }}</p>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-5 text-muted">
                                <i class="bi bi-bell-slash fs-1 d-block mb-3 opacity-50"></i>
                                <h5>{{ __('No notifications found') }}</h5>
                            </div>
                        @endforelse
                    </div>
                </div>
                @if($notifications->hasPages())
                    <div class="card-footer bg-transparent border-0">
                        {{ $notifications->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection