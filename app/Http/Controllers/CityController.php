<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CityController extends Controller
{
    public function index()
    {
        return view('cities.index');
    }

    public function search(Request $request)
    {
        $search = $request->input('search', '');
        $cacheKey = 'cities_search_' . md5($search);

        $results = Cache::remember($cacheKey, 300, function () use ($search) {
            $query = City::query();

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name_ar', 'like', "%{$search}%")
                        ->orWhere('name_en', 'like', "%{$search}%");
                });
            }

            return $query->orderBy('name_ar')->limit(30)->get(['id', 'name_ar', 'name_en']);
        });

        return response()->json($results);
    }

    public function data(Request $request)
    {
        $query = City::withCount('centers');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name_ar', 'like', "%{$search}%")
                    ->orWhere('name_en', 'like', "%{$search}%");
            });
        }

        $cities = $query->latest()->paginate(10);

        return response()->json($cities);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name_ar' => ['required', 'string', 'max:255'],
            'name_en' => ['required', 'string', 'max:255'],
        ], [
            'name_ar.required' => __('cities.validation.name_ar_required'),
            'name_ar.max' => __('cities.validation.name_ar_max'),
            'name_en.required' => __('cities.validation.name_en_required'),
            'name_en.max' => __('cities.validation.name_en_max'),
        ]);

        $city = City::create($request->only(['name_ar', 'name_en']));

        ActivityLogger::log('created', $city);

        return response()->json([
            'success' => true,
            'city' => $city,
            'message' => __('cities.created'),
        ]);
    }

    public function show(City $city)
    {
        $city->loadCount('centers');

        return response()->json([
            'data' => $city,
            'can_edit' => true,
            'can_delete' => $city->centers_count === 0,
        ]);
    }

    public function update(Request $request, City $city)
    {
        $request->validate([
            'name_ar' => ['required', 'string', 'max:255'],
            'name_en' => ['required', 'string', 'max:255'],
        ], [
            'name_ar.required' => __('cities.validation.name_ar_required'),
            'name_ar.max' => __('cities.validation.name_ar_max'),
            'name_en.required' => __('cities.validation.name_en_required'),
            'name_en.max' => __('cities.validation.name_en_max'),
        ]);

        $oldValues = $city->getOriginal();
        $city->update($request->only(['name_ar', 'name_en']));

        ActivityLogger::log('updated', $city, $oldValues);

        return response()->json([
            'success' => true,
            'message' => __('cities.updated'),
        ]);
    }

    public function print(Request $request)
    {
        $query = City::withCount('centers');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name_ar', 'like', "%{$search}%")
                    ->orWhere('name_en', 'like', "%{$search}%");
            });
        }

        $cities = $query->orderBy('name_ar')->limit(500)->get();

        return view('cities.print', compact('cities'));
    }

    public function destroy(City $city)
    {
        if ($city->centers()->exists()) {
            return response()->json([
                'success' => false,
                'message' => __('cities.cannot_delete_has_centers'),
            ], 409);
        }

        ActivityLogger::log('deleted', $city);

        $city->delete();

        return response()->json([
            'success' => true,
            'message' => __('cities.deleted'),
        ]);
    }
}
