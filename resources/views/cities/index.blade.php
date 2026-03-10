@extends('layouts.app')

@section('title', __('cities.title') . ' - PrimaCare')

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
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">{{ __('cities.title') }}</h4>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-secondary" id="print-btn">
                    <i class="ti ti-printer me-1"></i>{{ __('common.print') }}
                </button>
                <button type="button" class="btn btn-outline-success" id="export-btn">
                    <i class="ti ti-file-spreadsheet me-1"></i>{{ __('common.export_excel') }}
                </button>
                <button type="button" class="btn btn-primary" id="btn-add" data-bs-toggle="modal" data-bs-target="#addModal">
                    <i class="ti ti-plus me-1"></i>{{ __('cities.add_city') }}
                </button>
            </div>
        </div>

        <div class="card position-relative">
            <div class="loading-overlay" id="loading" style="display:none;">
                <div class="spinner-border text-primary" role="status"></div>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <input type="text" id="search" class="form-control" placeholder="{{ __('cities.search') }}" />
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>{{ __('cities.name_ar') }}</th>
                                <th>{{ __('cities.name_en') }}</th>
                                <th>{{ __('cities.centers_count') }}</th>
                                <th>{{ __('cities.created_at') }}</th>
                            </tr>
                        </thead>
                        <tbody id="cities-table">
                        </tbody>
                    </table>
                </div>

                <div id="no-results" class="text-center py-4 text-muted" style="display:none;">
                    {{ __('cities.no_results') }}
                </div>

                <div id="pagination" class="d-flex justify-content-center mt-3"></div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('cities.add_city') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body position-relative">
                <div class="loading-overlay" id="add-modal-loading" style="display:none;">
                    <div class="spinner-border text-primary" role="status"></div>
                </div>
                <form id="add-form">
                    <div class="mb-3">
                        <label class="form-label">{{ __('cities.name_ar') }} <span class="text-danger">*</span></label>
                        <input type="text" name="name_ar" id="add-name_ar" class="form-control" dir="rtl" />
                        <div class="invalid-feedback" id="add-error-name_ar"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('cities.name_en') }} <span class="text-danger">*</span></label>
                        <input type="text" name="name_en" id="add-name_en" class="form-control" dir="ltr" />
                        <div class="invalid-feedback" id="add-error-name_en"></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('cities.cancel') }}</button>
                <button type="button" class="btn btn-primary" id="add-btn">
                    <i class="ti ti-check me-1"></i>{{ __('cities.save') }}
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('cities.edit_city') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body position-relative">
                <div class="loading-overlay" id="modal-loading" style="display:none;">
                    <div class="spinner-border text-primary" role="status"></div>
                </div>
                <form id="edit-form">
                    <div class="mb-3">
                        <label class="form-label">{{ __('cities.name_ar') }} <span class="text-danger">*</span></label>
                        <input type="text" name="name_ar" id="edit-name_ar" class="form-control" dir="rtl" />
                        <div class="invalid-feedback" id="edit-error-name_ar"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('cities.name_en') }} <span class="text-danger">*</span></label>
                        <input type="text" name="name_en" id="edit-name_en" class="form-control" dir="ltr" />
                        <div class="invalid-feedback" id="edit-error-name_en"></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger me-auto" id="delete-btn">
                    <i class="ti ti-trash me-1"></i>{{ __('cities.delete') }}
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('cities.cancel') }}</button>
                <button type="button" class="btn btn-primary" id="save-btn">
                    <i class="ti ti-check me-1"></i>{{ __('cities.save') }}
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script src="{{ asset('assets/plugins/sweetalert2/sweetalert2.min.js') }}"></script>
<script>
    let currentPage = 1;
    let searchTimer;
    let locale = '{{ app()->getLocale() }}';
    let editModal, addModal;
    let currentRecordId = null;

    document.addEventListener('DOMContentLoaded', function() {
        editModal = new bootstrap.Modal(document.getElementById('editModal'));
        addModal = new bootstrap.Modal(document.getElementById('addModal'));
        loadCities();

        document.getElementById('search').addEventListener('input', function() {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(function() {
                currentPage = 1;
                loadCities();
            }, 300);
        });

        document.getElementById('add-btn').addEventListener('click', addRecord);
        document.getElementById('save-btn').addEventListener('click', saveRecord);
        document.getElementById('delete-btn').addEventListener('click', deleteRecord);

        document.getElementById('print-btn').addEventListener('click', function() {
            let search = document.getElementById('search').value;
            window.open('{{ route("cities.print") }}?search=' + encodeURIComponent(search), '_blank');
        });

        document.getElementById('export-btn').addEventListener('click', function() {
            let search = document.getElementById('search').value;
            window.location.href = '{{ route("cities.export") }}?search=' + encodeURIComponent(search);
        });
    });

    function addRecord() {
        clearErrors('add-form');
        let btn = document.getElementById('add-btn');
        btn.disabled = true;
        document.getElementById('add-modal-loading').style.display = 'flex';

        let payload = {
            name_ar: document.getElementById('add-name_ar').value,
            name_en: document.getElementById('add-name_en').value
        };

        pcFetch('{{ route("cities.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(payload)
        })
        .then(response => response.json().then(data => ({status: response.status, body: data})))
        .then(({status, body}) => {
            btn.disabled = false;
            document.getElementById('add-modal-loading').style.display = 'none';

            if (status === 422) {
                showErrors(body.errors, 'add');
                return;
            }

            if (body.success) {
                addModal.hide();
                document.getElementById('add-form').reset();
                loadCities();
                Swal.fire({ icon: 'success', title: body.message, showConfirmButton: false, timer: 1500 });
            }
        })
        .catch(() => {
            btn.disabled = false;
            document.getElementById('add-modal-loading').style.display = 'none';
            Swal.fire({ icon: 'error', title: window.PrimaCare.messages.error });
        });
    }

    function openRecord(id) {
        currentRecordId = id;
        clearErrors('edit-form');
        document.getElementById('modal-loading').style.display = 'flex';
        editModal.show();

        pcFetch(`/cities/${id}`)
        .then(r => r.json())
        .then(result => {
            document.getElementById('modal-loading').style.display = 'none';
            let city = result.data;

            document.getElementById('edit-name_ar').value = city.name_ar || '';
            document.getElementById('edit-name_en').value = city.name_en || '';

            let formFields = document.querySelectorAll('#edit-form input');
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
        clearErrors('edit-form');
        let btn = document.getElementById('save-btn');
        btn.disabled = true;
        document.getElementById('modal-loading').style.display = 'flex';

        let payload = {
            name_ar: document.getElementById('edit-name_ar').value,
            name_en: document.getElementById('edit-name_en').value
        };

        pcFetch(`/cities/${currentRecordId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(payload)
        })
        .then(response => response.json().then(data => ({status: response.status, body: data})))
        .then(({status, body}) => {
            btn.disabled = false;
            document.getElementById('modal-loading').style.display = 'none';

            if (status === 422) {
                showErrors(body.errors, 'edit');
                return;
            }

            if (body.success) {
                editModal.hide();
                loadCities();
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
            title: '{{ __("cities.confirm_delete") }}',
            text: '{{ __("cities.confirm_delete_text") }}',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonText: '{{ __("cities.cancel") }}',
            confirmButtonText: '{{ __("cities.confirm_yes") }}'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('modal-loading').style.display = 'flex';

                pcFetch(`/cities/${currentRecordId}`, {
                    method: 'DELETE'
                })
                .then(response => response.json().then(data => ({status: response.status, body: data})))
                .then(({status, body}) => {
                    document.getElementById('modal-loading').style.display = 'none';
                    if (body.success) {
                        editModal.hide();
                        loadCities();
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

    function loadCities(page) {
        if (page) currentPage = page;
        let search = document.getElementById('search').value;
        document.getElementById('loading').style.display = 'flex';

        pcFetch(`{{ route('cities.data') }}?page=${currentPage}&search=${encodeURIComponent(search)}`)
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
        let tbody = document.getElementById('cities-table');
        let noResults = document.getElementById('no-results');

        if (data.data.length === 0) {
            tbody.innerHTML = '';
            noResults.style.display = 'block';
            return;
        }

        noResults.style.display = 'none';
        let startIndex = (data.current_page - 1) * data.per_page;
        let html = '';

        data.data.forEach(function(city, index) {
            let createdAt = new Date(city.created_at).toLocaleDateString(locale === 'ar' ? 'ar-SA' : 'en-US');

            html += `<tr onclick="openRecord(${city.id})" style="cursor:pointer">
                <td>${startIndex + index + 1}</td>
                <td>${escapeHtml(city.name_ar)}</td>
                <td>${escapeHtml(city.name_en)}</td>
                <td><span class="badge bg-primary-subtle text-primary">${city.centers_count}</span></td>
                <td>${createdAt}</td>
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
            html += `<li class="page-item"><a class="page-link" href="#" onclick="loadCities(${data.current_page - 1}); return false;">&laquo;</a></li>`;
        }

        for (let i = 1; i <= data.last_page; i++) {
            if (i === data.current_page) {
                html += `<li class="page-item active"><span class="page-link">${i}</span></li>`;
            } else if (Math.abs(i - data.current_page) <= 2 || i === 1 || i === data.last_page) {
                html += `<li class="page-item"><a class="page-link" href="#" onclick="loadCities(${i}); return false;">${i}</a></li>`;
            } else if (Math.abs(i - data.current_page) === 3) {
                html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            }
        }

        if (data.current_page < data.last_page) {
            html += `<li class="page-item"><a class="page-link" href="#" onclick="loadCities(${data.current_page + 1}); return false;">&raquo;</a></li>`;
        }

        html += '</ul></nav>';
        container.innerHTML = html;
    }

    function clearErrors(formId) {
        document.querySelectorAll(`#${formId} .is-invalid`).forEach(el => el.classList.remove('is-invalid'));
        document.querySelectorAll(`#${formId} .invalid-feedback`).forEach(el => el.textContent = '');
    }

    function showErrors(errors, prefix) {
        for (let field in errors) {
            let input = document.getElementById(prefix + '-' + field);
            let errorEl = document.getElementById(prefix + '-error-' + field);
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
