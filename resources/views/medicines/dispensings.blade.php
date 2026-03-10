@extends('layouts.app')

@section('title', __('medicines.dispensing_history') . ' - PrimaCare')

@push('css')
<link href="{{ asset('assets/plugins/choices/choices.min.css') }}" rel="stylesheet" />
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
            <h4 class="mb-0">{{ __('medicines.dispensing_history') }}</h4>
            <div>
                <button type="button" class="btn btn-outline-secondary me-1" id="print-btn">
                    <i class="ti ti-printer me-1"></i>{{ __('common.print') }}
                </button>
                <button type="button" class="btn btn-outline-success me-1" id="export-btn">
                    <i class="ti ti-file-spreadsheet me-1"></i>{{ __('common.export_excel') }}
                </button>
                <a href="{{ route('medicines.index') }}" class="btn btn-secondary">
                    <i class="ti ti-arrow-right me-1"></i>{{ __('medicines.back') }}
                </a>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-md-4 col-lg-3">
                        <select id="filter-medicine" class="form-select form-select-sm">
                            <option value="">{{ __('medicines.all_medicines') }}</option>
                        </select>
                    </div>
                    <div class="col-md-4 col-lg-3">
                        <input type="date" id="filter-date-from" class="form-control form-control-sm" title="{{ __('medicines.date_from') }}" />
                    </div>
                    <div class="col-md-4 col-lg-3">
                        <input type="date" id="filter-date-to" class="form-control form-control-sm" title="{{ __('medicines.date_to') }}" />
                    </div>
                    <div class="col-md-4 col-lg-3">
                        <button type="button" class="btn btn-outline-secondary btn-sm w-100" id="reset-btn">
                            <i class="ti ti-refresh me-1"></i>{{ __('medicines.reset_filters') }}
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
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>{{ __('dispensings.medicine') }}</th>
                                <th>{{ __('medicines.patient_name') }}</th>
                                <th>{{ __('medicines.national_id') }}</th>
                                <th>{{ __('medicines.center') }}</th>
                                <th>{{ __('medicines.quantity') }}</th>
                                <th>{{ __('medicines.employee') }}</th>
                                <th>{{ __('medicines.dispensed_at') }}</th>
                            </tr>
                        </thead>
                        <tbody id="table-body">
                        </tbody>
                    </table>
                </div>

                <div id="no-results" class="text-center py-4 text-muted" style="display:none;">
                    <i class="ti ti-database-off fs-1 d-block mb-2"></i>
                    {{ __('medicines.no_results') }}
                </div>

                <div id="pagination" class="mt-3"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script src="{{ asset('assets/plugins/choices/choices.min.js') }}"></script>
<script src="{{ asset('assets/plugins/sweetalert2/sweetalert2.min.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var currentPage = 1;
        var isLoading = false;
        var locale = '{{ app()->getLocale() }}';
        var centerNameKey = locale === 'ar' ? 'name_ar' : 'name_en';

        var medicineChoices = new Choices(document.getElementById('filter-medicine'), {
            searchEnabled: true,
            removeItemButton: true,
            shouldSort: false,
            placeholderValue: '',
            itemSelectText: '',
            noResultsText: '',
            noChoicesText: ''
        });

        function loadMedicineOptions(search) {
            pcFetch('/medicines/search?q=' + encodeURIComponent(search || ''))
                .then(function(r) { return r.json(); })
                .then(function(items) {
                    medicineChoices.clearChoices();
                    medicineChoices.setChoices(
                        items.map(function(item) { return { value: String(item.id), label: item.name }; }),
                        'value', 'label', true
                    );
                })
                .catch(function() {
                    Swal.fire({ icon: 'error', title: window.PrimaCare.messages.error });
                });
        }

        loadMedicineOptions('');

        document.getElementById('filter-medicine').addEventListener('search', function(e) {
            loadMedicineOptions(e.detail.value);
        });

        document.getElementById('filter-medicine').addEventListener('change', function() {
            currentPage = 1;
            fetchData();
        });

        document.getElementById('filter-date-from').addEventListener('change', function() {
            currentPage = 1;
            fetchData();
        });

        document.getElementById('filter-date-to').addEventListener('change', function() {
            currentPage = 1;
            fetchData();
        });

        document.getElementById('reset-btn').addEventListener('click', function() {
            medicineChoices.removeActiveItems();
            document.getElementById('filter-date-from').value = '';
            document.getElementById('filter-date-to').value = '';
            currentPage = 1;
            fetchData();
        });

        document.getElementById('print-btn').addEventListener('click', function() {
            var params = buildParams();
            var medicineName = '';
            var selected = medicineChoices.getValue();
            if (selected && selected.label) {
                params.set('medicine_name', selected.label);
            }
            window.open('{{ route("admin.dispensings.print") }}?' + params.toString(), '_blank');
        });

        document.getElementById('export-btn').addEventListener('click', function() {
            var params = buildParams();
            var selected = medicineChoices.getValue();
            if (selected && selected.label) {
                params.set('medicine_name', selected.label);
            }
            window.location.href = '{{ route("admin.dispensings.export") }}?' + params.toString();
        });

        function buildParams() {
            var params = new URLSearchParams();

            var medicineId = document.getElementById('filter-medicine').value;
            if (medicineId) params.set('medicine_id', medicineId);

            var dateFrom = document.getElementById('filter-date-from').value;
            if (dateFrom) params.set('date_from', dateFrom);

            var dateTo = document.getElementById('filter-date-to').value;
            if (dateTo) params.set('date_to', dateTo);

            return params;
        }

        function fetchData(page) {
            if (isLoading) return;
            isLoading = true;

            page = page || currentPage;
            document.getElementById('loading').style.display = 'flex';

            var params = buildParams();
            params.set('page', page);

            pcFetch('{{ route("medicines.dispensings.data") }}?' + params.toString(), {
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
                var medicineName = d.medicine ? esc(d.medicine.name) : '-';
                var employeeName = d.dispensed_by ? esc(d.dispensed_by.name) : '-';

                return '<tr>' +
                    '<td>' + (start + i + 1) + '</td>' +
                    '<td>' + medicineName + '</td>' +
                    '<td>' + patientName + '</td>' +
                    '<td><span class="fw-semibold">' + nationalId + '</span></td>' +
                    '<td>' + centerName + '</td>' +
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
                    var pg = parseInt(this.dataset.page);
                    if (pg >= 1 && pg <= last) {
                        currentPage = pg;
                        fetchData(currentPage);
                    }
                });
            });
        }

        function esc(text) {
            if (!text) return '';
            var div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        fetchData();
    });
</script>
@endpush
