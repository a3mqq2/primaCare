<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMedicineRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('medicines', 'name')->ignore($this->route('medicine'))],
            'description' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => __('medicines.validation.name_required'),
            'name.max' => __('medicines.validation.name_max'),
            'name.unique' => __('medicines.validation.name_unique'),
            'description.max' => __('medicines.validation.description_max'),
        ];
    }
}
