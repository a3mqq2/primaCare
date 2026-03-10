@extends('layouts.app')

@section('title', __('statistics.title') . ' - PrimaCare')

@push('css')
<link href="{{ asset('assets/plugins/choices/choices.min.css') }}" rel="stylesheet" />
<style>
    .stat-card {
        border: none;
        border-radius: .5rem;
        transition: transform .15s;
    }
    .stat-card:hover {
        transform: translateY(-2px);
    }
    .stat-card .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: .5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
    }
    .stat-card .stat-value {
        font-size: 1.6rem;
        font-weight: 700;
        line-height: 1.2;
    }
    .stat-card .stat-label {
        font-size: .8rem;
        opacity: .7;
        margin-top: 2px;
    }
    .chart-card {
        min-height: 380px;
    }
    .date-toggle .btn {
        padding: 3px 12px;
        font-size: .78rem;
    }
    .loading-overlay {
        position: fixed;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(255,255,255,0.6);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1050;
    }
    [data-bs-theme="dark"] .loading-overlay {
        background: rgba(0,0,0,0.5);
    }
    .support-table th {
        font-size: .78rem;
        font-weight: 600;
    }
    .support-table td {
        font-size: .8rem;
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
<div class="loading-overlay" id="loading" style="display:none;">
    <div class="spinner-border text-primary" role="status"></div>
</div>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">{{ __('statistics.title') }}</h4>
            <button type="button" class="btn btn-secondary no-print" id="print-btn">
                <i class="ti ti-printer me-1"></i>{{ __('statistics.print') }}
            </button>
        </div>

        <div class="card mb-3 no-print">
            <div class="card-body">
                <div class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label mb-1 small">{{ __('statistics.center') }}</label>
                        <select id="filter-center" class="form-select form-select-sm">
                            <option value="">{{ __('statistics.all_centers') }}</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label mb-1 small">{{ __('statistics.date_from') }}</label>
                        <input type="date" id="filter-date-from" class="form-control form-control-sm" />
                    </div>
                    <div class="col-md-3">
                        <label class="form-label mb-1 small">{{ __('statistics.date_to') }}</label>
                        <input type="date" id="filter-date-to" class="form-control form-control-sm" />
                    </div>
                    <div class="col-md-3">
                        <button type="button" class="btn btn-outline-secondary btn-sm w-100" id="reset-btn">
                            <i class="ti ti-refresh me-1"></i>{{ __('statistics.reset_filters') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-3">
                <div class="card stat-card h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                            <i class="ti ti-report-medical"></i>
                        </div>
                        <div>
                            <div class="stat-value" id="total-records">0</div>
                            <div class="stat-label">{{ __('statistics.total_records') }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="stat-icon bg-success bg-opacity-10 text-success">
                            <i class="ti ti-vaccine"></i>
                        </div>
                        <div>
                            <div class="stat-value" id="total-dispensings">0</div>
                            <div class="stat-label">{{ __('statistics.total_dispensings') }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                            <i class="ti ti-packages"></i>
                        </div>
                        <div>
                            <div class="stat-value" id="total-quantity">0</div>
                            <div class="stat-label">{{ __('statistics.total_quantity') }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="stat-icon bg-info bg-opacity-10 text-info">
                            <i class="ti ti-pill"></i>
                        </div>
                        <div>
                            <div class="stat-value" id="total-medicines">0</div>
                            <div class="stat-label">{{ __('statistics.total_medicines') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-lg-8">
                <div class="card chart-card">
                    <div class="card-header">
                        <h6 class="card-title mb-0">{{ __('statistics.records_by_center') }}</h6>
                    </div>
                    <div class="card-body">
                        <div id="chart-records-center" style="min-height:300px;"></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card chart-card">
                    <div class="card-header">
                        <h6 class="card-title mb-0">{{ __('statistics.records_by_gender') }}</h6>
                    </div>
                    <div class="card-body">
                        <div id="chart-records-gender" style="min-height:300px;"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="card-title mb-0">{{ __('statistics.records_by_date') }}</h6>
                        <div class="btn-group btn-group-sm date-toggle no-print" role="group">
                            <button type="button" class="btn btn-outline-primary active" data-period="monthly">{{ __('statistics.monthly') }}</button>
                            <button type="button" class="btn btn-outline-primary" data-period="daily">{{ __('statistics.daily') }}</button>
                            <button type="button" class="btn btn-outline-primary" data-period="yearly">{{ __('statistics.yearly') }}</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="chart-records-date" style="min-height:300px;"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-lg-6">
                <div class="card chart-card">
                    <div class="card-header">
                        <h6 class="card-title mb-0">{{ __('statistics.dispensings_by_center') }}</h6>
                    </div>
                    <div class="card-body">
                        <div id="chart-dispensings-center" style="min-height:300px;"></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card chart-card">
                    <div class="card-header">
                        <h6 class="card-title mb-0">{{ __('statistics.top_medicines') }}</h6>
                    </div>
                    <div class="card-body">
                        <div id="chart-top-medicines" style="min-height:300px;"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h6 class="card-title mb-0">{{ __('statistics.records_by_center') }}</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover support-table mb-0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>{{ __('statistics.center') }}</th>
                                        <th>{{ __('statistics.count') }}</th>
                                        <th>{{ __('statistics.percentage') }}</th>
                                    </tr>
                                </thead>
                                <tbody id="table-records-center"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h6 class="card-title mb-0">{{ __('statistics.top_medicines') }}</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover support-table mb-0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>{{ __('statistics.medicine') }}</th>
                                        <th>{{ __('statistics.dispensing_count') }}</th>
                                        <th>{{ __('statistics.quantity') }}</th>
                                    </tr>
                                </thead>
                                <tbody id="table-top-medicines"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('js')
<script src="{{ asset('assets/plugins/apexcharts/apexcharts.min.js') }}"></script>
<script src="{{ asset('assets/plugins/choices/choices.min.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentPeriod = 'monthly';
    let statsData = null;
    let isLoading = false;

    const isDark = () => document.documentElement.getAttribute('data-bs-theme') === 'dark';
    const textColor = () => isDark() ? '#adb5bd' : '#6c757d';
    const gridColor = () => isDark() ? '#2a2f34' : '#e9ecef';

    const genderLabels = {
        male: '{{ __("statistics.male") }}',
        female: '{{ __("statistics.female") }}',
        unknown: '{{ __("statistics.unknown") }}'
    };

    const chartColors = ['#3b7ddd', '#28a745', '#ffc107', '#dc3545', '#17a2b8', '#6f42c1', '#fd7e14', '#20c997', '#e83e8c', '#6610f2'];

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

    let chartRecordsCenter = new ApexCharts(document.getElementById('chart-records-center'), getBarOptions('{{ __("statistics.records") }}'));
    let chartRecordsGender = new ApexCharts(document.getElementById('chart-records-gender'), getDonutOptions());
    let chartRecordsDate = new ApexCharts(document.getElementById('chart-records-date'), getAreaOptions());
    let chartDispensingsCenter = new ApexCharts(document.getElementById('chart-dispensings-center'), getBarOptions('{{ __("statistics.operations") }}'));
    let chartTopMedicines = new ApexCharts(document.getElementById('chart-top-medicines'), getHorizontalBarOptions());

    chartRecordsCenter.render();
    chartRecordsGender.render();
    chartRecordsDate.render();
    chartDispensingsCenter.render();
    chartTopMedicines.render();

    fetchStats();

    document.getElementById('filter-center').addEventListener('change', fetchStats);
    document.getElementById('filter-date-from').addEventListener('change', fetchStats);
    document.getElementById('filter-date-to').addEventListener('change', fetchStats);

    document.getElementById('reset-btn').addEventListener('click', function() {
        centerChoices.removeActiveItems();
        document.getElementById('filter-date-from').value = '';
        document.getElementById('filter-date-to').value = '';
        fetchStats();
    });

    document.getElementById('print-btn').addEventListener('click', function() {
        window.open(`{{ route('admin.statistics.print') }}?${buildParams()}`, '_blank');
    });

    document.querySelectorAll('.date-toggle [data-period]').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.date-toggle [data-period]').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentPeriod = this.dataset.period;
            if (statsData) updateDateChart(statsData.records_by_date);
        });
    });

    new MutationObserver(function() {
        if (statsData) renderAllCharts(statsData);
    }).observe(document.documentElement, { attributes: true, attributeFilter: ['data-bs-theme'] });

    function buildParams() {
        let params = new URLSearchParams();
        let center = document.getElementById('filter-center').value;
        if (center) params.set('center_id', center);
        let dateFrom = document.getElementById('filter-date-from').value;
        if (dateFrom) params.set('date_from', dateFrom);
        let dateTo = document.getElementById('filter-date-to').value;
        if (dateTo) params.set('date_to', dateTo);
        return params.toString();
    }

    function fetchStats() {
        if (isLoading) return;
        isLoading = true;
        document.getElementById('loading').style.display = 'flex';

        pcFetch(`{{ route('admin.statistics.data') }}?${buildParams()}`, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => {
            isLoading = false;
            document.getElementById('loading').style.display = 'none';
            statsData = data;
            updateSummary(data.summary);
            renderAllCharts(data);
            updateTables(data);
        })
        .catch(function() {
            isLoading = false;
            document.getElementById('loading').style.display = 'none';
            Swal.fire({ icon: 'error', title: window.PrimaCare.messages.error });
        });
    }

    function updateSummary(s) {
        document.getElementById('total-records').textContent = s.total_records.toLocaleString();
        document.getElementById('total-dispensings').textContent = s.total_dispensings.toLocaleString();
        document.getElementById('total-quantity').textContent = s.total_quantity.toLocaleString();
        document.getElementById('total-medicines').textContent = s.total_medicines.toLocaleString();
    }

    function renderAllCharts(data) {
        chartRecordsCenter.updateOptions({
            xaxis: {
                categories: data.records_by_center.map(r => r.center),
                labels: { style: { colors: textColor() } }
            },
            yaxis: { labels: { style: { colors: textColor() } } },
            grid: { borderColor: gridColor() },
            tooltip: { theme: isDark() ? 'dark' : 'light' }
        });
        chartRecordsCenter.updateSeries([{
            name: '{{ __("statistics.records") }}',
            data: data.records_by_center.map(r => r.count)
        }]);

        let genderData = data.records_by_gender;
        chartRecordsGender.updateOptions({
            labels: genderData.map(g => genderLabels[g.gender] || g.gender),
            legend: { labels: { colors: textColor() } },
            tooltip: { theme: isDark() ? 'dark' : 'light' }
        });
        chartRecordsGender.updateSeries(genderData.map(g => g.count));

        updateDateChart(data.records_by_date);

        chartDispensingsCenter.updateOptions({
            xaxis: {
                categories: data.dispensings_by_center.map(r => r.center),
                labels: { style: { colors: textColor() } }
            },
            yaxis: { labels: { style: { colors: textColor() } } },
            grid: { borderColor: gridColor() },
            tooltip: { theme: isDark() ? 'dark' : 'light' }
        });
        chartDispensingsCenter.updateSeries([{
            name: '{{ __("statistics.operations") }}',
            data: data.dispensings_by_center.map(r => r.count)
        }]);

        chartTopMedicines.updateOptions({
            xaxis: {
                categories: data.top_medicines.map(m => m.name),
                labels: { style: { colors: textColor() } }
            },
            yaxis: { labels: { style: { colors: textColor() } } },
            grid: { borderColor: gridColor() },
            tooltip: { theme: isDark() ? 'dark' : 'light' }
        });
        chartTopMedicines.updateSeries([{
            name: '{{ __("statistics.quantity") }}',
            data: data.top_medicines.map(m => m.total_quantity)
        }]);
    }

    function updateDateChart(dateData) {
        let series = dateData[currentPeriod] || [];
        chartRecordsDate.updateOptions({
            xaxis: {
                categories: series.map(s => s.date),
                labels: { style: { colors: textColor() }, rotate: -45, rotateAlways: series.length > 15 }
            },
            yaxis: { labels: { style: { colors: textColor() } } },
            grid: { borderColor: gridColor() },
            tooltip: { theme: isDark() ? 'dark' : 'light' }
        });
        chartRecordsDate.updateSeries([{
            name: '{{ __("statistics.records") }}',
            data: series.map(s => s.count)
        }]);
    }

    function updateTables(data) {
        let tbody1 = document.getElementById('table-records-center');
        if (data.records_by_center.length === 0) {
            tbody1.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-3">{{ __("statistics.no_data") }}</td></tr>';
        } else {
            tbody1.innerHTML = data.records_by_center.map((r, i) =>
                `<tr><td>${i+1}</td><td>${esc(r.center)}</td><td>${r.count}</td><td>${r.percentage}%</td></tr>`
            ).join('');
        }

        let tbody2 = document.getElementById('table-top-medicines');
        if (data.top_medicines.length === 0) {
            tbody2.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-3">{{ __("statistics.no_data") }}</td></tr>';
        } else {
            tbody2.innerHTML = data.top_medicines.map((m, i) =>
                `<tr><td>${i+1}</td><td>${esc(m.name)}</td><td>${m.dispensing_count}</td><td>${m.total_quantity}</td></tr>`
            ).join('');
        }
    }

    function getBarOptions(seriesName) {
        return {
            chart: { type: 'bar', height: 300, toolbar: { show: false }, fontFamily: 'inherit' },
            series: [{ name: seriesName, data: [] }],
            colors: chartColors,
            plotOptions: { bar: { borderRadius: 4, columnWidth: '55%', distributed: true } },
            dataLabels: { enabled: false },
            xaxis: { categories: [], labels: { style: { colors: textColor(), fontSize: '11px' } } },
            yaxis: { labels: { style: { colors: textColor() } } },
            grid: { borderColor: gridColor() },
            legend: { show: false },
            tooltip: { theme: isDark() ? 'dark' : 'light' }
        };
    }

    function getDonutOptions() {
        return {
            chart: { type: 'donut', height: 300, fontFamily: 'inherit' },
            series: [],
            labels: [],
            colors: ['#3b7ddd', '#dc3545', '#adb5bd'],
            legend: { position: 'bottom', labels: { colors: textColor() } },
            dataLabels: { enabled: true, formatter: function(val) { return Math.round(val) + '%'; } },
            tooltip: { theme: isDark() ? 'dark' : 'light' },
            plotOptions: { pie: { donut: { size: '55%' } } }
        };
    }

    function getAreaOptions() {
        return {
            chart: { type: 'area', height: 300, toolbar: { show: false }, fontFamily: 'inherit' },
            series: [{ name: '{{ __("statistics.records") }}', data: [] }],
            colors: ['#3b7ddd'],
            stroke: { curve: 'smooth', width: 2 },
            fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.1 } },
            dataLabels: { enabled: false },
            xaxis: { categories: [], labels: { style: { colors: textColor(), fontSize: '11px' } } },
            yaxis: { labels: { style: { colors: textColor() } } },
            grid: { borderColor: gridColor() },
            tooltip: { theme: isDark() ? 'dark' : 'light' }
        };
    }

    function getHorizontalBarOptions() {
        return {
            chart: { type: 'bar', height: 300, toolbar: { show: false }, fontFamily: 'inherit' },
            series: [{ name: '{{ __("statistics.quantity") }}', data: [] }],
            colors: ['#28a745'],
            plotOptions: { bar: { borderRadius: 4, horizontal: true, barHeight: '60%' } },
            dataLabels: { enabled: false },
            xaxis: { categories: [], labels: { style: { colors: textColor() } } },
            yaxis: { labels: { style: { colors: textColor(), fontSize: '11px' } } },
            grid: { borderColor: gridColor() },
            tooltip: { theme: isDark() ? 'dark' : 'light' }
        };
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
