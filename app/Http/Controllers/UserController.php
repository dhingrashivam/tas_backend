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
}
