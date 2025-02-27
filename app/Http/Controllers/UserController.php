<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use App\Http\Resources\UserResource;
use App\Http\Helpers\ApiResponse;
use Illuminate\Support\Facades\Hash;

/**
 * @OA\Tag(
 *     name="Users",
 *     description="API Endpoints for User Management"
 * )
 */
class UserController extends Controller
{
    /**
     * @OA\Post(
     *      path="/api/users",
     *      operationId="storeUser",
     *      tags={"Users"},
     *      summary="Create a new user",
     *      description="Stores a new user and assigns roles",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"name", "email", "password", "roles"},
     *              @OA\Property(property="name", type="string", example="John Doe"),
     *              @OA\Property(property="email", type="string", example="johndoe@example.com"),
     *              @OA\Property(property="password", type="string", example="password123"),
     *              @OA\Property(property="team_id", type="integer", nullable=true),
     *              @OA\Property(property="phone_num", type="string", nullable=true),
     *              @OA\Property(property="emergency_phone_num", type="string", nullable=true),
     *              @OA\Property(property="address", type="string", nullable=true),
     *              @OA\Property(property="roles", type="array", @OA\Items(type="integer")),
     *              @OA\Property(property="profile_pic", type="string", format="binary", nullable=true)
     *          )
     *      ),
     *      @OA\Response(response=201, description="User created successfully"),
     *      @OA\Response(response=422, description="Validation error")
     * )
     */
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
                'roles' => 'required|array', 
                'roles.*' => 'exists:roles,id',
                'profile_pic' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::error('Validation failed', $e->errors(), 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'address' => $request->address,
            'phone_num' => $request->phone_num,
            'emergency_phone_num' => $request->emergency_phone_num,
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

    /**
     * @OA\Get(
     *      path="/api/users",
     *      operationId="getUsers",
     *      tags={"Users"},
     *      summary="Get all users",
     *      description="Fetches all users with their teams and roles",
     *      @OA\Response(response=200, description="Users fetched successfully")
     * )
     */
    public function index()
    {
        $users = User::with(['team', 'roles'])->get();
        return ApiResponse::success('Users fetched successfully', UserResource::collection($users));
    }

    /**
     * @OA\Get(
     *      path="/api/users/{id}",
     *      operationId="getUserById",
     *      tags={"Users"},
     *      summary="Get user details",
     *      description="Fetches user details by ID",
     *      @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *      @OA\Response(response=200, description="User details fetched successfully"),
     *      @OA\Response(response=404, description="User not found")
     * )
     */
    public function show($id)
    {
        $user = User::with(['team', 'roles'])->find($id);
        if (!$user) {
            return ApiResponse::error('User not found', [], 404);
        }
        return ApiResponse::success('User details fetched successfully', new UserResource($user));
    }

    /**
     * @OA\Delete(
     *      path="/api/users/{id}",
     *      operationId="deleteUser",
     *      tags={"Users"},
     *      summary="Delete a user",
     *      description="Deletes a user by ID",
     *      @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *      @OA\Response(response=200, description="User deleted successfully"),
     *      @OA\Response(response=404, description="User not found")
     * )
     */
    public function destroy($id)
    {
        $user = User::find($id);
        if (!$user) {
            return ApiResponse::error('User not found', [], 404);
        }
        $user->delete();
        return ApiResponse::success('User deleted successfully');
    }
}
