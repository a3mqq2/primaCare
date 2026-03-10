<?php

namespace App\Http\Controllers;

use App\Models\Center;
use App\Models\Dispensing;
use App\Models\MedicalRecord;
use App\Models\Medicine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class StatisticsController extends Controller
{
    public function index()
    {
        return view('admin.statistics.index');
    }

    public function data(Request $request)
    {
        $cacheKey = 'statistics_data_' . md5(json_encode($request->only('center_id', 'date_from', 'date_to')));

        $data = Cache::remember($cacheKey, 300, function () use ($request) {
            return [
                'summary' => $this->getSummary($request),
                'records_by_center' => $this->getRecordsByCenter($request),
                'records_by_date' => $this->getRecordsByDate($request),
                'records_by_gender' => $this->getRecordsByGender($request),
                'dispensings_by_center' => $this->getDispensingsByCenter($request),
                'top_medicines' => $this->getTopMedicines($request),
            ];
        });

        return response()->json($data);
    }

    public function print(Request $request)
    {
        $data = [
            'summary' => $this->getSummary($request),
            'records_by_center' => $this->getRecordsByCenter($request),
            'records_by_gender' => $this->getRecordsByGender($request),
            'dispensings_by_center' => $this->getDispensingsByCenter($request),
            'top_medicines' => $this->getTopMedicines($request),
            'filters' => [
                'center' => $request->input('center_id') ? Center::find($request->input('center_id'))?->name : null,
                'date_from' => $request->input('date_from'),
                'date_to' => $request->input('date_to'),
            ],
        ];

        return view('admin.statistics.print', $data);
    }

    private function getSummary(Request $request): array
    {
        $recordsQuery = $this->applyRecordFilters(MedicalRecord::query(), $request);
        $dispensingsQuery = $this->applyDispensingFilters(Dispensing::query(), $request);

        $totalRecords = $recordsQuery->count();
        $totalDispensings = (clone $dispensingsQuery)->count();
        $totalQuantity = (clone $dispensingsQuery)->sum('quantity');

        $medicinesQuery = $this->applyDispensingFilters(Dispensing::query(), $request);
        $totalMedicines = $medicinesQuery->distinct('medicine_id')->count('medicine_id');

        return [
            'total_records' => $totalRecords,
            'total_dispensings' => $totalDispensings,
            'total_quantity' => (int) $totalQuantity,
            'total_medicines' => $totalMedicines,
        ];
    }

    private function getRecordsByCenter(Request $request): array
    {
        $query = $this->applyRecordFilters(MedicalRecord::query(), $request);

        $results = $query->join('centers', 'medical_records.center_id', '=', 'centers.id')
            ->select(
                'medical_records.center_id',
                'centers.name_ar',
                'centers.name_en',
                DB::raw('count(*) as count')
            )
            ->groupBy('medical_records.center_id', 'centers.name_ar', 'centers.name_en')
            ->get();

        $total = $results->sum('count');

        return $results->map(function ($item) use ($total) {
            return [
                'center' => app()->getLocale() === 'ar' ? $item->name_ar : $item->name_en,
                'count' => $item->count,
                'percentage' => $total > 0 ? round(($item->count / $total) * 100, 1) : 0,
            ];
        })->sortByDesc('count')->values()->take(10)->toArray();
    }

    private function getRecordsByDate(Request $request): array
    {
        $baseQuery = fn () => $this->applyRecordFilters(MedicalRecord::query(), $request);

        $daily = $baseQuery()
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn ($r) => ['date' => $r->date, 'count' => $r->count])
            ->toArray();

        $monthly = $baseQuery()
            ->select(DB::raw("DATE_FORMAT(created_at, '%Y-%m') as date"), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn ($r) => ['date' => $r->date, 'count' => $r->count])
            ->toArray();

        $yearly = $baseQuery()
            ->select(DB::raw('YEAR(created_at) as date'), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn ($r) => ['date' => (string) $r->date, 'count' => $r->count])
            ->toArray();

        return compact('daily', 'monthly', 'yearly');
    }

    private function getRecordsByGender(Request $request): array
    {
        $query = $this->applyRecordFilters(MedicalRecord::query(), $request);

        return $query->select('gender', DB::raw('count(*) as count'))
            ->groupBy('gender')
            ->get()
            ->map(fn ($r) => [
                'gender' => $r->gender ?? 'unknown',
                'count' => $r->count,
            ])
            ->toArray();
    }

    private function getDispensingsByCenter(Request $request): array
    {
        $query = $this->applyDispensingFilters(Dispensing::query(), $request);

        return $query->join('medical_records', 'dispensings.medical_record_id', '=', 'medical_records.id')
            ->join('centers', 'medical_records.center_id', '=', 'centers.id')
            ->select(
                'medical_records.center_id',
                DB::raw('count(dispensings.id) as count'),
                DB::raw('sum(dispensings.quantity) as quantity'),
                'centers.name_ar',
                'centers.name_en'
            )
            ->groupBy('medical_records.center_id', 'centers.name_ar', 'centers.name_en')
            ->orderByDesc('count')
            ->limit(10)
            ->get()
            ->map(fn ($r) => [
                'center' => app()->getLocale() === 'ar' ? $r->name_ar : $r->name_en,
                'count' => $r->count,
                'quantity' => (int) $r->quantity,
            ])
            ->toArray();
    }

    private function getTopMedicines(Request $request): array
    {
        $query = $this->applyDispensingFilters(Dispensing::query(), $request);

        return $query->join('medicines', 'dispensings.medicine_id', '=', 'medicines.id')
            ->select(
                'medicines.name',
                DB::raw('count(*) as dispensing_count'),
                DB::raw('sum(dispensings.quantity) as total_quantity')
            )
            ->groupBy('dispensings.medicine_id', 'medicines.name')
            ->orderByDesc('dispensing_count')
            ->limit(10)
            ->get()
            ->map(fn ($r) => [
                'name' => $r->name,
                'dispensing_count' => $r->dispensing_count,
                'total_quantity' => (int) $r->total_quantity,
            ])
            ->toArray();
    }

    private function applyRecordFilters($query, Request $request)
    {
        if ($centerId = $request->input('center_id')) {
            $query->where('center_id', $centerId);
        }

        if ($dateFrom = $request->input('date_from')) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo = $request->input('date_to')) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        return $query;
    }

    private function applyDispensingFilters($query, Request $request)
    {
        if ($centerId = $request->input('center_id')) {
            $query->whereHas('medicalRecord', function ($q) use ($centerId) {
                $q->where('center_id', $centerId);
            });
        }

        if ($dateFrom = $request->input('date_from')) {
            $query->whereDate('dispensed_at', '>=', $dateFrom);
        }

        if ($dateTo = $request->input('date_to')) {
            $query->whereDate('dispensed_at', '<=', $dateTo);
        }

        return $query;
    }
}
