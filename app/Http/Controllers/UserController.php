<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Center;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function index()
    {
        $centers = Center::orderBy('name_ar')->get();

        return view('users.index', compact('centers'));
    }

    public function data(Request $request)
    {
        $query = User::with('center')->visibleTo(auth()->user());

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($role = $request->input('role')) {
            $query->where('role', $role);
        }

        if ($centerId = $request->input('center_id')) {
            $query->where('center_id', $centerId);
        }

        $users = $query->latest()->paginate(10);

        return response()->json($users);
    }

    public function create()
    {
        $centers = Center::orderBy('name_ar')->get();

        return view('users.create', compact('centers'));
    }

    public function store(StoreUserRequest $request)
    {
        $data = $request->validated();
        $authUser = auth()->user();

        if ($authUser->isCenterManager()) {
            $data['role'] = 'center_employee';
            $data['center_id'] = $authUser->center_id;
        }

        if ($data['role'] === 'system_admin') {
            $data['center_id'] = null;
            $data['is_center_manager'] = false;
        }

        $data['is_center_manager'] = !empty($data['is_center_manager']);
        $data['username'] = explode('@', $data['email'])[0] . rand(100, 999);

        while (User::where('username', $data['username'])->exists()) {
            $data['username'] = explode('@', $data['email'])[0] . rand(100, 999);
        }

        User::create($data);

        return response()->json([
            'success' => true,
            'message' => __('users.created'),
        ]);
    }

    public function show(User $user)
    {
        $this->authorizeAccess($user);
        $user->load('center');

        $authUser = auth()->user();
        $isSelf = $user->id === $authUser->id;
        $isLastAdmin = $user->isSystemAdmin() && User::where('role', 'system_admin')->count() <= 1;

        return response()->json([
            'data' => $user,
            'can_edit' => true,
            'can_delete' => !$isSelf && !$isLastAdmin,
        ]);
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $this->authorizeAccess($user);
        $data = $request->validated();
        $authUser = auth()->user();

        if ($authUser->isCenterManager()) {
            $data['role'] = 'center_employee';
            $data['center_id'] = $authUser->center_id;
        }

        if ($data['role'] === 'system_admin') {
            $data['center_id'] = null;
            $data['is_center_manager'] = false;
        }

        $data['is_center_manager'] = !empty($data['is_center_manager']);

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $user->update($data);

        return response()->json([
            'success' => true,
            'message' => __('users.updated'),
        ]);
    }

    public function destroy(User $user)
    {
        $this->authorizeAccess($user);

        if ($user->id === auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => __('users.cannot_delete_self'),
            ], 403);
        }

        if ($user->isSystemAdmin() && User::where('role', 'system_admin')->count() <= 1) {
            return response()->json([
                'success' => false,
                'message' => __('users.cannot_delete_last_admin'),
            ], 403);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => __('users.deleted'),
        ]);
    }

    public function impersonate(User $user)
    {
        $admin = auth()->user();

        if (!$admin->isSystemAdmin()) {
            abort(403);
        }

        if ($user->isSystemAdmin()) {
            return response()->json([
                'success' => false,
                'message' => __('users.impersonate_admin_error'),
            ], 403);
        }

        if ($user->id === $admin->id) {
            return response()->json([
                'success' => false,
                'message' => __('users.impersonate_self_error'),
            ], 403);
        }

        if (session()->has('impersonator_id')) {
            return response()->json([
                'success' => false,
                'message' => __('users.impersonate_already_active'),
            ], 403);
        }

        session()->put('impersonator_id', $admin->id);
        session()->put('impersonator_name', $admin->name);

        Log::info('Impersonation started', [
            'admin_id' => $admin->id,
            'admin_name' => $admin->name,
            'target_user_id' => $user->id,
            'target_user_name' => $user->name,
            'ip' => request()->ip(),
        ]);

        Auth::loginUsingId($user->id);

        return response()->json([
            'success' => true,
            'redirect' => route('dashboard'),
        ]);
    }

    public function leaveImpersonation()
    {
        $impersonatorId = session()->get('impersonator_id');

        if (!$impersonatorId) {
            abort(403);
        }

        $currentUser = auth()->user();

        Log::info('Impersonation ended', [
            'admin_id' => $impersonatorId,
            'impersonated_user_id' => $currentUser->id,
            'impersonated_user_name' => $currentUser->name,
            'ip' => request()->ip(),
        ]);

        session()->forget('impersonator_id');
        session()->forget('impersonator_name');

        Auth::loginUsingId($impersonatorId);

        return redirect()->route('dashboard');
    }

    private function authorizeAccess(User $user): void
    {
        $authUser = auth()->user();

        if ($authUser->isCenterManager() && $user->center_id !== $authUser->center_id) {
            abort(403);
        }
    }
}
