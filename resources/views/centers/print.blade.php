<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8" />
    <title>{{ __('centers.print_title') }} - PrimaCare</title>
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
            font-size: 11.5px;
            letter-spacing: 0.3px;
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

        tbody tr:nth-child(even) {
            background: #f9f9f9;
        }

        td.num {
            text-align: center;
            font-weight: 600;
            width: 40px;
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
                margin: 10mm;
            }
        }

        @media screen {
            body {
                max-width: 1100px;
                margin: 0 auto;
                padding: 30px;
            }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="report-header">
        <div class="report-logo">
            <img src="{{ asset('assets/images/logo-black.png') }}" alt="PrimaCare" />
        </div>
        <h1>{{ __('centers.print_title') }}</h1>
        <div class="report-meta">
            <span>{{ __('common.print_date') }}: {{ now()->format('Y-m-d H:i') }}</span>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>{{ __('centers.name_ar') }}</th>
                <th>{{ __('centers.name_en') }}</th>
                <th>{{ __('centers.city') }}</th>
                <th>{{ __('centers.phone') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($centers as $index => $center)
            <tr>
                <td class="num">{{ $index + 1 }}</td>
                <td>{{ $center->name_ar }}</td>
                <td>{{ $center->name_en }}</td>
                <td>{{ $center->city->name ?? '-' }}</td>
                <td>{{ $center->phone ?? '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align:center; padding:20px;">{{ __('centers.no_results') }}</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="total-count">
        {{ __('centers.total_centers') }}: {{ $centers->count() }}
    </div>

    <div class="report-footer">
        تنفيذ قسم تقنية المعلومات وزارة الصحة بحكومة الوحدة الوطنية &copy; {{ date('Y') }}
    </div>

</body>
</html>
