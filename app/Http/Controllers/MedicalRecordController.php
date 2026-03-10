<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDispensingRequest;
use App\Http\Requests\StoreMedicalRecordRequest;
use App\Models\Dispensing;
use App\Models\MedicalRecord;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class MedicalRecordController extends Controller
{
    public function index()
    {
        $centerId = auth()->user()->center_id;

        $todayRecords = MedicalRecord::forCenter($centerId)
            ->whereDate('created_at', today())
            ->count();

        $todayDispensings = Dispensing::whereHas('medicalRecord', function ($q) use ($centerId) {
            $q->where('center_id', $centerId);
        })->whereDate('dispensed_at', today())->count();

        return view('medical-records.index', compact('todayRecords', 'todayDispensings'));
    }

    public function data(Request $request)
    {
        $user = auth()->user();
        $query = MedicalRecord::with('creator')->forCenter($user->center_id);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('national_id', 'like', "%{$search}%")
                    ->orWhere('full_name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('occupation', 'like', "%{$search}%");
            });
        }

        $records = $query->latest()->paginate(15);

        return response()->json($records);
    }

    public function store(StoreMedicalRecordRequest $request)
    {
        $data = $request->validated();
        $data['center_id'] = auth()->user()->center_id;
        $data['created_by'] = auth()->id();

        $record = MedicalRecord::create($data);

        ActivityLogger::log('created', $record);

        return response()->json([
            'success' => true,
            'message' => __('medical_records.created'),
            'record' => $record,
        ]);
    }

    public function search(Request $request)
    {
        $search = $request->input('search');
        $centerId = auth()->user()->center_id;

        $query = MedicalRecord::forCenter($centerId);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('national_id', 'like', "%{$search}%")
                    ->orWhere('full_name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('occupation', 'like', "%{$search}%");
            });
        }

        $records = $query->latest()->paginate(10);

        return response()->json($records);
    }

    public function show(MedicalRecord $medicalRecord)
    {
        $this->authorizeCenter($medicalRecord);

        $medicalRecord->load(['creator', 'center']);
        $medicalRecord->loadCount('dispensings');

        return view('medical-records.show', compact('medicalRecord'));
    }

    public function printRecord(MedicalRecord $medicalRecord)
    {
        $this->authorizeCenter($medicalRecord);

        $medicalRecord->load(['creator', 'center']);
        $dispensings = $medicalRecord->dispensings()
            ->with(['medicine', 'dispensedBy'])
            ->latest('dispensed_at')
            ->get();

        return view('medical-records.print', compact('medicalRecord', 'dispensings'));
    }

    public function update(StoreMedicalRecordRequest $request, MedicalRecord $medicalRecord)
    {
        $this->authorizeCenter($medicalRecord);

        $oldValues = $medicalRecord->getOriginal();
        $medicalRecord->update($request->validated());

        ActivityLogger::log('updated', $medicalRecord, $oldValues);

        return response()->json([
            'success' => true,
            'message' => __('medical_records.updated'),
        ]);
    }

    public function dispensings(MedicalRecord $medicalRecord)
    {
        $this->authorizeCenter($medicalRecord);

        $dispensings = $medicalRecord->dispensings()
            ->with(['medicine', 'dispensedBy'])
            ->latest('dispensed_at')
            ->get();

        return response()->json($dispensings);
    }

    public function dispense(StoreDispensingRequest $request, MedicalRecord $medicalRecord)
    {
        $this->authorizeCenter($medicalRecord);

        $data = $request->validated();
        $data['medical_record_id'] = $medicalRecord->id;
        $data['dispensed_by'] = auth()->id();
        $data['dispensed_at'] = now();

        $dispensing = Dispensing::create($data);

        ActivityLogger::log('created', $dispensing);

        return response()->json([
            'success' => true,
            'message' => __('medical_records.dispensed'),
        ]);
    }

    public function checkNationalId(Request $request)
    {
        $nationalId = $request->input('national_id');
        $excludeId = $request->input('exclude_id');

        $query = MedicalRecord::where('national_id', $nationalId);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        $existing = $query->first(['id', 'full_name', 'center_id']);

        if ($existing) {
            $existing->load('center:id,name_ar,name_en');
            return response()->json([
                'exists' => true,
                'record' => [
                    'full_name' => $existing->full_name,
                    'center' => $existing->center?->name ?? '',
                ],
            ]);
        }

        return response()->json(['exists' => false]);
    }

    private function authorizeCenter(MedicalRecord $record): void
    {
        $user = auth()->user();
        if ($user->isSystemAdmin()) {
            return;
        }
        if ($record->center_id !== $user->center_id) {
            abort(403);
        }
    }
}
