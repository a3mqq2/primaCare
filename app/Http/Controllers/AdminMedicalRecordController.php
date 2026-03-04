<?php

namespace App\Http\Controllers;

use App\Models\Center;
use App\Models\MedicalRecord;
use App\Models\User;
use Illuminate\Http\Request;

class AdminMedicalRecordController extends Controller
{
    public function index()
    {
        $centers = Center::orderBy('name_ar')->get();
        $employees = User::where('role', 'center_employee')->orderBy('name')->get();

        return view('admin.medical-records.index', compact('centers', 'employees'));
    }

    public function data(Request $request)
    {
        $query = $this->applyFilters($request);
        $records = $query->latest()->paginate(20);

        return response()->json($records);
    }

    public function show(MedicalRecord $medicalRecord)
    {
        $medicalRecord->load(['creator', 'center']);
        $medicalRecord->loadCount('dispensings');

        return view('admin.medical-records.show', compact('medicalRecord'));
    }

    public function print(Request $request)
    {
        $query = $this->applyFilters($request);
        $records = $query->latest()->get();

        return view('admin.medical-records.print', compact('records'));
    }

    private function applyFilters(Request $request)
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

        return $query;
    }
}
