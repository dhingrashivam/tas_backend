<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Http\Helpers\ApiResponse;
use App\Http\Resources\ProjectResource;

class ProjectController extends Controller
{
    public function index()
    {
        return ApiResponse::success('Projects fetched successfully', ProjectResource::collection(Project::with('client', 'salesTeam')->get()));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'sales_team_id' => 'required|exists:teams,id',
            'client_id' => 'required|exists:clients,id',
            'project_name' => 'required|string|max:255',
            'requirements' => 'nullable|string',
            'budget' => 'nullable|numeric',
            'deadline' => 'nullable|date'
        ]);

        $project = Project::create($validatedData);
        return ApiResponse::success('Project created successfully', $project, 201);
    }

    public function update(Request $request, $id)
    {
        $project = Project::find($id);

        if (!$project) {
            return ApiResponse::error('Project not found', [], 404);
        }

        $validatedData = $request->validate([
            'sales_team_id' => 'required|exists:teams,id',
            'client_id' => 'required|exists:clients,id',
            'project_name' => 'required|string|max:255',
            'requirements' => 'nullable|string',
            'budget' => 'nullable|numeric',
            'deadline' => 'nullable|date'
        ]);

        $project->update($validatedData);

        return ApiResponse::success('Project updated successfully', new ProjectResource($project));
    }

    public function destroy($id)
    {
        $project = Project::find($id);

        if (!$project) {
            return ApiResponse::error('Project not found', [], 404);
        }

        $project->delete();
        return ApiResponse::success('Project deleted successfully');
    }

}
