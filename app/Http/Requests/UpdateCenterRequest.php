<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCenterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name_ar' => ['required', 'string', 'max:255'],
            'name_en' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,svg,webp', 'max:2048'],
            'city_id' => ['required', 'exists:cities,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'name_ar.required' => __('centers.validation.name_ar_required'),
            'name_ar.max' => __('centers.validation.name_max'),
            'name_en.required' => __('centers.validation.name_en_required'),
            'name_en.max' => __('centers.validation.name_max'),
            'city_id.required' => __('centers.validation.city_required'),
            'city_id.exists' => __('centers.validation.city_exists'),
            'phone.max' => __('centers.validation.phone_max'),
            'notes.max' => __('centers.validation.notes_max'),
            'logo.image' => __('centers.validation.logo_image'),
            'logo.max' => __('centers.validation.logo_max'),
        ];
    }
}
