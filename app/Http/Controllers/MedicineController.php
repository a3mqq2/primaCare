<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMedicineRequest;
use App\Http\Requests\UpdateMedicineRequest;
use App\Models\Medicine;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class MedicineController extends Controller
{
    public function index()
    {
        return view('medicines.index');
    }

    public function data(Request $request)
    {
        $query = Medicine::withCount('dispensings')->withSum('dispensings', 'quantity');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $medicines = $query->latest()->paginate(10);

        return response()->json($medicines);
    }

    public function print(Request $request)
    {
        $query = Medicine::withCount('dispensings')->withSum('dispensings', 'quantity');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $medicines = $query->latest()->limit(500)->get();

        return view('medicines.print', compact('medicines'));
    }

    public function dispensingHistory()
    {
        return view('medicines.dispensings');
    }

    public function dispensingHistoryData(Request $request)
    {
        $query = \App\Models\Dispensing::with(['medicalRecord.center', 'medicine', 'dispensedBy']);

        if ($medicineId = $request->input('medicine_id')) {
            $query->where('medicine_id', $medicineId);
        }

        if ($dateFrom = $request->input('date_from')) {
            $query->whereDate('dispensed_at', '>=', $dateFrom);
        }

        if ($dateTo = $request->input('date_to')) {
            $query->whereDate('dispensed_at', '<=', $dateTo);
        }

        return response()->json($query->latest('dispensed_at')->paginate(20));
    }

    public function search(Request $request)
    {
        $query = $request->input('q', '');

        if (mb_strlen($query) < 1) {
            return response()->json([]);
        }

        $cacheKey = 'medicines_search_' . md5($query);

        $medicines = Cache::remember($cacheKey, 300, function () use ($query) {
            return Medicine::where('name', 'like', "%{$query}%")
                ->select('id', 'name')
                ->orderBy('name')
                ->limit(20)
                ->get();
        });

        return response()->json($medicines);
    }

    public function create()
    {
        return view('medicines.create');
    }

    public function store(StoreMedicineRequest $request)
    {
        $data = $request->validated();
        $data['created_by'] = auth()->id();

        $medicine = Medicine::create($data);

        ActivityLogger::log('created', $medicine);

        return response()->json([
            'success' => true,
            'message' => __('medicines.created'),
        ]);
    }

    public function show(Medicine $medicine)
    {
        return response()->json([
            'data' => $medicine,
            'can_edit' => true,
            'can_delete' => !$medicine->dispensings()->exists(),
        ]);
    }

    public function update(UpdateMedicineRequest $request, Medicine $medicine)
    {
        $oldValues = $medicine->getOriginal();
        $medicine->update($request->validated());

        ActivityLogger::log('updated', $medicine, $oldValues);

        return response()->json([
            'success' => true,
            'message' => __('medicines.updated'),
        ]);
    }

    public function destroy(Medicine $medicine)
    {
        if ($medicine->dispensings()->exists()) {
            return response()->json([
                'success' => false,
                'message' => __('medicines.cannot_delete_in_use'),
            ], 409);
        }

        ActivityLogger::log('deleted', $medicine);

        $medicine->delete();

        return response()->json([
            'success' => true,
            'message' => __('medicines.deleted'),
        ]);
    }
}
