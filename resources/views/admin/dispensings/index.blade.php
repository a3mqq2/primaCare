@extends('layouts.app')

@section('title', __('dispensings.title') . ' - PrimaCare')

@push('css')
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
    .choices { margin-bottom: 0; }
    .choices .choices__inner {
        min-height: 31px;
        padding: 2px 4px;
        font-size: .875rem;
        border-color: var(--bs-border-color);
        background-color: var(--bs-body-bg);
        border-radius: var(--bs-border-radius);
    }
    .choices .choices__input { font-size: .875rem; background-color: transparent; color: var(--bs-body-color); }
    .choices .choices__list--dropdown { border-color: var(--bs-border-color); background-color: var(--bs-body-bg); }
    .choices .choices__list--dropdown .choices__item { color: var(--bs-body-color); font-size: .875rem; }
    .choices .choices__list--dropdown .choices__item--selectable.is-highlighted { background-color: var(--bs-primary); color: #fff; }
    .choices .choices__list--single .choices__item { color: var(--bs-body-color); }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">{{ __('dispensings.title') }}</h4>
            <div>
                <button type="button" class="btn btn-outline-success me-1" id="export-btn">
                    <i class="ti ti-file-spreadsheet me-1"></i>{{ __('common.export_excel') }}
                </button>
                <button type="button" class="btn btn-secondary" id="print-btn">
                    <i class="ti ti-printer me-1"></i>{{ __('dispensings.print') }}
                </button>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <div class="row g-2 mb-3">
                    <div class="col-12">
                        <input type="text" id="general-search" class="form-control" placeholder="{{ __('dispensings.general_search_placeholder') }}" />
                    </div>
                </div>

                <div class="row g-2">
                    <div class="col-md-4 col-lg-3">
                        <select id="filter-center" class="form-select form-select-sm">
                            <option value="">{{ __('dispensings.all_centers') }}</option>
                        </select>
                    </div>
                    <div class="col-md-4 col-lg-3">
                        <input type="text" id="filter-medicine" class="form-control form-control-sm" placeholder="{{ __('dispensings.filter_medicine') }}" />
                    </div>
                    <div class="col-md-4 col-lg-3">
                        <select id="filter-employee" class="form-select form-select-sm">
                            <option value="">{{ __('dispensings.all_employees') }}</option>
                        </select>
                    </div>
                    <div class="col-md-4 col-lg-3">
                        <input type="text" id="filter-patient" class="form-control form-control-sm" placeholder="{{ __('dispensings.filter_patient') }}" />
                    </div>
                    <div class="col-md-4 col-lg-3">
                        <input type="text" id="filter-national-id" class="form-control form-control-sm" placeholder="{{ __('dispensings.filter_national_id') }}" />
                    </div>
                    <div class="col-md-4 col-lg-3">
                        <select id="filter-gender" class="form-select form-select-sm">
                            <option value="">{{ __('dispensings.all_genders') }}</option>
                            <option value="male">{{ __('dispensings.gender') }} - {{ __('medical_records.male') }}</option>
                            <option value="female">{{ __('dispensings.gender') }} - {{ __('medical_records.female') }}</option>
                        </select>
                    </div>
                    <div class="col-md-4 col-lg-3">
                        <input type="text" id="filter-occupation" class="form-control form-control-sm" placeholder="{{ __('dispensings.filter_occupation') }}" />
                    </div>
                    <div class="col-md-4 col-lg-3">
                        <input type="date" id="filter-date-from" class="form-control form-control-sm" title="{{ __('dispensings.date_from') }}" />
                    </div>
                    <div class="col-md-4 col-lg-3">
                        <input type="date" id="filter-date-to" class="form-control form-control-sm" title="{{ __('dispensings.date_to') }}" />
                    </div>
                    <div class="col-md-4 col-lg-3">
                        <button type="button" class="btn btn-outline-secondary btn-sm w-100" id="reset-btn">
                            <i class="ti ti-refresh me-1"></i>{{ __('dispensings.reset_filters') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="card position-relative">
            <div class="loading-overlay" id="loading" style="display:none;">
                <div class="spinner-border text-primary" role="status"></div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>{{ __('dispensings.center') }}</th>
                                <th>{{ __('dispensings.patient_name') }}</th>
                                <th>{{ __('dispensings.national_id') }}</th>
                                <th>{{ __('dispensings.gender') }}</th>
                                <th>{{ __('dispensings.occupation') }}</th>
                                <th>{{ __('dispensings.date_of_birth') }}</th>
                                <th>{{ __('dispensings.medicine') }}</th>
                                <th>{{ __('dispensings.quantity') }}</th>
                                <th>{{ __('dispensings.dispensed_by') }}</th>
                                <th>{{ __('dispensings.dispensed_at') }}</th>
                            </tr>
                        </thead>
                        <tbody id="table-body"></tbody>
                    </table>
                </div>
                <div id="no-results" class="text-center py-4 text-muted" style="display:none;">
                    <i class="ti ti-database-off fs-1 d-block mb-2"></i>
                    {{ __('dispensings.no_results') }}
                </div>
                <div id="pagination" class="mt-3"></div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="detailModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-semibold">
                    <i class="ti ti-pill me-1"></i>{{ __('dispensings.details') }}
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detail-body">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('dispensings.close') }}</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script src="{{ asset('assets/plugins/choices/choices.min.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var currentPage = 1;
        var debounceTimer = null;
        var isLoading = false;
        var centerNameKey = '{{ app()->getLocale() === "ar" ? "name_ar" : "name_en" }}';
        var detailModal = new bootstrap.Modal(document.getElementById('detailModal'));
        var genderLabels = { male: '{{ __("medical_records.male") }}', female: '{{ __("medical_records.female") }}' };

        function initSearchSelect(selectEl, url, labelFn) {
            var choices = new Choices(selectEl, {
                searchEnabled: true,
                removeItemButton: true,
                shouldSort: false,
                placeholderValue: '',
                itemSelectText: '',
                noResultsText: '',
                noChoicesText: ''
            });

            function loadOptions(search) {
                pcFetch(url + '?search=' + encodeURIComponent(search || ''))
                    .then(function(r) { return r.json(); })
                    .then(function(items) {
                        choices.clearChoices();
                        choices.setChoices(
                            items.map(function(item) { return { value: String(item.id), label: labelFn(item) }; }),
                            'value', 'label', true
                        );
                    })
                    .catch(function() {
                        Swal.fire({ icon: 'error', title: window.PrimaCare.messages.error });
                    });
            }
            loadOptions('');

            selectEl.addEventListener('search', function(e) {
                loadOptions(e.detail.value);
            });

            return choices;
        }

        var centerChoices = initSearchSelect(
            document.getElementById('filter-center'),
            '/centers/search',
            function(c) { return window.PrimaCare.locale === 'ar' ? c.name_ar : c.name_en; }
        );

        var employeeChoices = initSearchSelect(
            document.getElementById('filter-employee'),
            '/users/search-employees',
            function(e) { return e.name; }
        );

        fetchData();

        document.getElementById('general-search').addEventListener('input', debounceSearch);
        document.getElementById('filter-patient').addEventListener('input', debounceSearch);
        document.getElementById('filter-national-id').addEventListener('input', debounceSearch);
        document.getElementById('filter-center').addEventListener('change', function() { currentPage = 1; fetchData(); });
        document.getElementById('filter-medicine').addEventListener('input', debounceSearch);
        document.getElementById('filter-occupation').addEventListener('input', debounceSearch);
        document.getElementById('filter-gender').addEventListener('change', function() { currentPage = 1; fetchData(); });
        document.getElementById('filter-employee').addEventListener('change', function() { currentPage = 1; fetchData(); });
        document.getElementById('filter-date-from').addEventListener('change', function() { currentPage = 1; fetchData(); });
        document.getElementById('filter-date-to').addEventListener('change', function() { currentPage = 1; fetchData(); });

        document.getElementById('reset-btn').addEventListener('click', function() {
            document.getElementById('general-search').value = '';
            centerChoices.removeActiveItems();
            document.getElementById('filter-medicine').value = '';
            employeeChoices.removeActiveItems();
            document.getElementById('filter-patient').value = '';
            document.getElementById('filter-national-id').value = '';
            document.getElementById('filter-gender').value = '';
            document.getElementById('filter-occupation').value = '';
            document.getElementById('filter-date-from').value = '';
            document.getElementById('filter-date-to').value = '';
            currentPage = 1;
            fetchData();
        });

        document.getElementById('print-btn').addEventListener('click', function() {
            window.open('{{ route("admin.dispensings.print") }}?' + buildParams(), '_blank');
        });

        document.getElementById('export-btn').addEventListener('click', function() {
            window.location.href = '{{ route("admin.dispensings.export") }}?' + buildParams();
        });

        function debounceSearch() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(function() { currentPage = 1; fetchData(); }, 400);
        }

        function buildParams(page) {
            var params = new URLSearchParams();
            params.set('page', page || currentPage);

            var search = document.getElementById('general-search').value;
            if (search) params.set('search', search);

            var centerId = document.getElementById('filter-center').value;
            if (centerId) params.set('center_id', centerId);

            var medicine = document.getElementById('filter-medicine').value;
            if (medicine) params.set('medicine_name', medicine);

            var employeeId = document.getElementById('filter-employee').value;
            if (employeeId) params.set('employee_id', employeeId);

            var patient = document.getElementById('filter-patient').value;
            if (patient) params.set('patient_name', patient);

            var nationalId = document.getElementById('filter-national-id').value;
            if (nationalId) params.set('national_id', nationalId);

            var gender = document.getElementById('filter-gender').value;
            if (gender) params.set('gender', gender);

            var occupation = document.getElementById('filter-occupation').value;
            if (occupation) params.set('occupation', occupation);

            var dateFrom = document.getElementById('filter-date-from').value;
            if (dateFrom) params.set('date_from', dateFrom);

            var dateTo = document.getElementById('filter-date-to').value;
            if (dateTo) params.set('date_to', dateTo);

            return params.toString();
        }

        function fetchData(page) {
            if (isLoading) return;
            isLoading = true;

            page = page || currentPage;
            document.getElementById('loading').style.display = 'flex';

            pcFetch('{{ route("admin.dispensings.data") }}?' + buildParams(page), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                isLoading = false;
                document.getElementById('loading').style.display = 'none';
                renderData(data);
            })
            .catch(function() {
                isLoading = false;
                document.getElementById('loading').style.display = 'none';
                Swal.fire({ icon: 'error', title: window.PrimaCare.messages.error });
            });
        }

        function renderData(data) {
            var tbody = document.getElementById('table-body');
            var noResults = document.getElementById('no-results');

            if (data.data.length === 0) {
                tbody.innerHTML = '';
                noResults.style.display = 'block';
                document.getElementById('pagination').innerHTML = '';
                return;
            }

            noResults.style.display = 'none';
            var start = (data.current_page - 1) * data.per_page;

            tbody.innerHTML = data.data.map(function(d, i) {
                var date = new Date(d.dispensed_at);
                var formatted = date.getFullYear() + '-' +
                    String(date.getMonth() + 1).padStart(2, '0') + '-' +
                    String(date.getDate()).padStart(2, '0') + ' ' +
                    String(date.getHours()).padStart(2, '0') + ':' +
                    String(date.getMinutes()).padStart(2, '0');

                var centerName = d.medical_record && d.medical_record.center ? esc(d.medical_record.center[centerNameKey]) : '-';
                var patientName = d.medical_record ? esc(d.medical_record.full_name) : '-';
                var nationalId = d.medical_record ? esc(d.medical_record.national_id) : '-';
                var genderVal = d.medical_record && d.medical_record.gender ? (genderLabels[d.medical_record.gender] || d.medical_record.gender) : '-';
                var occupationVal = d.medical_record && d.medical_record.occupation ? esc(d.medical_record.occupation) : '-';
                var dobVal = d.medical_record && d.medical_record.date_of_birth ? d.medical_record.date_of_birth.substring(0, 10) : '-';
                var medicineName = d.medicine ? esc(d.medicine.name) : '-';
                var employeeName = d.dispensed_by ? esc(d.dispensed_by.name) : '-';

                return '<tr onclick="showDetails(' + d.id + ')" style="cursor:pointer">' +
                    '<td>' + (start + i + 1) + '</td>' +
                    '<td>' + centerName + '</td>' +
                    '<td>' + patientName + '</td>' +
                    '<td><span class="fw-semibold">' + nationalId + '</span></td>' +
                    '<td>' + genderVal + '</td>' +
                    '<td>' + occupationVal + '</td>' +
                    '<td>' + dobVal + '</td>' +
                    '<td>' + medicineName + '</td>' +
                    '<td>' + d.quantity + '</td>' +
                    '<td>' + employeeName + '</td>' +
                    '<td style="white-space:nowrap">' + formatted + '</td>' +
                '</tr>';
            }).join('');

            var container = document.getElementById('pagination');
            if (data.last_page <= 1) { container.innerHTML = ''; return; }

            var current = data.current_page;
            var last = data.last_page;
            var pages = [];
            var delta = 2;
            pages.push(1);
            var rangeStart = Math.max(2, current - delta);
            var rangeEnd = Math.min(last - 1, current + delta);
            if (rangeStart > 2) pages.push('...');
            for (var p = rangeStart; p <= rangeEnd; p++) pages.push(p);
            if (rangeEnd < last - 1) pages.push('...');
            if (last > 1) pages.push(last);

            var prevDisabled = current === 1 ? ' disabled' : '';
            var nextDisabled = current === last ? ' disabled' : '';
            var html = '<nav><ul class="pagination pagination-sm justify-content-center mb-0 flex-wrap">';
            html += '<li class="page-item' + prevDisabled + '"><a class="page-link" href="#" data-page="' + (current - 1) + '">&laquo;</a></li>';
            pages.forEach(function(pg) {
                if (pg === '...') {
                    html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
                } else {
                    html += '<li class="page-item ' + (pg === current ? 'active' : '') + '"><a class="page-link" href="#" data-page="' + pg + '">' + pg + '</a></li>';
                }
            });
            html += '<li class="page-item' + nextDisabled + '"><a class="page-link" href="#" data-page="' + (current + 1) + '">&raquo;</a></li>';
            html += '</ul></nav>';
            html += '<div class="text-center text-muted mt-1" style="font-size:.8rem">' + data.from + '-' + data.to + ' {{ __("common.of") }} ' + data.total + '</div>';
            container.innerHTML = html;

            container.querySelectorAll('[data-page]').forEach(function(link) {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    var page = parseInt(this.dataset.page);
                    if (page >= 1 && page <= last) {
                        currentPage = page;
                        fetchData(currentPage);
                    }
                });
            });
        }

        window.showDetails = function(id) {
            var body = document.getElementById('detail-body');
            body.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div></div>';
            detailModal.show();

            pcFetch('/admin/dispensings/' + id, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(function(r) { return r.json(); })
            .then(function(d) {
                var centerName = d.medical_record && d.medical_record.center ? esc(d.medical_record.center[centerNameKey]) : '-';
                var date = new Date(d.dispensed_at);
                var formatted = date.getFullYear() + '-' +
                    String(date.getMonth() + 1).padStart(2, '0') + '-' +
                    String(date.getDate()).padStart(2, '0') + ' ' +
                    String(date.getHours()).padStart(2, '0') + ':' +
                    String(date.getMinutes()).padStart(2, '0');

                body.innerHTML = '<div class="row g-3">' +
                    '<div class="col-sm-6"><div class="detail-label">{{ __("dispensings.center") }}</div><div class="detail-value">' + centerName + '</div></div>' +
                    '<div class="col-sm-6"><div class="detail-label">{{ __("dispensings.patient_name") }}</div><div class="detail-value">' + (d.medical_record ? esc(d.medical_record.full_name) : '-') + '</div></div>' +
                    '<div class="col-sm-6"><div class="detail-label">{{ __("dispensings.national_id") }}</div><div class="detail-value fw-semibold" style="font-family:monospace;direction:ltr;display:inline-block">' + (d.medical_record ? esc(d.medical_record.national_id) : '-') + '</div></div>' +
                    '<div class="col-sm-6"><div class="detail-label">{{ __("dispensings.phone") }}</div><div class="detail-value">' + (d.medical_record && d.medical_record.phone ? esc(d.medical_record.phone) : '-') + '</div></div>' +
                    '<div class="col-sm-6"><div class="detail-label">{{ __("dispensings.gender") }}</div><div class="detail-value">' + (d.medical_record && d.medical_record.gender ? (genderLabels[d.medical_record.gender] || d.medical_record.gender) : '-') + '</div></div>' +
                    '<div class="col-sm-6"><div class="detail-label">{{ __("dispensings.occupation") }}</div><div class="detail-value">' + (d.medical_record && d.medical_record.occupation ? esc(d.medical_record.occupation) : '-') + '</div></div>' +
                    '<div class="col-sm-6"><div class="detail-label">{{ __("dispensings.date_of_birth") }}</div><div class="detail-value">' + (d.medical_record && d.medical_record.date_of_birth ? d.medical_record.date_of_birth.substring(0, 10) : '-') + '</div></div>' +
                    '<div class="col-12"><hr class="my-1"></div>' +
                    '<div class="col-sm-6"><div class="detail-label">{{ __("dispensings.medicine") }}</div><div class="detail-value">' + (d.medicine ? esc(d.medicine.name) : '-') + '</div></div>' +
                    '<div class="col-sm-6"><div class="detail-label">{{ __("dispensings.quantity") }}</div><div class="detail-value">' + d.quantity + '</div></div>' +
                    '<div class="col-12"><div class="detail-label">{{ __("dispensings.notes") }}</div><div class="detail-value">' + (d.notes ? esc(d.notes) : '-') + '</div></div>' +
                    '<div class="col-12"><hr class="my-1"></div>' +
                    '<div class="col-sm-6"><div class="detail-label">{{ __("dispensings.dispensed_by") }}</div><div class="detail-value">' + (d.dispensed_by ? esc(d.dispensed_by.name) : '-') + '</div></div>' +
                    '<div class="col-sm-6"><div class="detail-label">{{ __("dispensings.dispensed_at") }}</div><div class="detail-value">' + formatted + '</div></div>' +
                '</div>';
            })
            .catch(function() {
                body.innerHTML = '<div class="text-center py-4 text-muted">Error</div>';
                Swal.fire({ icon: 'error', title: window.PrimaCare.messages.error });
            });
        };

        function esc(text) {
            if (!text) return '';
            var div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    });
</script>
@endpush
