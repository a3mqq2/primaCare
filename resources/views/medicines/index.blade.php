@extends('layouts.app')

@section('title', __('medicines.title') . ' - PrimaCare')

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
            <h4 class="mb-0">{{ __('medicines.title') }}</h4>
            <div>
                <a href="{{ route('medicines.dispensings') }}" class="btn btn-info me-1">
                    <i class="ti ti-history me-1"></i>{{ __('medicines.dispensing_history') }}
                </a>
                <button type="button" class="btn btn-outline-success me-1" id="export-btn">
                    <i class="ti ti-file-spreadsheet me-1"></i>{{ __('common.export_excel') }}
                </button>
                <button type="button" class="btn btn-outline-secondary me-1" id="print-btn">
                    <i class="ti ti-printer me-1"></i>{{ __('common.print') }}
                </button>
                <a href="{{ route('medicines.create') }}" class="btn btn-primary">
                    <i class="ti ti-plus me-1"></i>{{ __('medicines.add_medicine') }}
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
                        <input type="text" id="search" class="form-control" placeholder="{{ __('medicines.search') }}" />
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>{{ __('medicines.name') }}</th>
                                <th>{{ __('medicines.description') }}</th>
                                <th>{{ __('medicines.dispensing_count') }}</th>
                                <th>{{ __('medicines.total_quantity') }}</th>
                                <th>{{ __('medicines.created_at') }}</th>
                            </tr>
                        </thead>
                        <tbody id="medicines-table">
                        </tbody>
                    </table>
                </div>

                <div id="no-results" class="text-center py-4 text-muted" style="display:none;">
                    {{ __('medicines.no_results') }}
                </div>

                <div id="pagination" class="d-flex justify-content-center mt-3"></div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-title">{{ __('medicines.edit_medicine') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body position-relative">
                <div class="loading-overlay" id="modal-loading" style="display:none;">
                    <div class="spinner-border text-primary" role="status"></div>
                </div>
                <form id="edit-form">
                    <div class="mb-3">
                        <label class="form-label">{{ __('medicines.name') }} <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="edit-name" class="form-control" />
                        <div class="invalid-feedback" id="edit-error-name"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('medicines.description') }}</label>
                        <textarea name="description" id="edit-description" class="form-control" rows="3"></textarea>
                        <div class="invalid-feedback" id="edit-error-description"></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger me-auto" id="delete-btn">
                    <i class="ti ti-trash me-1"></i>{{ __('medicines.delete') }}
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('medicines.cancel') }}</button>
                <button type="button" class="btn btn-primary" id="save-btn">
                    <i class="ti ti-check me-1"></i>{{ __('medicines.save') }}
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
    let editModal;
    let currentRecordId = null;

    document.addEventListener('DOMContentLoaded', function() {
        editModal = new bootstrap.Modal(document.getElementById('editModal'));
        loadMedicines();

        document.getElementById('search').addEventListener('input', function() {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(function() {
                currentPage = 1;
                loadMedicines();
            }, 300);
        });

        document.getElementById('save-btn').addEventListener('click', saveRecord);
        document.getElementById('delete-btn').addEventListener('click', deleteRecord);

        document.getElementById('print-btn').addEventListener('click', function() {
            let search = document.getElementById('search').value;
            let params = new URLSearchParams();
            if (search) params.set('search', search);
            window.open('/medicines/print?' + params.toString(), '_blank');
        });

        document.getElementById('export-btn').addEventListener('click', function() {
            let search = document.getElementById('search').value;
            let params = new URLSearchParams();
            if (search) params.set('search', search);
            window.location.href = '/medicines/export?' + params.toString();
        });
    });

    function openRecord(id) {
        currentRecordId = id;
        clearErrors();
        document.getElementById('modal-loading').style.display = 'flex';
        editModal.show();

        pcFetch(`/medicines/${id}`)
        .then(r => r.json())
        .then(result => {
            document.getElementById('modal-loading').style.display = 'none';
            let medicine = result.data;

            document.getElementById('edit-name').value = medicine.name || '';
            document.getElementById('edit-description').value = medicine.description || '';

            let formFields = document.querySelectorAll('#edit-form input, #edit-form textarea');
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

        let payload = {
            name: document.getElementById('edit-name').value,
            description: document.getElementById('edit-description').value
        };

        pcFetch(`/medicines/${currentRecordId}`, {
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
                showErrors(body.errors);
                return;
            }

            if (body.success) {
                editModal.hide();
                loadMedicines();
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
            title: '{{ __("medicines.confirm_delete") }}',
            text: '{{ __("medicines.confirm_delete_text") }}',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonText: '{{ __("medicines.cancel") }}',
            confirmButtonText: '{{ __("medicines.confirm_yes") }}'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('modal-loading').style.display = 'flex';

                pcFetch(`/medicines/${currentRecordId}`, {
                    method: 'DELETE'
                })
                .then(response => response.json().then(data => ({status: response.status, body: data})))
                .then(({status, body}) => {
                    document.getElementById('modal-loading').style.display = 'none';
                    if (body.success) {
                        editModal.hide();
                        loadMedicines();
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

    function loadMedicines(page) {
        if (page) currentPage = page;
        let search = document.getElementById('search').value;
        document.getElementById('loading').style.display = 'flex';

        pcFetch(`{{ route('medicines.data') }}?page=${currentPage}&search=${encodeURIComponent(search)}`)
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
        let tbody = document.getElementById('medicines-table');
        let noResults = document.getElementById('no-results');

        if (data.data.length === 0) {
            tbody.innerHTML = '';
            noResults.style.display = 'block';
            return;
        }

        noResults.style.display = 'none';
        let startIndex = (data.current_page - 1) * data.per_page;
        let html = '';

        data.data.forEach(function(medicine, index) {
            let createdAt = new Date(medicine.created_at).toLocaleDateString(locale === 'ar' ? 'ar-SA' : 'en-US');

            html += `<tr onclick="openRecord(${medicine.id})" style="cursor:pointer">
                <td>${startIndex + index + 1}</td>
                <td>${escapeHtml(medicine.name)}</td>
                <td>${medicine.description ? escapeHtml(medicine.description) : '-'}</td>
                <td><span class="badge bg-primary-subtle text-primary">${medicine.dispensings_count}</span></td>
                <td><span class="badge bg-success-subtle text-success">${medicine.dispensings_sum_quantity || 0}</span></td>
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
            html += `<li class="page-item"><a class="page-link" href="#" onclick="loadMedicines(${data.current_page - 1}); return false;">&laquo;</a></li>`;
        }

        for (let i = 1; i <= data.last_page; i++) {
            if (i === data.current_page) {
                html += `<li class="page-item active"><span class="page-link">${i}</span></li>`;
            } else if (Math.abs(i - data.current_page) <= 2 || i === 1 || i === data.last_page) {
                html += `<li class="page-item"><a class="page-link" href="#" onclick="loadMedicines(${i}); return false;">${i}</a></li>`;
            } else if (Math.abs(i - data.current_page) === 3) {
                html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            }
        }

        if (data.current_page < data.last_page) {
            html += `<li class="page-item"><a class="page-link" href="#" onclick="loadMedicines(${data.current_page + 1}); return false;">&raquo;</a></li>`;
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
