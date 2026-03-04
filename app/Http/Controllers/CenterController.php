<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCenterRequest;
use App\Http\Requests\UpdateCenterRequest;
use App\Models\Center;
use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CenterController extends Controller
{
    public function index()
    {
        $cities = City::orderBy('name_ar')->get();

        return view('centers.index', compact('cities'));
    }

    public function data(Request $request)
    {
        $query = Center::with('city');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name_ar', 'like', "%{$search}%")
                    ->orWhere('name_en', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhereHas('city', function ($q) use ($search) {
                        $q->where('name_ar', 'like', "%{$search}%")
                            ->orWhere('name_en', 'like', "%{$search}%");
                    });
            });
        }

        $centers = $query->latest()->paginate(10);

        return response()->json($centers);
    }

    public function create()
    {
        $cities = City::orderBy('name_ar')->get();

        return view('centers.create', compact('cities'));
    }

    public function store(StoreCenterRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('centers', 'public');
        }

        Center::create($data);

        return response()->json([
            'success' => true,
            'message' => __('centers.created'),
        ]);
    }

    public function show(Center $center)
    {
        $center->load('city');
        $center->loadCount('users');

        return response()->json([
            'data' => $center,
            'can_edit' => true,
            'can_delete' => true,
        ]);
    }

    public function update(UpdateCenterRequest $request, Center $center)
    {
        $data = $request->validated();

        if ($request->hasFile('logo')) {
            if ($center->logo) {
                Storage::disk('public')->delete($center->logo);
            }
            $data['logo'] = $request->file('logo')->store('centers', 'public');
        } else {
            unset($data['logo']);
        }

        $center->update($data);

        return response()->json([
            'success' => true,
            'message' => __('centers.updated'),
        ]);
    }

    public function destroy(Center $center)
    {
        if ($center->logo) {
            Storage::disk('public')->delete($center->logo);
        }

        $center->delete();

        return response()->json([
            'success' => true,
            'message' => __('centers.deleted'),
        ]);
    }
}
