<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\User;
use App\Models\ProjectUser;
use Illuminate\Http\Request;

class ProjectUserController extends Controller
{
    // Show all members of a project
    public function index($projectId)
    {
        $project = Project::with('users')->findOrFail($projectId);
        $members = $project->users()->withPivot('hourly_rate', 'role')->get();

        return view('admin.projects.members.index', compact('project', 'members'));
    }

    // Add new member form
    public function create($projectId)
    {
        $project = Project::findOrFail($projectId);
        $users = User::all();

        return view('admin.projects.members.create', compact('project', 'users'));
    }

    // Store new member
    public function store(Request $request, $projectId)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'hourly_rate' => 'nullable|numeric',
            'role' => 'required|string|max:255'
        ]);

        $project = Project::findOrFail($projectId);

        $project->users()->attach($request->user_id, [
            'hourly_rate' => $request->hourly_rate ?? 0,
            'role' => $request->role
        ]);

        return redirect()->route('project-members.index', $projectId)->with('success', 'Member added.');
    }

    // Remove member
    public function destroy($projectId, $userId)
    {
        $project = Project::findOrFail($projectId);
        $project->users()->detach($userId);

        return redirect()->route('project-members.index', $projectId)->with('success', 'Member removed.');
    }
}
