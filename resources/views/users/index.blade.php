@extends('layouts.app')

@section('title', __('users.title') . ' - PrimaCare')

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
            <h4 class="mb-0">{{ __('users.title') }}</h4>
            <div>
                <button type="button" class="btn btn-outline-success me-1" id="export-btn">
                    <i class="ti ti-file-spreadsheet me-1"></i>{{ __('common.export_excel') }}
                </button>
                <button type="button" class="btn btn-secondary me-1" id="print-btn">
                    <i class="ti ti-printer me-1"></i>{{ __('common.print') }}
                </button>
                <a href="{{ route('users.create') }}" class="btn btn-primary">
                    <i class="ti ti-plus me-1"></i>{{ __('users.add_user') }}
                </a>
            </div>
        </div>

        <div class="card position-relative">
            <div class="loading-overlay" id="loading" style="display:none;">
                <div class="spinner-border text-primary" role="status"></div>
            </div>
            <div class="card-body">
                <div class="row mb-3 g-2">
                    <div class="col-md-4">
                        <input type="text" id="search" class="form-control" placeholder="{{ __('users.search') }}" />
                    </div>
                    <div class="col-md-3">
                        <select id="filter-role" class="form-select">
                            <option value="">{{ __('users.all_roles') }}</option>
                            <option value="system_admin">{{ __('users.roles.system_admin') }}</option>
                            <option value="center_employee">{{ __('users.roles.center_employee') }}</option>
                        </select>
                    </div>
                    @if(auth()->user()->isSystemAdmin())
                    <div class="col-md-3">
                        <select id="filter-center" class="form-select">
                            <option value="">{{ __('users.all_centers') }}</option>
                        </select>
                    </div>
                    @endif
                </div>

                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>{{ __('users.name') }}</th>
                                <th>{{ __('users.email') }}</th>
                                <th>{{ __('users.role') }}</th>
                                <th>{{ __('users.center') }}</th>
                                <th>{{ __('users.employee_type') }}</th>
                                <th>{{ __('users.created_at') }}</th>
                                @if(auth()->user()->isSystemAdmin())
                                <th style="width:50px;"></th>
                                @endif
                            </tr>
                        </thead>
                        <tbody id="users-table">
                        </tbody>
                    </table>
                </div>

                <div id="no-results" class="text-center py-4 text-muted" style="display:none;">
                    {{ __('users.no_results') }}
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
                <h5 class="modal-title" id="modal-title">{{ __('users.edit_user') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body position-relative">
                <div class="loading-overlay" id="modal-loading" style="display:none;">
                    <div class="spinner-border text-primary" role="status"></div>
                </div>
                <form id="edit-form">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('users.name') }} <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="edit-name" class="form-control" />
                            <div class="invalid-feedback" id="edit-error-name"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('users.email') }} <span class="text-danger">*</span></label>
                            <input type="email" name="email" id="edit-email" class="form-control" dir="ltr" />
                            <div class="invalid-feedback" id="edit-error-email"></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('users.password') }}</label>
                            <input type="password" name="password" id="edit-password" class="form-control" />
                            <div class="form-text">{{ __('users.password_hint') }}</div>
                            <div class="invalid-feedback" id="edit-error-password"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('users.password_confirmation') }}</label>
                            <input type="password" name="password_confirmation" id="edit-password_confirmation" class="form-control" />
                        </div>
                    </div>
                    @if(auth()->user()->isSystemAdmin())
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('users.role') }} <span class="text-danger">*</span></label>
                            <select name="role" id="edit-role" class="form-select">
                                <option value="">{{ __('users.select_role') }}</option>
                                <option value="system_admin">{{ __('users.roles.system_admin') }}</option>
                                <option value="center_employee">{{ __('users.roles.center_employee') }}</option>
                            </select>
                            <div class="invalid-feedback" id="edit-error-role"></div>
                        </div>
                        <div class="col-md-6 mb-3" id="edit-center-field" style="display:none;">
                            <label class="form-label">{{ __('users.center') }} <span class="text-danger">*</span></label>
                            <select name="center_id" id="edit-center_id" class="form-select">
                                <option value="">{{ __('users.select_center') }}</option>
                            </select>
                            <div class="invalid-feedback" id="edit-error-center_id"></div>
                        </div>
                    </div>
                    <div class="mb-3" id="edit-manager-field" style="display:none;">
                        <div class="form-check">
                            <input type="checkbox" name="is_center_manager" id="edit-is_center_manager" class="form-check-input" value="1" />
                            <label class="form-check-label" for="edit-is_center_manager">{{ __('users.is_center_manager') }}</label>
                        </div>
                    </div>
                    @else
                    <input type="hidden" name="role" id="edit-role" value="center_employee" />
                    <input type="hidden" name="center_id" id="edit-center_id" value="{{ auth()->user()->center_id }}" />
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" name="is_center_manager" id="edit-is_center_manager" class="form-check-input" value="1" />
                            <label class="form-check-label" for="edit-is_center_manager">{{ __('users.is_center_manager') }}</label>
                        </div>
                    </div>
                    @endif
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger me-auto" id="delete-btn">
                    <i class="ti ti-trash me-1"></i>{{ __('users.confirm_yes') }}
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('users.cancel') }}</button>
                <button type="button" class="btn btn-primary" id="save-btn">
                    <i class="ti ti-check me-1"></i>{{ __('users.save') }}
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
    let locale = window.PrimaCare.locale;
    let isSystemAdmin = {{ auth()->user()->isSystemAdmin() ? 'true' : 'false' }};
    let roleLabels = {
        system_admin: '{{ __("users.roles.system_admin") }}',
        center_employee: '{{ __("users.roles.center_employee") }}'
    };
    let centerManagerLabel = '{{ __("users.is_center_manager") }}';
    let regularEmployeeLabel = '{{ __("users.regular_employee") }}';
    let impersonateConfirmTitle = '{{ __("users.impersonate_confirm_title") }}';
    let impersonateConfirmText = '{{ __("users.impersonate_confirm_text") }}';
    let impersonateConfirmYes = '{{ __("users.impersonate_confirm_yes") }}';
    let impersonateUrl = '{{ url("/impersonate") }}';
    let editModal;
    let currentRecordId = null;
    let filterCenterChoices = null;
    let editCenterChoices = null;

    function initCenterChoices(selectEl) {
        let choices = new Choices(selectEl, {
            searchEnabled: true,
            removeItemButton: true,
            shouldSort: false,
            placeholderValue: '',
            itemSelectText: '',
            noResultsText: '',
            noChoicesText: ''
        });

        function loadCenters(search) {
            pcFetch('/centers/search?search=' + encodeURIComponent(search || ''))
                .then(r => r.json())
                .then(centers => {
                    let locale = window.PrimaCare.locale;
                    choices.clearChoices();
                    choices.setChoices(
                        centers.map(c => ({ value: String(c.id), label: locale === 'ar' ? c.name_ar : c.name_en })),
                        'value', 'label', true
                    );
                });
        }
        loadCenters('');

        selectEl.addEventListener('search', function(e) {
            loadCenters(e.detail.value);
        });

        return choices;
    }

    document.addEventListener('DOMContentLoaded', function() {
        editModal = new bootstrap.Modal(document.getElementById('editModal'));

        let filterCenterEl = document.getElementById('filter-center');
        if (filterCenterEl) {
            filterCenterChoices = initCenterChoices(filterCenterEl);
            filterCenterEl.addEventListener('change', function() {
                currentPage = 1;
                loadUsers();
            });
        }

        let editCenterEl = document.getElementById('edit-center_id');
        if (editCenterEl && editCenterEl.type !== 'hidden') {
            editCenterChoices = initCenterChoices(editCenterEl);
        }

        loadUsers();

        document.getElementById('search').addEventListener('input', function() {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(function() {
                currentPage = 1;
                loadUsers();
            }, 300);
        });

        document.getElementById('filter-role').addEventListener('change', function() {
            currentPage = 1;
            loadUsers();
        });

        document.getElementById('save-btn').addEventListener('click', saveRecord);
        document.getElementById('delete-btn').addEventListener('click', deleteRecord);

        document.getElementById('print-btn').addEventListener('click', function() {
            window.open(`{{ route('users.print') }}?${buildUserParams()}`, '_blank');
        });

        document.getElementById('export-btn').addEventListener('click', function() {
            window.location.href = `{{ route('users.export') }}?${buildUserParams()}`;
        });

        if (isSystemAdmin) {
            document.getElementById('edit-role').addEventListener('change', function() {
                toggleCenterFields(this.value);
            });
        }
    });

    function toggleCenterFields(role) {
        let centerField = document.getElementById('edit-center-field');
        let managerField = document.getElementById('edit-manager-field');
        if (!centerField || !managerField) return;

        if (role === 'center_employee') {
            centerField.style.display = '';
            managerField.style.display = '';
        } else {
            centerField.style.display = 'none';
            managerField.style.display = 'none';
            if (editCenterChoices) {
                editCenterChoices.removeActiveItems();
                editCenterChoices.setChoiceByValue('');
            }
            document.getElementById('edit-is_center_manager').checked = false;
        }
    }

    function openRecord(id) {
        currentRecordId = id;
        clearErrors();
        document.getElementById('edit-password').value = '';
        document.getElementById('edit-password_confirmation').value = '';
        document.getElementById('modal-loading').style.display = 'flex';
        editModal.show();

        pcFetch(`/users/${id}`)
        .then(r => r.json())
        .then(result => {
            document.getElementById('modal-loading').style.display = 'none';
            let user = result.data;

            document.getElementById('edit-name').value = user.name || '';
            document.getElementById('edit-email').value = user.email || '';
            document.getElementById('edit-role').value = user.role || '';
            document.getElementById('edit-is_center_manager').checked = user.is_center_manager;

            if (isSystemAdmin) {
                toggleCenterFields(user.role);
                if (user.role === 'center_employee' && user.center_id && editCenterChoices) {
                    let centerLabel = user.center
                        ? (locale === 'ar' ? user.center.name_ar : user.center.name_en)
                        : String(user.center_id);
                    editCenterChoices.setChoices(
                        [{ value: String(user.center_id), label: centerLabel, selected: true }],
                        'value', 'label', false
                    );
                }
            }

            let formFields = document.querySelectorAll('#edit-form input:not([type="hidden"]), #edit-form select, #edit-form textarea');
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
            email: document.getElementById('edit-email').value,
            password: document.getElementById('edit-password').value,
            password_confirmation: document.getElementById('edit-password_confirmation').value,
            role: document.getElementById('edit-role').value,
            center_id: document.getElementById('edit-center_id').value || null,
            is_center_manager: document.getElementById('edit-is_center_manager').checked ? 1 : 0
        };

        pcFetch(`/users/${currentRecordId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
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
                loadUsers();
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
            title: '{{ __("users.confirm_delete") }}',
            text: '{{ __("users.confirm_delete_text") }}',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonText: '{{ __("users.cancel") }}',
            confirmButtonText: '{{ __("users.confirm_yes") }}'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('modal-loading').style.display = 'flex';

                pcFetch(`/users/${currentRecordId}`, {
                    method: 'DELETE'
                })
                .then(response => response.json().then(data => ({status: response.status, body: data})))
                .then(({status, body}) => {
                    document.getElementById('modal-loading').style.display = 'none';
                    if (body.success) {
                        editModal.hide();
                        loadUsers();
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

    function buildUserParams() {
        let params = new URLSearchParams();
        let search = document.getElementById('search').value;
        if (search) params.set('search', search);
        let role = document.getElementById('filter-role').value;
        if (role) params.set('role', role);
        let filterCenter = document.getElementById('filter-center');
        let centerId = filterCenter ? filterCenter.value : '';
        if (centerId) params.set('center_id', centerId);
        return params.toString();
    }

    function loadUsers(page) {
        if (page) currentPage = page;
        let search = document.getElementById('search').value;
        let role = document.getElementById('filter-role').value;
        let filterCenter = document.getElementById('filter-center');
        let centerId = filterCenter ? filterCenter.value : '';

        document.getElementById('loading').style.display = 'flex';

        let params = `page=${currentPage}&search=${encodeURIComponent(search)}`;
        if (role) params += `&role=${role}`;
        if (centerId) params += `&center_id=${centerId}`;

        pcFetch(`{{ route('users.data') }}?${params}`)
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
        let tbody = document.getElementById('users-table');
        let noResults = document.getElementById('no-results');

        if (data.data.length === 0) {
            tbody.innerHTML = '';
            noResults.style.display = 'block';
            return;
        }

        noResults.style.display = 'none';
        let startIndex = (data.current_page - 1) * data.per_page;
        let html = '';

        data.data.forEach(function(user, index) {
            let roleName = roleLabels[user.role] || user.role;
            let centerName = '-';
            if (user.center) {
                centerName = locale === 'ar' ? user.center.name_ar : user.center.name_en;
            }
            let employeeType = '-';
            if (user.role === 'center_employee') {
                employeeType = user.is_center_manager ? centerManagerLabel : regularEmployeeLabel;
            }

            let createdAt = new Date(user.created_at).toLocaleDateString(locale === 'ar' ? 'ar-SA' : 'en-US');

            let impersonateCell = '';
            if (isSystemAdmin) {
                if (user.role !== 'system_admin') {
                    impersonateCell = `<td class="text-center"><button class="btn btn-sm btn-outline-warning border-0 p-1 btn-impersonate" data-user-id="${user.id}" data-user-name="${escapeHtml(user.name)}" title="${impersonateConfirmTitle}"><i class="ti ti-user-share"></i></button></td>`;
                } else {
                    impersonateCell = '<td></td>';
                }
            }

            html += `<tr onclick="openRecord(${user.id})" style="cursor:pointer">
                <td>${startIndex + index + 1}</td>
                <td>${escapeHtml(user.name)}</td>
                <td>${escapeHtml(user.email)}</td>
                <td><span class="badge bg-${user.role === 'system_admin' ? 'primary' : 'info'}-subtle text-${user.role === 'system_admin' ? 'primary' : 'info'}">${roleName}</span></td>
                <td>${escapeHtml(centerName)}</td>
                <td>${employeeType}</td>
                <td>${createdAt}</td>
                ${impersonateCell}
            </tr>`;
        });

        tbody.innerHTML = html;

        tbody.querySelectorAll('.btn-impersonate').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                impersonateUser(this.dataset.userId, this.dataset.userName);
            });
        });
    }

    function renderPagination(data) {
        let container = document.getElementById('pagination');
        if (data.last_page <= 1) {
            container.innerHTML = '';
            return;
        }

        let html = '<nav><ul class="pagination mb-0">';

        if (data.current_page > 1) {
            html += `<li class="page-item"><a class="page-link" href="#" onclick="loadUsers(${data.current_page - 1}); return false;">&laquo;</a></li>`;
        }

        for (let i = 1; i <= data.last_page; i++) {
            if (i === data.current_page) {
                html += `<li class="page-item active"><span class="page-link">${i}</span></li>`;
            } else if (Math.abs(i - data.current_page) <= 2 || i === 1 || i === data.last_page) {
                html += `<li class="page-item"><a class="page-link" href="#" onclick="loadUsers(${i}); return false;">${i}</a></li>`;
            } else if (Math.abs(i - data.current_page) === 3) {
                html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            }
        }

        if (data.current_page < data.last_page) {
            html += `<li class="page-item"><a class="page-link" href="#" onclick="loadUsers(${data.current_page + 1}); return false;">&raquo;</a></li>`;
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

    function impersonateUser(userId, userName) {
        Swal.fire({
            title: impersonateConfirmTitle,
            text: impersonateConfirmText.replace(':name', userName),
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#e6a817',
            cancelButtonText: '{{ __("users.cancel") }}',
            confirmButtonText: impersonateConfirmYes
        }).then((result) => {
            if (result.isConfirmed) {
                pcFetch(`${impersonateUrl}/${userId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json().then(data => ({status: response.status, body: data})))
                .then(({status, body}) => {
                    if (body.success) {
                        window.location.href = body.redirect;
                    } else {
                        Swal.fire({ icon: 'error', title: body.message });
                    }
                })
                .catch(() => {
                    Swal.fire({ icon: 'error', title: window.PrimaCare.messages.error });
                });
            }
        });
    }

    function escapeHtml(text) {
        let div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
</script>
@endpush
