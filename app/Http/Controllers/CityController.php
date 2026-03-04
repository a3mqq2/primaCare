<?php

namespace App\Http\Controllers;

use App\Models\City;
use Illuminate\Http\Request;

class CityController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name_ar' => ['required', 'string', 'max:255'],
            'name_en' => ['required', 'string', 'max:255'],
        ], [
            'name_ar.required' => __('centers.validation.city_name_ar_required'),
            'name_en.required' => __('centers.validation.city_name_en_required'),
        ]);

        $city = City::create($request->only(['name_ar', 'name_en']));

        return response()->json([
            'success' => true,
            'city' => $city,
            'message' => __('centers.city_created'),
        ]);
    }
}
