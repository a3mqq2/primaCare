<?php

namespace App\Http\Controllers;

use App\Models\Center;
use App\Models\Dispensing;
use App\Models\User;
use Illuminate\Http\Request;

class AdminDispensingController extends Controller
{
    public function index()
    {
        $centers = Center::orderBy('name_ar')->get();
        $employees = User::where('role', 'center_employee')->orderBy('name')->get();

        return view('admin.dispensings.index', compact('centers', 'employees'));
    }

    public function data(Request $request)
    {
        $dispensings = $this->applyFilters($request)->latest('dispensed_at')->paginate(20);

        return response()->json($dispensings);
    }

    public function show(Dispensing $dispensing)
    {
        $dispensing->load(['medicalRecord.center', 'medicine', 'dispensedBy']);

        return response()->json($dispensing);
    }

    public function print(Request $request)
    {
        $dispensings = $this->applyFilters($request)->latest('dispensed_at')->get();

        return view('admin.dispensings.print', compact('dispensings'));
    }

    private function applyFilters(Request $request)
    {
        $query = Dispensing::with(['medicalRecord.center', 'medicine', 'dispensedBy']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('medicalRecord', function ($r) use ($search) {
                    $r->where('full_name', 'like', "%{$search}%")
                      ->orWhere('national_id', 'like', "%{$search}%")
                      ->orWhere('occupation', 'like', "%{$search}%");
                })->orWhereHas('medicine', function ($m) use ($search) {
                    $m->where('name', 'like', "%{$search}%");
                });
            });
        }

        if ($centerId = $request->input('center_id')) {
            $query->whereHas('medicalRecord', function ($q) use ($centerId) {
                $q->where('center_id', $centerId);
            });
        }

        if ($patientName = $request->input('patient_name')) {
            $query->whereHas('medicalRecord', function ($q) use ($patientName) {
                $q->where('full_name', 'like', "%{$patientName}%");
            });
        }

        if ($nationalId = $request->input('national_id')) {
            $query->whereHas('medicalRecord', function ($q) use ($nationalId) {
                $q->where('national_id', 'like', "%{$nationalId}%");
            });
        }

        if ($medicineName = $request->input('medicine_name')) {
            $query->whereHas('medicine', function ($q) use ($medicineName) {
                $q->where('name', 'like', "%{$medicineName}%");
            });
        }

        if ($gender = $request->input('gender')) {
            $query->whereHas('medicalRecord', function ($q) use ($gender) {
                $q->where('gender', $gender);
            });
        }

        if ($occupation = $request->input('occupation')) {
            $query->whereHas('medicalRecord', function ($q) use ($occupation) {
                $q->where('occupation', 'like', "%{$occupation}%");
            });
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

        return $query;
    }
}
