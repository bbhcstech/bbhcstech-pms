<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Project;
use App\Models\User;
use App\Models\TaskLabel;
use App\Models\TaskCategory;
use App\Models\Country;
use App\Models\Department;
use App\Models\Designation;
use App\Models\ParentDepartment;
use App\Models\ProjectMilestone;
use App\Models\TaskNote;
use App\Notifications\TaskAssignedNotification;
use App\Models\UserActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; // Add this line to import DB facade
use Carbon\Carbon;

class TaskController extends Controller
{
  


public function index(Request $request, Project $project = null) 
{
    $query = Task::with(['project', 'assignees', 'subTasks.assignee', 'timers'])
        ->whereNull('parent_id');

    // Filter by project
    if ($project) {
        $query->where('project_id', $project->id);
    }

    // Search filter
    if ($request->filled('search')) {
        $query->where(function ($q) use ($request) {
            $q->where('title', 'like', '%' . $request->search . '%')
              ->orWhere('id', $request->search)
              ->orWhere('task_short_code', $request->search);
        });
    }

    // Status filter
    if ($request->filled('status')) {
        if ($request->status === 'pending') {
            $query->where('status', '!=', 'Completed');
        } else {
            $query->where('status', $request->status);
        }
    }

    // Date filters
    if ($request->filled('start_date') && $request->filled('end_date')) {
        // Range filter
        $query->whereBetween('due_date', [
            $request->start_date,
            $request->end_date
        ]);
    } elseif ($request->filled('start_date') && !$request->filled('end_date')) {
        // Exact match if only start_date provided
        $query->whereDate('due_date', $request->start_date);
    } elseif ($request->filled('end_date') && !$request->filled('start_date')) {
        // Exact match if only end_date provided
        $query->whereDate('due_date', $request->end_date);
    }

    // Exclude completed tasks
    if ($request->boolean('exclude_completed')) {
        $query->where('status', '!=', 'Completed');
    }

    $tasks = $query->orderBy('created_at', 'desc')->get();

    return view('admin.tasks.index', compact('tasks', 'project'));
}



public function getAssignedToUsersAttribute()
{
    if (!$this->assigned_to) return collect(); // return empty collection if null

    $ids = explode(',', $this->assigned_to);
    return \App\Models\User::whereIn('id', $ids)->get();
}
public function create(Request $request)
{
    $duplicateTask = null;
    $assignedUserIds = [];

    $projects = Project::all();
    $users = User::all();
    $labels = TaskLabel::with('project')->get();
    $taskCategories = TaskCategory::all();
    $milestones = ProjectMilestone::all();

    $project = null;

    // If coming from a specific project page
    if ($request->has('project_id')) {
        $project = Project::find($request->project_id);
    }

    // Handle duplication
    if ($request->has('duplicate_id')) {
        $duplicateTask = Task::with(['assignees', 'task_labels'])->findOrFail($request->duplicate_id);

        $project = $duplicateTask->project ?? null;
        $assignedUserIds = $duplicateTask->assignees()->pluck('users.id')->toArray();
    }

    // Load available tasks for dependency dropdown
    $tasks = Task::whereNull('parent_id')
        ->when($project, function ($query) use ($project, $duplicateTask) {
            $query->where('project_id', $project->id);
            if ($duplicateTask) {
                $query->where('id', '!=', $duplicateTask->id); // exclude itself
            }
        })
        ->get();

    // departments, designations, countries, etc.
    $departments = Department::with('parent')->latest()->get();
    $designations = Designation::all();
    $users = User::all(); // for Reporting To
    $countries = Country::all();
    $employee = null;
    $prtdepartments = ParentDepartment::latest()->get();

    // Generate next task code (use duplicateTask code if duplicating)
    if ($duplicateTask && !empty($duplicateTask->task_short_code)) {
        $generatedTaskCode = $duplicateTask->task_short_code;
    } else {
        // get next auto-increment for tasks table
        $row = DB::select("SHOW TABLE STATUS LIKE 'tasks'");
        $nextId = $row && isset($row[0]->Auto_increment) ? $row[0]->Auto_increment : (Task::max('id') + 1);
        $generatedTaskCode = 'TASK_' . str_pad($nextId, 3, '0', STR_PAD_LEFT);
    }

    return view('admin.tasks.create', compact(
        'projects',
        'users',
        'tasks',
        'project',
        'labels',
        'taskCategories',
        'milestones',
        'duplicateTask',
        'assignedUserIds',
        'departments','designations','countries','employee','prtdepartments',
        'generatedTaskCode'      // <-- pass to view
    ));
}



//     public function create(Request $request)
// {
//     $projects = Project::all();
//     $users = User::all();
//     $tasks = Task::whereNull('parent_id')->get();
//     $labels = TaskLabel::with('project')->get();
//     $taskCategories =TaskCategory::all();
//     $milestones = ProjectMilestone::all();

//     $project = null;
//     if ($request->has('project_id')) {
//         $project = Project::find($request->project_id);
//         $tasks = Task::where('project_id', $project->id)->whereNull('parent_id')->get();
//     }

//     return view('admin.tasks.create', compact('projects', 'users', 'tasks', 'project','labels','taskCategories','milestones'));
// }

 public function store(Request $request)
{
   // return $request->all();
    $request->validate([
        'task_short_code'  => 'required|string|max:255',
        'title'             => 'required|string|max:255',
        'project_id'        => 'required|exists:projects,id',
        'start_date'        => 'nullable|date',
        // 'due_date'          => $request->has('without_due_date') ? 'nullable' : 'nullable|date',
        'due_date' => $request->has('without_due_date') ? 'nullable' : 'nullable|date|after_or_equal:start_date',
         'assigned_to.*' => 'nullable|integer|exists:users,id',
        'description'       => 'nullable|string',
        'task_labels'       => 'nullable|array',
        'task_labels.*'     => 'exists:task_label_list,id',
        'milestone_id'      => 'nullable|integer',
        'board_column_id'   => 'nullable|integer',
        'is_private' => 'nullable',
        'billable' => 'nullable',
        'repeat' => 'nullable',
        'estimate_hours'    => 'nullable|integer|min:0',
        'estimate_minutes'  => 'nullable|integer|min:0',
        'repeat_complete'   => 'nullable|boolean',
        'repeat_count'      => 'nullable|integer',
        'repeat_type'       => 'nullable|in:day,week,month,year',
        'repeat_cycles'     => 'nullable|integer',
        'dependent_task_id' => 'nullable|integer',
        'priority'          => 'nullable|string',
        'category_id'       => 'nullable|integer',
        'parent_id'         => 'nullable|integer',
        'status'            => 'nullable|string',
        'image_url' => [
    'nullable',
    'file',
    'mimes:jpg,jpeg,png,pdf,docx,xlsx,txt,zip', // allowed file types
    function ($attribute, $value, $fail) {
        $extension = strtolower($value->getClientOriginalExtension());
        if (in_array($extension, ['exe', 'sql'])) {
            $fail("Files with the .$extension extension are not allowed.");
        }
    },
]


    ]);
    
    
    
    $profileImagePath = null;

    // Handle profile image upload
    if ($request->hasFile('image_url')) {
        $image = $request->file('image_url');
        $imageName = time() . '-' . $image->getClientOriginalName();
        $image->move(public_path('admin/uploads/task-files'), $imageName);

        $profileImagePath = 'admin/uploads/task-files/' . $imageName;
    }

    // Convert task labels to comma-separated string if provided
    $task_labels = $request->has('task_label_list') 
        ? implode(',', $request->task_label_list) 
        : null;

    $task = Task::create([
        'task_short_code'  => $request->task_short_code,
        'title'             => $request->title,
        'project_id'        => $request->project_id,
        'start_date'        => $request->start_date,
        'due_date' => $request->has('without_due_date') ? null : $request->due_date,
        'assigned_to' => $request->has('assigned_to') ? implode(',', $request->assigned_to) : null,
        'description'       => $request->description,
        'task_labels'       => $request->has('task_labels') ? implode(',', $request->task_labels) : null,
        'milestone_id'      => $request->milestone_id,
        'board_column_id'   => $request->board_column_id ?? 1,
        'is_private'        => $request->has('is_private'),
        'billable'          => $request->has('billable'),
        'estimate_hours'    => $request->estimate_hours,
        'estimate_minutes'  => $request->estimate_minutes,
        'repeat'            => $request->has('repeat'),
        'repeat_complete'   => $request->has('repeat_complete'),
        'repeat_count'      => $request->repeat_count,
        'repeat_type'       => $request->repeat_type,
        'repeat_cycles'     => $request->repeat_cycles,
        'dependent_task_id' => $request->dependent_task_id,
        'image_url'         => $profileImagePath,
        'priority'          => $request->priority,
        'category_id'       => $request->category_id,
        'parent_id'         => $request->parent_id,
        'status'            => $request->status ?? 'To Do',
        'is_completed'      => 0,
    ]);
    
    UserActivity::create([
    'company_id' => auth()->user()->company_id,
    'user_id' => auth()->id(),
    'activity' => 'Updated task: ' . $task->title,
]);

    if ($request->has('assigned_to')) {
    $task->assignees()->sync($request->assigned_to);

    // 🔔 Send notification to each assigned user
    foreach ($request->assigned_to as $userId) {
        $user = User::find($userId);
        if ($user) {
            $user->notify(new TaskAssignedNotification($task));
        }
    }
}


    // Redirect based on button clicked
    if ($request->input('action') === 'save_add_more') {
        return redirect()->back()->with('success', 'Task created. Add another.');
    }

    // Redirect to project board if coming from board
    if ($request->has('redirect_to_board') && $request->input('redirect_to_board') == 'yes') {
        return redirect()->route('projects.tasks.board', $request->project_id)
                         ->with('success', 'Task created successfully.');
    }

    return redirect()->route('tasks.index')->with('success', 'Task created successfully.');
}

  public function edit(Task $task)
{
    $task->load(['assignees']); // eager load to avoid null

    $projects = Project::all();
    $users = User::all();
    $labels = TaskLabel::with('project')->get();
    $taskCategories = TaskCategory::all();
    $milestones = ProjectMilestone::all();
    $project = $task->project ?? null;
    

    $tasks = Task::whereNull('parent_id')
    ->where('id', '!=', $task->id)
    ->when($project, function ($query) use ($project) {
        $query->where('project_id', $project->id);
    })
    ->get();
    $assignedUserIds = $task->assignees()->pluck('users.id')->toArray();
    
       $departments = Department::all();
        
        $designations = Designation::all();
         $departments = Department::with('parent')->latest()->get();
        $users = User::all(); // for Reporting To
       $countries = Country::all();
       $employee = null;
       $prtdepartments = ParentDepartment::latest()->get();

    return view('admin.tasks.edit', compact(
        'task',
        'projects',
        'users',
        'labels',
        'taskCategories',
        'tasks',
        'project',
        'milestones', 'assignedUserIds',
        'departments','designations','countries','employee','prtdepartments'
    ));
}


public function update(Request $request, Task $task)
{
    $request->validate([
        'task_short_code'  => 'required|string|max:255',
        'title'             => 'required|string|max:255',
        'project_id'        => 'required|exists:projects,id',
        'start_date'        => 'nullable|date',
        // 'due_date'          => $request->has('without_due_date') ? 'nullable' : 'nullable|date',
        'due_date' => $request->has('without_due_date') ? 'nullable' : 'nullable|date|after_or_equal:start_date',
        'assigned_to.*'     => 'nullable|integer|exists:users,id',
        'description'       => 'nullable|string',
        'task_labels'       => 'nullable|array',
        'task_labels.*'     => 'exists:task_label_list,id',
        'milestone_id'      => 'nullable|integer',
        'board_column_id'   => 'nullable|integer',
        'is_private'        => 'nullable',
        'billable'          => 'nullable',
        'repeat'            => 'nullable',
        'estimate_hours'    => 'nullable|integer|min:0',
        'estimate_minutes'  => 'nullable|integer|min:0',
        'repeat_complete'   => 'nullable|boolean',
        'repeat_count'      => 'nullable|integer',
        'repeat_type'       => 'nullable|in:day,week,month,year',
        'repeat_cycles'     => 'nullable|integer',
        'dependent_task_id' => 'nullable|integer',
        'priority'          => 'nullable|string',
        'category_id'       => 'nullable|integer',
        'parent_id'         => 'nullable|integer',
        'status'            => 'nullable|string',
        'image_url' => [
            'nullable',
            'file',
            'mimes:jpg,jpeg,png,pdf,docx,xlsx,txt,zip', // allowed file types
            function ($attribute, $value, $fail) {
                $extension = strtolower($value->getClientOriginalExtension());
                if (in_array($extension, ['exe', 'sql'])) {
                    $fail("Files with the .$extension extension are not allowed.");
                }
            },
        ]

    ]);

    // Handle file update
    $profileImagePath = $task->image_url;

    if ($request->hasFile('image_url')) {
        // Delete old file
        if ($task->image_url && file_exists(public_path($task->image_url))) {
            unlink(public_path($task->image_url));
        }

        $image = $request->file('image_url');
        $imageName = time() . '-' . $image->getClientOriginalName();
        $image->move(public_path('admin/uploads/task-files'), $imageName);
        $profileImagePath = 'admin/uploads/task-files/' . $imageName;
    }

    $task->update([
        'task_short_code'  => $request->task_short_code,
        'title'             => $request->title,
        'project_id'        => $request->project_id,
        'start_date'        => $request->start_date,
        'due_date'          => $request->has('without_due_date') ? null : $request->due_date,
        'assigned_to'       => $request->has('assigned_to') ? implode(',', $request->assigned_to) : null,
        'description'       => $request->description,
        'task_labels'       => $request->has('task_labels') ? implode(',', $request->task_labels) : null,
        'milestone_id'      => $request->milestone_id,
        'board_column_id'   => $request->board_column_id ?? $task->board_column_id,
        'is_private'        => $request->has('is_private'),
        'billable'          => $request->has('billable'),
        'estimate_hours'    => $request->estimate_hours,
        'estimate_minutes'  => $request->estimate_minutes,
        'repeat'            => $request->has('repeat'),
        'repeat_complete'   => $request->has('repeat_complete'),
        'repeat_count'      => $request->repeat_count,
        'repeat_type'       => $request->repeat_type,
        'repeat_cycles'     => $request->repeat_cycles,
        'dependent_task_id' => $request->dependent_task_id,
        'image_url'         => $profileImagePath,
        'priority'          => $request->priority,
        'category_id'       => $request->category_id,
        'parent_id'         => $request->parent_id,
        'status'            => $request->status ?? $task->status,
    ]);
    
    UserActivity::create([
    'company_id' => auth()->user()->company_id,
    'user_id' => auth()->id(),
    'activity' => 'Updated task: ' . $task->title,
]);


    // Sync assigned users
    if ($request->has('assigned_to')) {
    $task->assignees()->sync($request->assigned_to);

    foreach ($request->assigned_to as $userId) {
        $user = User::find($userId);
        if ($user) {
            $user->notify(new TaskAssignedNotification($task));
        }
    }
}


    return redirect()->route('projects.tasks.index', $task->project_id)
                     ->with('success', 'Task updated successfully.');
}


    public function destroy(Task $task)
    {
        $task->delete();
        return redirect()->route('tasks.index')->with('success', 'Task deleted successfully.');
    }

    private function checkAndCompleteParentTask(Task $subTask)
    {
        if ($subTask->parent_id) {
            $parent = Task::find($subTask->parent_id);
            if ($parent) {
                $allSubTasksCompleted = Task::where('parent_id', $parent->id)
                    ->where('is_completed', false)
                    ->count() === 0;

                if ($allSubTasksCompleted) {
                    $parent->is_completed = true;
                    $parent->save();
                }
            }
        }
    }

    private function uncompleteParentTaskIfNeeded(Task $subTask)
    {
        if ($subTask->parent_id) {
            $parent = Task::find($subTask->parent_id);
            if ($parent && $parent->is_completed) {
                $parent->is_completed = false;
                $parent->save();
            }
        }
    }

public function getTasksByProject($projectId)
{
    // Fetch only tasks (and sub-tasks if needed) for the given project
    $tasks = Task::where('project_id', $projectId)
    ->with('subTasks') // if subTasks() relation exists in Task model
    ->get();


    return response()->json($tasks);
}

// taskoard
public function taskBoard(Project $project)
{
    $tasks = Task::with(['assignee']) // no nesting needed
                 ->where('project_id', $project->id)
                 ->get(); // get all tasks (main, sub, sub-sub)

    $users = User::all();

    return view('admin.tasks.board', compact('tasks', 'project', 'users'));
}


public function updateStatus(Request $request, Task $task)
{
    \Log::info("TASK UPDATE", ['task_id' => $task->id, 'new_status' => $request->status]);

    $request->validate([
        'status' => 'required|string',
    ]);

    // ❗ Rule: If marking a parent task as 'Completed', check all sub-tasks
    if ($request->status === 'Completed' && $task->subTasks()->count() > 0) {
        $pendingSubTasks = $task->subTasks()->where(function ($q) {
            $q->where('is_completed', false)->orWhereNull('is_completed');
        })->count();

        if ($pendingSubTasks > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot mark task as Completed while sub-tasks are pending.',
            ], 422);
        }
    }

    if ($task->status === 'Waiting for Approval' && $request->status !== 'Waiting for Approval') {
        $task->status = $request->status;
    } elseif ($task->status !== 'Waiting for Approval') {
        $task->status = $request->status;
    } else {
        return response()->json([
            'success' => false,
            'message' => 'Tasks in Waiting for Approval cannot be updated directly from/to certain states.'
        ], 403);
    }

    // ✅ Sync completion
    $task->is_completed = $request->status === 'Completed' ? 1 : 0;

    // ✅ Set completed_on date only when status is Completed
    if ($request->status === 'Completed') {
        $task->completed_on = now();
    } else {
        $task->completed_on = null; // Clear date if status changed back
    }

    $task->save();

    return response()->json(['success' => true]);
}




public function storeLabel(Request $request)
{
    $request->validate([
        'label_name' => 'required|string|max:255',
        'color_code' => 'required|string|max:7', // e.g. #FF0000
        'description' => 'nullable|string',
        'project_id' => 'nullable|exists:projects,id',
    ]);

    $label = new TaskLabel();
    $label->name = $request->label_name;
    $label->color_code = $request->color_code;
    $label->description = $request->description;
    $label->project_id = $request->project_id;
    $label->save();

    return response()->json([
        'status' => 'success',
        'label' => $label
    ]);
}

// public function show(Task $task)
// {
//     $task->load(['project', 'assignee', 'parent','notes.user',
//     'activityLogs.user','subTasks.assignee','tasktimeLogs.user','activityLogs.subTask']);
//     return view('admin.tasks.show', compact('task'));
// }

public function show(Task $task)
{
    $task->load([
        'project', 'assignee', 'parent',
        'notes.user',
        'activityLogs.user',
        'subTasks.assignee',
        'tasktimeLogs.user', // from task_timers
         'category'
    ]);

    // Merge and sort both logs manually
    $history = collect();

    // Add task_history entries
    foreach ($task->activityLogs as $log) {
        $history->push([
            'type' => 'history',
            'user' => $log->user->name ?? 'System',
            'description' => $log->details ?? $log->description,
            'subtask' => optional($log->subTask)->title,
            'created_at' => $log->created_at,
        ]);
    }

    // Add task_timer entries
    foreach ($task->tasktimeLogs as $log) {
        if ($log->start_time) {
            $history->push([
                'type' => 'timer',
                'user' => $log->user->name ?? 'Unknown',
                'description' => 'Timer started',
                'created_at' => $log->start_time,
            ]);
        }

        if ($log->pause_time) {
            $history->push([
                'type' => 'timer',
                'user' => $log->user->name ?? 'Unknown',
                'description' => 'Timer paused',
                'created_at' => $log->pause_time,
            ]);
        }

        if ($log->end_time) {
            $history->push([
                'type' => 'timer',
                'user' => $log->user->name ?? 'Unknown',
                'description' => 'Timer stopped',
                'created_at' => $log->end_time,
            ]);
        }
    }

    // Sort all by time (desc)
    $history = $history->sortByDesc('created_at');

    return view('admin.tasks.show', compact('task', 'history'));
}


public function markComplete(Task $task)
{
    $task->status = 'Completed';
    $task->save();

    return redirect()->back()->with('success', 'Task marked as completed.');
}


public function uploadFile(Request $request, Task $task)
{
    $request->validate([
        'attachment' => 'required|file|max:2048', // max 2MB
    ]);

    if ($request->hasFile('attachment')) {
        // Delete old file if exists
        if ($task->image_url && file_exists(public_path($task->image_url))) {
            unlink(public_path($task->image_url));
        }

        // Upload new file to public/admin/uploads/task-files
        $file = $request->file('attachment');
        $fileName = time() . '-' . $file->getClientOriginalName();
        $file->move(public_path('admin/uploads/task-files'), $fileName);

        // Save to DB
        $task->image_url = 'admin/uploads/task-files/' . $fileName;
        $task->save();

        return redirect()->back()->with('success', 'File uploaded successfully.');
    }

    return redirect()->back()->with('error', 'No file was uploaded.');
}

public function deleteFile(Task $task)
{
    if ($task->image_url && file_exists(public_path($task->image_url))) {
        unlink(public_path($task->image_url));
        $task->image_url = null;
        $task->save();
    }

    return redirect()->back()->with('success', 'File deleted.');
}



public function storeNote(Request $request, $taskId)
{
    $request->validate([
        'note' => 'required|string|max:2000',
    ]);

    TaskNote::create([
        'task_id' => $taskId,
        'user_id' => auth()->id(),
        'note' => $request->note,
        'added_by' => auth()->id(),
        'last_updated_by' => auth()->id(),
    ]);

    return back()->with('success', 'Note added successfully.');
}

public function calendarView(Request $request)
{
    $query = Task::with(['project', 'assignee']);

    // ✅ Status filter
    if ($request->filled('status') && $request->status !== 'all') {
        if ($request->status === 'not finished') {
            $query->where('status', '!=', 'Completed');
        } else {
            $query->where('status', $request->status);
        }
    }

    // ✅ Project filter
    if ($request->filled('project_id') && $request->project_id !== 'all') {
        $query->where('project_id', $request->project_id);
    }

    // ✅ Search filter
    if ($request->filled('search')) {
        $query->where('title', 'like', '%' . $request->search . '%');
    }

    $tasks = $query->get()->map(function ($task) {
        return [
            'title' => $task->title,
            'start' => $task->start_date ?? $task->due_date,
            'end'   => $task->due_date,
            'url'   => route('tasks.show', $task->id),
            'color' => $task->status === 'Completed' ? '#198754' : '#0d6efd',
        ];
    });

    return view('admin.tasks.calendar', [
        'tasks' => $tasks,
        'projects' => Project::all(), // for project dropdown
    ]);
}

public function userTaskBoard(User $user, Request $request)
{
    $query = Task::with(['assignee']);

    // Filter by assigned user
    // $query->where('assigned_to', $user->id);

    // Optional filters
    if ($request->filled('search')) {
        $query->where(function($q) use ($request) {
            $q->where('title', 'like', '%'.$request->search.'%')
              ->orWhere('description', 'like', '%'.$request->search.'%');
        });
    }

    if ($request->filled('start_date') && $request->filled('end_date')) {
        $query->whereBetween('created_at', [$request->start_date, $request->end_date]);
    }

    $tasks = $query->get();
    $users = User::all();

    // ✅ Define board columns (statuses)
    $statuses = ['Incomplete', 'To Do', 'Doing', 'Completed', 'Waiting for Approval'];

    return view('admin.tasks.user-board', compact('tasks', 'user', 'users', 'statuses'));
}
// TaskController.php

public function waitingApproval(Request $request)
{
    $query = Task::with(['project', 'assignee']);

    // Project filter
    // if ($request->filled('project_id')) {
    //     $query->where('project_id', $request->project_id);
    // }

    // Assigned to filter
    // if ($request->filled('assigned_to')) {
    //     $query->where('assigned_to', $request->assigned_to);
    // }

    // Duration filter (start_date to end_date)
    if ($request->filled('duration')) {
        $dates = explode(' to ', $request->duration);
        if(count($dates) == 2) {
            $query->whereBetween('start_date', [$dates[0], $dates[1]]);
        }
    }

    // Search filter
    if ($request->filled('search')) {
        $query->where('title', 'like', '%' . $request->search . '%');
    }

    // Only waiting for approval
    $tasks = $query->where('status', 'Waiting for Approval')->get();

    // Pass projects & employees for filters
    $projects = Project::all();
    $employees = User::all();

    return view('admin.tasks.waiting-approval', compact('tasks', 'projects', 'employees'));
}

public function bulkStatusUpdate(Request $request)
{
    $request->validate([
        'ids' => 'required|array',
        'status' => 'required|string',
    ]);

    Task::whereIn('id', $request->ids)->update(['status' => $request->status]);

    return response()->json(['success' => true]);
}
public function bulkDelete(Request $request)
{
    $request->validate([
        'ids'   => 'required|array',
        'ids.*' => 'integer|exists:tasks,id',
    ]);

    $ids = $request->ids;

    $deleted = Task::whereIn('id', $ids)->delete(); // number of deleted rows

    // If called via AJAX (Bulk Delete button)
    if ($request->ajax()) {
        return response()->json([
            'success' => true,
            'deleted' => $deleted,
        ]);
    }

    // Fallback if you ever call it via normal form submit
    if ($deleted === 0) {
        return back()->with('error', 'No tasks deleted.');
    }

    return back()->with('success', 'Selected tasks deleted successfully.');
}




}
