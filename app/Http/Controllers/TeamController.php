<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Team;
use App\Http\Resources\TeamResource;
use App\Http\Helpers\ApiResponse;

/**
 * @OA\Info(
 *      version="1.0.0",
 *      title="Team Management API",
 *      description="Swagger API documentation for Team management in Laravel",
 *      @OA\Contact(email="your-email@example.com")
 * )
 *
 * @OA\Tag(
 *     name="Teams",
 *     description="API Endpoints for managing teams"
 * )
 */
class TeamController extends Controller
{
    /**
     * @OA\Get(
     *      path="/api/teams",
     *      operationId="getTeams",
     *      tags={"Teams"},
     *      summary="Get all teams",
     *      description="Returns a list of teams",
     *      @OA\Response(response=200, description="Successful operation")
     * )
     */
    public function index()
    {
        $teams = Team::with('users')->get();
        return ApiResponse::success('Teams fetched successfully', TeamResource::collection($teams));
    }

    /**
     * @OA\Get(
     *      path="/api/teams/{id}",
     *      operationId="getTeamById",
     *      tags={"Teams"},
     *      summary="Get team by ID",
     *      description="Returns team details",
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(response=200, description="Successful operation"),
     *      @OA\Response(response=404, description="Team not found")
     * )
     */
    public function show($id)
    {
        $team = Team::with('users')->find($id);

        if (!$team) {
            return ApiResponse::error('Team not found', [], 404);
        }

        return ApiResponse::success('Team details fetched successfully', new TeamResource($team));
    }

    /**
     * @OA\Post(
     *      path="/api/teams",
     *      operationId="createTeam",
     *      tags={"Teams"},
     *      summary="Create a new team",
     *      description="Creates a new team with a unique name",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"name"},
     *              @OA\Property(property="name", type="string", example="Development Team")
     *          )
     *      ),
     *      @OA\Response(response=201, description="Team created successfully"),
     *      @OA\Response(response=422, description="Validation error")
     * )
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255|unique:teams'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::error('Validation Error', $e->errors(), 422);
        }

        $team = Team::create([
            'name' => $request->name,
        ]);

        return ApiResponse::success('Team created successfully', $team, 201);
    }

    /**
     * @OA\Put(
     *      path="/api/teams/{id}",
     *      operationId="updateTeam",
     *      tags={"Teams"},
     *      summary="Update a team",
     *      description="Update an existing team's name",
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
     *              @OA\Property(property="name", type="string", example="QA Team")
     *          )
     *      ),
     *      @OA\Response(response=200, description="Team updated successfully"),
     *      @OA\Response(response=404, description="Team not found"),
     *      @OA\Response(response=422, description="Validation error")
     * )
     */
    public function update(Request $request, $id)
    {
        $team = Team::find($id);

        if (!$team) {
            return ApiResponse::error('Team not found', [], 404);
        }

        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255|unique:teams,name,' . $id
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::error('Validation Error', $e->errors(), 422);
        }

        $team->update([
            'name' => $request->name,
        ]);

        return ApiResponse::success('Team updated successfully', $team);
    }

    /**
     * @OA\Delete(
     *      path="/api/teams/{id}",
     *      operationId="deleteTeam",
     *      tags={"Teams"},
     *      summary="Delete a team",
     *      description="Delete a team by ID",
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(response=200, description="Team deleted successfully"),
     *      @OA\Response(response=404, description="Team not found")
     * )
     */
    public function destroy($id)
    {
        $team = Team::find($id);

        if (!$team) {
            return ApiResponse::error('Team not found', [], 404);
        }

        $team->delete();
        return ApiResponse::success('Team deleted successfully');
    }
}
