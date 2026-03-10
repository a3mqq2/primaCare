@extends('layouts.app')

@section('title', __('medical_records.admin_title') . ' - PrimaCare')

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
            <h4 class="mb-0">{{ __('medical_records.admin_title') }}</h4>
            <div>
                <button type="button" class="btn btn-outline-success me-1" id="export-btn">
                    <i class="ti ti-file-spreadsheet me-1"></i>{{ __('common.export_excel') }}
                </button>
                <button type="button" class="btn btn-secondary no-print" id="print-btn">
                    <i class="ti ti-printer me-1"></i>{{ __('medical_records.print') }}
                </button>
            </div>
        </div>

        <div class="card mb-3 no-print">
            <div class="card-body">
                <div class="row g-2 mb-3">
                    <div class="col-12">
                        <input type="text" id="general-search" class="form-control" placeholder="{{ __('medical_records.general_search_placeholder') }}" />
                    </div>
                </div>

                <div class="row g-2">
                    <div class="col-md-4 col-lg-3">
                        <select id="filter-center" class="form-select form-select-sm">
                            <option value="">{{ __('medical_records.all_centers') }}</option>
                        </select>
                    </div>
                    <div class="col-md-4 col-lg-3">
                        <input type="text" id="filter-name" class="form-control form-control-sm" placeholder="{{ __('medical_records.filter_by_name') }}" />
                    </div>
                    <div class="col-md-4 col-lg-3">
                        <input type="text" id="filter-national-id" class="form-control form-control-sm" placeholder="{{ __('medical_records.filter_by_national_id') }}" />
                    </div>
                    <div class="col-md-4 col-lg-3">
                        <input type="text" id="filter-phone" class="form-control form-control-sm" placeholder="{{ __('medical_records.filter_by_phone') }}" />
                    </div>
                    <div class="col-md-4 col-lg-3">
                        <select id="filter-gender" class="form-select form-select-sm">
                            <option value="">{{ __('medical_records.all_genders') }}</option>
                            <option value="male">{{ __('medical_records.male') }}</option>
                            <option value="female">{{ __('medical_records.female') }}</option>
                        </select>
                    </div>
                    <div class="col-md-4 col-lg-3">
                        <input type="text" id="filter-occupation" class="form-control form-control-sm" placeholder="{{ __('medical_records.filter_by_occupation') }}" />
                    </div>
                    <div class="col-md-4 col-lg-3">
                        <select id="filter-employee" class="form-select form-select-sm">
                            <option value="">{{ __('medical_records.all_employees') }}</option>
                        </select>
                    </div>
                    <div class="col-md-4 col-lg-3">
                        <input type="date" id="filter-date-from" class="form-control form-control-sm" placeholder="{{ __('medical_records.filter_date_from') }}" title="{{ __('medical_records.filter_date_from') }}" />
                    </div>
                    <div class="col-md-4 col-lg-3">
                        <input type="date" id="filter-date-to" class="form-control form-control-sm" placeholder="{{ __('medical_records.filter_date_to') }}" title="{{ __('medical_records.filter_date_to') }}" />
                    </div>
                    <div class="col-md-4 col-lg-3">
                        <button type="button" class="btn btn-outline-secondary btn-sm w-100" id="reset-btn">
                            <i class="ti ti-refresh me-1"></i>{{ __('medical_records.reset_filters') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div>
            <div class="card position-relative">
                <div class="loading-overlay" id="loading" style="display:none;">
                    <div class="spinner-border text-primary" role="status"></div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="records-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('medical_records.center') }}</th>
                                    <th>{{ __('medical_records.full_name') }}</th>
                                    <th>{{ __('medical_records.national_id') }}</th>
                                    <th>{{ __('medical_records.phone') }}</th>
                                    <th>{{ __('medical_records.gender') }}</th>
                                    <th>{{ __('medical_records.occupation') }}</th>
                                    <th>{{ __('medical_records.date_of_birth') }}</th>
                                    <th>{{ __('medical_records.notes') }}</th>
                                    <th>{{ __('medical_records.employee') }}</th>
                                    <th>{{ __('medical_records.created_at') }}</th>
                                </tr>
                            </thead>
                            <tbody id="table-body"></tbody>
                        </table>
                    </div>
                    <div id="no-results" class="text-center py-4 text-muted" style="display:none;">
                        <i class="ti ti-database-off fs-1 d-block mb-2"></i>
                        {{ __('medical_records.no_results') }}
                    </div>
                    <div id="pagination" class="mt-3 no-print"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script src="{{ asset('assets/plugins/choices/choices.min.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        let currentPage = 1;
        let debounceTimer = null;
        let isLoading = false;
        let genderLabels = { male: '{{ __("medical_records.male") }}', female: '{{ __("medical_records.female") }}' };

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
        document.getElementById('filter-name').addEventListener('input', debounceSearch);
        document.getElementById('filter-national-id').addEventListener('input', debounceSearch);
        document.getElementById('filter-phone').addEventListener('input', debounceSearch);
        document.getElementById('filter-occupation').addEventListener('input', debounceSearch);
        document.getElementById('filter-center').addEventListener('change', function() { currentPage = 1; fetchData(); });
        document.getElementById('filter-gender').addEventListener('change', function() { currentPage = 1; fetchData(); });
        document.getElementById('filter-employee').addEventListener('change', function() { currentPage = 1; fetchData(); });
        document.getElementById('filter-date-from').addEventListener('change', function() { currentPage = 1; fetchData(); });
        document.getElementById('filter-date-to').addEventListener('change', function() { currentPage = 1; fetchData(); });

        document.getElementById('reset-btn').addEventListener('click', function() {
            document.getElementById('general-search').value = '';
            centerChoices.removeActiveItems();
            document.getElementById('filter-name').value = '';
            document.getElementById('filter-national-id').value = '';
            document.getElementById('filter-phone').value = '';
            document.getElementById('filter-gender').value = '';
            document.getElementById('filter-occupation').value = '';
            employeeChoices.removeActiveItems();
            document.getElementById('filter-date-from').value = '';
            document.getElementById('filter-date-to').value = '';
            currentPage = 1;
            fetchData();
        });

        document.getElementById('print-btn').addEventListener('click', function() {
            window.open(`{{ route('admin.medical-records.print') }}?${buildParams()}`, '_blank');
        });

        document.getElementById('export-btn').addEventListener('click', function() {
            window.location.href = `{{ route('admin.medical-records.export') }}?${buildParams()}`;
        });

        function debounceSearch() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(function() { currentPage = 1; fetchData(); }, 400);
        }

        function buildParams(page) {
            let params = new URLSearchParams();
            params.set('page', page || currentPage);

            let search = document.getElementById('general-search').value;
            if (search) params.set('search', search);

            let centerId = document.getElementById('filter-center').value;
            if (centerId) params.set('center_id', centerId);

            let name = document.getElementById('filter-name').value;
            if (name) params.set('full_name', name);

            let nationalId = document.getElementById('filter-national-id').value;
            if (nationalId) params.set('national_id', nationalId);

            let phone = document.getElementById('filter-phone').value;
            if (phone) params.set('phone', phone);

            let gender = document.getElementById('filter-gender').value;
            if (gender) params.set('gender', gender);

            let occupation = document.getElementById('filter-occupation').value;
            if (occupation) params.set('occupation', occupation);

            let employee = document.getElementById('filter-employee').value;
            if (employee) params.set('created_by', employee);

            let dateFrom = document.getElementById('filter-date-from').value;
            if (dateFrom) params.set('date_from', dateFrom);

            let dateTo = document.getElementById('filter-date-to').value;
            if (dateTo) params.set('date_to', dateTo);

            return params.toString();
        }

        function fetchData(page) {
            if (isLoading) return;
            isLoading = true;

            page = page || currentPage;
            document.getElementById('loading').style.display = 'flex';

            pcFetch(`{{ route('admin.medical-records.data') }}?${buildParams(page)}`, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(data => {
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
            let tbody = document.getElementById('table-body');
            let noResults = document.getElementById('no-results');

            if (data.data.length === 0) {
                tbody.innerHTML = '';
                noResults.style.display = 'block';
                document.getElementById('pagination').innerHTML = '';
                return;
            }

            noResults.style.display = 'none';
            let start = (data.current_page - 1) * data.per_page;

            tbody.innerHTML = data.data.map((r, i) => `
                <tr onclick="window.location='/admin/medical-records/${r.id}'" style="cursor:pointer">
                    <td>${start + i + 1}</td>
                    <td>${r.center ? esc(r.center.name_{{ app()->getLocale() === 'ar' ? 'ar' : 'en' }}) : '-'}</td>
                    <td>${esc(r.full_name)}</td>
                    <td><span class="fw-semibold">${esc(r.national_id)}</span></td>
                    <td>${r.phone ? esc(r.phone) : '-'}</td>
                    <td>${r.gender ? (genderLabels[r.gender] || r.gender) : '-'}</td>
                    <td>${r.occupation ? esc(r.occupation) : '-'}</td>
                    <td>${r.date_of_birth ? r.date_of_birth.substring(0, 10) : '-'}</td>
                    <td>${r.notes ? esc(r.notes.substring(0, 50)) + (r.notes.length > 50 ? '...' : '') : '-'}</td>
                    <td>${r.creator ? esc(r.creator.name) : '-'}</td>
                    <td>${new Date(r.created_at).toLocaleDateString()}</td>
                </tr>
            `).join('');

            renderPagination(data);
        }

        function renderPagination(data) {
            let container = document.getElementById('pagination');
            if (data.last_page <= 1) { container.innerHTML = ''; return; }

            let current = data.current_page;
            let last = data.last_page;
            let pages = [];
            let delta = 2;

            pages.push(1);

            let rangeStart = Math.max(2, current - delta);
            let rangeEnd = Math.min(last - 1, current + delta);

            if (rangeStart > 2) pages.push('...');
            for (let p = rangeStart; p <= rangeEnd; p++) pages.push(p);
            if (rangeEnd < last - 1) pages.push('...');

            if (last > 1) pages.push(last);

            let prevDisabled = current === 1 ? ' disabled' : '';
            let nextDisabled = current === last ? ' disabled' : '';

            let html = '<nav><ul class="pagination pagination-sm justify-content-center mb-0 flex-wrap">';
            html += `<li class="page-item${prevDisabled}"><a class="page-link" href="#" data-page="${current - 1}">&laquo;</a></li>`;

            pages.forEach(function(p) {
                if (p === '...') {
                    html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
                } else {
                    html += `<li class="page-item ${p === current ? 'active' : ''}"><a class="page-link" href="#" data-page="${p}">${p}</a></li>`;
                }
            });

            html += `<li class="page-item${nextDisabled}"><a class="page-link" href="#" data-page="${current + 1}">&raquo;</a></li>`;
            html += '</ul></nav>';
            html += `<div class="text-center text-muted mt-1" style="font-size:.8rem">${data.from}-${data.to} {{ __('common.of') }} ${data.total}</div>`;
            container.innerHTML = html;

            container.querySelectorAll('[data-page]').forEach(function(link) {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    let page = parseInt(this.dataset.page);
                    if (page >= 1 && page <= last) {
                        currentPage = page;
                        fetchData(currentPage);
                    }
                });
            });
        }

        function esc(text) {
            if (!text) return '';
            let div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    });
</script>
@endpush
