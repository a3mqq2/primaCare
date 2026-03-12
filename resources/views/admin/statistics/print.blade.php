<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8" />
    <title>{{ __('statistics.print_title') }} - PrimaCare</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: "IBM Plex Sans Arabic", "Segoe UI", Tahoma, Arial, sans-serif;
            color: #1a1a1a;
            background: #fff;
            font-size: 13px;
            line-height: 1.7;
            padding: 15mm 15mm 20mm;
        }

        .report-header {
            text-align: center;
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 2px solid #1a1a1a;
        }

        .report-logo { margin-bottom: 12px; }
        .report-logo img { height: 80px; width: auto; }

        .report-header h1 {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 4px;
            letter-spacing: 0.5px;
            color: #000;
        }

        .report-meta {
            font-size: 12px;
            color: #444;
            margin-top: 6px;
            font-weight: 300;
        }

        .report-meta span { margin: 0 10px; }

        .section-title {
            font-size: 15px;
            font-weight: 700;
            margin: 25px 0 10px;
            padding-bottom: 5px;
            border-bottom: 1px solid #ccc;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-bottom: 20px;
        }

        .summary-box {
            border: 1.5px solid #1a1a1a;
            border-radius: 6px;
            padding: 12px;
            text-align: center;
        }

        .summary-box .value {
            font-size: 20px;
            font-weight: 700;
        }

        .summary-box .label {
            font-size: 11px;
            color: #555;
            margin-top: 2px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        thead { display: table-header-group; }
        tbody tr { page-break-inside: avoid; }

        th {
            background: #f0f0f0;
            font-weight: 600;
            font-size: 11.5px;
            padding: 9px 8px;
            border: 1.5px solid #1a1a1a;
            text-align: {{ app()->getLocale() === 'ar' ? 'right' : 'left' }};
        }

        td {
            padding: 7px 8px;
            border: 1px solid #555;
            font-size: 12px;
            vertical-align: top;
        }

        tbody tr:nth-child(even) { background: #f9f9f9; }

        td.num {
            text-align: center;
            font-weight: 600;
            width: 40px;
        }

        .report-footer {
            margin-top: 30px;
            padding-top: 12px;
            border-top: 1px solid #aaa;
            text-align: center;
            font-size: 11px;
            color: #777;
            font-weight: 300;
        }

        @media print {
            body { padding: 10mm; }
            thead { display: table-header-group; }
            tbody tr { page-break-inside: avoid; }
            @page { size: A4 portrait; margin: 10mm; }
        }

        @media screen {
            body { max-width: 900px; margin: 0 auto; padding: 30px; }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="report-header">
        <div class="report-logo">
            <img src="{{ asset('assets/images/primacare-ar.png') }}" alt="PrimaCare" />
        </div>
        <h1>{{ __('statistics.print_title') }}</h1>
        <div class="report-meta">
            <span>{{ __('statistics.print_date') }}: {{ now()->format('Y-m-d H:i') }}</span>
            @if($filters['center'])
                <span>{{ __('statistics.center') }}: {{ $filters['center'] }}</span>
            @endif
            @if($filters['date_from'])
                <span>{{ __('statistics.date_from') }}: {{ $filters['date_from'] }}</span>
            @endif
            @if($filters['date_to'])
                <span>{{ __('statistics.date_to') }}: {{ $filters['date_to'] }}</span>
            @endif
        </div>
    </div>

    {{-- Summary --}}
    <div class="summary-grid">
        <div class="summary-box">
            <div class="value">{{ number_format($summary['total_records']) }}</div>
            <div class="label">{{ __('statistics.total_records') }}</div>
        </div>
        <div class="summary-box">
            <div class="value">{{ number_format($summary['total_dispensings']) }}</div>
            <div class="label">{{ __('statistics.total_dispensings') }}</div>
        </div>
        <div class="summary-box">
            <div class="value">{{ number_format($summary['total_quantity']) }}</div>
            <div class="label">{{ __('statistics.total_quantity') }}</div>
        </div>
        <div class="summary-box">
            <div class="value">{{ number_format($summary['total_medicines']) }}</div>
            <div class="label">{{ __('statistics.total_medicines') }}</div>
        </div>
    </div>

    {{-- Records by Center --}}
    <div class="section-title">{{ __('statistics.records_by_center') }}</div>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>{{ __('statistics.center') }}</th>
                <th>{{ __('statistics.count') }}</th>
                <th>{{ __('statistics.percentage') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($records_by_center as $index => $row)
            <tr>
                <td class="num">{{ $index + 1 }}</td>
                <td>{{ $row['center'] }}</td>
                <td>{{ $row['count'] }}</td>
                <td>{{ $row['percentage'] }}%</td>
            </tr>
            @empty
            <tr>
                <td colspan="4" style="text-align:center; padding:15px;">{{ __('statistics.no_data') }}</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Records by Gender --}}
    <div class="section-title">{{ __('statistics.records_by_gender') }}</div>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>{{ __('statistics.records_by_gender') }}</th>
                <th>{{ __('statistics.count') }}</th>
            </tr>
        </thead>
        <tbody>
            @php
                $genderMap = ['male' => __('statistics.male'), 'female' => __('statistics.female'), 'unknown' => __('statistics.unknown')];
            @endphp
            @forelse($records_by_gender as $index => $row)
            <tr>
                <td class="num">{{ $index + 1 }}</td>
                <td>{{ $genderMap[$row['gender']] ?? $row['gender'] }}</td>
                <td>{{ $row['count'] }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="3" style="text-align:center; padding:15px;">{{ __('statistics.no_data') }}</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Dispensings by Center --}}
    <div class="section-title">{{ __('statistics.dispensings_by_center') }}</div>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>{{ __('statistics.center') }}</th>
                <th>{{ __('statistics.dispensing_count') }}</th>
                <th>{{ __('statistics.quantity') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($dispensings_by_center as $index => $row)
            <tr>
                <td class="num">{{ $index + 1 }}</td>
                <td>{{ $row['center'] }}</td>
                <td>{{ $row['count'] }}</td>
                <td>{{ $row['quantity'] }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="4" style="text-align:center; padding:15px;">{{ __('statistics.no_data') }}</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Top 10 Medicines --}}
    <div class="section-title">{{ __('statistics.top_medicines') }}</div>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>{{ __('statistics.medicine') }}</th>
                <th>{{ __('statistics.dispensing_count') }}</th>
                <th>{{ __('statistics.quantity') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($top_medicines as $index => $row)
            <tr>
                <td class="num">{{ $index + 1 }}</td>
                <td>{{ $row['name'] }}</td>
                <td>{{ $row['dispensing_count'] }}</td>
                <td>{{ $row['total_quantity'] }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="4" style="text-align:center; padding:15px;">{{ __('statistics.no_data') }}</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="report-footer">
        تنفيذ مكتب تقنية المعلومات الصحية بوزارة الصحة الليبية &copy; {{ date('Y') }}
    </div>

</body>
</html>
