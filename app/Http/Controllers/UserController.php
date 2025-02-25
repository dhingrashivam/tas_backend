<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
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
                'roles' => 'required', 
                'roles.*' => 'exists:roles,id',
                'profile_pic' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            // dd('Validation Passed', $validatedData);

            } catch (\Illuminate\Validation\ValidationException $e) {
                return response()->json(['errors' => $e->errors()], 422);
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
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

            return response()->json([
                'message' => 'User created successfully',
                'user' => $user->load('roles'),
                'profile_pic_url' => $user->profile_pic ? asset('storage/profile_pics/' . $user->profile_pic) : null
            ], 201);
    }
}
