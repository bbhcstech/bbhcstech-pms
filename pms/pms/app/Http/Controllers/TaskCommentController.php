<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\TaskComment;

class TaskCommentController extends Controller
{
    public function store(Request $request, Task $task)
    {
        $request->validate([
            'comment' => 'required|string|max:1000',
        ]);

        TaskComment::create([
            'task_id' => $task->id,
            'comment' => $request->comment,
            'user_id' => auth()->id(),
            'added_by' => auth()->id(),
            'last_updated_by' => auth()->id(),
        ]);

        return redirect()->back()->with('success', 'Comment added successfully.');
    }
}
