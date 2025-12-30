<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Client;
use App\Models\Department;
use App\Models\Designation;
use App\Models\ParentDepartment;
use App\Models\EmployeeDetail;

use App\Models\Currency;
use App\Models\Country;
use App\Models\User;
use App\Models\Task;
use App\Models\ProjectActivity;
use App\Models\UserActivity;
use App\Models\ProjectCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Leave; // if you have this model; required by updatePaidStatus

class ProjectController extends Controller
{
   public function index(Request $request)
{
    $query = Project::with(['client', 'users.employeeDetail'])->whereNull('deleted_at');

    if ($request->filled('search')) {
        $query->where('name', 'like', '%' . $request->search . '%');
    }

    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    if ($request->filled('start_date')) {
        $query->whereDate('start_date', '>=', $request->start_date);
    }

    if ($request->filled('end_date')) {
        $query->whereDate('deadline', '<=', $request->end_date);
    }

    if ($request->filled('progress')) {
        $query->where(function($q) use ($request) {
            foreach ($request->progress as $range) {
                [$min, $max] = explode('-', $range);
                $q->orWhereBetween('completion_percent', [(int)$min, (int)$max]);
            }
        });
    }

    $projects = $query->orderBy('created_at', 'desc')->get();

    // ensure the users collection used for filters/UI also includes employeeDetail
    $users = User::with('employeeDetail')->select('id','name')->get();

    $departments = Department::with('parent')->latest()->get();
    $clients = Client::all();

    return view('admin.projects.index', compact('projects','users','departments','clients'));
}


public function create()
{
    $clients        = Client::all();
$users = User::select('users.id', 'users.name', 'employee_details.employee_id')
    ->join('employee_details', 'employee_details.user_id', '=', 'users.id')
    ->get();


    $categories     = ProjectCategory::all();
    $departments    = Department::with('parent')->latest()->get();
    $designations   = Designation::all();
    $countries      = Country::all();
    $employee       = null;
    $currency       = Currency::all();
    $prtdepartments = ParentDepartment::latest()->get();

    // preview next project code like TASK_005
    $now  = Carbon::now();
    $year = (int) $now->format('Y');

    // same FY logic as store(): Apr–Mar
    $fyStart = $now->month >= 4 ? $year : $year - 1;
    $fyEnd   = $fyStart + 1;

    $fyString = substr($fyStart, -2) . '-' . substr($fyEnd, -2);
    $prefix   = 'Xink' . $fyString . '/';

    $last = DB::table('projects')
        ->where('project_code', 'like', $prefix.'%')
        ->orderBy('id', 'desc')
        ->value('project_code');

    $lastNum = 0;
    if ($last && preg_match('/\/(\d{4})$/', $last, $m)) {
        $lastNum = (int) $m[1];
    }

    $nextNum        = $lastNum + 1;
    $nextProjectCode = $prefix . str_pad($nextNum, 4, '0', STR_PAD_LEFT);

    \Log::debug('Project create departments', $departments->pluck('dpt_name','id')->toArray());

    return view('admin.projects.create', compact(
        'clients',
        'users',
        'categories',
        'departments',
        'designations',
        'countries',
        'employee',
        'prtdepartments',
        'currency',
        'nextProjectCode'   // <- pass to blade
    ));
}




public function store(Request $request)
{
    Log::info('Project Request:', $request->all());

    $rules = [
        'name' => 'required|string|max:255',
        'start_date' => 'nullable|date',
        'without_deadline' => 'nullable',
        'employee_ids'   => 'sometimes|array',
        'employee_ids.*' => 'integer|exists:users,id',
        'currency_id' => 'nullable|integer',
        'project_budget' => 'nullable|numeric',
        'hours_allocated' => 'nullable|numeric',
    ];

    if (!$request->boolean('without_deadline')) {
        $rules['deadline'] = $request->filled('start_date')
            ? 'required|date|after_or_equal:start_date'
            : 'required|date';
    }

    if ($request->input('shortcode_option') === 'manual') {
        $rules['shortcode_manual'] = ['required','string','max:100', Rule::unique('projects','project_code')];
    }

    $request->validate($rules);

    // generate project code (keeps your existing logic)
    $projectCode = null;
    if ($request->input('shortcode_option') === 'manual') {
        $projectCode = $request->input('shortcode_manual');
    } else {
        DB::beginTransaction();
        try {
            $now = Carbon::now();
            $year = (int) $now->format('Y');
            $fyStart = $now->month >= 4 ? $year : $year - 1;
            $fyEnd = $fyStart + 1;
            $fyString = substr($fyStart, -2) . '-' . substr($fyEnd, -2);

            $prefix = 'Xink' . $fyString . '/';
            $like = $prefix . '%';

            $last = DB::table('projects')
                ->where('project_code', 'like', $like)
                ->lockForUpdate()
                ->orderBy('id', 'desc')
                ->value('project_code');

            $lastNum = 0;
            if ($last && preg_match('/\/(\d{4})$/', $last, $m)) {
                $lastNum = (int) $m[1];
            }

            $nextNum = $lastNum + 1;
            $projectCode = $prefix . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Shortcode generation failed: ' . $e->getMessage());
            return back()->withInput()->withErrors(['shortcode' => 'Failed to generate project code.']);
        }
    }

    try {
        $project = Project::create([
            'client_id' => $request->client_id,
            'name' => $request->name,
            'project_code' => $projectCode,
            'category_id' => $request->category_id,
            'department_id' => $request->department_id,
            'notes' => $request->notes,
            'public_gantt_chart' => $request->has('public_gantt_chart') ? 1 : 0,
            'public_taskboard' => $request->has('public_taskboard') ? 1 : 0,
            'client_access' => $request->has('client_access') ? 1 : 0,
            'need_approval_by_admin' => $request->has('need_approval_by_admin') ? 1 : 0,
            'public' => $request->has('public') ? 1 : 0,
            'description' => $request->description,
            'start_date' => $request->start_date,
            'deadline' => $request->boolean('without_deadline') ? null : $request->deadline,
            'without_deadline' => $request->boolean('without_deadline'),
            'currency_id' => $request->currency_id,
            'project_budget' => $request->project_budget,
            'hours_allocated' => $request->hours_allocated,
            'enable_miroboard' => $request->has('enable_miroboard') ? 1 : 0,
            'allow_client_notification' => $request->has('allow_client_notification') ? 1 : 0,
            'manual_timelog' => $request->has('manual_timelog') ? 1 : 0,
        ]);

        // commit shortcode transaction if used
        if ($request->input('shortcode_option') !== 'manual' && DB::transactionLevel() > 0) {
            DB::commit();
        }

        // file upload
        if ($request->hasFile('project_file')) {
            $file = $request->file('project_file');
            $fileName = time() . '-' . $file->getClientOriginalName();
            $file->move(public_path('admin/uploads/project-files'), $fileName);
            $project->project_file = 'admin/uploads/project-files/' . $fileName;
            $project->save();
        }

        ProjectActivity::create([
            'project_id' => $project->id,
            'activity' => auth()->user()->name . ' created a new project: ' . $project->name,
        ]);

        UserActivity::create([
            'company_id' => auth()->user()->company_id ?? 1,
            'user_id' => auth()->id(),
            'activity' => 'Created a new project: ' . $project->name,
        ]);

        // sync members from form (employee_ids)
        $project->users()->sync($request->input('employee_ids', []));

        // load users for log verification
        $project->load('users');
        Log::info('Project created with users:', ['project' => $project->toArray(), 'users' => $project->users->pluck('id','name')->toArray()]);

        return redirect()->route('projects.index')->with('success', 'Project created successfully.');
    } catch (\Exception $e) {
        if (DB::transactionLevel() > 0) {
            DB::rollBack();
        }
        Log::error('Project create failed: ' . $e->getMessage());
        return back()->withInput()->withErrors(['general' => 'Failed to create project.']);
    }
}



public function edit($id)
{
    $project = Project::with('users')->findOrFail($id);

    $clients        = Client::all();
    // eager-load employeeDetail to avoid N+1
    $users = User::select('users.id', 'users.name', 'employee_details.employee_id')
    ->join('employee_details', 'employee_details.user_id', '=', 'users.id')
    ->get();

    $categories     = ProjectCategory::all();
    $departments    = Department::with('parent')->latest()->get();
    $designations   = Designation::all();
    $countries      = Country::all();
    $employee       = null;
    $currency       = Currency::all();
    $prtdepartments = ParentDepartment::latest()->get();

    return view('admin.projects.edit', compact(
        'project',
        'clients',
        'users',
        'categories',
        'departments',
        'designations',
        'countries',
        'employee',
        'prtdepartments',
        'currency'
    ));
}



 public function update(Request $request, $id)
{
    Log::info('Project Update Request:', $request->all());

    $project = Project::findOrFail($id);

    $rules = [
        'client_id'       => 'required|exists:clients,id',
        'name'            => 'required|string|max:255',
        'project_code'    => ['nullable','string','max:50', Rule::unique('projects','project_code')->ignore($project->id)],
        'description'     => 'nullable|string',
        'start_date'      => 'nullable|date',
        'without_deadline'=> 'nullable',
        'employee_ids'    => 'sometimes|array',
        'employee_ids.*'  => 'integer|exists:users,id',
        'currency_id'     => 'nullable|integer',
        'project_budget'  => 'nullable|numeric',
        'hours_allocated' => 'nullable|numeric',
    ];

    if (! $request->boolean('without_deadline')) {
        $rules['deadline'] = $request->filled('start_date')
            ? 'required|date|after_or_equal:start_date'
            : 'required|date';
    }

    $validated = $request->validate($rules);

    DB::beginTransaction();

    try {
        // Use fill + save to avoid mass-assignment surprises. Make sure Project::$fillable includes these fields.
        $project->fill([
            'client_id' => $validated['client_id'],
            'name' => $validated['name'],
            'project_code' => $validated['project_code'] ?? $project->project_code,
            'category_id' => $request->input('category_id'),
            'department_id' => $request->input('department_id'),
            'notes' => $request->input('notes'),
            'public_gantt_chart' => $request->has('public_gantt_chart') ? 1 : 0,
            'public_taskboard' => $request->has('public_taskboard') ? 1 : 0,
            'client_access' => $request->has('client_access') ? 1 : 0,
            'need_approval_by_admin' => $request->has('need_approval_by_admin') ? 1 : 0,
            'public' => $request->has('public') ? 1 : 0,
            'description' => $request->input('description'),
            'start_date' => $request->input('start_date'),
            'deadline' => $request->boolean('without_deadline') ? null : $request->input('deadline'),
            'without_deadline' => $request->boolean('without_deadline'),
            'currency_id' => $request->input('currency_id'),
            'project_budget' => $request->input('project_budget'),
            'hours_allocated' => $request->input('hours_allocated'),
            'enable_miroboard' => $request->has('enable_miroboard') ? 1 : 0,
            'allow_client_notification' => $request->has('allow_client_notification') ? 1 : 0,
            'manual_timelog' => $request->has('manual_timelog') ? 1 : 0,
        ]);

        $project->save();

        if ($request->hasFile('project_file')) {
            try {
                if ($project->project_file && file_exists(public_path($project->project_file))) {
                    @unlink(public_path($project->project_file));
                }

                $file = $request->file('project_file');
                $fileName = time() . '-' . preg_replace('/\s+/', '_', $file->getClientOriginalName());
                $file->move(public_path('admin/uploads/project-files'), $fileName);
                $project->project_file = 'admin/uploads/project-files/' . $fileName;
                $project->save();
            } catch (\Throwable $ex) {
                // don't let file issues silently break everything - log and rethrow so transaction rolls back
                Log::error('Project file upload failed: ' . $ex->getMessage(), ['trace' => $ex->getTraceAsString()]);
                throw $ex;
            }
        }

        ProjectActivity::create([
            'project_id' => $project->id,
            'activity' => (auth()->user()->name ?? 'System') . ' updated the project: ' . $project->name,
        ]);

        UserActivity::create([
            'company_id' => auth()->user()->company_id ?? 1,
            'user_id' => auth()->id(),
            'activity' => 'Updated the project: ' . $project->name,
        ]);

        // Sync only if employee_ids present in request. This avoids unintentional clearing.
        if ($request->has('employee_ids')) {
            $project->users()->sync($request->input('employee_ids', []));
        }

        $project->load('users');

        Log::info('Project updated with users:', [
            'project' => $project->toArray(),
            'users' => $project->users->pluck('id','name')->toArray()
        ]);

        DB::commit();

        return redirect()->route('projects.index')->with('success', 'Project updated successfully.');
    } catch (\Throwable $e) {
        DB::rollBack();
        Log::error('Project update failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString(), 'request' => $request->all()]);
        return back()->withInput()->withErrors(['general' => 'Failed to update project. Check logs for details.']);
    }
}


    public function destroy($id)
    {
        Project::destroy($id);
        return redirect()->route('projects.index')->with('success', 'Project deleted successfully.');
    }

    public function show($id)
    {
        $project = Project::with(['client', 'users'])->findOrFail($id);
        return view('admin.projects.show', compact('project'));
    }

    public function ganttChart($projectId)
    {
        $project = Project::with('tasks')->findOrFail($projectId);
        return view('admin.projects.gantt', compact('project'));
    }

    public function getGanttTasks($projectId)
    {
        $tasks = Task::where('project_id', $projectId)
            ->whereNotNull('start_date')
            ->whereNotNull('due_date')
            ->get()
            ->map(function ($task) {
                $start = Carbon::parse($task->start_date);
                $end = Carbon::parse($task->due_date);
                if ($start->equalTo($end)) {
                    $end->addDay();
                }
                return [
                    'id' => $task->id,
                    'name' => $task->title,
                    'start' => $start->toDateString(),
                    'end' => $end->toDateString(),
                    'progress' => $task->is_completed ? 100 : 0,
                    'custom_class' => $task->is_completed ? 'bar-complete' : 'bar-incomplete'
                ];
            });

        return response()->json($tasks->values());
    }

    public function burndown(Request $request, $projectId)
    {
        $start = Carbon::parse($request->start_date ?? now()->subDays(7))->startOfDay();
        $end = Carbon::parse($request->end_date ?? now())->endOfDay();

        $labels = [];
        $actual = [];
        $ideal = [];

        $totalTasks = Task::where('project_id', $projectId)->count();
        $days = $start->diffInDays($end) + 1;

        if ($totalTasks > 0 && $days > 0) {
            for ($i = 0; $i < $days; $i++) {
                $date = $start->copy()->addDays($i);
                $labels[] = $date->format('d-m-Y');

                // Ideal line
                $ideal[] = $days > 1
                    ? round($totalTasks - ($totalTasks / ($days - 1)) * $i, 2)
                    : $totalTasks;

                // Actual line
                $completed = Task::where('project_id', $projectId)
                    ->whereDate('updated_at', '<=', $date)
                    ->where('status', 'Completed')
                    ->count();

                $actual[] = $totalTasks - $completed;
            }
        }

        $project = Project::with('tasks')->findOrFail($projectId);
        return view('admin.projects.burndown', compact('labels', 'actual', 'ideal', 'start', 'end', 'project'));
    }

    public function categorystore(Request $request)
    {
        $request->validate([
            'category_name' => 'required|string|max:255'
        ]);

        $cat = ProjectCategory::create([
            'category_name' => $request->category_name
        ]);

        return response()->json([
            'status' => 'success',
            'cat' => $cat
        ]);
    }

    public function categorydestroy($id)
    {
        $cat = ProjectCategory::findOrFail($id);
        $cat->delete();

        return response()->json(['status' => 'success']);
    }

public function clientstore(Request $request)
{
    $request->validate([
        'name'  => 'required|string|max:255',
        'email' => [
            'nullable',      // or 'required' if you want email mandatory
            'email',
            'max:255',
            Rule::unique('clients', 'email'),
        ],
        'company_name'  => 'nullable|string|max:255',
        'login_allowed' => 'required|boolean',
    ]);

    $client = Client::create([
        'name'          => $request->name,
        'email'         => $request->email,
        'company_name'  => $request->company_name,
        'login_allowed' => $request->login_allowed,
        'password'      => Hash::make($request->input('password', '123456789')),
    ]);

    return response()->json([
        'status' => 'success',
        'client' => $client
    ]);
}


    public function duplicate(Request $request, $id)
    {
        $request->validate([
            'project_name' => 'required|string|max:255',
            'start_date'   => 'required|date',
            'deadline'     => 'nullable|date|after_or_equal:start_date',
            'department_id'=> 'nullable|array',
            'user_id'      => 'nullable|array'
        ]);

        $project = Project::with(['tasks.subTasks', 'users', 'milestones', 'files'])->findOrFail($id);

        $newProject = $project->replicate();
        $newProject->name         = $request->project_name ?? $project->name . ' (Copy)';
        $newProject->project_code = $request->project_code ?? null;
        $newProject->status       = 'not started';
        $newProject->start_date   = $request->start_date;
        $newProject->deadline = $request->boolean('without_deadline') ? null : $request->deadline;
        $newProject->client_id = $request->client_id;
        $newProject->public    = $request->boolean('public');

        // Generate shortcode inline and save within a transaction to avoid collisions
        $newProject = DB::transaction(function () use ($newProject) {

            $prefix = 'Xink';
            $now = now();
            $y = (int) $now->format('Y');
            $m = (int) $now->format('n');

            if ($m >= 4) {
                $start = $y % 100;
                $end = ($y + 1) % 100;
            } else {
                $start = ($y - 1) % 100;
                $end = $y % 100;
            }

            $fy = sprintf('%02d-%02d', $start, $end);
            $like = $prefix . $fy . '/%';

            $last = DB::table('projects')
                ->where('shortcode', 'like', $like)
                ->lockForUpdate()
                ->orderBy('id', 'desc')
                ->value('shortcode');

            $lastNum = 0;
            if ($last && preg_match('/\/(\d{4})$/', $last, $m)) {
                $lastNum = (int) $m[1];
            }

            $next = str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);
            $shortcode = $prefix . $fy . '/' . $next;

            // set shortcode and save
            $newProject->shortcode = $shortcode;
            $newProject->save();

            return $newProject;
        });

        // attach members if provided
        if ($request->filled('user_id')) {
            $newProject->users()->sync($request->user_id);
        }

        // duplicate tasks if requested
        if ($request->has('task')) {
            foreach ($project->tasks as $task) {
                $newTask = $task->replicate();
                $newTask->project_id = $newProject->id;
                $newTask->save();

                if ($request->has('same_assignee') && $task->users->count()) {
                    $newTask->users()->sync($task->users->pluck('id')->toArray());
                }

                if ($request->has('sub_task')) {
                    foreach ($task->subTasks as $subTask) {
                        $newSubTask = $subTask->replicate();
                        $newSubTask->task_id = $newTask->id;
                        $newSubTask->save();
                    }
                }
            }
        }

        // duplicate milestones
        if ($request->has('milestone')) {
            foreach ($project->milestones as $milestone) {
                $newMilestone = $milestone->replicate();
                $newMilestone->project_id = $newProject->id;
                $newMilestone->save();
            }
        }

        // duplicate files
        if ($request->has('file')) {
            foreach ($project->files as $file) {
                $newFile = $file->replicate();
                $newFile->project_id = $newProject->id;
                $newFile->save();
            }
        }

        return redirect()->route('projects.index')->with('success', 'Project duplicated successfully!');
    }

    public function publicGantt($projectId)
    {
        $project = Project::with('tasks')->findOrFail($projectId);
        return view('admin.projects.gantt-public', compact('project'));
    }

    // Show archived projects
    public function archive()
    {
        $projects = Project::onlyTrashed()->get();
        return view('admin.projects.archive', compact('projects'));
    }

    // Archive a project (soft delete)
    public function archiveProject(Project $project)
    {
        $project->delete();
        return redirect()->route('projects.index')->with('success', 'Project archived successfully!');
    }

    public function projectCalendar()
    {
        $projects = Project::all();

        $events = $projects->map(function ($p) {
            return [
                'title' => $p->project_name ?? $p->name,
                'start' => $p->start_date,
                'end' => $p->deadline ?? $p->start_date,
                'url' => route('projects.show', $p->id),
            ];
        })->values();

        return view('admin.projects.calendar', compact('events'));
    }

    // Restore a project
    public function restore($id)
    {
        $project = Project::withTrashed()->findOrFail($id);

        if($project->trashed()){
            $project->restore();
            return redirect()->route('projects.index')->with('success', 'Project restored successfully!');
        }

        return redirect()->route('projects.index')->with('info', 'Project was not archived.');
    }

    public function updatePaidStatus(Request $request)
    {
        $request->validate([
            'leave_id' => 'required|exists:leaves,id',
            'paid' => 'required|in:0,1',
        ]);

        $leave = Leave::findOrFail($request->leave_id);
        $leave->paid = $request->paid;
        $leave->save();

        return response()->json([
            'success' => true,
            'message' => 'Leave status updated successfully!',
            'paid_status' => $leave->paid == 1 ? 'Paid' : 'Unpaid'
        ]);
    }

  public function bulkStatus(Request $request)
{
    $request->validate([
        'ids' => 'required|array',
        'ids.*' => 'integer|exists:projects,id',
        'status' => 'required|string|max:50'
    ]);

    $ids = $request->ids;
    $status = $request->status;

    DB::beginTransaction();
    try {
        $projects = Project::whereIn('id', $ids)->get();

        foreach ($projects as $project) {
            $old = $project->status;
            $project->status = $status;
            $project->save();

            ProjectActivity::create([
                'project_id' => $project->id,
                'activity' => auth()->user()->name . ' changed project status from ' . ($old ?? 'n/a') . ' to ' . $status,
            ]);

            UserActivity::create([
                'company_id' => auth()->user()->company_id ?? 1,
                'user_id' => auth()->id(),
                'activity' => 'Bulk status update: project ' . $project->name . ' -> ' . $status,
            ]);
        }

        DB::commit();

        return response()->json([
            'success' => true,
            'updated' => $projects->count(),
            'status' => $status
        ]);
    } catch (\Throwable $e) {
        DB::rollBack();
        Log::error('bulkStatus failed: ' . $e->getMessage());

        return response()->json([
            'success' => false,
            'message' => 'Bulk update failed'
        ], 500);
    }
}
    
    
public function toggleStatus(Request $request, $id)
{
    if (! auth()->check()) {
        return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
    }

    $raw = (string) $request->input('status', '');

    // canonical DB enum values (exact)
    $allowed = [
        'not started',
        'in progress',
        'on hold',
        'completed',
    ];

    // map incoming variants to canonical DB enum values
    $map = [
        // not started
        'not started' => 'not started',
        'notstarted'  => 'not started',
        'not-started' => 'not started',
        'not_started' => 'not started',

        // in progress
        'in progress' => 'in progress',
        'inprogress'  => 'in progress',
        'in-progress' => 'in progress',
        'in_progress' => 'in progress',

        // on hold
        'on hold' => 'on hold',
        'onhold'  => 'on hold',
        'on-hold' => 'on hold',
        'on_hold' => 'on hold',

        // completed / finished
        'completed' => 'completed',
        'complete'  => 'completed',
        'finished'  => 'completed',
        'done'      => 'completed',

        // canceled/cancelled -> map to on hold (DB has no cancelled/canceled)
        'canceled'  => 'on hold',
        'cancelled' => 'on hold',
        'cancel'    => 'on hold',

        // common UI extras -> map sensibly
        'active'    => 'in progress', // active -> in progress
        'inactive'  => 'on hold',     // inactive -> on hold
    ];

    $key = strtolower(trim(preg_replace('/\s+/', ' ', $raw)));

    if ($key === '') {
        return response()->json([
            'success' => false,
            'message' => 'Status is required.',
            'errors' => ['status' => ['Status is required.']],
        ], 422);
    }

    if (! isset($map[$key])) {
        return response()->json([
            'success' => false,
            'message' => 'Invalid status value.',
            'errors' => ['status' => ['Invalid status. Allowed: ' . implode(', ', $allowed)]],
        ], 422);
    }

    $status = $map[$key]; // mapped canonical DB value

    DB::beginTransaction();
    try {
        $project = Project::findOrFail($id);
        $oldStatus = $project->status;

        if ($oldStatus === $status) {
            DB::rollBack();
            return response()->json([
                'success' => true,
                'message' => 'Status unchanged.',
                'status' => $project->status,
            ]);
        }

        $project->status = $status;
        $project->save();

        // explicit activity logging to avoid mass assignment issues
        $pa = new ProjectActivity();
        $pa->project_id = $project->id;
        $pa->activity = (auth()->user()->name ?? 'System') .
                        ' changed project status from "' . ($oldStatus ?? 'N/A') . '" to "' . $project->status . '"';
        $pa->save();

        $ua = new UserActivity();
        $ua->company_id = auth()->user()->company_id ?? 1;
        $ua->user_id = auth()->id();
        $ua->activity = 'Changed status of project "' . ($project->name ?? $project->id) . '" to "' . $project->status . '"';
        $ua->save();

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Project status updated.',
            'status' => $project->status,
        ]);
    } catch (\Throwable $e) {
        DB::rollBack();
        Log::error('Failed to update project status: ' . $e->getMessage(), [
            'project_id' => $id,
            'user_id' => auth()->id(),
            'attempted_status_raw' => $raw,
            'mapped_status' => $status ?? null,
        ]);

        return response()->json(['success' => false, 'message' => 'Failed to update project status. See server logs for details.'], 500);
    }
}



public function bulkDelete(Request $request)
{
    $ids = $request->input('ids', []);

    if (empty($ids)) {
        return response()->json([
            'success' => false,
            'message' => 'No project ids provided.',
        ], 422);
    }

    $deletedCount = Project::whereIn('id', $ids)->forceDelete(); // <- HARD delete

    return response()->json([
        'success' => true,
        'deleted' => $deletedCount,
    ]);
}






}
