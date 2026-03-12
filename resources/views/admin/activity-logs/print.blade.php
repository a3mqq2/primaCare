<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8" />
    <title>{{ __('activity_logs.print_title') }} - PrimaCare</title>
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

        td.date-col {
            white-space: nowrap;
            text-align: center;
        }

        .changes-list {
            list-style: none;
            padding: 0;
            margin: 3px 0 0;
            font-size: 9px;
        }

        .changes-list li {
            padding: 1px 0;
            color: #444;
        }

        .changes-list .field-name {
            font-weight: 600;
        }

        .changes-list .old-value {
            text-decoration: line-through;
            color: #999;
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
            <img src="{{ asset('assets/images/logo-black.png') }}" alt="PrimaCare" />
        </div>
        <h1>{{ __('activity_logs.print_title') }}</h1>
        <div class="report-meta">
            <span>{{ __('common.print_date') }}: {{ now()->format('Y-m-d H:i') }}</span>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>{{ __('activity_logs.date') }}</th>
                <th>{{ __('activity_logs.user') }}</th>
                <th>{{ __('activity_logs.action') }}</th>
                <th>{{ __('activity_logs.details') }}</th>
                <th>{{ __('activity_logs.ip_address') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $index => $log)
            <tr>
                <td class="num">{{ $index + 1 }}</td>
                <td class="date-col">{{ $log->created_at->format('Y-m-d H:i') }}</td>
                <td>{{ $log->user->name ?? '-' }}</td>
                <td>{{ __('activity_logs.' . $log->action) }}</td>
                <td>
                    {{ $log->description }}
                    @if($log->action === 'updated' && $log->properties && isset($log->properties['changed_fields']))
                        <ul class="changes-list">
                            @foreach($log->properties['changed_fields'] as $field)
                                <li>
                                    @if($field === 'password')
                                        <span class="field-name">{{ __('activity_logs.fields.' . $field) }}</span>: ••••••
                                    @else
                                        <span class="field-name">{{ __('activity_logs.fields.' . $field) }}</span>:
                                        <span class="old-value">{{ $log->properties['old'][$field] ?? '-' }}</span>
                                        →
                                        {{ $log->properties['new'][$field] ?? '-' }}
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </td>
                <td>{{ $log->ip_address ?? '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align:center; padding:20px;">{{ __('activity_logs.no_results') }}</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="total-count">
        {{ __('common.total') }}: {{ $logs->count() }}
    </div>

    <div class="report-footer">
        تنفيذ قسم تقنية المعلومات وزارة الصحة بحكومة الوحدة الوطنية &copy; {{ date('Y') }}
    </div>

</body>
</html>
