<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMedicineRequest;
use App\Http\Requests\UpdateMedicineRequest;
use App\Models\Medicine;
use Illuminate\Http\Request;

class MedicineController extends Controller
{
    public function index()
    {
        return view('medicines.index');
    }

    public function data(Request $request)
    {
        $query = Medicine::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $medicines = $query->latest()->paginate(10);

        return response()->json($medicines);
    }

    public function search(Request $request)
    {
        $query = $request->input('q', '');

        if (mb_strlen($query) < 1) {
            return response()->json([]);
        }

        $medicines = Medicine::where('name', 'like', "%{$query}%")
            ->select('id', 'name')
            ->orderBy('name')
            ->limit(20)
            ->get();

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

        Medicine::create($data);

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
        $medicine->update($request->validated());

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

        $medicine->delete();

        return response()->json([
            'success' => true,
            'message' => __('medicines.deleted'),
        ]);
    }
}
