@extends('layouts.employee')

@section('title', __('medical_records.title') . ' - PrimaCare')

@push('css')
<link href="{{ asset('assets/plugins/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" />
<style>
    .action-card {
        border: 2px solid transparent;
        border-radius: 1rem;
        padding: 3rem 2rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s;
    }
    .action-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    .action-card-add {
        border-color: rgba(var(--bs-primary-rgb), 0.2);
    }
    .action-card-add:hover {
        border-color: var(--bs-primary);
        background: rgba(var(--bs-primary-rgb), 0.05);
    }
    .action-card-query {
        border-color: rgba(var(--bs-success-rgb), 0.2);
    }
    .action-card-query:hover {
        border-color: var(--bs-success);
        background: rgba(var(--bs-success-rgb), 0.05);
    }
    .action-card-all {
        border-color: rgba(var(--bs-info-rgb), 0.2);
    }
    .action-card-all:hover {
        border-color: var(--bs-info);
        background: rgba(var(--bs-info-rgb), 0.05);
    }
    .action-card i {
        font-size: 4rem;
        display: block;
        margin-bottom: 1rem;
    }
    .action-card h4 {
        margin: 0;
        font-weight: 600;
    }
    .panel { display: none; }
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
    [data-bs-theme="dark"] .action-card:hover {
        box-shadow: 0 10px 30px rgba(0,0,0,0.3);
    }
    .welcome-banner {
        background: #4a6cf7;
        border-radius: 12px;
        padding: 22px 28px;
        display: flex;
        align-items: center;
        gap: 16px;
    }
    .welcome-banner .welcome-avatar {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: rgba(255,255,255,0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .welcome-banner .welcome-avatar i {
        font-size: 22px;
        color: #fff;
    }
    .welcome-banner .greeting {
        font-size: 1.15rem;
        font-weight: 600;
        color: #fff;
        margin-bottom: 2px;
    }
    .welcome-banner .sub-message {
        font-size: 13px;
        color: rgba(255,255,255,0.75);
    }
</style>
@endpush

@section('content')
<div id="main-actions">
    <div class="row justify-content-center" style="margin-top: 8vh;">
        <div class="{{ auth()->user()->isCenterManager() ? 'col-lg-9 col-md-12' : 'col-lg-6 col-md-8' }} mb-4">
            <div class="welcome-banner">
                <div class="welcome-avatar">
                    <i class="ti ti-stethoscope"></i>
                </div>
                <div>
                    <div class="greeting">{{ __('medical_records.welcome_greeting', ['name' => Auth::user()->name]) }}</div>
                    <div class="sub-message">{{ __('medical_records.welcome_message') }}</div>
                </div>
            </div>
        </div>
        <div class="w-100"></div>
        <div class="col-md-4 col-lg-3 mb-3">
            <div class="card action-card action-card-add" id="btn-show-add">
                <i class="ti ti-file-plus text-primary"></i>
                <h4>{{ __('medical_records.add_record') }}</h4>
            </div>
        </div>
        <div class="col-md-4 col-lg-3 mb-3">
            <div class="card action-card action-card-query" id="btn-show-query">
                <i class="ti ti-file-search text-success"></i>
                <h4>{{ __('medical_records.query_record') }}</h4>
            </div>
        </div>
        @if(auth()->user()->isCenterManager())
        <div class="col-md-4 col-lg-3 mb-3">
            <div class="card action-card action-card-all" id="btn-show-all">
                <i class="ti ti-list-details text-info"></i>
                <h4>{{ __('medical_records.all_records') }}</h4>
            </div>
        </div>
        @endif
    </div>
</div>

<div class="panel" id="add-panel">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="mb-0">{{ __('medical_records.add_record') }}</h4>
                <button type="button" class="btn btn-secondary btn-back">
                    <i class="ti ti-arrow-right me-1"></i>{{ __('medical_records.back') }}
                </button>
            </div>
            <div class="card position-relative">
                <div class="loading-overlay" id="add-loading" style="display:none;">
                    <div class="spinner-border text-primary" role="status"></div>
                </div>
                <div class="card-body">
                    <form id="record-form">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('medical_records.full_name') }} <span class="text-danger">*</span></label>
                                <input type="text" name="full_name" id="full_name" class="form-control" />
                                <div class="invalid-feedback" id="error-full_name"></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('medical_records.national_id') }} <span class="text-danger">*</span></label>
                                <input type="text" name="national_id" id="national_id" class="form-control" />
                                <div class="invalid-feedback" id="error-national_id"></div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('medical_records.phone') }}</label>
                                <input type="text" name="phone" id="phone" class="form-control" />
                                <div class="invalid-feedback" id="error-phone"></div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">{{ __('medical_records.gender') }}</label>
                                <select name="gender" id="gender" class="form-select">
                                    <option value="">—</option>
                                    <option value="male">{{ __('medical_records.male') }}</option>
                                    <option value="female">{{ __('medical_records.female') }}</option>
                                </select>
                                <div class="invalid-feedback" id="error-gender"></div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">{{ __('medical_records.occupation') }}</label>
                                <input type="text" name="occupation" id="occupation" class="form-control" />
                                <div class="invalid-feedback" id="error-occupation"></div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">{{ __('medical_records.date_of_birth') }}</label>
                                <input type="date" name="date_of_birth" id="date_of_birth" class="form-control" max="{{ date('Y-m-d') }}" />
                                <div class="invalid-feedback" id="error-date_of_birth"></div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('medical_records.notes') }}</label>
                            <textarea name="notes" id="notes" class="form-control" rows="3"></textarea>
                            <div class="invalid-feedback" id="error-notes"></div>
                        </div>
                        <button type="submit" class="btn btn-primary" id="save-btn">
                            <i class="ti ti-check me-1"></i>{{ __('medical_records.save') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="panel" id="query-panel">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="mb-0">{{ __('medical_records.query_record') }}</h4>
                <button type="button" class="btn btn-secondary btn-back">
                    <i class="ti ti-arrow-right me-1"></i>{{ __('medical_records.back') }}
                </button>
            </div>
            <div class="card mb-3">
                <div class="card-body">
                    <div class="input-group">
                        <input type="text" id="query-input" class="form-control form-control-lg" placeholder="{{ __('medical_records.query_placeholder') }}" />
                        <button class="btn btn-primary" id="query-btn" type="button">
                            <i class="ti ti-search me-1"></i>{{ __('medical_records.search') }}
                        </button>
                    </div>
                </div>
            </div>
            <div class="card position-relative" id="query-results-card" style="display:none;">
                <div class="loading-overlay" id="query-loading" style="display:none;">
                    <div class="spinner-border text-primary" role="status"></div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('medical_records.full_name') }}</th>
                                    <th>{{ __('medical_records.national_id') }}</th>
                                    <th>{{ __('medical_records.phone') }}</th>
                                    <th>{{ __('medical_records.created_at') }}</th>
                                </tr>
                            </thead>
                            <tbody id="query-table"></tbody>
                        </table>
                    </div>
                    <div id="query-no-results" class="text-center py-4 text-muted" style="display:none;">
                        <i class="ti ti-search-off fs-1 d-block mb-2"></i>
                        {{ __('medical_records.no_results') }}
                        <div class="mt-3">
                            <button type="button" class="btn btn-primary" id="btn-add-from-query">
                                <i class="ti ti-file-plus me-1"></i>{{ __('medical_records.add_record') }}
                            </button>
                        </div>
                    </div>
                    <div id="query-pagination" class="mt-3"></div>
                </div>
            </div>
        </div>
    </div>
</div>

@if(auth()->user()->isCenterManager())
<div class="panel" id="all-panel">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="mb-0">{{ __('medical_records.all_records') }}</h4>
                <button type="button" class="btn btn-secondary btn-back">
                    <i class="ti ti-arrow-right me-1"></i>{{ __('medical_records.back') }}
                </button>
            </div>
            <div class="card mb-3">
                <div class="card-body">
                    <input type="text" id="filter-input" class="form-control" placeholder="{{ __('medical_records.filter_placeholder') }}" />
                </div>
            </div>
            <div class="card position-relative">
                <div class="loading-overlay" id="all-loading" style="display:none;">
                    <div class="spinner-border text-primary" role="status"></div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('medical_records.full_name') }}</th>
                                    <th>{{ __('medical_records.national_id') }}</th>
                                    <th>{{ __('medical_records.phone') }}</th>
                                    <th>{{ __('medical_records.created_by') }}</th>
                                    <th>{{ __('medical_records.created_at') }}</th>
                                </tr>
                            </thead>
                            <tbody id="all-table"></tbody>
                        </table>
                    </div>
                    <div id="all-no-results" class="text-center py-4 text-muted" style="display:none;">
                        <i class="ti ti-database-off fs-1 d-block mb-2"></i>
                        {{ __('medical_records.no_results') }}
                    </div>
                    <div id="all-pagination" class="mt-3"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@push('js')
<script src="{{ asset('assets/plugins/sweetalert2/sweetalert2.min.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        let csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        let mainActions = document.getElementById('main-actions');
        let panels = document.querySelectorAll('.panel');

        function showPanel(id) {
            mainActions.style.display = 'none';
            panels.forEach(p => p.style.display = 'none');
            document.getElementById(id).style.display = 'block';
        }

        document.querySelectorAll('.btn-back').forEach(btn => {
            btn.addEventListener('click', function() {
                panels.forEach(p => p.style.display = 'none');
                mainActions.style.display = 'block';
            });
        });

        document.getElementById('btn-show-add').addEventListener('click', () => showPanel('add-panel'));
        document.getElementById('btn-add-from-query').addEventListener('click', () => showPanel('add-panel'));
        document.getElementById('btn-show-query').addEventListener('click', function() {
            showPanel('query-panel');
            document.getElementById('query-input').focus();
        });

        let queryPage = 1;

        document.getElementById('query-btn').addEventListener('click', () => { queryPage = 1; queryRecords(); });
        document.getElementById('query-input').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') { queryPage = 1; queryRecords(); }
        });

        function queryRecords(page) {
            let search = document.getElementById('query-input').value;
            if (!search.trim()) return;

            page = page || queryPage;
            document.getElementById('query-results-card').style.display = 'block';
            document.getElementById('query-loading').style.display = 'flex';

            fetch(`{{ route('medical-records.search') }}?search=${encodeURIComponent(search)}&page=${page}`, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(data => {
                document.getElementById('query-loading').style.display = 'none';
                if (data.total === 1) {
                    window.location.href = '/medical-records/' + data.data[0].id;
                    return;
                }
                renderTable(data, 'query-table', 'query-no-results', 'query-pagination', false, function(p) { queryPage = p; queryRecords(p); });
            })
            .catch(() => { document.getElementById('query-loading').style.display = 'none'; });
        }

        @if(auth()->user()->isCenterManager())
        let allPage = 1;
        let filterTimer = null;

        document.getElementById('btn-show-all').addEventListener('click', function() {
            showPanel('all-panel');
            allPage = 1;
            fetchAllRecords();
        });

        document.getElementById('filter-input').addEventListener('input', function() {
            clearTimeout(filterTimer);
            filterTimer = setTimeout(function() { allPage = 1; fetchAllRecords(); }, 400);
        });

        function fetchAllRecords(page) {
            page = page || allPage;
            let search = document.getElementById('filter-input').value;
            document.getElementById('all-loading').style.display = 'flex';

            fetch(`{{ route('medical-records.data') }}?search=${encodeURIComponent(search)}&page=${page}`, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(data => {
                document.getElementById('all-loading').style.display = 'none';
                renderTable(data, 'all-table', 'all-no-results', 'all-pagination', true, function(p) { allPage = p; fetchAllRecords(p); });
            })
            .catch(() => { document.getElementById('all-loading').style.display = 'none'; });
        }
        @endif

        document.getElementById('record-form').addEventListener('submit', function(e) {
            e.preventDefault();
            clearErrors();
            let btn = document.getElementById('save-btn');
            btn.disabled = true;
            document.getElementById('add-loading').style.display = 'flex';

            let payload = {
                full_name: document.getElementById('full_name').value,
                national_id: document.getElementById('national_id').value,
                phone: document.getElementById('phone').value,
                gender: document.getElementById('gender').value || null,
                occupation: document.getElementById('occupation').value || null,
                date_of_birth: document.getElementById('date_of_birth').value || null,
                notes: document.getElementById('notes').value,
            };

            fetch('{{ route("medical-records.store") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(payload)
            })
            .then(response => response.json().then(data => ({status: response.status, body: data})))
            .then(({status, body}) => {
                btn.disabled = false;
                document.getElementById('add-loading').style.display = 'none';
                if (status === 422) { showErrors(body.errors); return; }
                if (body.success) {
                    document.getElementById('record-form').reset();
                    Swal.fire({ icon: 'success', title: body.message, showConfirmButton: false, timer: 1500 })
                        .then(() => { window.location.href = '/medical-records/' + body.record.id; });
                }
            })
            .catch(() => { btn.disabled = false; document.getElementById('add-loading').style.display = 'none'; });
        });

        function renderTable(data, tbodyId, noResultsId, paginationId, showCreator, onPageClick) {
            let tbody = document.getElementById(tbodyId);
            let noResults = document.getElementById(noResultsId);

            if (data.data.length === 0) {
                tbody.innerHTML = '';
                noResults.style.display = 'block';
                document.getElementById(paginationId).innerHTML = '';
                return;
            }

            noResults.style.display = 'none';
            let start = (data.current_page - 1) * data.per_page;
            tbody.innerHTML = data.data.map((record, i) => `
                <tr onclick="window.location='/medical-records/${record.id}'" style="cursor:pointer">
                    <td>${start + i + 1}</td>
                    <td>${escapeHtml(record.full_name)}</td>
                    <td><span class="fw-semibold">${escapeHtml(record.national_id)}</span></td>
                    <td>${record.phone ? escapeHtml(record.phone) : '-'}</td>
                    ${showCreator ? `<td>${record.creator ? escapeHtml(record.creator.name) : '-'}</td>` : ''}
                    <td>${new Date(record.created_at).toLocaleDateString()}</td>
                </tr>
            `).join('');

            let container = document.getElementById(paginationId);
            if (data.last_page <= 1) { container.innerHTML = ''; return; }
            let html = '<nav><ul class="pagination pagination-sm justify-content-center mb-0">';
            for (let p = 1; p <= data.last_page; p++) {
                html += `<li class="page-item ${p === data.current_page ? 'active' : ''}"><a class="page-link" href="#" data-page="${p}">${p}</a></li>`;
            }
            html += '</ul></nav>';
            container.innerHTML = html;
            container.querySelectorAll('[data-page]').forEach(link => {
                link.addEventListener('click', function(e) { e.preventDefault(); onPageClick(parseInt(this.dataset.page)); });
            });
        }

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

        function escapeHtml(text) {
            let div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    });
</script>
@endpush
