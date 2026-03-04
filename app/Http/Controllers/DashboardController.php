<?php

namespace App\Http\Controllers;

use App\Models\Center;
use App\Models\Dispensing;
use App\Models\MedicalRecord;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->isCenterEmployee()) {
            return redirect()->route('medical-records.index');
        }

        return view('dashboard');
    }

    public function data(Request $request)
    {
        $user = auth()->user();

        if (!$user->isSystemAdmin()) {
            abort(403);
        }

        $period = $request->input('period', 'today');
        [$start, $end] = $this->getPeriodRange($period);
        [$prevStart, $prevEnd] = $this->getPreviousPeriodRange($period);

        $useHourly = in_array($period, ['today', 'yesterday']);

        return response()->json([
            'summary' => $this->getSummary($start, $end, $prevStart, $prevEnd),
            'hourly_activity' => $this->getActivity($start, $end, $useHourly),
            'gender_distribution' => $this->getGenderDistribution($start, $end),
            'active_centers' => $this->getActiveCenters($start, $end),
            'top_medicines' => $this->getTopMedicines($start, $end),
            'use_hourly' => $useHourly,
        ]);
    }

    private function getPeriodRange(string $period): array
    {
        return match ($period) {
            'yesterday' => [Carbon::yesterday()->startOfDay(), Carbon::yesterday()->endOfDay()],
            'week' => [Carbon::now()->subDays(6)->startOfDay(), Carbon::now()],
            'month' => [Carbon::now()->startOfMonth(), Carbon::now()],
            default => [Carbon::today(), Carbon::now()],
        };
    }

    private function getPreviousPeriodRange(string $period): array
    {
        return match ($period) {
            'yesterday' => [Carbon::yesterday()->subDay()->startOfDay(), Carbon::yesterday()->subDay()->endOfDay()],
            'week' => [Carbon::now()->subDays(13)->startOfDay(), Carbon::now()->subDays(7)->endOfDay()],
            'month' => [Carbon::now()->subMonthNoOverflow()->startOfMonth(), Carbon::now()->subMonthNoOverflow()->endOfMonth()],
            default => [Carbon::yesterday()->startOfDay(), Carbon::yesterday()->endOfDay()],
        };
    }

    private function getSummary(Carbon $start, Carbon $end, Carbon $prevStart, Carbon $prevEnd): array
    {
        $currentRecords = MedicalRecord::whereBetween('created_at', [$start, $end])->count();
        $prevRecords = MedicalRecord::whereBetween('created_at', [$prevStart, $prevEnd])->count();

        $currentDispensings = Dispensing::whereBetween('dispensed_at', [$start, $end])->count();
        $prevDispensings = Dispensing::whereBetween('dispensed_at', [$prevStart, $prevEnd])->count();

        $currentNewPatients = MedicalRecord::whereBetween('created_at', [$start, $end])
            ->whereNotIn('national_id', function ($q) use ($start) {
                $q->select('national_id')->from('medical_records')
                    ->where('created_at', '<', $start);
            })->count();
        $prevNewPatients = MedicalRecord::whereBetween('created_at', [$prevStart, $prevEnd])
            ->whereNotIn('national_id', function ($q) use ($prevStart) {
                $q->select('national_id')->from('medical_records')
                    ->where('created_at', '<', $prevStart);
            })->count();

        $currentActiveCenters = MedicalRecord::whereBetween('created_at', [$start, $end])
            ->distinct()->count('center_id');
        $prevActiveCenters = MedicalRecord::whereBetween('created_at', [$prevStart, $prevEnd])
            ->distinct()->count('center_id');

        return [
            'records' => $this->buildStat($currentRecords, $prevRecords),
            'dispensings' => $this->buildStat($currentDispensings, $prevDispensings),
            'new_patients' => $this->buildStat($currentNewPatients, $prevNewPatients),
            'active_centers' => $this->buildStat($currentActiveCenters, $prevActiveCenters),
        ];
    }

    private function buildStat(int $current, int $previous): array
    {
        $change = $previous > 0
            ? round((($current - $previous) / $previous) * 100, 1)
            : ($current > 0 ? 100 : 0);

        return [
            'current' => $current,
            'previous' => $previous,
            'change' => $change,
        ];
    }

    private function getActivity(Carbon $start, Carbon $end, bool $hourly): array
    {
        if ($hourly) {
            $records = MedicalRecord::select(
                    DB::raw('HOUR(created_at) as label'),
                    DB::raw('count(*) as count')
                )
                ->whereBetween('created_at', [$start, $end])
                ->groupBy('label')
                ->orderBy('label')
                ->pluck('count', 'label')
                ->toArray();

            $dispensings = Dispensing::select(
                    DB::raw('HOUR(dispensed_at) as label'),
                    DB::raw('count(*) as count')
                )
                ->whereBetween('dispensed_at', [$start, $end])
                ->groupBy('label')
                ->orderBy('label')
                ->pluck('count', 'label')
                ->toArray();

            $result = [];
            for ($h = 0; $h < 24; $h++) {
                $result[] = [
                    'label' => sprintf('%02d:00', $h),
                    'records' => $records[$h] ?? 0,
                    'dispensings' => $dispensings[$h] ?? 0,
                ];
            }

            return $result;
        }

        $records = MedicalRecord::select(
                DB::raw('DATE(created_at) as label'),
                DB::raw('count(*) as count')
            )
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('label')
            ->orderBy('label')
            ->pluck('count', 'label')
            ->toArray();

        $dispensings = Dispensing::select(
                DB::raw('DATE(dispensed_at) as label'),
                DB::raw('count(*) as count')
            )
            ->whereBetween('dispensed_at', [$start, $end])
            ->groupBy('label')
            ->orderBy('label')
            ->pluck('count', 'label')
            ->toArray();

        $result = [];
        $current = $start->copy()->startOfDay();
        $endDate = $end->copy()->startOfDay();

        while ($current->lte($endDate)) {
            $key = $current->format('Y-m-d');
            $result[] = [
                'label' => $key,
                'records' => $records[$key] ?? 0,
                'dispensings' => $dispensings[$key] ?? 0,
            ];
            $current->addDay();
        }

        return $result;
    }

    private function getGenderDistribution(Carbon $start, Carbon $end): array
    {
        return MedicalRecord::select('gender', DB::raw('count(*) as count'))
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('gender')
            ->get()
            ->map(fn ($r) => [
                'gender' => $r->gender ?? 'unknown',
                'count' => $r->count,
            ])
            ->toArray();
    }

    private function getActiveCenters(Carbon $start, Carbon $end): array
    {
        $recordCounts = MedicalRecord::select('center_id', DB::raw('count(*) as records_count'))
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('center_id')
            ->pluck('records_count', 'center_id')
            ->toArray();

        $dispensingCounts = Dispensing::join('medical_records', 'dispensings.medical_record_id', '=', 'medical_records.id')
            ->select('medical_records.center_id', DB::raw('count(dispensings.id) as dispensings_count'))
            ->whereBetween('dispensings.dispensed_at', [$start, $end])
            ->groupBy('medical_records.center_id')
            ->pluck('dispensings_count', 'center_id')
            ->toArray();

        $centerIds = array_unique(array_merge(array_keys($recordCounts), array_keys($dispensingCounts)));

        if (empty($centerIds)) {
            return [];
        }

        $centers = Center::whereIn('id', $centerIds)->get()->keyBy('id');

        $result = [];
        foreach ($centerIds as $id) {
            $center = $centers->get($id);
            if (!$center) continue;

            $result[] = [
                'center' => $center->name,
                'records' => $recordCounts[$id] ?? 0,
                'dispensings' => $dispensingCounts[$id] ?? 0,
                'total' => ($recordCounts[$id] ?? 0) + ($dispensingCounts[$id] ?? 0),
            ];
        }

        usort($result, fn ($a, $b) => $b['total'] - $a['total']);

        return $result;
    }

    private function getTopMedicines(Carbon $start, Carbon $end): array
    {
        return Dispensing::select(
                'medicine_id',
                DB::raw('count(*) as dispensing_count'),
                DB::raw('sum(quantity) as total_quantity')
            )
            ->whereBetween('dispensed_at', [$start, $end])
            ->groupBy('medicine_id')
            ->orderByDesc('dispensing_count')
            ->limit(10)
            ->with('medicine')
            ->get()
            ->map(fn ($r) => [
                'name' => $r->medicine->name ?? '-',
                'dispensing_count' => $r->dispensing_count,
                'total_quantity' => (int) $r->total_quantity,
            ])
            ->toArray();
    }
}
