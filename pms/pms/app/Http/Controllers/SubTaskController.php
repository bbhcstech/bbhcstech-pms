<?php
namespace App\Http\Controllers;

use App\Models\SubTask;
use App\Models\Task;
use Illuminate\Http\Request;

class SubTaskController extends Controller
{
    public function store(Request $request, Task $task)
{
    $request->validate([
        'title' => 'required|string|max:255',
        'start_date' => 'nullable|date',
        'due_date' => 'nullable|date|after_or_equal:start_date',
        'assigned_to' => 'required|exists:users,id',
        'description' => 'nullable|string',
        'files' => 'nullable|mimes:jpg,jpeg,png,pdf,doc,docx,xlsx|max:2048',
    ]);

    $attachmentPath = null;

    if ($request->hasFile('file')) {
        $file = $request->file('file');
        $filename = time() . '-' . $file->getClientOriginalName();
        $file->move(public_path('admin/uploads/subtask-file'), $filename);
        $attachmentPath = 'admin/uploads/subtask-file/' . $filename;
    }

    SubTask::create([
        'task_id' => $task->id,
        'title' => $request->title,
        'start_date' => $request->start_date,
        'due_date' => $request->due_date,
        'status' => 'Incomplete',
        'assigned_to' => $request->assigned_to,
        'added_by' => auth()->id(),
        'last_updated_by' => auth()->id(),
        'description' => $request->description,
        'files' => $attachmentPath,
    ]);

    return redirect()->back()->with('success', 'Sub-task added successfully.');
}


public function update(Request $request, SubTask $subtask)
{
    $request->validate([
        'title' => 'required|string|max:255',
        'start_date' => 'nullable|date',
        'due_date' => 'nullable|date|after_or_equal:start_date',
        'assigned_to' => 'required|exists:users,id',
        'description' => 'nullable|string',
        'file' => 'nullable|mimes:jpg,jpeg,png,pdf,doc,docx,xlsx|max:2048',
    ]);

    // Delete old file if new one uploaded
    if ($request->hasFile('file')) {
        if ($subtask->file && file_exists(public_path($subtask->file))) {
            unlink(public_path($subtask->file));
        }

        $file = $request->file('file');
        $filename = time() . '-' . $file->getClientOriginalName();
        $file->move(public_path('admin/uploads/subtask-file'), $filename);
        $subtask->files = 'admin/uploads/subtask-file/' . $filename;
    }

    $subtask->update([
        'title' => $request->title,
        'start_date' => $request->start_date,
        'due_date' => $request->due_date,
        'assigned_to' => $request->assigned_to,
        'description' => $request->description,
        'last_updated_by' => auth()->id(),
    ]);

    return redirect()->back()->with('success', 'Sub-task updated successfully.');
}

public function destroy(SubTask $subtask)
{
    if ($subtask->file && file_exists(public_path($subtask->file))) {
        unlink(public_path($subtask->file));
    }

    $subtask->delete();

    return redirect()->back()->with('success', 'Sub-task deleted successfully.');
}

public function deleteFile(SubTask $subtask)
{
    if ($subtask->files && file_exists(public_path($subtask->files))) {
        unlink(public_path($subtask->files));
    }

    $subtask->files = null;
    $subtask->save();

    return back()->with('success', 'File deleted successfully.');
}



}
