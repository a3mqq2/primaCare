@extends('layouts.app')

@section('title', __('centers.add_center') . ' - PrimaCare')

@push('css')
<link href="{{ asset('assets/plugins/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" />
<style>
    .loading-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255,255,255,0.7);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10;
        border-radius: .375rem;
    }
    [data-bs-theme="dark"] .loading-overlay {
        background: rgba(0,0,0,0.5);
    }
    .drop-zone {
        border: 2px dashed #d1d5db;
        border-radius: .5rem;
        padding: 2rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        background: #f9fafb;
        position: relative;
    }
    [data-bs-theme="dark"] .drop-zone {
        background: #1e293b;
        border-color: #475569;
    }
    .drop-zone:hover,
    .drop-zone.dragover {
        border-color: var(--bs-primary);
        background: rgba(var(--bs-primary-rgb), 0.05);
    }
    .drop-zone-icon {
        font-size: 2.5rem;
        color: #9ca3af;
        margin-bottom: .5rem;
    }
    .drop-zone-text {
        color: #6b7280;
        font-size: .875rem;
    }
    .drop-zone-text span {
        color: var(--bs-primary);
        font-weight: 600;
    }
    .drop-zone-hint {
        color: #9ca3af;
        font-size: .75rem;
        margin-top: .25rem;
    }
    .drop-zone-preview {
        max-height: 160px;
        border-radius: .375rem;
        object-fit: contain;
    }
    .drop-zone-remove {
        position: absolute;
        top: .5rem;
        left: .5rem;
        background: rgba(220,53,69,0.9);
        color: #fff;
        border: none;
        border-radius: 50%;
        width: 28px;
        height: 28px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: .875rem;
    }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">{{ __('centers.add_center') }}</h4>
            <a href="{{ route('centers.index') }}" class="btn btn-secondary">
                <i class="ti ti-arrow-right me-1"></i>{{ __('centers.back') }}
            </a>
        </div>

        <div class="card position-relative">
            <div class="loading-overlay" id="loading" style="display:none;">
                <div class="spinner-border text-primary" role="status"></div>
            </div>
            <div class="card-body">
                <form id="center-form">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('centers.name_ar') }} <span class="text-danger">*</span></label>
                            <input type="text" name="name_ar" id="name_ar" class="form-control" dir="rtl" />
                            <div class="invalid-feedback" id="error-name_ar"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('centers.name_en') }} <span class="text-danger">*</span></label>
                            <input type="text" name="name_en" id="name_en" class="form-control" dir="ltr" />
                            <div class="invalid-feedback" id="error-name_en"></div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('centers.city') }} <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <select name="city_id" id="city_id" class="form-select">
                                    <option value="">{{ __('centers.select_city') }}</option>
                                    @foreach($cities as $city)
                                        <option value="{{ $city->id }}">{{ $city->name_ar }} - {{ $city->name_en }}</option>
                                    @endforeach
                                </select>
                                <button type="button" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#addCityModal">
                                    <i class="ti ti-plus"></i>
                                </button>
                            </div>
                            <div class="invalid-feedback" id="error-city_id"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('centers.phone') }}</label>
                            <input type="text" name="phone" id="phone" class="form-control" />
                            <div class="invalid-feedback" id="error-phone"></div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ __('centers.notes') }}</label>
                        <textarea name="notes" id="notes" class="form-control" rows="3"></textarea>
                        <div class="invalid-feedback" id="error-notes"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ __('centers.logo') }}</label>
                        <div class="drop-zone" id="drop-zone">
                            <div id="drop-zone-prompt">
                                <div class="drop-zone-icon"><i class="ti ti-cloud-upload"></i></div>
                                <div class="drop-zone-text">{!! __('centers.drop_zone_text') !!}</div>
                                <div class="drop-zone-hint">{{ __('centers.drop_zone_hint') }}</div>
                            </div>
                            <div id="drop-zone-preview-container" style="display:none;">
                                <img id="drop-zone-preview" class="drop-zone-preview" />
                                <button type="button" class="drop-zone-remove" id="remove-logo"><i class="ti ti-x"></i></button>
                            </div>
                        </div>
                        <input type="file" id="logo-input" name="logo" accept="image/*" style="display:none;" />
                        <div class="invalid-feedback d-block" id="error-logo"></div>
                    </div>

                    <button type="submit" class="btn btn-primary" id="submit-btn">
                        <i class="ti ti-check me-1"></i>{{ __('centers.save') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addCityModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('centers.add_city') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="city-form">
                    <div class="mb-3">
                        <label class="form-label">{{ __('centers.city_name_ar') }} <span class="text-danger">*</span></label>
                        <input type="text" name="city_name_ar" id="city_name_ar" class="form-control" dir="rtl" />
                        <div class="invalid-feedback" id="error-city_name_ar"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('centers.city_name_en') }} <span class="text-danger">*</span></label>
                        <input type="text" name="city_name_en" id="city_name_en" class="form-control" dir="ltr" />
                        <div class="invalid-feedback" id="error-city_name_en"></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('centers.cancel') }}</button>
                <button type="button" class="btn btn-primary" id="save-city-btn">
                    <i class="ti ti-check me-1"></i>{{ __('centers.save') }}
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script src="{{ asset('assets/plugins/sweetalert2/sweetalert2.min.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        let csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        let dropZone = document.getElementById('drop-zone');
        let logoInput = document.getElementById('logo-input');
        let selectedFile = null;

        dropZone.addEventListener('click', function() {
            if (!selectedFile) logoInput.click();
        });

        dropZone.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.classList.add('dragover');
        });

        dropZone.addEventListener('dragleave', function() {
            this.classList.remove('dragover');
        });

        dropZone.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('dragover');
            if (e.dataTransfer.files.length) {
                handleFile(e.dataTransfer.files[0]);
            }
        });

        logoInput.addEventListener('change', function() {
            if (this.files.length) handleFile(this.files[0]);
        });

        document.getElementById('remove-logo').addEventListener('click', function(e) {
            e.stopPropagation();
            selectedFile = null;
            logoInput.value = '';
            document.getElementById('drop-zone-prompt').style.display = '';
            document.getElementById('drop-zone-preview-container').style.display = 'none';
        });

        function handleFile(file) {
            if (!file.type.startsWith('image/')) return;
            selectedFile = file;
            let reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('drop-zone-preview').src = e.target.result;
                document.getElementById('drop-zone-prompt').style.display = 'none';
                document.getElementById('drop-zone-preview-container').style.display = '';
            };
            reader.readAsDataURL(file);
        }

        document.getElementById('center-form').addEventListener('submit', function(e) {
            e.preventDefault();
            clearErrors();

            let btn = document.getElementById('submit-btn');
            btn.disabled = true;
            document.getElementById('loading').style.display = 'flex';

            let formData = new FormData();
            formData.append('name_ar', document.getElementById('name_ar').value);
            formData.append('name_en', document.getElementById('name_en').value);
            formData.append('city_id', document.getElementById('city_id').value);
            formData.append('phone', document.getElementById('phone').value);
            formData.append('notes', document.getElementById('notes').value);
            if (selectedFile) formData.append('logo', selectedFile);

            fetch('{{ route("centers.store") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(response => response.json().then(data => ({status: response.status, body: data})))
            .then(({status, body}) => {
                btn.disabled = false;
                document.getElementById('loading').style.display = 'none';

                if (status === 422) {
                    showErrors(body.errors);
                    return;
                }

                if (body.success) {
                    Swal.fire({
                        icon: 'success',
                        title: body.message,
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        window.location.href = '{{ route("centers.index") }}';
                    });
                }
            })
            .catch(() => {
                btn.disabled = false;
                document.getElementById('loading').style.display = 'none';
            });
        });

        document.getElementById('save-city-btn').addEventListener('click', function() {
            let btn = this;
            let nameAr = document.getElementById('city_name_ar');
            let nameEn = document.getElementById('city_name_en');

            nameAr.classList.remove('is-invalid');
            nameEn.classList.remove('is-invalid');
            document.getElementById('error-city_name_ar').textContent = '';
            document.getElementById('error-city_name_en').textContent = '';

            btn.disabled = true;

            fetch('{{ route("cities.store") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ name_ar: nameAr.value, name_en: nameEn.value })
            })
            .then(response => response.json().then(data => ({status: response.status, body: data})))
            .then(({status, body}) => {
                btn.disabled = false;

                if (status === 422) {
                    if (body.errors) {
                        if (body.errors.name_ar) {
                            nameAr.classList.add('is-invalid');
                            document.getElementById('error-city_name_ar').textContent = body.errors.name_ar[0];
                        }
                        if (body.errors.name_en) {
                            nameEn.classList.add('is-invalid');
                            document.getElementById('error-city_name_en').textContent = body.errors.name_en[0];
                        }
                    }
                    return;
                }

                if (body.success) {
                    let select = document.getElementById('city_id');
                    let option = new Option(body.city.name_ar + ' - ' + body.city.name_en, body.city.id, true, true);
                    select.appendChild(option);

                    nameAr.value = '';
                    nameEn.value = '';
                    bootstrap.Modal.getInstance(document.getElementById('addCityModal')).hide();

                    Swal.fire({
                        icon: 'success',
                        title: body.message,
                        showConfirmButton: false,
                        timer: 1500
                    });
                }
            })
            .catch(() => {
                btn.disabled = false;
            });
        });
    });

    function clearErrors() {
        document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        document.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
    }

    function showErrors(errors) {
        for (let field in errors) {
            let input = document.querySelector(`[name="${field}"]`);
            let errorEl = document.getElementById(`error-${field}`);
            if (input) input.classList.add('is-invalid');
            if (errorEl) errorEl.textContent = errors[field][0];
        }
    }
</script>
@endpush
