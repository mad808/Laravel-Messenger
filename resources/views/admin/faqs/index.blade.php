@extends('admin.layouts.master')

@section('title', __('FAQs'))
@section('header', __('FAQs'))

@push('styles')
<style>
    .faq-stat-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        flex-shrink: 0;
    }

    .faq-table thead th {
        font-size: .75rem;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
        color: var(--bs-secondary-color);
        background-color: var(--bs-tertiary-bg);
        border-bottom-width: 1px;
        padding-top: .85rem;
        padding-bottom: .85rem;
    }

    .faq-empty-icon {
        width: 64px;
        height: 64px;
        border-radius: 16px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.75rem;
    }
</style>
@endpush

@section('content')
@php
    $totalFaqs  = \App\Models\Faq::count();
    $activeFaqs = \App\Models\Faq::where('is_active', true)->count();
    $locale     = app()->getLocale();
    $qCol       = in_array($locale, ['en', 'tk', 'ru']) ? "question_{$locale}" : 'question_en';
    $aCol       = in_array($locale, ['en', 'tk', 'ru']) ? "answer_{$locale}" : 'answer_en';
@endphp

{{-- Summary Cards --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-4">
        <div class="card shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="faq-stat-icon bg-primary-subtle text-primary-emphasis">
                    <i class="bi bi-question-circle"></i>
                </div>
                <div class="min-w-0">
                    <div class="small text-muted text-truncate">{{ __('Total') }}</div>
                    <div class="fs-4 fw-bold lh-sm">{{ number_format($totalFaqs) }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-md-4">
        <div class="card shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="faq-stat-icon bg-success-subtle text-success-emphasis">
                    <i class="bi bi-check-circle"></i>
                </div>
                <div class="min-w-0">
                    <div class="small text-muted text-truncate">{{ __('Active') }}</div>
                    <div class="fs-4 fw-bold lh-sm">{{ number_format($activeFaqs) }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-4">
        <div class="card shadow-sm h-100">
            <div class="card-body d-flex align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3 min-w-0">
                    <div class="faq-stat-icon bg-secondary-subtle text-secondary-emphasis">
                        <i class="bi bi-eye-slash"></i>
                    </div>
                    <div class="min-w-0">
                        <div class="small text-muted text-truncate">{{ __('Inactive') }}</div>
                        <div class="fs-4 fw-bold lh-sm">{{ number_format(max(0, $totalFaqs - $activeFaqs)) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Table Card --}}
<div class="card shadow-sm overflow-hidden">
    <div class="card-header bg-transparent py-3 px-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h5 class="mb-0 fw-bold d-flex align-items-center gap-2">
            <i class="bi bi-question-circle text-primary"></i>
            <span>{{ __('FAQs') }}</span>
        </h5>

        <button type="button" class="btn btn-primary btn-sm rounded-3 px-3 fw-semibold" id="btnAddFaq" data-bs-toggle="modal" data-bs-target="#faqModal">
            <i class="bi bi-plus-lg me-1"></i>{{ __('Add FAQ') }}
        </button>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 faq-table">
                <thead>
                    <tr>
                        <th class="ps-4">ID</th>
                        <th>{{ __('Question') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Created At') }}</th>
                        <th class="text-end pe-4">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($faqs as $faq)
                    <tr>
                        <td class="ps-4 text-muted fw-medium text-nowrap">#{{ $faq->id }}</td>

                        <td style="max-width: 460px;">
                            <div class="fw-semibold text-truncate">{{ $faq->$qCol }}</div>
                            <small class="text-muted d-block text-truncate">{{ $faq->$aCol }}</small>
                        </td>

                        <td class="text-nowrap">
                            <form action="{{ route('admin.faqs.toggle', $faq) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-sm p-0 border-0 bg-transparent">
                                    @if($faq->is_active)
                                        <span class="badge rounded-pill bg-success-subtle text-success-emphasis px-2.5 py-1">
                                            <i class="bi bi-check-circle me-1"></i>{{ __('Active') }}
                                        </span>
                                    @else
                                        <span class="badge rounded-pill bg-secondary-subtle text-secondary-emphasis px-2.5 py-1">
                                            <i class="bi bi-pause-circle me-1"></i>{{ __('Inactive') }}
                                        </span>
                                    @endif
                                </button>
                            </form>
                        </td>

                        <td class="text-nowrap">
                            <div>{{ $faq->created_at->format('d M, Y') }}</div>
                        </td>

                        <td class="text-end pe-4 text-nowrap">
                            <div class="d-inline-flex align-items-center gap-1">
                                <button type="button"
                                        class="btn btn-sm btn-outline-primary rounded-3 js-edit-faq"
                                        data-bs-toggle="modal"
                                        data-bs-target="#faqModal"
                                        data-action="{{ route('admin.faqs.update', $faq) }}"
                                        data-q-en="{{ $faq->question_en }}"
                                        data-q-tk="{{ $faq->question_tk }}"
                                        data-q-ru="{{ $faq->question_ru }}"
                                        data-a-en="{{ $faq->answer_en }}"
                                        data-a-tk="{{ $faq->answer_tk }}"
                                        data-a-ru="{{ $faq->answer_ru }}"
                                        data-active="{{ $faq->is_active ? '1' : '0' }}">
                                    <i class="bi bi-pencil"></i>
                                </button>

                                <form action="{{ route('admin.faqs.destroy', $faq) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Are you sure you want to delete this FAQ?') }}');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-3">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5">
                            <div class="faq-empty-icon bg-body-tertiary text-muted mx-auto mb-3">
                                <i class="bi bi-question-circle"></i>
                            </div>
                            <div class="text-muted fw-medium">{{ __('No FAQs found.') }}</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($faqs->hasPages())
    <div class="card-footer bg-transparent py-3 px-4">
        {{ $faqs->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>

{{-- Create / Edit Modal --}}
<div class="modal fade" id="faqModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form id="faqForm" action="{{ route('admin.faqs.store') }}" method="POST" class="modal-content rounded-4 overflow-hidden">
            @csrf
            <input type="hidden" name="_method" id="faqMethod" value="POST">

            <div class="modal-header py-3 px-4">
                <h6 class="modal-title fw-bold mb-0" id="faqModalTitle">{{ __('Add FAQ') }}</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4">
                <ul class="nav nav-pills gap-2 mb-3" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active py-1 px-3 small fw-semibold" data-bs-toggle="pill" data-bs-target="#faqLangEn" type="button">🇬🇧 EN</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link py-1 px-3 small fw-semibold" data-bs-toggle="pill" data-bs-target="#faqLangTk" type="button">🇹🇲 TK</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link py-1 px-3 small fw-semibold" data-bs-toggle="pill" data-bs-target="#faqLangRu" type="button">🇷🇺 RU</button>
                    </li>
                </ul>

                <div class="tab-content">
                    <div class="tab-pane fade show active" id="faqLangEn">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">{{ __('Question') }} (EN)</label>
                            <input type="text" name="question_en" id="faqQEn" class="form-control" maxlength="255" required>
                        </div>
                        <div>
                            <label class="form-label small fw-semibold">{{ __('Answer') }} (EN)</label>
                            <textarea name="answer_en" id="faqAEn" class="form-control" rows="4" required></textarea>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="faqLangTk">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">{{ __('Question') }} (TK)</label>
                            <input type="text" name="question_tk" id="faqQTk" class="form-control" maxlength="255" required>
                        </div>
                        <div>
                            <label class="form-label small fw-semibold">{{ __('Answer') }} (TK)</label>
                            <textarea name="answer_tk" id="faqATk" class="form-control" rows="4" required></textarea>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="faqLangRu">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">{{ __('Question') }} (RU)</label>
                            <input type="text" name="question_ru" id="faqQRu" class="form-control" maxlength="255" required>
                        </div>
                        <div>
                            <label class="form-label small fw-semibold">{{ __('Answer') }} (RU)</label>
                            <textarea name="answer_ru" id="faqARu" class="form-control" rows="4" required></textarea>
                        </div>
                    </div>
                </div>

                <div class="form-check form-switch mt-3 pt-3 border-top">
                    <input type="hidden" name="is_active" value="0">
                    <input class="form-check-input" type="checkbox" name="is_active" id="faqActive" value="1" checked>
                    <label class="form-check-label fw-semibold small" for="faqActive">{{ __('Active') }}</label>
                </div>
            </div>

            <div class="modal-footer py-2 px-4">
                <button type="button" class="btn btn-outline-secondary rounded-3" data-bs-dismiss="modal">{{ __('Close') }}</button>
                <button type="submit" class="btn btn-primary rounded-3 fw-semibold px-4">{{ __('Save Changes') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
const faqForm   = document.getElementById('faqForm');
const faqMethod = document.getElementById('faqMethod');
const faqTitle  = document.getElementById('faqModalTitle');

document.getElementById('btnAddFaq').addEventListener('click', () => {
    faqForm.action = "{{ route('admin.faqs.store') }}";
    faqMethod.value = 'POST';
    faqTitle.textContent = "{{ __('Add FAQ') }}";
    faqForm.reset();
    document.getElementById('faqActive').checked = true;
});

document.querySelectorAll('.js-edit-faq').forEach((btn) => {
    btn.addEventListener('click', () => {
        faqForm.action = btn.dataset.action;
        faqMethod.value = 'PUT';
        faqTitle.textContent = "{{ __('Edit') }}";
        document.getElementById('faqQEn').value = btn.dataset.qEn;
        document.getElementById('faqQTk').value = btn.dataset.qTk;
        document.getElementById('faqQRu').value = btn.dataset.qRu;
        document.getElementById('faqAEn').value = btn.dataset.aEn;
        document.getElementById('faqATk').value = btn.dataset.aTk;
        document.getElementById('faqARu').value = btn.dataset.aRu;
        document.getElementById('faqActive').checked = btn.dataset.active === '1';
    });
});
</script>
@endpush