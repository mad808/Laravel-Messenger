@extends('admin.layouts.master')

@section('title', __('Conversation Details'))
@section('header', $conversation->title ?? __('Private Chat'))

@push('styles')
<style>
    .conv-info-card {
        position: sticky;
        top: calc(var(--adm-header) + 1.25rem);
    }

    .conv-detail-avatar {
        width: 88px;
        height: 88px;
        border-radius: 22px;
        object-fit: cover;
    }

    .conv-detail-avatar-fallback {
        width: 88px;
        height: 88px;
        border-radius: 22px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 2.25rem;
        font-weight: 700;
    }

    .conv-meta-list {
        background: var(--bs-tertiary-bg);
        border: 1px solid var(--bs-border-color-translucent);
        border-radius: 12px;
        padding: .25rem 1rem;
    }

    .conv-meta-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: .75rem;
        padding: .65rem 0;
        border-bottom: 1px solid var(--bs-border-color-translucent);
        font-size: .9rem;
    }

    .conv-meta-row:last-child {
        border-bottom: 0;
    }

    .chat-scroll-area {
        height: 620px;
        max-height: 72vh;
        overflow-y: auto;
        padding: 1.25rem;
        background: var(--bs-tertiary-bg);
    }

    .msg-row {
        display: flex;
        align-items: flex-end;
        gap: .6rem;
        margin-bottom: 1rem;
    }

    .msg-row.msg-own {
        flex-direction: row-reverse;
    }

    .msg-avatar {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        object-fit: cover;
        flex-shrink: 0;
    }

    .msg-avatar-fallback {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: .8rem;
        flex-shrink: 0;
    }

    .msg-bubble {
        max-width: 78%;
        background: var(--bs-body-bg);
        border: 1px solid var(--bs-border-color-translucent);
        border-radius: 14px 14px 14px 4px;
        padding: .75rem 1rem;
        box-shadow: 0 2px 6px rgba(0, 0, 0, .03);
    }

    .msg-row.msg-own .msg-bubble {
        background: rgba(var(--bs-primary-rgb), .12);
        border-color: rgba(var(--bs-primary-rgb), .22);
        border-radius: 14px 14px 4px 14px;
    }

    .msg-bubble.msg-deleted {
        border-style: dashed;
        opacity: .78;
    }

    .msg-body-text {
        white-space: pre-wrap;
        word-break: break-word;
        font-size: .93rem;
        line-height: 1.45;
    }

    .msg-reply-box {
        background: var(--bs-tertiary-bg);
        border-left: 3px solid var(--bs-primary);
        border-radius: 8px;
        padding: .45rem .7rem;
        margin-bottom: .5rem;
        font-size: .82rem;
    }

    .attachment-preview {
        border-radius: 10px;
        max-width: 220px;
        max-height: 220px;
        object-fit: cover;
        cursor: pointer;
        border: 1px solid var(--bs-border-color-translucent);
        transition: opacity .15s ease;
    }

    .attachment-preview:hover {
        opacity: .9;
    }

    .msg-delete-btn {
        opacity: .65;
        transition: opacity .15s ease;
    }

    .msg-bubble:hover .msg-delete-btn {
        opacity: 1;
    }

    .conv-empty-icon {
        width: 64px;
        height: 64px;
        border-radius: 16px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.75rem;
    }

    @media (max-width: 991.98px) {
        .conv-info-card {
            position: static;
        }
        .msg-bubble {
            max-width: 86%;
        }
    }
</style>
@endpush

@section('content')
@php
    $convTitle   = $conversation->title ?? __('Private Chat');
    $convInitial = mb_strtoupper(mb_substr($conversation->title ?? $conversation->type, 0, 1));
    $convTone    = match($conversation->type) {
        'private' => 'bg-secondary-subtle text-secondary-emphasis',
        'group'   => 'bg-primary-subtle text-primary-emphasis',
        default   => 'bg-info-subtle text-info-emphasis',
    };
    $typeLabel   = match($conversation->type) {
        'private' => __('Private'),
        'group'   => __('Group'),
        'channel' => __('Channel'),
        default   => ucfirst($conversation->type),
    };
@endphp

<div class="row g-4 align-items-start">
    {{-- Left Column: Conversation Info --}}
    <div class="col-lg-4">
        <div class="card shadow-sm conv-info-card">
            <div class="card-body p-4 text-center">
                @if($conversation->avatar)
                    <img src="{{ asset('storage/'.$conversation->avatar) }}"
                         class="conv-detail-avatar mb-3"
                         alt="{{ $convTitle }}"
                         onerror="this.classList.add('d-none'); this.nextElementSibling.classList.remove('d-none');">
                    <div class="conv-detail-avatar-fallback {{ $convTone }} mb-3 d-none">{{ $convInitial }}</div>
                @else
                    <div class="conv-detail-avatar-fallback {{ $convTone }} mb-3">{{ $convInitial }}</div>
                @endif

                <h5 class="fw-bold mb-2">{{ $convTitle }}</h5>

                <span class="badge rounded-pill {{ $convTone }} px-3 py-1 mb-3">
                    {{ $typeLabel }}
                </span>

                <div class="conv-meta-list text-start mt-2">
                    <div class="conv-meta-row">
                        <span class="text-muted">{{ __('Owner') }}</span>
                        <span class="fw-semibold text-truncate">{{ $conversation->owner->name ?? '-' }}</span>
                    </div>
                    <div class="conv-meta-row">
                        <span class="text-muted">{{ __('Participants') }}</span>
                        <span class="badge rounded-pill bg-body-secondary text-body">{{ $conversation->participants->count() }}</span>
                    </div>
                    <div class="conv-meta-row">
                        <span class="text-muted">{{ __('Created At') }}</span>
                        <span class="fw-medium">{{ $conversation->created_at->format('d M, Y') }}</span>
                    </div>
                </div>

                <div class="d-grid gap-2 mt-4">
                    <a href="{{ route('admin.conversations.index') }}" class="btn btn-outline-secondary rounded-3 fw-medium">
                        <i class="bi bi-arrow-left me-1"></i>{{ __('Back to List') }}
                    </a>

                    <form action="{{ route('admin.conversations.destroy', $conversation) }}" method="POST" onsubmit="return confirm('{{ __('Are you sure you want to delete this entire conversation?') }}');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger rounded-3 w-100 fw-medium">
                            <i class="bi bi-trash me-1"></i>{{ __('Delete Conversation') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Right Column: Message History --}}
    <div class="col-lg-8">
        <div class="card shadow-sm overflow-hidden">
            <div class="card-header bg-transparent py-3 px-4 d-flex justify-content-between align-items-center gap-2">
                <h6 class="mb-0 fw-bold d-flex align-items-center gap-2">
                    <i class="bi bi-chat-dots text-primary"></i>
                    <span>{{ __('Message History') }}</span>
                </h6>
                <span class="badge bg-body-tertiary text-body border rounded-pill px-3 py-2 fw-semibold">
                    {{ number_format($conversation->messages->count()) }} {{ __('Messages') }}
                </span>
            </div>

            <div class="chat-scroll-area" id="chatScrollArea">
                @forelse($conversation->messages->reverse() as $message)
                    @php
                        $isOwn       = $message->user_id == auth()->id();
                        $senderName  = $message->user->name ?? __('Deleted User');
                        $senderInit  = mb_strtoupper(mb_substr($senderName, 0, 1));
                    @endphp

                    <div class="msg-row {{ $isOwn ? 'msg-own' : '' }}">
                        @if($message->user && $message->user->avatar)
                            <img src="{{ asset('storage/'.$message->user->avatar) }}"
                                 class="msg-avatar"
                                 alt="{{ $senderName }}"
                                 onerror="this.classList.add('d-none'); this.nextElementSibling.classList.remove('d-none');">
                            <div class="msg-avatar-fallback bg-primary-subtle text-primary-emphasis d-none">{{ $senderInit }}</div>
                        @else
                            <div class="msg-avatar-fallback bg-primary-subtle text-primary-emphasis">{{ $senderInit }}</div>
                        @endif

                        <div class="msg-bubble {{ $message->is_deleted ? 'msg-deleted' : '' }}">
                            <div class="d-flex justify-content-between align-items-center gap-3 mb-1">
                                <small class="fw-bold text-primary text-truncate">{{ $senderName }}</small>
                                <small class="text-muted text-nowrap" style="font-size: .72rem;">
                                    {{ $message->created_at->format('d M, H:i') }}
                                </small>
                            </div>

                            @if($message->is_deleted)
                                <p class="mb-0 fst-italic text-muted small">
                                    <i class="bi bi-slash-circle me-1"></i>{{ __('This message was deleted.') }}
                                </p>
                            @else
                                @if($message->replyTo)
                                    <div class="msg-reply-box">
                                        <div class="fw-bold text-truncate">{{ $message->replyTo->user->name ?? __('Deleted User') }}</div>
                                        <div class="text-muted text-truncate">{{ $message->replyTo->body ?? __('Attachment') }}</div>
                                    </div>
                                @endif

                                @if($message->body)
                                    <div class="msg-body-text">{{ $message->body }}</div>
                                @endif

                                @if($message->attachments->count() > 0)
                                    <div class="mt-2 d-flex flex-wrap gap-2">
                                        @foreach($message->attachments as $attachment)
                                            @if(str_contains($attachment->mime_type, 'image'))
                                                <img src="{{ asset('storage/'.$attachment->file_path) }}"
                                                     class="attachment-preview js-img-lightbox"
                                                     data-bs-toggle="modal"
                                                     data-bs-target="#imgPreviewModal"
                                                     data-src="{{ asset('storage/'.$attachment->file_path) }}"
                                                     alt="Attachment">
                                            @elseif(str_contains($attachment->mime_type, 'video'))
                                                <video src="{{ asset('storage/'.$attachment->file_path) }}" controls class="rounded-3" style="max-width: 260px; max-height: 200px;"></video>
                                            @else
                                                <a href="{{ asset('storage/'.$attachment->file_path) }}" target="_blank" class="btn btn-sm btn-outline-secondary rounded-3">
                                                    <i class="bi bi-paperclip me-1"></i>{{ $attachment->file_name }}
                                                </a>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif

                                <div class="text-end mt-1">
                                    <form action="{{ route('admin.conversations.message.destroy', $message) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Are you sure you want to delete this message?') }}');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-link text-danger p-0 msg-delete-btn" style="font-size: .78rem;" title="{{ __('Delete Message') }}">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="h-100 d-flex flex-column align-items-center justify-content-center text-center py-5">
                        <div class="conv-empty-icon bg-body text-muted border mb-3">
                            <i class="bi bi-chat-square"></i>
                        </div>
                        <div class="text-muted fw-medium">{{ __('No messages in this conversation yet.') }}</div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- Attachment Image Lightbox Modal --}}
<div class="modal fade" id="imgPreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content bg-dark border-0 rounded-4 overflow-hidden">
            <div class="modal-header border-0 py-2 px-3">
                <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0 text-center pb-3 px-3">
                <img id="lightboxImg" src="" class="img-fluid rounded-3" style="max-height: 78vh; object-fit: contain;" alt="Attachment">
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const chatArea = document.getElementById('chatScrollArea');
if (chatArea) {
    chatArea.scrollTop = chatArea.scrollHeight;
}

document.querySelectorAll('.js-img-lightbox').forEach((img) => {
    img.addEventListener('click', () => {
        document.getElementById('lightboxImg').src = img.dataset.src;
    });
});
</script>
@endpush