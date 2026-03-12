@extends('layouts.app')

@section('title', __('centers.title') . ' - PrimaCare')

@push('css')
<link href="{{ asset('assets/plugins/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/plugins/choices/choices.min.css') }}" rel="stylesheet" />
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
    .center-logo {
        width: 40px;
        height: 40px;
        border-radius: .375rem;
        object-fit: cover;
    }
    .center-logo-placeholder {
        width: 40px;
        height: 40px;
        border-radius: .375rem;
        background: #e5e7eb;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #9ca3af;
        font-size: 1.25rem;
    }
    [data-bs-theme="dark"] .center-logo-placeholder {
        background: #374151;
        color: #6b7280;
    }
    .modal-logo-preview {
        width: 80px;
        height: 120px;
        border-radius: .5rem;
        object-fit: cover;
        border: 1px solid #e5e7eb;
    }
    [data-bs-theme="dark"] .modal-logo-preview {
        border-color: #374151;
    }
    .choices { margin-bottom: 0; }
    .choices .choices__inner {
        min-height: 38px;
        padding: 4px 8px;
        border-color: var(--bs-border-color);
        background-color: var(--bs-body-bg);
        border-radius: var(--bs-border-radius);
    }
    .choices .choices__input { background-color: transparent; color: var(--bs-body-color); }
    .choices .choices__list--dropdown { border-color: var(--bs-border-color); background-color: var(--bs-body-bg); }
    .choices .choices__list--dropdown .choices__item { color: var(--bs-body-color); }
    .choices .choices__list--dropdown .choices__item--selectable.is-highlighted { background-color: var(--bs-primary); color: #fff; }
    .choices .choices__list--single .choices__item { color: var(--bs-body-color); }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">{{ __('centers.title') }}</h4>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-secondary" id="print-btn">
                    <i class="ti ti-printer me-1"></i>{{ __('common.print') }}
                </button>
                <button type="button" class="btn btn-outline-success" id="export-btn">
                    <i class="ti ti-file-spreadsheet me-1"></i>{{ __('common.export_excel') }}
                </button>
                <a href="{{ route('centers.create') }}" class="btn btn-primary">
                    <i class="ti ti-plus me-1"></i>{{ __('centers.add_center') }}
                </a>
            </div>
        </div>

        <div class="card position-relative">
            <div class="loading-overlay" id="loading" style="display:none;">
                <div class="spinner-border text-primary" role="status"></div>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <input type="text" id="search" class="form-control" placeholder="{{ __('centers.search') }}" />
                    </div>
                    <div class="col-md-3">
                        <select id="filter-city" class="form-select">
                            <option value="">{{ __('centers.all_cities') }}</option>
                        </select>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>{{ __('centers.logo') }}</th>
                                <th>{{ __('centers.center_name') }}</th>
                                <th>{{ __('centers.city') }}</th>
                                <th>{{ __('centers.phone') }}</th>
                                <th>{{ __('centers.notes') }}</th>
                            </tr>
                        </thead>
                        <tbody id="centers-table">
                        </tbody>
                    </table>
                </div>

                <div id="no-results" class="text-center py-4 text-muted" style="display:none;">
                    {{ __('centers.no_results') }}
                </div>

                <div id="pagination" class="d-flex justify-content-center mt-3"></div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-title">{{ __('centers.edit_center') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body position-relative">
                <div class="loading-overlay" id="modal-loading" style="display:none;">
                    <div class="spinner-border text-primary" role="status"></div>
                </div>
                <form id="edit-form">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('centers.name_ar') }} <span class="text-danger">*</span></label>
                            <input type="text" name="name_ar" id="edit-name_ar" class="form-control" dir="rtl" />
                            <div class="invalid-feedback" id="edit-error-name_ar"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('centers.name_en') }} <span class="text-danger">*</span></label>
                            <input type="text" name="name_en" id="edit-name_en" class="form-control" dir="ltr" />
                            <div class="invalid-feedback" id="edit-error-name_en"></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('centers.city') }} <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <select name="city_id" id="edit-city_id" class="form-select">
                                    <option value="">{{ __('centers.select_city') }}</option>
                                </select>
                                <button type="button" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#addCityModal">
                                    <i class="ti ti-plus"></i>
                                </button>
                            </div>
                            <div class="invalid-feedback" id="edit-error-city_id"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('centers.phone') }}</label>
                            <input type="text" name="phone" id="edit-phone" class="form-control" />
                            <div class="invalid-feedback" id="edit-error-phone"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('centers.notes') }}</label>
                        <textarea name="notes" id="edit-notes" class="form-control" rows="3"></textarea>
                        <div class="invalid-feedback" id="edit-error-notes"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('centers.logo') }}</label>
                        <div class="d-flex align-items-center gap-3">
                            <div id="current-logo-container">
                                <img id="current-logo" class="modal-logo-preview" style="display:none;" />
                                <div id="current-logo-placeholder" class="center-logo-placeholder" style="width:80px;height:80px;font-size:2rem;">
                                    <i class="ti ti-building-hospital"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <input type="file" id="edit-logo" class="form-control" accept="image/*" />
                                <div class="invalid-feedback" id="edit-error-logo"></div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger me-auto" id="delete-btn">
                    <i class="ti ti-trash me-1"></i>{{ __('centers.delete') }}
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('centers.cancel') }}</button>
                <button type="button" class="btn btn-primary" id="save-btn">
                    <i class="ti ti-check me-1"></i>{{ __('centers.save') }}
                </button>
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
<script src="{{ asset('assets/plugins/choices/choices.min.js') }}"></script>
<script>
    let currentPage = 1;
    let searchTimer;
    let locale = '{{ app()->getLocale() }}';
    let storageUrl = '{{ asset("storage") }}';
    let editModal;
    let currentRecordId = null;
    let selectedFile = null;
    let editCityChoices;
    let filterCityChoices;

    document.addEventListener('DOMContentLoaded', function() {
        editModal = new bootstrap.Modal(document.getElementById('editModal'));

        let filterCitySelect = document.getElementById('filter-city');
        filterCityChoices = new Choices(filterCitySelect, {
            searchEnabled: true,
            removeItemButton: true,
            shouldSort: false,
            placeholderValue: '',
            searchPlaceholderValue: '{{ __("centers.filter_by_city") }}',
            itemSelectText: '',
            noResultsText: '',
            noChoicesText: ''
        });

        function loadFilterCities(search) {
            pcFetch('/cities/search?search=' + encodeURIComponent(search || ''))
                .then(r => r.json())
                .then(cities => {
                    filterCityChoices.clearChoices();
                    filterCityChoices.setChoices(
                        cities.map(c => ({ value: String(c.id), label: c.name_ar + ' - ' + c.name_en })),
                        'value', 'label', true
                    );
                });
        }
        loadFilterCities('');

        filterCitySelect.addEventListener('search', function(e) {
            loadFilterCities(e.detail.value);
        });

        filterCitySelect.addEventListener('change', function() {
            currentPage = 1;
            loadCenters();
        });

        document.getElementById('print-btn').addEventListener('click', function() {
            let search = document.getElementById('search').value;
            let cityId = document.getElementById('filter-city').value;
            let url = '{{ route("centers.print") }}?search=' + encodeURIComponent(search) + '&city_id=' + encodeURIComponent(cityId);
            window.open(url, '_blank');
        });

        document.getElementById('export-btn').addEventListener('click', function() {
            let search = document.getElementById('search').value;
            let cityId = document.getElementById('filter-city').value;
            window.location.href = '{{ route("centers.export") }}?search=' + encodeURIComponent(search) + '&city_id=' + encodeURIComponent(cityId);
        });

        let editCitySelect = document.getElementById('edit-city_id');
        editCityChoices = new Choices(editCitySelect, {
            searchEnabled: true,
            removeItemButton: true,
            shouldSort: false,
            placeholderValue: '',
            searchPlaceholderValue: '{{ __("centers.select_city") }}',
            itemSelectText: '',
            noResultsText: '',
            noChoicesText: ''
        });

        function loadEditCities(search) {
            pcFetch('/cities/search?search=' + encodeURIComponent(search || ''))
                .then(r => r.json())
                .then(cities => {
                    editCityChoices.clearChoices();
                    editCityChoices.setChoices(
                        cities.map(c => ({ value: String(c.id), label: c.name_ar + ' - ' + c.name_en })),
                        'value', 'label', true
                    );
                });
        }
        loadEditCities('');

        editCitySelect.addEventListener('search', function(e) {
            loadEditCities(e.detail.value);
        });

        loadCenters();

        document.getElementById('search').addEventListener('input', function() {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(function() {
                currentPage = 1;
                loadCenters();
            }, 300);
        });

        document.getElementById('save-btn').addEventListener('click', saveRecord);
        document.getElementById('delete-btn').addEventListener('click', deleteRecord);

        document.getElementById('edit-logo').addEventListener('change', function() {
            if (this.files.length) {
                selectedFile = this.files[0];
                let reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('current-logo').src = e.target.result;
                    document.getElementById('current-logo').style.display = '';
                    document.getElementById('current-logo-placeholder').style.display = 'none';
                };
                reader.readAsDataURL(selectedFile);
            }
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

            pcFetch('{{ route("cities.store") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ name_ar: nameAr.value, name_en: nameEn.value })
            })
            .then(response => response.json().then(data => ({status: response.status, body: data})))
            .then(({status, body}) => {
                btn.disabled = false;
                if (status === 422) {
                    if (body.errors) {
                        if (body.errors.name_ar) { nameAr.classList.add('is-invalid'); document.getElementById('error-city_name_ar').textContent = body.errors.name_ar[0]; }
                        if (body.errors.name_en) { nameEn.classList.add('is-invalid'); document.getElementById('error-city_name_en').textContent = body.errors.name_en[0]; }
                    }
                    return;
                }
                if (body.success) {
                    editCityChoices.setChoices([{ value: String(body.city.id), label: body.city.name_ar + ' - ' + body.city.name_en }], 'value', 'label', false);
                    editCityChoices.setChoiceByValue(String(body.city.id));
                    nameAr.value = '';
                    nameEn.value = '';
                    bootstrap.Modal.getInstance(document.getElementById('addCityModal')).hide();
                    Swal.fire({ icon: 'success', title: body.message, showConfirmButton: false, timer: 1500 });
                }
            })
            .catch(() => { btn.disabled = false; Swal.fire({ icon: 'error', title: window.PrimaCare.messages.error }); });
        });
    });

    function openRecord(id) {
        currentRecordId = id;
        selectedFile = null;
        document.getElementById('edit-logo').value = '';
        clearErrors();
        document.getElementById('modal-loading').style.display = 'flex';
        editModal.show();

        pcFetch(`/centers/${id}`)
        .then(r => r.json())
        .then(result => {
            document.getElementById('modal-loading').style.display = 'none';
            let center = result.data;

            document.getElementById('edit-name_ar').value = center.name_ar || '';
            document.getElementById('edit-name_en').value = center.name_en || '';
            document.getElementById('edit-phone').value = center.phone || '';
            document.getElementById('edit-notes').value = center.notes || '';

            if (center.city_id && center.city) {
                editCityChoices.setChoices([{
                    value: String(center.city.id),
                    label: center.city.name_ar + ' - ' + center.city.name_en
                }], 'value', 'label', false);
                editCityChoices.setChoiceByValue(String(center.city.id));
            } else {
                editCityChoices.removeActiveItems();
            }

            if (center.logo) {
                document.getElementById('current-logo').src = storageUrl + '/' + center.logo;
                document.getElementById('current-logo').style.display = '';
                document.getElementById('current-logo-placeholder').style.display = 'none';
            } else {
                document.getElementById('current-logo').style.display = 'none';
                document.getElementById('current-logo-placeholder').style.display = '';
            }

            let formFields = document.querySelectorAll('#edit-form input, #edit-form select, #edit-form textarea');
            formFields.forEach(f => f.disabled = !result.can_edit);

            document.getElementById('save-btn').style.display = result.can_edit ? '' : 'none';
            document.getElementById('delete-btn').style.display = result.can_delete ? '' : 'none';
        })
        .catch(() => {
            document.getElementById('modal-loading').style.display = 'none';
            editModal.hide();
            Swal.fire({ icon: 'error', title: window.PrimaCare.messages.error });
        });
    }

    function saveRecord() {
        clearErrors();
        let btn = document.getElementById('save-btn');
        btn.disabled = true;
        document.getElementById('modal-loading').style.display = 'flex';

        let formData = new FormData();
        formData.append('_method', 'PUT');
        formData.append('name_ar', document.getElementById('edit-name_ar').value);
        formData.append('name_en', document.getElementById('edit-name_en').value);
        formData.append('city_id', document.getElementById('edit-city_id').value);
        formData.append('phone', document.getElementById('edit-phone').value);
        formData.append('notes', document.getElementById('edit-notes').value);
        if (selectedFile) formData.append('logo', selectedFile);

        pcFetch(`/centers/${currentRecordId}`, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json().then(data => ({status: response.status, body: data})))
        .then(({status, body}) => {
            btn.disabled = false;
            document.getElementById('modal-loading').style.display = 'none';

            if (status === 422) {
                showErrors(body.errors);
                return;
            }

            if (body.success) {
                editModal.hide();
                loadCenters();
                Swal.fire({ icon: 'success', title: body.message, showConfirmButton: false, timer: 1500 });
            }
        })
        .catch(() => {
            btn.disabled = false;
            document.getElementById('modal-loading').style.display = 'none';
            Swal.fire({ icon: 'error', title: window.PrimaCare.messages.error });
        });
    }

    function deleteRecord() {
        Swal.fire({
            title: '{{ __("centers.confirm_delete") }}',
            text: '{{ __("centers.confirm_delete_text") }}',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonText: '{{ __("centers.cancel") }}',
            confirmButtonText: '{{ __("centers.confirm_yes") }}'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('modal-loading').style.display = 'flex';

                pcFetch(`/centers/${currentRecordId}`, {
                    method: 'DELETE'
                })
                .then(response => response.json().then(data => ({status: response.status, body: data})))
                .then(({status, body}) => {
                    document.getElementById('modal-loading').style.display = 'none';
                    if (body.success) {
                        editModal.hide();
                        loadCenters();
                        Swal.fire({ icon: 'success', title: body.message, showConfirmButton: false, timer: 1500 });
                    } else {
                        Swal.fire({ icon: 'error', title: body.message });
                    }
                })
                .catch(() => {
                    document.getElementById('modal-loading').style.display = 'none';
                    Swal.fire({ icon: 'error', title: window.PrimaCare.messages.error });
                });
            }
        });
    }

    function loadCenters(page) {
        if (page) currentPage = page;
        let search = document.getElementById('search').value;
        document.getElementById('loading').style.display = 'flex';

        let cityId = document.getElementById('filter-city').value;

        pcFetch(`{{ route('centers.data') }}?page=${currentPage}&search=${encodeURIComponent(search)}&city_id=${encodeURIComponent(cityId)}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('loading').style.display = 'none';
            renderTable(data);
            renderPagination(data);
        })
        .catch(() => {
            document.getElementById('loading').style.display = 'none';
            Swal.fire({ icon: 'error', title: window.PrimaCare.messages.error });
        });
    }

    function renderTable(data) {
        let tbody = document.getElementById('centers-table');
        let noResults = document.getElementById('no-results');

        if (data.data.length === 0) {
            tbody.innerHTML = '';
            noResults.style.display = 'block';
            return;
        }

        noResults.style.display = 'none';
        let startIndex = (data.current_page - 1) * data.per_page;
        let html = '';

        data.data.forEach(function(center, index) {
            let centerName = locale === 'ar' ? center.name_ar : center.name_en;
            let cityName = '';
            if (center.city) {
                cityName = locale === 'ar' ? center.city.name_ar : center.city.name_en;
            }

            let logoHtml = center.logo
                ? `<img src="${storageUrl}/${center.logo}" class="center-logo" />`
                : `<div class="center-logo-placeholder"><i class="ti ti-building-hospital"></i></div>`;

            html += `<tr onclick="openRecord(${center.id})" style="cursor:pointer">
                <td>${startIndex + index + 1}</td>
                <td>${logoHtml}</td>
                <td>${escapeHtml(centerName)}</td>
                <td>${cityName ? escapeHtml(cityName) : '-'}</td>
                <td>${center.phone ? escapeHtml(center.phone) : '-'}</td>
                <td>${center.notes ? escapeHtml(center.notes) : '-'}</td>
            </tr>`;
        });

        tbody.innerHTML = html;
    }

    function renderPagination(data) {
        let container = document.getElementById('pagination');
        if (data.last_page <= 1) {
            container.innerHTML = '';
            return;
        }

        let html = '<nav><ul class="pagination mb-0">';

        if (data.current_page > 1) {
            html += `<li class="page-item"><a class="page-link" href="#" onclick="loadCenters(${data.current_page - 1}); return false;">&laquo;</a></li>`;
        }

        for (let i = 1; i <= data.last_page; i++) {
            if (i === data.current_page) {
                html += `<li class="page-item active"><span class="page-link">${i}</span></li>`;
            } else if (Math.abs(i - data.current_page) <= 2 || i === 1 || i === data.last_page) {
                html += `<li class="page-item"><a class="page-link" href="#" onclick="loadCenters(${i}); return false;">${i}</a></li>`;
            } else if (Math.abs(i - data.current_page) === 3) {
                html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            }
        }

        if (data.current_page < data.last_page) {
            html += `<li class="page-item"><a class="page-link" href="#" onclick="loadCenters(${data.current_page + 1}); return false;">&raquo;</a></li>`;
        }

        html += '</ul></nav>';
        container.innerHTML = html;
    }

    function clearErrors() {
        document.querySelectorAll('#edit-form .is-invalid').forEach(el => el.classList.remove('is-invalid'));
        document.querySelectorAll('#edit-form .invalid-feedback').forEach(el => el.textContent = '');
    }

    function showErrors(errors) {
        for (let field in errors) {
            let input = document.getElementById('edit-' + field);
            let errorEl = document.getElementById('edit-error-' + field);
            if (input) input.classList.add('is-invalid');
            if (errorEl) errorEl.textContent = errors[field][0];
        }
    }

    function escapeHtml(text) {
        let div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
</script>
@endpush
