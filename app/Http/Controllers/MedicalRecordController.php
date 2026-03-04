<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDispensingRequest;
use App\Http\Requests\StoreMedicalRecordRequest;
use App\Models\Dispensing;
use App\Models\MedicalRecord;
use Illuminate\Http\Request;

class MedicalRecordController extends Controller
{
    public function index()
    {
        return view('medical-records.index');
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

    public function update(StoreMedicalRecordRequest $request, MedicalRecord $medicalRecord)
    {
        $this->authorizeCenter($medicalRecord);

        $medicalRecord->update($request->validated());

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

        Dispensing::create($data);

        return response()->json([
            'success' => true,
            'message' => __('medical_records.dispensed'),
        ]);
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
