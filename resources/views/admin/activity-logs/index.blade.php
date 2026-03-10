@extends('layouts.app')

@section('title', __('activity_logs.title') . ' - PrimaCare')

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
    .changes-list {
        list-style: none;
        padding: 0;
        margin: 4px 0 0;
        font-size: 12px;
    }
    .changes-list li {
        padding: 2px 0;
        color: #666;
    }
    [data-bs-theme="dark"] .changes-list li {
        color: #aaa;
    }
    .changes-list .field-name {
        font-weight: 600;
        color: #333;
    }
    [data-bs-theme="dark"] .changes-list .field-name {
        color: #ccc;
    }
    .changes-list .old-value {
        color: #dc3545;
        text-decoration: line-through;
    }
    .changes-list .new-value {
        color: #198754;
    }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">{{ __('activity_logs.title') }}</h4>
            <button type="button" class="btn btn-secondary" id="print-btn">
                <i class="ti ti-printer me-1"></i>{{ __('common.print') }}
            </button>
        </div>

        <div class="card position-relative">
            <div class="loading-overlay" id="loading" style="display:none;">
                <div class="spinner-border text-primary" role="status"></div>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <input type="text" id="search" class="form-control" placeholder="{{ __('activity_logs.search') }}" />
                    </div>
                    <div class="col-md-3">
                        <select id="action-filter" class="form-select">
                            <option value="">{{ __('activity_logs.all_actions') }}</option>
                            <option value="created">{{ __('activity_logs.created') }}</option>
                            <option value="updated">{{ __('activity_logs.updated') }}</option>
                            <option value="deleted">{{ __('activity_logs.deleted') }}</option>
                            <option value="impersonated">{{ __('activity_logs.impersonated') }}</option>
                        </select>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>{{ __('activity_logs.date') }}</th>
                                <th>{{ __('activity_logs.user') }}</th>
                                <th>{{ __('activity_logs.action') }}</th>
                                <th>{{ __('activity_logs.details') }}</th>
                                <th>{{ __('activity_logs.ip_address') }}</th>
                            </tr>
                        </thead>
                        <tbody id="logs-table">
                        </tbody>
                    </table>
                </div>

                <div id="no-results" class="text-center py-4 text-muted" style="display:none;">
                    {{ __('activity_logs.no_results') }}
                </div>

                <div id="pagination" class="d-flex justify-content-center mt-3"></div>
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
    let actionLabels = {
        created: '{{ __("activity_logs.created") }}',
        updated: '{{ __("activity_logs.updated") }}',
        deleted: '{{ __("activity_logs.deleted") }}',
        impersonated: '{{ __("activity_logs.impersonated") }}',
        left_impersonation: '{{ __("activity_logs.left_impersonation") }}',
        login: '{{ __("activity_logs.login") }}'
    };

    let fieldLabels = @json(__('activity_logs.fields'));

    document.addEventListener('DOMContentLoaded', function() {
        loadLogs();

        document.getElementById('search').addEventListener('input', function() {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(function() {
                currentPage = 1;
                loadLogs();
            }, 300);
        });

        document.getElementById('action-filter').addEventListener('change', function() {
            currentPage = 1;
            loadLogs();
        });

        document.getElementById('print-btn').addEventListener('click', function() {
            let params = new URLSearchParams();
            let search = document.getElementById('search').value;
            if (search) params.set('search', search);
            let action = document.getElementById('action-filter').value;
            if (action) params.set('action', action);
            window.open(`{{ route('admin.activity-logs.print') }}?${params.toString()}`, '_blank');
        });
    });

    function loadLogs(page) {
        if (page) currentPage = page;
        let search = document.getElementById('search').value;
        let action = document.getElementById('action-filter').value;
        document.getElementById('loading').style.display = 'flex';

        let url = `{{ route('admin.activity-logs.data') }}?page=${currentPage}&search=${encodeURIComponent(search)}`;
        if (action) url += `&action=${action}`;

        pcFetch(url)
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
        let tbody = document.getElementById('logs-table');
        let noResults = document.getElementById('no-results');

        if (data.data.length === 0) {
            tbody.innerHTML = '';
            noResults.style.display = 'block';
            return;
        }

        noResults.style.display = 'none';
        let startIndex = (data.current_page - 1) * data.per_page;
        let html = '';

        data.data.forEach(function(log, index) {
            let date = new Date(log.created_at).toLocaleString(locale === 'ar' ? 'ar-SA' : 'en-US');
            let userName = log.user ? escapeHtml(log.user.name) : '-';
            let actionLabel = actionLabels[log.action] || log.action;

            let actionBadge = 'bg-info-subtle text-info';
            if (log.action === 'created') actionBadge = 'bg-success-subtle text-success';
            if (log.action === 'updated') actionBadge = 'bg-warning-subtle text-warning';
            if (log.action === 'deleted') actionBadge = 'bg-danger-subtle text-danger';

            let details = escapeHtml(log.description || '-');
            let changesHtml = '';

            if (log.action === 'updated' && log.properties && log.properties.changed_fields) {
                changesHtml = '<ul class="changes-list">';
                log.properties.changed_fields.forEach(function(field) {
                    if (field === 'password') {
                        changesHtml += `<li><span class="field-name">${escapeHtml(fieldLabels[field] || field)}</span>: ••••••</li>`;
                        return;
                    }
                    let oldVal = log.properties.old[field] ?? '-';
                    let newVal = log.properties.new[field] ?? '-';
                    let label = fieldLabels[field] || field;
                    changesHtml += `<li><span class="field-name">${escapeHtml(label)}</span>: <span class="old-value">${escapeHtml(String(oldVal))}</span> → <span class="new-value">${escapeHtml(String(newVal))}</span></li>`;
                });
                changesHtml += '</ul>';
            }

            html += `<tr>
                <td>${startIndex + index + 1}</td>
                <td><small>${date}</small></td>
                <td>${userName}</td>
                <td><span class="badge ${actionBadge}">${actionLabel}</span></td>
                <td>${details}${changesHtml}</td>
                <td><small class="text-muted">${log.ip_address || '-'}</small></td>
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
            html += `<li class="page-item"><a class="page-link" href="#" onclick="loadLogs(${data.current_page - 1}); return false;">&laquo;</a></li>`;
        }

        for (let i = 1; i <= data.last_page; i++) {
            if (i === data.current_page) {
                html += `<li class="page-item active"><span class="page-link">${i}</span></li>`;
            } else if (Math.abs(i - data.current_page) <= 2 || i === 1 || i === data.last_page) {
                html += `<li class="page-item"><a class="page-link" href="#" onclick="loadLogs(${i}); return false;">${i}</a></li>`;
            } else if (Math.abs(i - data.current_page) === 3) {
                html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            }
        }

        if (data.current_page < data.last_page) {
            html += `<li class="page-item"><a class="page-link" href="#" onclick="loadLogs(${data.current_page + 1}); return false;">&raquo;</a></li>`;
        }

        html += '</ul></nav>';
        container.innerHTML = html;
    }

    function escapeHtml(text) {
        let div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
</script>
@endpush
