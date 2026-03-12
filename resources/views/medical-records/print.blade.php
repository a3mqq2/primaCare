<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8" />
    <title>{{ __('medical_records.print_record_title') }} - {{ $medicalRecord->full_name }} - PrimaCare</title>
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
            padding: 15mm;
        }

        .report-header {
            text-align: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #1a1a1a;
        }

        .report-logo {
            margin-bottom: 10px;
        }

        .report-logo img {
            height: 70px;
            width: auto;
        }

        .report-header h1 {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 4px;
            color: #000;
        }

        .report-meta {
            font-size: 11px;
            color: #555;
            margin-top: 4px;
            font-weight: 300;
        }

        .patient-section {
            margin-bottom: 20px;
            border: 1.5px solid #333;
            border-radius: 6px;
            overflow: hidden;
        }

        .patient-header {
            background: #f0f0f0;
            padding: 10px 16px;
            border-bottom: 1.5px solid #333;
        }

        .patient-header h2 {
            font-size: 16px;
            font-weight: 700;
            margin: 0;
        }

        .patient-header .record-number {
            font-size: 12px;
            color: #555;
            font-weight: 400;
        }

        .patient-body {
            padding: 14px 16px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px 20px;
        }

        .info-item .info-label {
            font-size: 10px;
            font-weight: 600;
            color: #777;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            margin-bottom: 2px;
        }

        .info-item .info-value {
            font-size: 13px;
            font-weight: 500;
            color: #1a1a1a;
        }

        .notes-box {
            margin-top: 12px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
        }

        .notes-box .info-label {
            font-size: 10px;
            font-weight: 600;
            color: #777;
            margin-bottom: 4px;
        }

        .notes-box .info-value {
            font-size: 12px;
            line-height: 1.6;
            color: #333;
        }

        .section-title {
            font-size: 14px;
            font-weight: 700;
            margin-bottom: 10px;
            padding-bottom: 6px;
            border-bottom: 1.5px solid #1a1a1a;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
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
            padding: 7px 8px;
            border: 1.5px solid #1a1a1a;
            text-align: {{ app()->getLocale() === 'ar' ? 'right' : 'left' }};
            white-space: nowrap;
        }

        td {
            padding: 6px 8px;
            border: 1px solid #555;
            font-size: 11px;
            vertical-align: top;
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
        }

        .no-data {
            text-align: center;
            padding: 20px;
            color: #888;
            font-size: 12px;
        }

        .total-count {
            text-align: {{ app()->getLocale() === 'ar' ? 'right' : 'left' }};
            margin-top: 10px;
            font-size: 12px;
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

            @page {
                size: A4 portrait;
                margin: 8mm;
            }
        }

        @media screen {
            body {
                max-width: 900px;
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
        <h1>{{ __('medical_records.print_record_title') }}</h1>
        <div class="report-meta">
            {{ __('common.print_date') }}: {{ now()->format('Y-m-d H:i') }}
        </div>
    </div>

    <div class="patient-section">
        <div class="patient-header">
            <h2>
                {{ $medicalRecord->full_name }}
                <span class="record-number">{{ __('medical_records.record_number') }}: #{{ $medicalRecord->id }}</span>
            </h2>
        </div>
        <div class="patient-body">
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">{{ __('medical_records.national_id') }}</div>
                    <div class="info-value" style="direction:ltr; display:inline-block;">{{ $medicalRecord->national_id }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">{{ __('medical_records.phone') }}</div>
                    <div class="info-value">{{ $medicalRecord->phone ?? '—' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">{{ __('medical_records.gender') }}</div>
                    <div class="info-value">{{ $medicalRecord->gender ? __('medical_records.' . $medicalRecord->gender) : '—' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">{{ __('medical_records.occupation') }}</div>
                    <div class="info-value">{{ $medicalRecord->occupation ?? '—' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">{{ __('medical_records.date_of_birth') }}</div>
                    <div class="info-value">{{ $medicalRecord->date_of_birth ? $medicalRecord->date_of_birth->format('Y-m-d') : '—' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">{{ __('medical_records.center') }}</div>
                    <div class="info-value">{{ $medicalRecord->center->name ?? '—' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">{{ __('medical_records.created_by') }}</div>
                    <div class="info-value">{{ $medicalRecord->creator->name ?? '—' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">{{ __('medical_records.created_at') }}</div>
                    <div class="info-value">{{ $medicalRecord->created_at->format('Y-m-d') }}</div>
                </div>
            </div>

            @if($medicalRecord->notes)
            <div class="notes-box">
                <div class="info-label">{{ __('medical_records.notes') }}</div>
                <div class="info-value">{{ $medicalRecord->notes }}</div>
            </div>
            @endif
        </div>
    </div>

    <div class="section-title">{{ __('medical_records.dispensing_operations') }}</div>

    @if($dispensings->count() > 0)
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>{{ __('medical_records.medicine') }}</th>
                <th>{{ __('medical_records.quantity') }}</th>
                <th>{{ __('medical_records.dispensed_by') }}</th>
                <th>{{ __('medical_records.dispensed_at') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($dispensings as $index => $dispensing)
            <tr>
                <td class="num">{{ $index + 1 }}</td>
                <td>{{ $dispensing->medicine->name ?? '-' }}</td>
                <td>{{ $dispensing->quantity }}</td>
                <td>{{ $dispensing->dispensedBy->name ?? '-' }}</td>
                <td class="date-col">{{ $dispensing->dispensed_at?->format('Y-m-d H:i') ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total-count">
        {{ __('common.total') }}: {{ $dispensings->count() }}
    </div>
    @else
    <div class="no-data">{{ __('medical_records.no_operations') }}</div>
    @endif

    <div class="report-footer">
        تنفيذ قسم تقنية المعلومات وزارة الصحة بحكومة الوحدة الوطنية &copy; {{ date('Y') }}
    </div>

</body>
</html>
