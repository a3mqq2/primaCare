<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function index()
    {
        $user = auth()->user()->load('center');

        return view('profile', compact('user'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
        ], [
            'name.required' => __('profile.validation.name_required'),
            'email.required' => __('profile.validation.email_required'),
            'email.email' => __('profile.validation.email_email'),
            'email.unique' => __('profile.validation.email_unique'),
        ]);

        $user->update($request->only(['name', 'email']));

        return response()->json([
            'success' => true,
            'message' => __('profile.updated'),
        ]);
    }

    public function updatePassword(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'current_password' => ['required'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'current_password.required' => __('profile.validation.current_password_required'),
            'new_password.required' => __('profile.validation.new_password_required'),
            'new_password.min' => __('profile.validation.new_password_min'),
            'new_password.confirmed' => __('profile.validation.new_password_confirmed'),
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'errors' => [
                    'current_password' => [__('profile.current_password_incorrect')],
                ],
            ], 422);
        }

        $user->update([
            'password' => $request->new_password,
        ]);

        return response()->json([
            'success' => true,
            'message' => __('profile.password_updated'),
        ]);
    }
}
