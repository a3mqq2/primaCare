<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8" />
    <title>{{ __('medicines.print_title') }} - PrimaCare</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

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

        .report-logo {
            margin-bottom: 12px;
        }

        .report-logo img {
            height: 120px;
            width: auto;
        }

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

        .report-meta span {
            margin: 0 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            table-layout: fixed;
        }

        thead {
            display: table-header-group;
        }

        tbody tr {
            page-break-inside: avoid;
        }

        th {
            background: #f0f0f0;
            font-weight: 600;
            font-size: 10px;
            letter-spacing: 0.3px;
            padding: 6px 4px;
            border: 1.5px solid #1a1a1a;
            text-align: {{ app()->getLocale() === 'ar' ? 'right' : 'left' }};
            white-space: nowrap;
        }

        td {
            padding: 5px 4px;
            border: 1px solid #555;
            font-size: 10px;
            vertical-align: top;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        tbody tr:nth-child(even) {
            background: #f9f9f9;
        }

        td.num {
            text-align: center;
            font-weight: 600;
            width: 30px;
        }

        td.center-col {
            text-align: center;
        }

        td.date-col {
            white-space: nowrap;
            text-align: center;
        }

        .total-count {
            text-align: {{ app()->getLocale() === 'ar' ? 'right' : 'left' }};
            margin-top: 14px;
            font-size: 13px;
            font-weight: 600;
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
            body {
                padding: 10mm;
            }

            thead {
                display: table-header-group;
            }

            tbody tr {
                page-break-inside: avoid;
            }

            @page {
                size: A4 landscape;
                margin: 8mm;
            }
        }

        @media screen {
            body {
                max-width: 1200px;
                margin: 0 auto;
                padding: 20px;
            }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="report-header">
        <div class="report-logo">
            <img src="{{ asset('assets/images/primacare-ar.png') }}" alt="PrimaCare" />
        </div>
        <h1>{{ __('medicines.print_title') }}</h1>
        <div class="report-meta">
            <span>{{ __('common.print_date') }}: {{ now()->format('Y-m-d H:i') }}</span>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>{{ __('medicines.name') }}</th>
                <th>{{ __('medicines.description') }}</th>
                <th>{{ __('medicines.dispensing_count') }}</th>
                <th>{{ __('medicines.total_quantity') }}</th>
                <th>{{ __('medicines.created_at') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($medicines as $index => $medicine)
            <tr>
                <td class="num">{{ $index + 1 }}</td>
                <td>{{ $medicine->name }}</td>
                <td>{{ $medicine->description ?? '-' }}</td>
                <td class="center-col">{{ $medicine->dispensings_count }}</td>
                <td class="center-col">{{ $medicine->dispensings_sum_quantity ?? 0 }}</td>
                <td class="date-col">{{ $medicine->created_at->format('Y-m-d') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align:center; padding:20px;">{{ __('medicines.no_results') }}</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="total-count">
        {{ __('common.total') }}: {{ $medicines->count() }}
    </div>

    <div class="report-footer">
        تنفيذ قسم تقنية المعلومات وزارة الصحة بحكومة الوحدة الوطنية &copy; {{ date('Y') }}
    </div>

</body>
</html>
