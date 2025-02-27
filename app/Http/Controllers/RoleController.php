<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;
use App\Http\Helpers\ApiResponse;
use App\Http\Resources\RoleResource;

/**
 * @OA\Info(
 *      version="1.0.0",
 *      title="Role Management API",
 *      description="Swagger API documentation for Role management in Laravel",
 *      @OA\Contact(email="your-email@example.com")
 * )
 *
 * @OA\Tag(
 *     name="Roles",
 *     description="API Endpoints for managing roles"
 * )
 */
class RoleController extends Controller
{
    /**
     * @OA\Get(
     *      path="/api/roles",
     *      operationId="getRoles",
     *      tags={"Roles"},
     *      summary="Get all roles",
     *      description="Returns a list of roles",
     *      @OA\Response(response=200, description="Successful operation")
     * )
     */
    public function index()
    {
        $roles = Role::all();
        return ApiResponse::success('Roles fetched successfully', RoleResource::collection($roles));
    }

    /**
     * @OA\Get(
     *      path="/api/roles/{id}",
     *      operationId="getRoleById",
     *      tags={"Roles"},
     *      summary="Get role by ID",
     *      description="Returns role details",
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(response=200, description="Successful operation"),
     *      @OA\Response(response=404, description="Role not found")
     * )
     */
    public function show($id)
    {
        $role = Role::find($id);

        if (!$role) {
            return ApiResponse::error('Role not found', [], 404);
        }

        return ApiResponse::success('Role details fetched successfully', new RoleResource($role));
    }

    /**
     * @OA\Post(
     *      path="/api/roles",
     *      operationId="createRole",
     *      tags={"Roles"},
     *      summary="Create a new role",
     *      description="Creates a new role with a unique name",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"name"},
     *              @OA\Property(property="name", type="string", example="Admin")
     *          )
     *      ),
     *      @OA\Response(response=201, description="Role created successfully"),
     *      @OA\Response(response=422, description="Validation error")
     * )
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255|unique:roles'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::error('Validation Error', $e->errors(), 422);
        }

        $role = Role::create([
            'name' => $request->name,
        ]);

        return ApiResponse::success('Role created successfully', $role, 201);
    }

    /**
     * @OA\Put(
     *      path="/api/roles/{id}",
     *      operationId="updateRole",
     *      tags={"Roles"},
     *      summary="Update a role",
     *      description="Update an existing role's name",
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"name"},
     *              @OA\Property(property="name", type="string", example="Super Admin")
     *          )
     *      ),
     *      @OA\Response(response=200, description="Role updated successfully"),
     *      @OA\Response(response=404, description="Role not found"),
     *      @OA\Response(response=422, description="Validation error")
     * )
     */
    public function update(Request $request, $id)
    {
        $role = Role::find($id);

        if (!$role) {
            return ApiResponse::error('Role not found', [], 404);
        }

        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255|unique:roles,name,' . $id
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::error('Validation Error', $e->errors(), 422);
        }

        $role->update([
            'name' => $request->name,
        ]);

        return ApiResponse::success('Role updated successfully', $role);
    }

    /**
     * @OA\Delete(
     *      path="/api/roles/{id}",
     *      operationId="deleteRole",
     *      tags={"Roles"},
     *      summary="Delete a role",
     *      description="Delete a role by ID",
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(response=200, description="Role deleted successfully"),
     *      @OA\Response(response=404, description="Role not found")
     * )
     */
    public function destroy($id)
    {
        $role = Role::find($id);

        if (!$role) {
            return ApiResponse::error('Role not found', [], 404);
        }

        $role->delete();
        return ApiResponse::success('Role deleted successfully');
    }
}