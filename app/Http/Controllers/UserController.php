<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use App\Http\Resources\UserResource;
use App\Http\Helpers\ApiResponse;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users',
                'password' => 'required|min:6',
                'team_id' => 'nullable|exists:teams,id',
                'phone_num' => 'nullable|string|max:15',
                'emergency_phone_num' => 'nullable|string|max:15',
                'address' => 'nullable|string',
                'roles' => 'required', 
                'roles.*' => 'exists:roles,id',
                'profile_pic' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            // dd('Validation Passed', $validatedData);

            } catch (\Illuminate\Validation\ValidationException $e) {
                return ApiResponse::error('Validation failed', $e->errors(), 422);
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'address' => $request->address,
                'phone_num' => $request->phone_num,
                'emergency_phone_num' => $request->emergency_phone_num,
                'pm_id' => $request->pm_id,
                'password' => Hash::make($request->password),
                'team_id' => $request->team_id,
            ]);

            if ($request->hasFile('profile_pic')) {
                $file = $request->file('profile_pic');
                $filename = time() . '.' . $file->getClientOriginalExtension();
                $file->storeAs('public/profile_pics', $filename);

                $user->profile_pic = $filename;
                $user->save();
            }

            $user->roles()->attach($request->roles);

            return ApiResponse::success('User created successfully', new UserResource($user), 201);
    }

    public function index()
    {
        $users = User::with(['team', 'roles'])->get();
        return ApiResponse::success('Users fetched successfully', UserResource::collection($users));
    }

    public function show($id)
    {
        $user = User::with(['team', 'roles'])->find($id);

        if (!$user) {
            return ApiResponse::error('User not found', [], 404);
        }

        return ApiResponse::success('User details fetched successfully', new UserResource($user));
    }

    public function destroy($id)
    {
        $user = User::find($id);

        if (!$user) {
            return ApiResponse::error('User not found', [], 404);
        }

        $user->delete();
        return ApiResponse::success('User deleted successfully');
    }

    public function update(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return ApiResponse::error('User not found', [], 404);
        }

        try {
            $validatedData = $request->validate([
                'name' => 'sometimes|string|max:255',
                'email' => 'sometimes|email|unique:users,email,' . $id,
                'phone_num' => 'nullable|string|max:15',
                'emergency_phone_num' => 'nullable|string|max:15',
                'address' => 'nullable|string',
                'team_id' => 'nullable|exists:teams,id',
                'roles' => 'nullable|array',
                'roles.*' => 'exists:roles,id',
                'profile_pic' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::error('Validation Error', $e->errors(), 422);
        }

        $user->update($request->only(['name', 'email', 'phone', 'address', 'team_id','pm_id','emergency_phone_num']));

        if ($request->hasFile('profile_pic')) {
            $file = $request->file('profile_pic');
            $filename = time() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('public/profile_pics', $filename);
            $user->profile_pic = $filename;
        }

        if ($request->has('roles')) {
            $user->roles()->sync($request->roles);
        }

        return ApiResponse::success('User updated successfully', new UserResource($user));
    }

}
