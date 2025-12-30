<?php
namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectNote;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectNoteController extends Controller
{
    public function index($projectId)
    {
        $project = Project::findOrFail($projectId);
        $notes = ProjectNote::where('project_id', $projectId)->latest()->get();

        return view('admin.project-notes.index', compact('project', 'notes'));
    }

    public function create($projectId)
    {
        $project = Project::findOrFail($projectId);
        $employees = User::all();
        return view('admin.project-notes.create', compact('project','employees'));
    }

   public function store(Request $request, $projectId)
    {
        $request->validate([
            'title' => 'required|string|max:191',
            'type' => 'required|in:0,1',
            'details' => 'required|string',
            'employee_id' => 'nullable|exists:users,id',
            'is_client_show' => 'nullable|boolean',
            'ask_password' => 'nullable|boolean',
        ]);
    
        ProjectNote::create([
            'project_id'        => $projectId,
            'title'             => $request->title,
            'type'              => $request->type,
            'client_id'         => null, // You can update this if client is involved
            'employee_id'       => $request->employee_id, // only for private
            'is_client_show'    => $request->has('is_client_show') ? 1 : 0,
            'ask_password'      => $request->has('ask_password') ? 1 : 0,
            'details'           => $request->details,
            'added_by'          => Auth::id(),
            'last_updated_by'   => Auth::id(),
        ]);
    
        return redirect()->route('projects.notes.index', $projectId)
                         ->with('success', 'Note added successfully!');
    }
    
    public function destroy($projectId, $noteId)
    {
        $note = ProjectNote::where('project_id', $projectId)->findOrFail($noteId);
        $note->delete();
    
        return redirect()->route('projects.notes.index', $projectId)
                         ->with('success', 'Note deleted successfully!');
    }
    public function show($projectId, $noteId)
    {
        $project = Project::findOrFail($projectId);
        
        //print_r($project);die;
    
        $note = ProjectNote::where('project_id', $projectId)
            ->with('employee') // for displaying assigned employee in view
            ->findOrFail($noteId);
    
        //Only creator or assigned employee can view private note
        if ($note->type == 1 && !in_array(Auth::id(), [$note->employee_id, $note->added_by])) {
            abort(403, 'Unauthorized to view this note.');
        }
    
        return view('admin.project-notes.show', compact('project', 'note'));
    }

    
    public function edit($projectId, $noteId)
    {
        $note = ProjectNote::where('project_id', $projectId)->findOrFail($noteId);
    
        if ($note->type == 1 && Auth::id() != $note->added_by) {
            abort(403, 'Unauthorized to edit this note.');
        }
        $project = Project::findOrFail($projectId);
        $employees = User::all();
        return view('admin.project-notes.edit', compact('note', 'employees', 'projectId','project'));
    }
    
    public function update(Request $request, $projectId, $noteId)
    {
        $note = ProjectNote::where('project_id', $projectId)->findOrFail($noteId);
    
        if ($note->type == 1 && Auth::id() != $note->added_by) {
            abort(403, 'Unauthorized to update this note.');
        }
    
        $request->validate([
            'title' => 'required|string|max:191',
            'details' => 'required|string',
        ]);
    
        $note->update([
            'title' => $request->title,
            'type' => $request->type ?? 0,
            'employee_id' => $request->employee_id,
            'is_client_show' => $request->is_client_show ? 1 : 0,
            'ask_password' => $request->ask_password ? 1 : 0,
            'details' => $request->details,
            'last_updated_by' => Auth::id(),
        ]);
        
        return redirect()->route('projects.notes.index', $projectId)
                         ->with('success', 'Note updated successfully!');
    
        
    }



}
