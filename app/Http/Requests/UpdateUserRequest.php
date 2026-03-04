<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->route('user'))],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role' => ['required', Rule::in(['system_admin', 'center_employee'])],
            'center_id' => [Rule::requiredIf($this->input('role') === 'center_employee'), 'nullable', 'exists:centers,id'],
            'is_center_manager' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => __('users.validation.name_required'),
            'name.max' => __('users.validation.name_max'),
            'email.required' => __('users.validation.email_required'),
            'email.email' => __('users.validation.email_email'),
            'email.unique' => __('users.validation.email_unique'),
            'password.min' => __('users.validation.password_min'),
            'password.confirmed' => __('users.validation.password_confirmed'),
            'role.required' => __('users.validation.role_required'),
            'role.in' => __('users.validation.role_in'),
            'center_id.required' => __('users.validation.center_required'),
            'center_id.exists' => __('users.validation.center_exists'),
        ];
    }
}
