<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMedicalRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'national_id' => ['required', 'string', 'max:20'],
            'phone' => ['nullable', 'string', 'max:20'],
            'gender' => ['nullable', 'in:male,female'],
            'occupation' => ['nullable', 'string', 'max:255'],
            'date_of_birth' => ['nullable', 'date', 'before_or_equal:today'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'full_name.required' => __('medical_records.validation.full_name_required'),
            'full_name.max' => __('medical_records.validation.full_name_max'),
            'national_id.required' => __('medical_records.validation.national_id_required'),
            'national_id.max' => __('medical_records.validation.national_id_max'),
            'phone.max' => __('medical_records.validation.phone_max'),
            'gender.in' => __('medical_records.validation.gender_in'),
            'occupation.max' => __('medical_records.validation.occupation_max'),
            'date_of_birth.date' => __('medical_records.validation.date_of_birth_date'),
            'date_of_birth.before_or_equal' => __('medical_records.validation.date_of_birth_before'),
            'notes.max' => __('medical_records.validation.notes_max'),
        ];
    }
}
