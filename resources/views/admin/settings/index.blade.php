@extends('admin.layouts.master')

@section('title', __('Settings'))
@section('header', __('Settings'))

@push('styles')
<style>
    .settings-sidebar-card {
        position: sticky;
        top: calc(var(--adm-header) + 1.25rem);
    }

    .settings-nav .nav-link {
        display: flex;
        align-items: center;
        gap: .75rem;
        padding: .65rem .85rem;
        border-radius: 10px;
        color: var(--bs-body-color);
        font-weight: 500;
        font-size: .95rem;
        transition: background-color .15s, color .15s;
    }

    .settings-nav .nav-link:hover {
        background: var(--bs-tertiary-bg);
    }

    .settings-nav .nav-link.active {
        background: rgba(var(--bs-primary-rgb), .12);
        color: var(--bs-primary);
        font-weight: 600;
    }

    .settings-icon-badge {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.15rem;
        flex-shrink: 0;
    }

    .setting-box {
        background: var(--bs-tertiary-bg);
        border: 1px solid var(--bs-border-color-translucent);
        border-radius: 12px;
        padding: 1rem 1.15rem;
    }

    .setting-label {
        font-weight: 600;
        font-size: .875rem;
        margin-bottom: .45rem;
        display: block;
    }

    .media-preview-box {
        height: 72px;
        border-radius: 10px;
        border: 1px dashed var(--bs-border-color);
        background: var(--bs-body-bg);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: .5rem;
        overflow: hidden;
    }

    .media-preview-box img {
        max-height: 100%;
        max-width: 100%;
        object-fit: contain;
    }

    .lang-group-card {
        background: var(--bs-tertiary-bg);
        border: 1px solid var(--bs-border-color-translucent);
        border-radius: 12px;
        padding: 1.15rem;
    }

    @media (max-width: 991.98px) {
        .settings-sidebar-card {
            position: static;
        }
    }
</style>
@endpush

@section('content')
@php
    $settings = \App\Models\Setting::whereIn('key', [
        'site_name',
        'site_logo',
        'site_favicon',
        'contact_phone',
        'contact_address',
    ])->get()->keyBy('key');

    $siteName = $settings->get('site_name');
    $logo     = $settings->get('site_logo');
    $favicon  = $settings->get('site_favicon');
    $phone    = $settings->get('contact_phone');
    $address  = $settings->get('contact_address');
@endphp

<div class="row g-4 align-items-start">
    {{-- Left Navigation --}}
    <div class="col-lg-3">
        <div class="card shadow-sm settings-sidebar-card">
            <div class="card-body p-3">
                <div class="small fw-bold text-uppercase text-muted px-2 mb-2" style="letter-spacing: .05em; font-size: .72rem;">
                    {{ __('Categories') }}
                </div>
                <div class="nav flex-column nav-pills settings-nav gap-1" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                    <button class="nav-link active text-start" id="v-pills-general-tab" data-bs-toggle="pill" data-bs-target="#v-pills-general" type="button" role="tab">
                        <i class="bi bi-gear"></i>
                        <span>{{ __('General Settings') }}</span>
                    </button>
                    <button class="nav-link text-start" id="v-pills-contact-tab" data-bs-toggle="pill" data-bs-target="#v-pills-contact" type="button" role="tab">
                        <i class="bi bi-telephone"></i>
                        <span>{{ __('Contact Info') }}</span>
                    </button>
                    <button class="nav-link text-start" id="v-pills-translations-tab" data-bs-toggle="pill" data-bs-target="#v-pills-translations" type="button" role="tab">
                        <i class="bi bi-translate"></i>
                        <span>{{ __('Translations') }}</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Right Content --}}
    <div class="col-lg-9">
        <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="tab-content" id="v-pills-tabContent">

                {{-- TAB 1: GENERAL SETTINGS --}}
                <div class="tab-pane fade show active" id="v-pills-general" role="tabpanel">
                    <div class="card shadow-sm">
                        <div class="card-header bg-transparent py-3 px-4 d-flex align-items-center gap-3">
                            <div class="settings-icon-badge bg-primary-subtle text-primary-emphasis">
                                <i class="bi bi-gear"></i>
                            </div>
                            <h5 class="fw-bold mb-0">{{ __('General Settings') }}</h5>
                        </div>

                        <div class="card-body p-4">
                            <div class="setting-box mb-3">
                                <label class="setting-label" for="siteNameInput">{{ __('Site Name') }}</label>
                                <input type="text" id="siteNameInput" name="site_name" class="form-control" value="{{ old('site_name', $siteName->value ?? 'ASTORE Messenger') }}" required>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="setting-box h-100">
                                        <label class="setting-label" for="siteLogoInput">{{ __('Site Logo') }}</label>
                                        <div class="media-preview-box mb-3">
                                            <img id="logoPreview"
                                                 src="{{ ($logo && $logo->value) ? asset('storage/'.$logo->value) : '' }}"
                                                 alt="Logo"
                                                 class="{{ ($logo && $logo->value) ? '' : 'd-none' }}">
                                            <i id="logoPlaceholder" class="bi bi-image text-muted fs-3 {{ ($logo && $logo->value) ? 'd-none' : '' }}"></i>
                                        </div>
                                        <input type="file" id="siteLogoInput" name="site_logo" class="form-control form-control-sm js-image-preview" data-preview="logoPreview" data-placeholder="logoPlaceholder" accept="image/*">
                                        <small class="text-muted d-block mt-2">{{ __('Upload new logo to replace. (PNG, JPG, SVG)') }}</small>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="setting-box h-100">
                                        <label class="setting-label" for="siteFaviconInput">{{ __('Site Favicon') }}</label>
                                        <div class="media-preview-box mb-3">
                                            <img id="faviconPreview"
                                                 src="{{ ($favicon && $favicon->value) ? asset('storage/'.$favicon->value) : '' }}"
                                                 alt="Favicon"
                                                 style="max-height: 36px;"
                                                 class="{{ ($favicon && $favicon->value) ? '' : 'd-none' }}">
                                            <i id="faviconPlaceholder" class="bi bi-app-indicator text-muted fs-3 {{ ($favicon && $favicon->value) ? 'd-none' : '' }}"></i>
                                        </div>
                                        <input type="file" id="siteFaviconInput" name="site_favicon" class="form-control form-control-sm js-image-preview" data-preview="faviconPreview" data-placeholder="faviconPlaceholder" accept="image/x-icon,image/png">
                                        <small class="text-muted d-block mt-2">{{ __('Upload new favicon to replace. (ICO, PNG)') }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- TAB 2: CONTACT INFO --}}
                <div class="tab-pane fade" id="v-pills-contact" role="tabpanel">
                    <div class="card shadow-sm">
                        <div class="card-header bg-transparent py-3 px-4 d-flex align-items-center gap-3">
                            <div class="settings-icon-badge bg-success-subtle text-success-emphasis">
                                <i class="bi bi-telephone"></i>
                            </div>
                            <h5 class="fw-bold mb-0">{{ __('Contact Info') }}</h5>
                        </div>

                        <div class="card-body p-4">
                            <div class="setting-box mb-3">
                                <label class="setting-label" for="contactPhoneInput">{{ __('Contact Phone') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                                    <input type="text" id="contactPhoneInput" name="contact_phone" class="form-control" value="{{ old('contact_phone', $phone->value ?? '') }}">
                                </div>
                            </div>

                            <div class="setting-box">
                                <label class="setting-label" for="contactAddressInput">{{ __('Contact Address') }}</label>
                                <textarea id="contactAddressInput" name="contact_address" class="form-control" rows="3">{{ old('contact_address', $address->value ?? '') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- TAB 3: TRANSLATIONS --}}
                <div class="tab-pane fade" id="v-pills-translations" role="tabpanel">
                    <div class="card shadow-sm">
                        <div class="card-header bg-transparent py-3 px-4 d-flex align-items-center gap-3">
                            <div class="settings-icon-badge bg-info-subtle text-info-emphasis">
                                <i class="bi bi-translate"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-0">{{ __('Translations') }}</h5>
                                <p class="text-muted small mb-0">{{ __('Update the site name and address in all 3 languages.') }}</p>
                            </div>
                        </div>

                        <div class="card-body p-4">
                            <div class="lang-group-card mb-3">
                                <h6 class="fw-bold mb-3 d-flex align-items-center gap-2">
                                    <i class="bi bi-fonts text-primary"></i>
                                    <span>{{ __('Site Name Translations') }}</span>
                                </h6>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="setting-label">🇬🇧 English (EN)</label>
                                        <input type="text" name="trans_site_name_en" class="form-control" value="{{ old('trans_site_name_en', $siteName->en_name ?? '') }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="setting-label">🇹🇲 Türkmençe (TK)</label>
                                        <input type="text" name="trans_site_name_tk" class="form-control" value="{{ old('trans_site_name_tk', $siteName->tk_name ?? '') }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="setting-label">🇷🇺 Русский (RU)</label>
                                        <input type="text" name="trans_site_name_ru" class="form-control" value="{{ old('trans_site_name_ru', $siteName->ru_name ?? '') }}">
                                    </div>
                                </div>
                            </div>

                            <div class="lang-group-card">
                                <h6 class="fw-bold mb-3 d-flex align-items-center gap-2">
                                    <i class="bi bi-geo-alt text-primary"></i>
                                    <span>{{ __('Address Translations') }}</span>
                                </h6>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="setting-label">🇬🇧 English (EN)</label>
                                        <input type="text" name="trans_address_en" class="form-control" value="{{ old('trans_address_en', $address->en_name ?? '') }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="setting-label">🇹🇲 Türkmençe (TK)</label>
                                        <input type="text" name="trans_address_tk" class="form-control" value="{{ old('trans_address_tk', $address->tk_name ?? '') }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="setting-label">🇷🇺 Русский (RU)</label>
                                        <input type="text" name="trans_address_ru" class="form-control" value="{{ old('trans_address_ru', $address->ru_name ?? '') }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Save Action Bar --}}
            <div class="card shadow-sm mt-3">
                <div class="card-body py-3 px-4 d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary px-4 py-2 fw-semibold rounded-3">
                        <i class="bi bi-save me-2"></i>{{ __('Save Changes') }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Restore active tab on reload/redirect
const tabStorageKey = 'adm_settings_active_tab';
const savedTab = localStorage.getItem(tabStorageKey);
if (savedTab) {
    const triggerEl = document.querySelector(`#v-pills-tab button[data-bs-target="${savedTab}"]`);
    if (triggerEl) {
        bootstrap.Tab.getOrCreateInstance(triggerEl).show();
    }
}

document.querySelectorAll('#v-pills-tab button[data-bs-toggle="pill"]').forEach((btn) => {
    btn.addEventListener('shown.bs.tab', (e) => {
        localStorage.setItem(tabStorageKey, e.target.dataset.bsTarget);
    });
});

// Live preview for logo & favicon file inputs
document.querySelectorAll('.js-image-preview').forEach((input) => {
    input.addEventListener('change', () => {
        const file = input.files && input.files[0];
        if (!file) return;

        const img = document.getElementById(input.dataset.preview);
        const placeholder = document.getElementById(input.dataset.placeholder);

        img.src = URL.createObjectURL(file);
        img.classList.remove('d-none');
        if (placeholder) placeholder.classList.add('d-none');
    });
});
</script>
@endpush