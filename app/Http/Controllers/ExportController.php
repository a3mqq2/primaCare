<?php

namespace App\Http\Controllers;

use App\Models\Center;
use App\Models\City;
use App\Models\Dispensing;
use App\Models\MedicalRecord;
use App\Models\Medicine;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function medicalRecords(Request $request): StreamedResponse
    {
        $query = MedicalRecord::with(['center', 'creator']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('national_id', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('occupation', 'like', "%{$search}%");
            });
        }

        if ($centerId = $request->input('center_id')) {
            $query->where('center_id', $centerId);
        }

        if ($name = $request->input('full_name')) {
            $query->where('full_name', 'like', "%{$name}%");
        }

        if ($nationalId = $request->input('national_id')) {
            $query->where('national_id', 'like', "%{$nationalId}%");
        }

        if ($phone = $request->input('phone')) {
            $query->where('phone', 'like', "%{$phone}%");
        }

        if ($gender = $request->input('gender')) {
            $query->where('gender', $gender);
        }

        if ($occupation = $request->input('occupation')) {
            $query->where('occupation', 'like', "%{$occupation}%");
        }

        if ($employeeId = $request->input('created_by')) {
            $query->where('created_by', $employeeId);
        }

        if ($dateFrom = $request->input('date_from')) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo = $request->input('date_to')) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        return $this->streamCsv('medical-records', [
            '#', __('medical_records.center'), __('medical_records.full_name'),
            __('medical_records.national_id'), __('medical_records.phone'),
            __('medical_records.gender'), __('medical_records.occupation'),
            __('medical_records.date_of_birth'), __('medical_records.notes'),
            __('medical_records.employee'), __('medical_records.created_at'),
        ], $query->latest()->limit(10000), function ($record, $index) {
            return [
                $index + 1,
                $record->center->name ?? '-',
                $record->full_name,
                $record->national_id,
                $record->phone ?? '-',
                $record->gender ? __('medical_records.' . $record->gender) : '-',
                $record->occupation ?? '-',
                $record->date_of_birth?->format('Y-m-d') ?? '-',
                $record->notes ?? '-',
                $record->creator->name ?? '-',
                $record->created_at->format('Y-m-d H:i'),
            ];
        });
    }

    public function dispensings(Request $request): StreamedResponse
    {
        $query = Dispensing::with(['medicalRecord.center', 'medicine', 'dispensedBy']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('medicalRecord', function ($r) use ($search) {
                    $r->where('full_name', 'like', "%{$search}%")
                        ->orWhere('national_id', 'like', "%{$search}%");
                })->orWhereHas('medicine', function ($m) use ($search) {
                    $m->where('name', 'like', "%{$search}%");
                });
            });
        }

        if ($centerId = $request->input('center_id')) {
            $query->whereHas('medicalRecord', fn ($q) => $q->where('center_id', $centerId));
        }

        if ($patientName = $request->input('patient_name')) {
            $query->whereHas('medicalRecord', fn ($q) => $q->where('full_name', 'like', "%{$patientName}%"));
        }

        if ($nationalId = $request->input('national_id')) {
            $query->whereHas('medicalRecord', fn ($q) => $q->where('national_id', 'like', "%{$nationalId}%"));
        }

        if ($medicineName = $request->input('medicine_name')) {
            $query->whereHas('medicine', fn ($q) => $q->where('name', 'like', "%{$medicineName}%"));
        }

        if ($employeeId = $request->input('employee_id')) {
            $query->where('dispensed_by', $employeeId);
        }

        if ($dateFrom = $request->input('date_from')) {
            $query->whereDate('dispensed_at', '>=', $dateFrom);
        }

        if ($dateTo = $request->input('date_to')) {
            $query->whereDate('dispensed_at', '<=', $dateTo);
        }

        return $this->streamCsv('dispensings', [
            '#', __('dispensings.patient_name'), __('dispensings.national_id'),
            __('dispensings.center'), __('dispensings.medicine'),
            __('dispensings.quantity'), __('dispensings.employee'),
            __('dispensings.dispensed_at'),
        ], $query->latest('dispensed_at')->limit(10000), function ($d, $index) {
            return [
                $index + 1,
                $d->medicalRecord->full_name ?? '-',
                $d->medicalRecord->national_id ?? '-',
                $d->medicalRecord->center->name ?? '-',
                $d->medicine->name ?? '-',
                $d->quantity,
                $d->dispensedBy->name ?? '-',
                $d->dispensed_at?->format('Y-m-d H:i') ?? '-',
            ];
        });
    }

    public function medicines(Request $request): StreamedResponse
    {
        $query = Medicine::withCount('dispensings')
            ->withSum('dispensings', 'quantity');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        return $this->streamCsv('medicines', [
            '#', __('medicines.name'), __('medicines.description'),
            __('medicines.dispensing_count'), __('medicines.total_quantity'),
            __('medicines.created_at'),
        ], $query->latest()->limit(5000), function ($m, $index) {
            return [
                $index + 1,
                $m->name,
                $m->description ?? '-',
                $m->dispensings_count,
                (int) $m->dispensings_sum_quantity,
                $m->created_at->format('Y-m-d'),
            ];
        });
    }

    public function cities(Request $request): StreamedResponse
    {
        $query = City::withCount('centers');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name_ar', 'like', "%{$search}%")
                    ->orWhere('name_en', 'like', "%{$search}%");
            });
        }

        return $this->streamCsv('cities', [
            '#', __('cities.name_ar'), __('cities.name_en'),
            __('cities.centers_count'),
        ], $query->orderBy('name_ar')->limit(5000), function ($c, $index) {
            return [
                $index + 1,
                $c->name_ar,
                $c->name_en,
                $c->centers_count,
            ];
        });
    }

    public function centers(Request $request): StreamedResponse
    {
        $query = Center::with('city');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name_ar', 'like', "%{$search}%")
                    ->orWhere('name_en', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($cityId = $request->input('city_id')) {
            $query->where('city_id', $cityId);
        }

        return $this->streamCsv('centers', [
            '#', __('centers.name_ar'), __('centers.name_en'),
            __('centers.city'), __('centers.phone'),
        ], $query->orderBy('name_ar')->limit(5000), function ($c, $index) {
            return [
                $index + 1,
                $c->name_ar,
                $c->name_en,
                $c->city->name ?? '-',
                $c->phone ?? '-',
            ];
        });
    }

    public function users(Request $request): StreamedResponse
    {
        $query = User::with('center')->visibleTo(auth()->user());

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($role = $request->input('role')) {
            $query->where('role', $role);
        }

        if ($centerId = $request->input('center_id')) {
            $query->where('center_id', $centerId);
        }

        return $this->streamCsv('users', [
            '#', __('users.name'), __('users.email'),
            __('users.role'), __('users.center'),
            __('users.created_at'),
        ], $query->latest()->limit(5000), function ($u, $index) {
            return [
                $index + 1,
                $u->name,
                $u->email,
                __('users.roles.' . $u->role),
                $u->center->name ?? '-',
                $u->created_at->format('Y-m-d'),
            ];
        });
    }

    private function streamCsv(string $filename, array $headers, $query, callable $rowMapper): StreamedResponse
    {
        $filename = $filename . '-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($headers, $query, $rowMapper) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($handle, $headers);

            $index = 0;
            $query->chunk(500, function ($rows) use ($handle, $rowMapper, &$index) {
                foreach ($rows as $row) {
                    fputcsv($handle, $rowMapper($row, $index));
                    $index++;
                }
            });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
