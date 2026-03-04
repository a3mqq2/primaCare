<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDispensingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'medicine_id' => ['required', 'exists:medicines,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'medicine_id.required' => __('medical_records.validation.medicine_required'),
            'medicine_id.exists' => __('medical_records.validation.medicine_exists'),
            'quantity.required' => __('medical_records.validation.quantity_required'),
            'quantity.integer' => __('medical_records.validation.quantity_integer'),
            'quantity.min' => __('medical_records.validation.quantity_min'),
        ];
    }
}
