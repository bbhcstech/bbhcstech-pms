<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\ProjectFile;
use Illuminate\Support\Facades\Storage;

class ProjectFileController extends Controller
{
    public function index(Project $project)
    {
        $files = $project->files;
        return view('admin.projects.files.index', compact('project', 'files'));
    }

  
    public function store(Request $request, Project $project)
    {
        $request->validate([
            'file' => 'required|file|max:10240', // Max 10MB
        ]);
    
        $uploadedFile = $request->file('file');
        $fileName = time() . '-' . $uploadedFile->getClientOriginalName();
        $uploadedFile->move(public_path('admin/uploads/project-files'), $fileName);
        $filePath = 'admin/uploads/project-files/' . $fileName;
    
        ProjectFile::create([
            'project_id' => $project->id,
            'filename' => $fileName,
            'file_path' => $filePath,
            'uploaded_by' => auth()->id(),
        ]);
    
        return back()->with('success', 'File uploaded successfully.');
    }

    public function destroy(Project $project, ProjectFile $file)
    {
        Storage::delete($file->file_path);
        $file->delete();

        return back()->with('success', 'File deleted.');
    }
}
