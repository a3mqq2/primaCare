@extends('layouts.app')

@section('title', __('dashboard.daily_dashboard') . ' - PrimaCare')

@push('css')
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
        width: 50px;
        height: 80px;
        border-radius: .5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        flex-shrink: 0;
    }
    .stat-card .stat-value {
        font-size: 1.75rem;
        font-weight: 700;
        line-height: 1.2;
    }
    .stat-card .stat-label {
        font-size: .82rem;
        opacity: .65;
        margin-top: 2px;
    }
    .stat-card .stat-change {
        font-size: .75rem;
        margin-top: 4px;
    }
    .period-btn.active {
        font-weight: 600;
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
    .table-dashboard th {
        font-size: .78rem;
        font-weight: 600;
    }
    .table-dashboard td {
        font-size: .82rem;
    }
</style>
@endpush

@section('content')
<div class="loading-overlay" id="loading" style="display:none;">
    <div class="spinner-border text-primary" role="status"></div>
</div>

<div class="row">
    <div class="col-12">
        {{-- Header with period filter --}}
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <h4 class="mb-0">{{ __('dashboard.daily_dashboard') }}</h4>
            <div class="btn-group btn-group-sm" role="group">
                <button type="button" class="btn btn-outline-primary period-btn active" data-period="today">{{ __('dashboard.today') }}</button>
                <button type="button" class="btn btn-outline-primary period-btn" data-period="yesterday">{{ __('dashboard.yesterday') }}</button>
                <button type="button" class="btn btn-outline-primary period-btn" data-period="week">{{ __('dashboard.last_7_days') }}</button>
                <button type="button" class="btn btn-outline-primary period-btn" data-period="month">{{ __('dashboard.this_month') }}</button>
            </div>
        </div>

        {{-- Summary Cards --}}
        <div class="row g-3 mb-3">
            <div class="col-md-6 col-xl-3">
                <div class="card stat-card h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                            <i class="ti ti-report-medical"></i>
                        </div>
                        <div>
                            <div class="stat-value" id="val-records">0</div>
                            <div class="stat-label">{{ __('dashboard.records_today') }}</div>
                            <div class="stat-change" id="change-records"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card stat-card h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="stat-icon bg-success bg-opacity-10 text-success">
                            <i class="ti ti-vaccine"></i>
                        </div>
                        <div>
                            <div class="stat-value" id="val-dispensings">0</div>
                            <div class="stat-label">{{ __('dashboard.dispensings_today') }}</div>
                            <div class="stat-change" id="change-dispensings"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card stat-card h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="stat-icon bg-info bg-opacity-10 text-info">
                            <i class="ti ti-user-plus"></i>
                        </div>
                        <div>
                            <div class="stat-value" id="val-patients">0</div>
                            <div class="stat-label">{{ __('dashboard.new_patients') }}</div>
                            <div class="stat-change" id="change-patients"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card stat-card h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                            <i class="ti ti-building-hospital"></i>
                        </div>
                        <div>
                            <div class="stat-value" id="val-centers">0</div>
                            <div class="stat-label">{{ __('dashboard.active_centers') }}</div>
                            <div class="stat-change" id="change-centers"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Charts Row --}}
        <div class="row g-3 mb-3">
            <div class="col-lg-8">
                <div class="card h-100">
                    <div class="card-header">
                        <h6 class="card-title mb-0" id="activity-title">{{ __('dashboard.hourly_activity') }}</h6>
                    </div>
                    <div class="card-body">
                        <div id="chart-activity" style="min-height:300px;"></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h6 class="card-title mb-0">{{ __('dashboard.gender_distribution') }}</h6>
                    </div>
                    <div class="card-body">
                        <div id="chart-gender" style="min-height:300px;"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tables Row --}}
        <div class="row g-3">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h6 class="card-title mb-0">{{ __('dashboard.most_active_centers') }}</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover table-dashboard mb-0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>{{ __('dashboard.center_name') }}</th>
                                        <th>{{ __('dashboard.records_count') }}</th>
                                        <th>{{ __('dashboard.dispensings_count') }}</th>
                                    </tr>
                                </thead>
                                <tbody id="table-centers"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h6 class="card-title mb-0">{{ __('dashboard.top_medicines_today') }}</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover table-dashboard mb-0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>{{ __('dashboard.medicine_name') }}</th>
                                        <th>{{ __('dashboard.times_dispensed') }}</th>
                                        <th>{{ __('dashboard.total_qty') }}</th>
                                    </tr>
                                </thead>
                                <tbody id="table-medicines"></tbody>
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
<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentPeriod = 'today';
    let isLoading = false;

    const isDark = () => document.documentElement.getAttribute('data-bs-theme') === 'dark';
    const textColor = () => isDark() ? '#adb5bd' : '#6c757d';
    const gridColor = () => isDark() ? '#2a2f34' : '#e9ecef';

    const genderLabels = {
        male: '{{ __("dashboard.male") }}',
        female: '{{ __("dashboard.female") }}',
        unknown: '{{ __("dashboard.unknown_gender") }}'
    };

    const noChangeText = '{{ __("dashboard.no_change") }}';
    const noActivityText = '{{ __("dashboard.no_activity") }}';
    const hourlyTitle = '{{ __("dashboard.hourly_activity") }}';
    const dailyTitle = '{{ __("dashboard.daily_activity") }}';

    // Init charts
    let chartActivity = new ApexCharts(document.getElementById('chart-activity'), {
        chart: { type: 'area', height: 300, toolbar: { show: false }, fontFamily: 'inherit' },
        series: [
            { name: '{{ __("dashboard.records_count") }}', data: [] },
            { name: '{{ __("dashboard.dispensings_count") }}', data: [] }
        ],
        colors: ['#3b7ddd', '#28a745'],
        stroke: { curve: 'smooth', width: 2 },
        fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.35, opacityTo: 0.05 } },
        dataLabels: { enabled: false },
        xaxis: { categories: [], labels: { style: { colors: textColor(), fontSize: '10px' }, rotate: -45 } },
        yaxis: { labels: { style: { colors: textColor() } }, min: 0 },
        grid: { borderColor: gridColor() },
        legend: { position: 'top', labels: { colors: textColor() } },
        tooltip: { theme: isDark() ? 'dark' : 'light' }
    });

    let chartGender = new ApexCharts(document.getElementById('chart-gender'), {
        chart: { type: 'donut', height: 300, fontFamily: 'inherit' },
        series: [],
        labels: [],
        colors: ['#3b7ddd', '#dc3545', '#adb5bd'],
        legend: { position: 'bottom', labels: { colors: textColor() } },
        dataLabels: { enabled: true, formatter: function(val) { return Math.round(val) + '%'; } },
        tooltip: { theme: isDark() ? 'dark' : 'light' },
        plotOptions: { pie: { donut: { size: '55%' } } }
    });

    chartActivity.render();
    chartGender.render();

    // Fetch on load
    fetchDashboard();

    // Period buttons
    document.querySelectorAll('.period-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.period-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentPeriod = this.dataset.period;
            fetchDashboard();
        });
    });

    // Theme change observer
    new MutationObserver(function() {
        if (!isLoading) fetchDashboard();
    }).observe(document.documentElement, { attributes: true, attributeFilter: ['data-bs-theme'] });

    function fetchDashboard() {
        if (isLoading) return;
        isLoading = true;
        document.getElementById('loading').style.display = 'flex';

        pcFetch(`{{ route('dashboard.data') }}?period=${currentPeriod}`)
        .then(r => r.json())
        .then(data => {
            isLoading = false;
            document.getElementById('loading').style.display = 'none';
            updateCards(data.summary);
            updateActivityChart(data.hourly_activity, data.use_hourly);
            updateGenderChart(data.gender_distribution);
            updateCentersTable(data.active_centers);
            updateMedicinesTable(data.top_medicines);
        })
        .catch(() => {
            isLoading = false;
            document.getElementById('loading').style.display = 'none';
        });
    }

    function updateCards(summary) {
        setCard('records', summary.records);
        setCard('dispensings', summary.dispensings);
        setCard('patients', summary.new_patients);
        setCard('centers', summary.active_centers);
    }

    function setCard(key, stat) {
        document.getElementById('val-' + key).textContent = stat.current.toLocaleString();
        let changeEl = document.getElementById('change-' + key);

        if (stat.change > 0) {
            changeEl.innerHTML = `<span class="text-success"><i class="ti ti-arrow-up fs-12"></i> ${stat.change}%</span>`;
        } else if (stat.change < 0) {
            changeEl.innerHTML = `<span class="text-danger"><i class="ti ti-arrow-down fs-12"></i> ${Math.abs(stat.change)}%</span>`;
        } else {
            changeEl.innerHTML = `<span class="text-muted">${noChangeText}</span>`;
        }
    }

    function updateActivityChart(activity, useHourly) {
        document.getElementById('activity-title').textContent = useHourly ? hourlyTitle : dailyTitle;

        chartActivity.updateOptions({
            xaxis: {
                categories: activity.map(a => a.label),
                labels: {
                    style: { colors: textColor(), fontSize: '10px' },
                    rotate: activity.length > 15 ? -45 : 0,
                    rotateAlways: activity.length > 15
                }
            },
            yaxis: { labels: { style: { colors: textColor() } }, min: 0 },
            grid: { borderColor: gridColor() },
            legend: { labels: { colors: textColor() } },
            tooltip: { theme: isDark() ? 'dark' : 'light' }
        });

        chartActivity.updateSeries([
            { name: '{{ __("dashboard.records_count") }}', data: activity.map(a => a.records) },
            { name: '{{ __("dashboard.dispensings_count") }}', data: activity.map(a => a.dispensings) }
        ]);
    }

    function updateGenderChart(genderData) {
        if (genderData.length === 0) {
            chartGender.updateSeries([]);
            chartGender.updateOptions({
                labels: [],
                noData: { text: noActivityText }
            });
            return;
        }

        chartGender.updateOptions({
            labels: genderData.map(g => genderLabels[g.gender] || g.gender),
            legend: { labels: { colors: textColor() } },
            tooltip: { theme: isDark() ? 'dark' : 'light' }
        });
        chartGender.updateSeries(genderData.map(g => g.count));
    }

    function updateCentersTable(centers) {
        let tbody = document.getElementById('table-centers');
        if (centers.length === 0) {
            tbody.innerHTML = `<tr><td colspan="4" class="text-center text-muted py-3">${noActivityText}</td></tr>`;
            return;
        }
        tbody.innerHTML = centers.map((c, i) =>
            `<tr><td>${i+1}</td><td>${esc(c.center)}</td><td>${c.records}</td><td>${c.dispensings}</td></tr>`
        ).join('');
    }

    function updateMedicinesTable(medicines) {
        let tbody = document.getElementById('table-medicines');
        if (medicines.length === 0) {
            tbody.innerHTML = `<tr><td colspan="4" class="text-center text-muted py-3">${noActivityText}</td></tr>`;
            return;
        }
        tbody.innerHTML = medicines.map((m, i) =>
            `<tr><td>${i+1}</td><td>${esc(m.name)}</td><td>${m.dispensing_count}</td><td>${m.total_quantity}</td></tr>`
        ).join('');
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
