<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Http\Helpers\ApiResponse;

class ProjectController extends Controller
{
    public function index()
    {
        return ApiResponse::success('Projects fetched successfully', Project::with('client', 'salesTeam')->get());
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
}
