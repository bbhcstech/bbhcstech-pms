<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use App\Models\TimeLog;
use App\Models\TaskTimer;
use App\Models\Project;
use App\Models\User;
use App\Models\Task;
use DB;

class TimeLogController extends Controller
{
    public function index(Request $request, Project $project = null)
    {
        $query = TaskTimer::with(['project', 'task', 'user']);

        // Admin sees all, employee sees only own logs
        if (auth()->user()->role !== 'admin') {
            $query->where('user_id', auth()->id());
        }

        // Project filter
        if ($project) {
            $query->where('project_id', $project->id);
        }

        // Employee filter
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Date range filter
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [
                $request->start_date . " 00:00:00",
                $request->end_date . " 23:59:59"
            ]);
        } elseif ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        } elseif ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $logs = $query->latest()->get();

        // Dropdown employees list
        if (auth()->user()->role === 'admin') {
            $employees = User::where('role', 'employee')->orderBy('name')->get();
        } else {
            $employees = User::where('id', auth()->id())->get();
        }

        return view('admin.timelogs.index', compact('logs', 'project', 'employees'));
    }

public function create()
{
    $projects = Project::all();
    $tasks = Task::all();              // ✅ correct
    $employees = [];

    if (auth()->user()->role === 'admin') {
        $employees = User::where('role', 'employee')->orderBy('name')->get();
    }

    return view('admin.timelogs.create', compact('projects', 'tasks', 'employees'));
}


public function store(Request $request)
{
    $request->validate([
        'project_id' => 'required|exists:projects,id',
        'task_id'    => 'required|exists:tasks,id',
        'start_date' => 'required|date',
        'start_time' => 'required',
        'end_date'   => 'required|date',
        'end_time'   => 'required',
        'memo'       => 'nullable|string',
        'employee_id'=> 'nullable|exists:users,id',
    ]);

    // combine datetimes
    $startDatetime = $request->start_date . ' ' . $request->start_time . ':00';
    $endDatetime   = $request->end_date   . ' ' . $request->end_time   . ':00';

    $start = strtotime($startDatetime);
    $end   = strtotime($endDatetime);

    if ($start === $end) {
        return back()->withErrors(['end_time' => 'Start Time and End Time cannot be the same'])->withInput();
    }
    if ($start > $end) {
        return back()->withErrors(['end_time' => 'End Time must be after Start Time'])->withInput();
    }

    $total_hours = ($end - $start) / 3600;
    $employeeId  = $request->employee_id ?? auth()->id();

    // Use transaction to be safe
    DB::beginTransaction();
    try {
        // 1) create record without code
        $taskTimer = TaskTimer::create([
            'user_id'     => $employeeId,
            'project_id'  => $request->project_id,
            'task_id'     => $request->task_id,
            'start_date'  => $request->start_date,
            'start_time'  => $startDatetime,
            'end_date'    => $request->end_date,
            'end_time'    => $endDatetime,
            'memo'        => $request->memo,
            'total_hours' => $total_hours,
        ]);

        // 2) build code from created id (deterministic)
        $project = Project::find($request->project_id);
        $prefix = ($project && !empty($project->project_code)) ? $project->project_code : 'Xink25-26/';

        $generatedCode = $prefix . str_pad($taskTimer->id, 4, '0', STR_PAD_LEFT);

        // 3) update code if column exists (safe check)
        if (\Illuminate\Support\Facades\Schema::hasColumn('task_timers', 'code')) {
            $taskTimer->update(['code' => $generatedCode]);
        }

        DB::commit();
    } catch (\Throwable $e) {
        DB::rollBack();
        // log and return error
        \Log::error('TimeLog store error: '.$e->getMessage());
        return back()->withErrors(['error' => 'Failed to save time log'])->withInput();
    }

    if ($request->has('redirect_to_project')) {
        return redirect()->route('projects.show', $request->project_id)->with('success', 'Time log added.');
    }

    return redirect()->route('timelogs.index')->with('success', 'Time log added.');
}


    public function edit($id)
    {
        $log = TaskTimer::findOrFail($id);
        $projects = Project::all();
        $tasks = Task::where('project_id', $log->project_id)->get();
        return view('admin.timelogs.edit', compact('log', 'projects', 'tasks'));
    }

    public function getTasks($id)
    {
        $tasks = Task::where('project_id', $id)->get();
        return response()->json($tasks);
    }

    public function update(Request $request, $id)
    {
        $log = TaskTimer::findOrFail($id);

        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'task_id' => 'required|exists:tasks,id',
            'start_date' => 'required|date',
            'start_time' => 'required',
            'end_date' => 'required|date',
            'end_time' => 'required',
            'memo' => 'nullable|string',
        ]);

        // Combine start and end into full datetime strings
        $startDatetime = $request->start_date . ' ' . $request->start_time . ':00';
        $endDatetime = $request->end_date . ' ' . $request->end_time . ':00';

        // Convert to timestamps
        $start = strtotime($startDatetime);
        $end = strtotime($endDatetime);

        // Custom error if start == end
        if ($start === $end) {
            return back()->withErrors(['end_time' => 'Start Time and End Time cannot be the same'])->withInput();
        }

        // Custom error if start > end
        if ($start > $end) {
            return back()->withErrors(['end_time' => 'End Time must be after Start Time'])->withInput();
        }

        $total_hours = ($end - $start) / 3600;

        $log->update([
            'project_id' => $request->project_id,
            'task_id' => $request->task_id,
            'start_date' => $request->start_date,
            'start_time' => $startDatetime,
            'end_date' => $request->end_date,
            'end_time' => $endDatetime,
            'memo' => $request->memo,
            'total_hours' => $total_hours,
        ]);

        return redirect()->route('timelogs.index')->with('success', 'Time log updated.');
    }

    public function destroy($id)
    {
        TaskTimer::findOrFail($id)->delete();
        return back()->with('success', 'Time log deleted.');
    }

    public function show($id)
    {
        $log = TaskTimer::with(['project', 'task', 'user'])->findOrFail($id);
        return view('admin.timelogs.show', compact('log'));
    }

    public function createForProject(Request $request, $projectId)
    {
        $project = Project::findOrFail($projectId);
        $tasks = Task::where('project_id', $projectId)->get();
        $employee_data = User::all();

        $logsQuery = TaskTimer::where('project_id', $projectId)
            ->with(['task', 'employee']);

        // Apply filters
        if ($request->filled('employee_id')) {
            $logsQuery->where('user_id', $request->employee_id);
        }

        if ($request->filled('invoice_id')) {
            $logsQuery->where('invoice_id', $request->invoice_id === 'Yes' ? 1 : 0);
        }

        if ($request->filled('search')) {
            $searchTerm = $request->search;

            $logsQuery->where(function ($q) use ($searchTerm) {
                $q->whereHas('task', function ($q2) use ($searchTerm) {
                    $q2->where('title', 'like', "%$searchTerm%");
                })->orWhereHas('employee', function ($q3) use ($searchTerm) {
                    $q3->where('name', 'like', "%$searchTerm%");
                });
            });
        }

        $logs = $logsQuery->orderByDesc('id')->get();

        return view('admin.timelogs.create_project_log', compact('project', 'tasks', 'logs', 'employee_data'));
    }

    public function getTaskEmployee($taskId)
    {
        $task = Task::with('assignee')->findOrFail($taskId);

        if ($task->assignee) {
            return response()->json([
                'id' => $task->assignee->id,
                'name' => $task->assignee->name,
            ]);
        }

        return response()->json(null);
    }

    public function getTasksByProject($projectId)
    {
        $tasks = Task::where('project_id', $projectId)->get(['id', 'title']);
        return response()->json($tasks);
    }

    public function calendar()
    {
        $query = TaskTimer::with(['project', 'task', 'user']);

        // Admin sees all, employee sees only own logs
        if (auth()->user()->role !== 'admin') {
            $query->where('user_id', auth()->id());
        }

        $timelogs = $query->get()->map(function ($log) {
            return [
                'title' => $log->user->name . ' - ' . ($log->task->title ?? 'No Task'),
                'start' => $log->start_time,
                'end'   => $log->end_time,
                'allDay' => false,
            ];
        });

        return view('admin.timelogs.calendar', compact('timelogs'));
    }

    public function byEmployee(Request $request)
    {
        $query = TaskTimer::with(['project', 'task', 'user']);

        // Filter by employee
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        } elseif (auth()->user()->role !== 'admin') {
            $query->where('user_id', auth()->id());
        }

        // Filter by date range
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('start_time', [$request->start_date, $request->end_date]);
        }

        // Search (by project/task/user name)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('task', fn($q) => $q->where('title', 'like', "%$search%"))
                  ->orWhereHas('project', fn($q) => $q->where('project_code', 'like', "%$search%"))
                  ->orWhereHas('user', fn($q) => $q->where('name', 'like', "%$search%"));
        }

        $logs = $query->get();

        if (auth()->user()->role === 'admin') {
            $employees = User::where('role', 'employee')->orderBy('name')->get();
        } else {
            $employees = User::where('id', auth()->id())->get();
        }

        return view('admin.timelogs.by-employee', compact('logs', 'employees'));
    }

public function bulkStatusUpdate(Request $request)
{
    $request->validate([
        'ids'    => 'required|array|min:1',
        'ids.*'  => 'integer|exists:task_timers,id',
        'status' => 'required|string|in:pending,approved,rejected',
    ]);

    $ids = $request->ids;
    $status = $request->status;

    DB::beginTransaction();
    try {
        if (auth()->user()->role !== 'admin') {
            $affected = TaskTimer::whereIn('id', $ids)
                ->where('user_id', auth()->id())
                ->update(['status' => $status]);
        } else {
            $affected = TaskTimer::whereIn('id', $ids)
                ->update(['status' => $status]);
        }

        DB::commit();

        return response()->json([
            'success'  => true,
            'message'  => 'Status updated successfully',
            'affected' => $affected,
        ]);
    } catch (\Throwable $e) {
        DB::rollBack();
        Log::error('bulkStatusUpdate failed: ' . $e->getMessage(), ['ids' => $ids, 'status' => $status]);
        return response()->json(['success' => false, 'message' => 'Server error'], 500);
    }
}




public function bulkDelete(Request $request)
{
    $request->validate([
        'ids'   => 'required|array|min:1',
        'ids.*' => 'integer|exists:task_timers,id',
    ]);

    $ids = $request->ids;

    DB::beginTransaction();
    try {
        if (auth()->user()->role !== 'admin') {
            $deleted = TaskTimer::whereIn('id', $ids)
                ->where('user_id', auth()->id())
                ->delete();
        } else {
            $deleted = TaskTimer::whereIn('id', $ids)->delete();
        }

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Selected time logs deleted',
            'deleted' => $deleted,
        ]);
    } catch (\Throwable $e) {
        DB::rollBack();
        Log::error('bulkDelete failed: '.$e->getMessage(), ['ids' => $ids]);

        return response()->json([
            'success' => false,
            'message' => 'Server error',
        ], 500);
    }
}




}
