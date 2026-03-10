<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCenterRequest;
use App\Http\Requests\UpdateCenterRequest;
use App\Models\Center;
use App\Models\City;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class CenterController extends Controller
{
    public function index()
    {
        return view('centers.index');
    }

    public function searchCenters(Request $request)
    {
        $search = $request->input('search', '');
        $cacheKey = 'centers_search_' . md5($search);

        $results = Cache::remember($cacheKey, 300, function () use ($search) {
            $query = Center::query();

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name_ar', 'like', "%{$search}%")
                        ->orWhere('name_en', 'like', "%{$search}%");
                });
            }

            return $query->orderBy('name_ar')->limit(50)->get(['id', 'name_ar', 'name_en']);
        });

        return response()->json($results);
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

        if ($cityId = $request->input('city_id')) {
            $query->where('city_id', $cityId);
        }

        $centers = $query->latest()->paginate(10);

        return response()->json($centers);
    }

    public function create()
    {
        return view('centers.create');
    }

    public function store(StoreCenterRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('centers', 'public');
        }

        $center = Center::create($data);

        ActivityLogger::log('created', $center);

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

        $oldValues = $center->getOriginal();
        $center->update($data);

        ActivityLogger::log('updated', $center, $oldValues);

        return response()->json([
            'success' => true,
            'message' => __('centers.updated'),
        ]);
    }

    public function print(Request $request)
    {
        $query = Center::with('city');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name_ar', 'like', "%{$search}%")
                    ->orWhere('name_en', 'like', "%{$search}%");
            });
        }

        if ($cityId = $request->input('city_id')) {
            $query->where('city_id', $cityId);
        }

        $centers = $query->orderBy('name_ar')->limit(500)->get();

        return view('centers.print', compact('centers'));
    }

    public function destroy(Center $center)
    {
        if ($center->users()->exists() || $center->medicalRecords()->exists()) {
            return response()->json([
                'success' => false,
                'message' => __('centers.cannot_delete_has_data'),
            ], 409);
        }

        if ($center->logo) {
            Storage::disk('public')->delete($center->logo);
        }

        ActivityLogger::log('deleted', $center);

        $center->delete();

        return response()->json([
            'success' => true,
            'message' => __('centers.deleted'),
        ]);
    }
}
