<?php
namespace App\Http\Controllers;
use App\Models\Project;
use App\Models\ProjectMilestone;
use Illuminate\Http\Request;

class ProjectMilestoneController extends Controller
{
    public function index($projectId)
{
    $project = Project::findOrFail($projectId);
    $milestones = $project->milestones()->latest()->get();

    return view('admin.projects.project_milestones.index', compact('milestones', 'project'));
}
     public function store(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'title' => 'required|string|max:255',
            'summary' => 'required|string',
            'cost' => 'nullable|numeric',
            'status' => 'required|in:pending,in_progress,completed',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'add_to_budget' => 'nullable|boolean',
        ]);

        ProjectMilestone::create([
            'project_id' => $request->project_id,
            'title' => $request->title,
            'summary' => $request->summary,
            'cost' => $request->cost,
            'status' => $request->status,
            'add_to_budget' => $request->has('add_to_budget'),
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);

        return redirect()->back()->with('success', 'Milestone created successfully.');
    }
    
    public function destroy($id)
{
    $milestone = ProjectMilestone::findOrFail($id);
    $milestone->delete();

    return redirect()->back()->with('success', 'Milestone deleted successfully.');
}

}