@extends('layouts.employee')

@section('title', __('medical_records.record_details') . ' - PrimaCare')

@push('css')
<link href="{{ asset('assets/plugins/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" />
<style>
    .page-loader {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: var(--bs-body-bg);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        transition: opacity 0.3s ease;
    }
    .page-loader.fade-out {
        opacity: 0;
        pointer-events: none;
    }
    .patient-name {
        font-size: 1.35rem;
        font-weight: 600;
        color: var(--bs-body-color);
    }
    .national-id-value {
        font-family: "IBM Plex Mono", monospace;
        font-weight: 600;
        font-size: 15px;
        letter-spacing: 0.5px;
        direction: ltr;
        display: inline-block;
    }
    .detail-label {
        font-size: 12px;
        color: var(--bs-secondary-color);
        margin-bottom: 2px;
    }
    .detail-value {
        font-size: 14px;
        font-weight: 500;
        color: var(--bs-body-color);
    }
    .notes-section {
        padding: 14px 16px;
        border: 1px solid var(--bs-border-color);
        border-radius: 6px;
        background: var(--bs-tertiary-bg);
        font-size: 13.5px;
        line-height: 1.7;
        color: var(--bs-body-color);
    }
    .notes-section.empty {
        color: var(--bs-secondary-color);
        font-style: italic;
    }
    .ops-table th {
        font-size: 12px;
        font-weight: 600;
        color: var(--bs-secondary-color);
        border-bottom-width: 1px;
        padding: 10px 16px;
        white-space: nowrap;
    }
    .ops-table td {
        padding: 10px 16px;
        font-size: 13.5px;
        vertical-align: middle;
    }
    .empty-state {
        padding: 48px 20px;
        text-align: center;
    }
    .empty-state i {
        font-size: 36px;
        color: var(--bs-secondary-color);
        opacity: 0.35;
    }
    .empty-state p {
        color: var(--bs-secondary-color);
        font-size: 14px;
        margin: 10px 0 0;
    }
    .table-loader {
        padding: 48px 20px;
        text-align: center;
    }
    .ac-wrap {
        position: relative;
    }
    .ac-spinner {
        position: absolute;
        top: 50%;
        inset-inline-end: 12px;
        transform: translateY(-50%);
        display: none;
    }
    .ac-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        z-index: 1060;
        background: #fff;
        border: 1px solid #d1d5db;
        border-radius: 0 0 8px 8px;
        max-height: 200px;
        overflow-y: auto;
        display: none;
        box-shadow: 0 4px 12px rgba(0,0,0,0.12);
    }
    [data-bs-theme="dark"] .ac-dropdown {
        background: #1e2228;
        border-color: #3a3f47;
    }
    .ac-item {
        padding: 10px 14px;
        cursor: pointer;
        font-size: 14px;
        color: #1f2937;
        border-bottom: 1px solid #f0f0f0;
        transition: background 0.15s;
    }
    .ac-item:last-child {
        border-bottom: none;
    }
    [data-bs-theme="dark"] .ac-item {
        color: #e5e7eb;
        border-bottom-color: #2d333b;
    }
    .ac-item:hover,
    .ac-item.active {
        background: #f3f4f6;
    }
    [data-bs-theme="dark"] .ac-item:hover,
    [data-bs-theme="dark"] .ac-item.active {
        background: #2d333b;
    }
    .ac-empty {
        padding: 14px;
        color: #9ca3af;
        font-size: 13px;
        text-align: center;
    }
</style>
@endpush

@section('content')
<div class="page-loader" id="page-loader">
    <div class="spinner-border text-secondary" role="status"></div>
</div>

<div id="page-content" style="opacity: 0; transition: opacity 0.3s ease;">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0 fw-semibold">{{ __('medical_records.record_details') }}</h5>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editModal">
                <i class="ti ti-edit me-1"></i>{{ __('medical_records.edit') }}
            </button>
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#dispenseModal">
                <i class="ti ti-pill me-1"></i>{{ __('medical_records.dispense_medicine') }}
            </button>
            <a href="{{ route('medical-records.index') }}" class="btn btn-light btn-sm">
                <i class="ti ti-arrow-right me-1"></i>{{ __('medical_records.back') }}
            </a>
        </div>
    </div>

    <div class="card mb-4 border shadow-none">
        <div class="card-body pb-0">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <div class="patient-name mb-1">{{ $medicalRecord->full_name }}</div>
                    <span class="national-id-value text-muted">{{ $medicalRecord->national_id }}</span>
                </div>
                <span class="text-muted" style="font-size: 12px;">{{ __('medical_records.record_number') }}: #{{ $medicalRecord->id }}</span>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-sm-6 col-md-4">
                    <div class="detail-label">{{ __('medical_records.phone') }}</div>
                    <div class="detail-value">{{ $medicalRecord->phone ?? '—' }}</div>
                </div>
                <div class="col-sm-6 col-md-4">
                    <div class="detail-label">{{ __('medical_records.gender') }}</div>
                    <div class="detail-value">{{ $medicalRecord->gender ? __('medical_records.' . $medicalRecord->gender) : '—' }}</div>
                </div>
                <div class="col-sm-6 col-md-4">
                    <div class="detail-label">{{ __('medical_records.occupation') }}</div>
                    <div class="detail-value">{{ $medicalRecord->occupation ?? '—' }}</div>
                </div>
                <div class="col-sm-6 col-md-4">
                    <div class="detail-label">{{ __('medical_records.date_of_birth') }}</div>
                    <div class="detail-value">{{ $medicalRecord->date_of_birth ? $medicalRecord->date_of_birth->format('Y-m-d') : '—' }}</div>
                </div>
                <div class="col-sm-6 col-md-4">
                    <div class="detail-label">{{ __('medical_records.center') }}</div>
                    <div class="detail-value">{{ $medicalRecord->center->name ?? '—' }}</div>
                </div>
                <div class="col-sm-6 col-md-4">
                    <div class="detail-label">{{ __('medical_records.created_by') }}</div>
                    <div class="detail-value">{{ $medicalRecord->creator->name ?? '—' }}</div>
                </div>
                <div class="col-sm-6 col-md-4">
                    <div class="detail-label">{{ __('medical_records.created_at') }}</div>
                    <div class="detail-value">{{ $medicalRecord->created_at->format('Y-m-d') }}</div>
                </div>
            </div>

            <div class="mb-3">
                <div class="detail-label mb-2">{{ __('medical_records.notes') }}</div>
                @if($medicalRecord->notes)
                    <div class="notes-section">{{ $medicalRecord->notes }}</div>
                @else
                    <div class="notes-section empty">{{ __('medical_records.no_notes') }}</div>
                @endif
            </div>
        </div>
    </div>

    <div class="card border shadow-none">
        <div class="card-header bg-transparent d-flex align-items-center justify-content-between py-3">
            <h6 class="mb-0 fw-semibold d-flex align-items-center gap-2">
                <i class="ti ti-list text-muted" style="font-size: 18px;"></i>
                {{ __('medical_records.operations_log') }}
                <span class="badge bg-secondary-subtle text-secondary" style="font-size: 11px;" id="dispensing-count">{{ $medicalRecord->dispensings_count }}</span>
            </h6>
        </div>

        <div id="dispensings-loader" class="table-loader">
            <div class="spinner-border spinner-border-sm text-secondary" role="status"></div>
            <p class="text-muted mt-2 mb-0" style="font-size: 13px;">{{ __('medical_records.loading') }}</p>
        </div>

        <div id="dispensings-content" style="display: none;">
            <div class="table-responsive">
                <table class="table table-hover mb-0 ops-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ __('medical_records.operation_type') }}</th>
                            <th>{{ __('medical_records.operation_details') }}</th>
                            <th>{{ __('medical_records.operation_user') }}</th>
                            <th>{{ __('medical_records.operation_date') }}</th>
                        </tr>
                    </thead>
                    <tbody id="dispensings-table"></tbody>
                </table>
            </div>
        </div>

        <div id="dispensings-empty" class="empty-state" style="display: none;">
            <i class="ti ti-clipboard-off d-block"></i>
            <p>{{ __('medical_records.no_operations') }}</p>
        </div>
    </div>
</div>

<div class="modal fade" id="dispenseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-semibold">
                    <i class="ti ti-pill me-1"></i>{{ __('medical_records.dispense_medicine') }}
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="dispense-form">
                    <div class="mb-3">
                        <label class="form-label">{{ __('medical_records.medicine') }} <span class="text-danger">*</span></label>
                        <div class="ac-wrap" id="medicine-ac">
                            <input type="text" class="form-control" id="medicine-search" autocomplete="off" placeholder="{{ __('medical_records.search_medicine') }}" />
                            <div class="ac-spinner" id="medicine-spinner">
                                <div class="spinner-border spinner-border-sm text-secondary" role="status"></div>
                            </div>
                            <div class="ac-dropdown" id="medicine-dropdown"></div>
                            <input type="hidden" name="medicine_id" id="medicine_id" />
                        </div>
                        <div class="invalid-feedback" id="error-medicine_id" style="display:none;"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('medical_records.quantity') }} <span class="text-danger">*</span></label>
                        <input type="number" name="quantity" id="quantity" class="form-control" value="1" min="1" />
                        <div class="invalid-feedback" id="error-quantity"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('medical_records.dispense_notes') }}</label>
                        <textarea name="notes" id="dispense-notes" class="form-control" rows="3"></textarea>
                        <div class="invalid-feedback" id="error-notes"></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('medical_records.close') }}</button>
                <button type="button" class="btn btn-primary" id="dispense-btn">
                    <i class="ti ti-check me-1"></i>{{ __('medical_records.dispense') }}
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-semibold">
                    <i class="ti ti-edit me-1"></i>{{ __('medical_records.edit_record') }}
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="edit-form">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('medical_records.full_name') }} <span class="text-danger">*</span></label>
                            <input type="text" name="full_name" id="edit-full_name" class="form-control" value="{{ $medicalRecord->full_name }}" />
                            <div class="invalid-feedback" id="edit-error-full_name"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('medical_records.national_id') }} <span class="text-danger">*</span></label>
                            <input type="text" name="national_id" id="edit-national_id" class="form-control" value="{{ $medicalRecord->national_id }}" />
                            <div class="invalid-feedback" id="edit-error-national_id"></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('medical_records.phone') }}</label>
                            <input type="text" name="phone" id="edit-phone" class="form-control" value="{{ $medicalRecord->phone }}" />
                            <div class="invalid-feedback" id="edit-error-phone"></div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">{{ __('medical_records.gender') }}</label>
                            <select name="gender" id="edit-gender" class="form-select">
                                <option value="">—</option>
                                <option value="male" {{ $medicalRecord->gender === 'male' ? 'selected' : '' }}>{{ __('medical_records.male') }}</option>
                                <option value="female" {{ $medicalRecord->gender === 'female' ? 'selected' : '' }}>{{ __('medical_records.female') }}</option>
                            </select>
                            <div class="invalid-feedback" id="edit-error-gender"></div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">{{ __('medical_records.occupation') }}</label>
                            <input type="text" name="occupation" id="edit-occupation" class="form-control" value="{{ $medicalRecord->occupation }}" />
                            <div class="invalid-feedback" id="edit-error-occupation"></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">{{ __('medical_records.date_of_birth') }}</label>
                            <input type="date" name="date_of_birth" id="edit-date_of_birth" class="form-control" value="{{ $medicalRecord->date_of_birth ? $medicalRecord->date_of_birth->format('Y-m-d') : '' }}" max="{{ date('Y-m-d') }}" />
                            <div class="invalid-feedback" id="edit-error-date_of_birth"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('medical_records.notes') }}</label>
                        <textarea name="notes" id="edit-notes" class="form-control" rows="3">{{ $medicalRecord->notes }}</textarea>
                        <div class="invalid-feedback" id="edit-error-notes"></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('medical_records.close') }}</button>
                <button type="button" class="btn btn-primary" id="edit-save-btn">
                    <i class="ti ti-check me-1"></i>{{ __('medical_records.save') }}
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
        var csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        var dispenseLabel = '{{ __("medical_records.operation_dispense") }}';
        var searchUrl = '{{ route("medicines.search") }}';
        var noResultsText = '{{ __("medical_records.no_medicine_results") }}';

        var acInput = document.getElementById('medicine-search');
        var acHidden = document.getElementById('medicine_id');
        var acDropdown = document.getElementById('medicine-dropdown');
        var acSpinner = document.getElementById('medicine-spinner');
        var acTimer = null;
        var acIndex = -1;
        var acResults = [];

        loadDispensings();
        showPage();

        acInput.addEventListener('input', function() {
            var val = this.value.trim();
            acHidden.value = '';
            acIndex = -1;

            clearTimeout(acTimer);
            if (val.length < 1) {
                acDropdown.style.display = 'none';
                acSpinner.style.display = 'none';
                return;
            }

            acSpinner.style.display = 'block';
            acTimer = setTimeout(function() {
                fetch(searchUrl + '?q=' + encodeURIComponent(val), {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    acSpinner.style.display = 'none';
                    acResults = data;
                    acIndex = -1;
                    renderDropdown(data);
                })
                .catch(function() {
                    acSpinner.style.display = 'none';
                });
            }, 300);
        });

        acInput.addEventListener('keydown', function(e) {
            if (acDropdown.style.display === 'none') return;
            var items = acDropdown.querySelectorAll('.ac-item');
            if (!items.length) return;

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                acIndex = Math.min(acIndex + 1, items.length - 1);
                highlightItem(items);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                acIndex = Math.max(acIndex - 1, 0);
                highlightItem(items);
            } else if (e.key === 'Enter') {
                e.preventDefault();
                if (acIndex >= 0 && acIndex < items.length) {
                    items[acIndex].click();
                }
            } else if (e.key === 'Escape') {
                acDropdown.style.display = 'none';
            }
        });

        document.addEventListener('click', function(e) {
            if (!document.getElementById('medicine-ac').contains(e.target)) {
                acDropdown.style.display = 'none';
            }
        });

        document.getElementById('dispenseModal').addEventListener('hidden.bs.modal', function() {
            resetMedicineSearch();
        });

        function renderDropdown(data) {
            if (data.length === 0) {
                acDropdown.innerHTML = '<div class="ac-empty">' + noResultsText + '</div>';
                acDropdown.style.display = 'block';
                return;
            }

            acDropdown.innerHTML = data.map(function(m, i) {
                return '<div class="ac-item" data-id="' + m.id + '" data-name="' + esc(m.name) + '">' + esc(m.name) + '</div>';
            }).join('');

            acDropdown.querySelectorAll('.ac-item').forEach(function(item) {
                item.addEventListener('click', function() {
                    acHidden.value = this.dataset.id;
                    acInput.value = this.dataset.name;
                    acDropdown.style.display = 'none';
                    acInput.classList.remove('is-invalid');
                    document.getElementById('error-medicine_id').style.display = 'none';
                });
            });

            acDropdown.style.display = 'block';
        }

        function highlightItem(items) {
            items.forEach(function(el) { el.classList.remove('active'); });
            if (acIndex >= 0 && acIndex < items.length) {
                items[acIndex].classList.add('active');
                items[acIndex].scrollIntoView({ block: 'nearest' });
            }
        }

        function resetMedicineSearch() {
            acInput.value = '';
            acHidden.value = '';
            acDropdown.style.display = 'none';
            acSpinner.style.display = 'none';
            acIndex = -1;
            acResults = [];
        }

        function showPage() {
            var loader = document.getElementById('page-loader');
            var content = document.getElementById('page-content');
            loader.classList.add('fade-out');
            content.style.opacity = '1';
            setTimeout(function() { loader.style.display = 'none'; }, 300);
        }

        function loadDispensings() {
            fetch('{{ route("medical-records.dispensings", $medicalRecord->id) }}', {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                document.getElementById('dispensings-loader').style.display = 'none';

                if (data.length === 0) {
                    document.getElementById('dispensings-empty').style.display = 'block';
                    return;
                }

                document.getElementById('dispensings-content').style.display = 'block';
                var tbody = document.getElementById('dispensings-table');
                tbody.innerHTML = data.map(function(d, i) {
                    var date = new Date(d.dispensed_at);
                    var formatted = date.getFullYear() + '-' +
                        String(date.getMonth() + 1).padStart(2, '0') + '-' +
                        String(date.getDate()).padStart(2, '0') + ' ' +
                        String(date.getHours()).padStart(2, '0') + ':' +
                        String(date.getMinutes()).padStart(2, '0');
                    var details = esc(d.medicine ? d.medicine.name : '-') + ' &times; ' + d.quantity;
                    if (d.notes) details += '<div class="text-muted" style="font-size:12px;">' + esc(d.notes) + '</div>';
                    return '<tr>' +
                        '<td class="text-muted">' + (i + 1) + '</td>' +
                        '<td>' + dispenseLabel + '</td>' +
                        '<td>' + details + '</td>' +
                        '<td>' + esc(d.dispensed_by ? d.dispensed_by.name : '-') + '</td>' +
                        '<td class="text-muted" style="white-space:nowrap">' + formatted + '</td>' +
                    '</tr>';
                }).join('');
            })
            .catch(function() {
                document.getElementById('dispensings-loader').style.display = 'none';
                document.getElementById('dispensings-empty').style.display = 'block';
            });
        }

        document.getElementById('dispense-btn').addEventListener('click', function() {
            var medicineId = acHidden.value;
            var qty = document.getElementById('quantity').value;

            if (!medicineId || !qty) {
                clearErrors();
                if (!medicineId) {
                    acInput.classList.add('is-invalid');
                    var errEl = document.getElementById('error-medicine_id');
                    errEl.textContent = '{{ __("medical_records.validation.medicine_required") }}';
                    errEl.style.display = 'block';
                }
                if (!qty) showFieldError('quantity', '{{ __("medical_records.validation.quantity_required") }}');
                return;
            }

            Swal.fire({
                title: '{{ __("medical_records.dispense_medicine") }}',
                text: '{{ __("medical_records.confirm_dispense") }}',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: '{{ __("medical_records.confirm_yes") }}',
                cancelButtonText: '{{ __("medical_records.confirm_cancel") }}',
                confirmButtonColor: '#3085d6',
            }).then(function(result) {
                if (result.isConfirmed) {
                    submitDispense();
                }
            });
        });

        function submitDispense() {
            clearErrors();
            var btn = document.getElementById('dispense-btn');
            btn.disabled = true;

            var payload = {
                medicine_id: acHidden.value,
                quantity: document.getElementById('quantity').value,
                notes: document.getElementById('dispense-notes').value,
            };

            fetch('{{ route("medical-records.dispense", $medicalRecord->id) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(payload)
            })
            .then(function(response) {
                return response.json().then(function(data) {
                    return { status: response.status, body: data };
                });
            })
            .then(function(res) {
                btn.disabled = false;

                if (res.status === 422) {
                    if (res.body.errors && res.body.errors.medicine_id) {
                        acInput.classList.add('is-invalid');
                        var errEl = document.getElementById('error-medicine_id');
                        errEl.textContent = res.body.errors.medicine_id[0];
                        errEl.style.display = 'block';
                        delete res.body.errors.medicine_id;
                    }
                    showErrors(res.body.errors);
                    return;
                }

                if (res.body.success) {
                    bootstrap.Modal.getInstance(document.getElementById('dispenseModal')).hide();
                    document.getElementById('dispense-form').reset();
                    document.getElementById('quantity').value = 1;
                    resetMedicineSearch();

                    Swal.fire({
                        icon: 'success',
                        title: res.body.message,
                        showConfirmButton: false,
                        timer: 1500
                    });

                    var countEl = document.getElementById('dispensing-count');
                    countEl.textContent = parseInt(countEl.textContent) + 1;

                    document.getElementById('dispensings-loader').style.display = 'block';
                    document.getElementById('dispensings-content').style.display = 'none';
                    document.getElementById('dispensings-empty').style.display = 'none';
                    loadDispensings();
                }
            })
            .catch(function() {
                btn.disabled = false;
            });
        }

        function clearErrors() {
            document.querySelectorAll('.is-invalid').forEach(function(el) { el.classList.remove('is-invalid'); });
            document.querySelectorAll('.invalid-feedback').forEach(function(el) {
                el.textContent = '';
                el.style.display = '';
            });
        }

        function showErrors(errors) {
            if (!errors) return;
            for (var field in errors) {
                showFieldError(field, errors[field][0]);
            }
        }

        function showFieldError(field, message) {
            var input = document.querySelector('[name="' + field + '"]');
            var errorEl = document.getElementById('error-' + field);
            if (input) input.classList.add('is-invalid');
            if (errorEl) errorEl.textContent = message;
        }

        function esc(text) {
            if (!text) return '';
            var div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        document.getElementById('edit-save-btn').addEventListener('click', function() {
            var btn = this;
            btn.disabled = true;

            document.querySelectorAll('#edit-form .is-invalid').forEach(function(el) { el.classList.remove('is-invalid'); });
            document.querySelectorAll('#edit-form .invalid-feedback').forEach(function(el) { el.textContent = ''; });

            var payload = {
                full_name: document.getElementById('edit-full_name').value,
                national_id: document.getElementById('edit-national_id').value,
                phone: document.getElementById('edit-phone').value,
                gender: document.getElementById('edit-gender').value || null,
                occupation: document.getElementById('edit-occupation').value || null,
                date_of_birth: document.getElementById('edit-date_of_birth').value || null,
                notes: document.getElementById('edit-notes').value,
            };

            fetch('{{ route("medical-records.update", $medicalRecord->id) }}', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(payload)
            })
            .then(function(response) {
                return response.json().then(function(data) {
                    return { status: response.status, body: data };
                });
            })
            .then(function(res) {
                btn.disabled = false;

                if (res.status === 422) {
                    if (res.body.errors) {
                        for (var field in res.body.errors) {
                            var input = document.querySelector('#edit-form [name="' + field + '"]');
                            var errEl = document.getElementById('edit-error-' + field);
                            if (input) input.classList.add('is-invalid');
                            if (errEl) errEl.textContent = res.body.errors[field][0];
                        }
                    }
                    return;
                }

                if (res.body.success) {
                    bootstrap.Modal.getInstance(document.getElementById('editModal')).hide();
                    Swal.fire({
                        icon: 'success',
                        title: res.body.message,
                        showConfirmButton: false,
                        timer: 1500
                    }).then(function() {
                        window.location.reload();
                    });
                }
            })
            .catch(function() {
                btn.disabled = false;
            });
        });
    });
</script>
@endpush
