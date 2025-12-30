<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // Add this line to import DB facade
use App\Models\Attendance;
use App\Models\Task;
use App\Models\Leave;
use App\Models\Award;
use App\Models\AttendanceSetting;
use App\Models\Client;
use App\Models\Project;
use App\Models\Ticket;
use App\Models\ProjectMilestone;
use App\Models\TimeLog;
use App\Models\TaskTimer;
use App\Models\UserActivity;
use App\Models\EmployeeDetail;
use App\Models\Department;
use App\Models\Designation;
use App\Models\StickyNote;
use App\Models\User;
use Carbon\Carbon;


class DashboardController extends Controller
{

 public function timersstore(Request $request)
{
    // Check if creating a new task
    $createNewTask = $request->has('create_task');

    // Validation
    $request->validate([
        'project_id' => 'nullable|exists:projects,id',
        'task_id'    => $createNewTask ? 'nullable' : 'required|exists:tasks,id',
        'new_task_name' => $createNewTask ? 'required|string|max:255' : 'nullable',
        'memo'       => 'required|string',
    ]);

    // If creating a new task, save it first
    if ($createNewTask) {
        $task = new Task();
        $task->title = $request->new_task_name;
        $task->project_id = $request->project_id;
        // $task->user_id = Auth::id();
        $task->save();
        $taskId = $task->id;
    } else {
        $taskId = $request->task_id;
    }

    // Save the timer
    $timer = new TaskTimer();
    $timer->project_id = $request->project_id;
    $timer->task_id = $taskId;
    $timer->user_id  = Auth::id();
    $timer->start_date = now()->toDateString();
    $timer->start_time = now(); // current timestamp
    $timer->memo = $request->memo;
    $timer->save();

    return redirect()->back()->with('success', 'Timer started successfully!');
}



 // StickyNoteController.php
public function notestore(Request $request)
{
    $request->validate([
        'note_text' => 'required|string',
        'colour' => 'required|in:blue,yellow,red,gray,purple,green',
    ]);
    StickyNote::create([
        'company_id' => auth()->user()->company_id,
        'user_id' => auth()->id(),
        'note_text' => $request->note_text,
        'colour' => $request->colour,
    ]);

    return redirect()->back()->with('success', 'Note added successfully!');
}

    public function index()
    {
        $userId = Auth::id();

        if (auth()->user()->role == 'admin') {
            $totalEmployees = \App\Models\User::where('role', 'employee')->count();
            $today = now()->toDateString();

            $presentCount = \App\Models\Attendance::where('date', $today)
                ->whereIn('status', ['present', 'late'])
                ->count();

            $lateCount = \App\Models\Attendance::where('date', $today)
                ->where('status', 'late')
                ->count();

            $absentCount = $totalEmployees - $presentCount;

            $totalClient = \App\Models\Client::count();
            $totalProject = \App\Models\Project::count();
            $pendingTask = \App\Models\Task::where('status','!=' ,'Completed')->count();
            $unresolvedTicket = \App\Models\Ticket::where('status', '!=', 'closed')->count();


            // Only for Admin
           $pendingLeaves = Auth::user()->role == 'admin'
        ? Leave::with('user')->where('status', 'pending')->latest()->take(5)->get()
        : collect(); // empty if not admin

        $openTickets = \App\Models\Ticket::where('status', 'open')
            ->with(['project', 'agent'])  // eager loading for performance
            ->latest()
            ->take(5) // Show top 5 recent
            ->get();

            $pendingTasksTotal = \App\Models\Task::where('status', '!=', 'Completed')
            ->with('project')
            ->orderByDesc('start_date')
            ->take(5) // Show recent 5 tasks
            ->get();

          $activities = DB::table('project_activity')
        ->join('projects', 'projects.id', '=', 'project_activity.project_id')
        ->select(
            'project_activity.activity',
            'project_activity.created_at',
            'projects.name as project_name'
        )
        ->orderByDesc('project_activity.created_at')
        ->limit(15)
        ->get();


 $useractivities = UserActivity::with('user')
        ->orderBy('created_at', 'desc')
        ->limit(10)
        ->get();
          $projects = Project::all();
            $tasks = Task::all();

            return view('dashboard', compact(
                'totalEmployees',
                'presentCount',
                'lateCount',
                'absentCount',
                'totalClient',
                'totalProject',
                'unresolvedTicket',
                'pendingTask',
                'pendingLeaves',
                'openTickets',
                'pendingTasksTotal',
                'activities',
                'useractivities',
                'projects', 'tasks'

            ));
        }

        // ✅ Client  ogic

        if (auth()->user()->role == 'client') {

            return view('client-dashboard');
        }


        // ✅ Employee logic
        if (auth()->user()->role == 'employee') {
            $user = Auth::user();
            $today = now()->toDateString();

            $attendance = \App\Models\Attendance::where('user_id', $user->id)
                ->where('date', $today)
                ->first();


            // Fetch week data
            $startOfWeek = Carbon::now()->startOfWeek(); // Monday
            $endOfWeek = Carbon::now()->endOfWeek();     // Sunday

            $weeklyLogs = Attendance::where('user_id', $user->id)
                ->whereBetween('date', [$startOfWeek->toDateString(), $endOfWeek->toDateString()])
                ->get()
                ->keyBy('date');

            $openTasksCount = Task::where('status', '!=', 'completed')->count();
            $projectsCount = Project::count();
           $openTicketsCount = Ticket::where('status', 'open')->count();
           $userId = auth()->id();

            $projects = \DB::table('projects')
            ->join('project_user', 'projects.id', '=', 'project_user.project_id')
            ->where('project_user.user_id', $userId)
            ->where(function ($query) {
                $query->where('projects.status', 'in progress')
                      ->where(function ($sub) {
                          $sub->whereNull('projects.deadline') // allow no deadline
                               ->orWhere('projects.deadline', '>=', Carbon::today()); // not overdue
                      });
            })
            ->orWhere(function ($query) {
                $query->where('projects.status', 'in progress')
                      ->whereNotNull('projects.deadline')
                      ->where('projects.deadline', '<', Carbon::today()); // overdue
            })
            ->select('projects.*')
            ->distinct()
            ->get();

            $inProgress = \DB::table('projects')
                ->join('project_user', 'projects.id', '=', 'project_user.project_id')
                ->where('project_user.user_id', $userId)
                ->where('projects.status', 'in progress')
                ->where(function ($q) {
                    $q->whereNull('projects.deadline')
                      ->orWhere('projects.deadline', '>=', Carbon::today());
                })
                ->select('projects.*')
                ->get();

                // Overdue Projects

                $overdue = \DB::table('projects')
                ->join('project_user', 'projects.id', '=', 'project_user.project_id')
                ->where('project_user.user_id', $userId)
                ->where('projects.status', 'in progress')
                ->whereNotNull('projects.deadline')
                ->where('projects.deadline', '<', Carbon::today())
                ->select('projects.*')
                ->get();

            // In-Progress Projects

            // In-Progress Projects Count (assigned to user)
            $inProgressCount = DB::table('projects')
                ->join('project_user', 'projects.id', '=', 'project_user.project_id')
                ->where('project_user.user_id', $userId)
                ->where('projects.status', 'in progress')
                ->where(function ($q) {
                    $q->whereNull('projects.deadline')
                      ->orWhere('projects.deadline', '>=', Carbon::today());
                })
                ->count();

            // Overdue Projects Count (assigned to user)
            $overdueCount = DB::table('projects')
                ->join('project_user', 'projects.id', '=', 'project_user.project_id')
                ->where('project_user.user_id', $userId)
                ->where('projects.status', 'in progress')
                ->whereNotNull('projects.deadline')
                ->where('projects.deadline', '<', Carbon::today())
                ->count();
                $totalProjects = $inProgress->count() + $overdue->count();



            // Count Pending Tasks (assigned_to includes current user AND status is Incomplete or Doing)
            $pendingTasksCount = Task::whereIn('status', ['Incomplete', 'Doing'])
                ->whereRaw("FIND_IN_SET(?, assigned_to)", [auth()->id()])
                ->count();

            // Count Overdue Tasks (same filter + due date is past)
            $overdueTasksCount = Task::whereIn('status', ['Incomplete', 'Doing'])
                ->whereRaw("FIND_IN_SET(?, assigned_to)", [auth()->id()])
                ->whereDate('due_date', '<', Carbon::today())
                ->count();

        //   Birthdays Today
            $birthdaysToday = EmployeeDetail::whereRaw('DATE_FORMAT(dob, "%m-%d") = ?', [date('m-d')])->get();
            //  Employee Appreciations

            $appreciations = Award::whereDate('award_date', now())->get();

        // On Leave Today
         $onLeaveToday = Leave::where('status', 'approved')
        ->whereDate('start_date', '<=', now())
        ->whereDate('end_date', '>=', now())
        ->with('user')
        ->get();

       // Today’s Joinings & Work Anniversary

       $todaysJoinings = EmployeeDetail::whereDate('joining_date', now())->get();

       $workAnniversaries = EmployeeDetail::whereRaw('DATE_FORMAT(joining_date, "%m-%d") = ?', [date('m-d')])->get();


       $myTasks = Task::whereRaw("FIND_IN_SET(?, assigned_to)", [$userId])
            ->orderBy('due_date', 'asc')
            ->limit(5) // show latest 5 tasks
            ->get();

        $myTickets = Ticket::where('requester_id', $userId) // or assigned_to if you track it
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();


            $projects = Project::all();
            $tasks = Task::all();
            return view('employee-dashboard', compact('user','projects', 'tasks', 'attendance', 'weeklyLogs','openTasksCount', 'projectsCount', 'openTicketsCount','appreciations',
            'birthdaysToday','todaysJoinings','workAnniversaries','onLeaveToday','pendingTasksCount','overdueTasksCount','totalProjects','inProgressCount','overdueCount','myTasks','myTickets'));
        }

        abort(403);
    }

    public function globalSearch(Request $request)
{
    $type = $request->input('type');
    $query = $request->input('query');
    $results = [];

    // Get matching results
    switch ($type) {
        case 'ticket':
            $results = Ticket::where(function ($q) use ($query) {
                $q->where('requester_name', 'like', "%$query%")
                  ->orWhere('requester_type', 'like', "%$query%")
                  ->orWhere('subject', 'like', "%$query%")
                  ->orWhere('description', 'like', "%$query%")
                  ->orWhere('attachment', 'like', "%$query%")
                  ->orWhere('priority', 'like', "%$query%")
                  ->orWhere('channel', 'like', "%$query%")
                  ->orWhere('tags', 'like', "%$query%")
                  ->orWhere('status', 'like', "%$query%");
            })->get();
            break;

        case 'task':
            $results = Task::where(function ($q) use ($query) {
                $q->where('task_short_code', 'like', "%$query%")
                  ->orWhere('title', 'like', "%$query%")
                  ->orWhere('description', 'like', "%$query%")
                  ->orWhere('assigned_to', 'like', "%$query%")
                  ->orWhere('task_labels', 'like', "%$query%")
                  ->orWhere('priority', 'like', "%$query%")
                  ->orWhere('status', 'like', "%$query%");
            })->get();
            break;


      case 'project':
    $results = Project::with('client') // eager load client
        ->where(function ($q) use ($query) {
            $q->where('name', 'like', "%$query%")
              ->orWhere('project_code', 'like', "%$query%")
              ->orWhere('description', 'like', "%$query%")
              ->orWhere('start_date', 'like', "%$query%")
              ->orWhere('deadline', 'like', "%$query%")
              ->orWhere('status', 'like', "%$query%");
        })
        ->orWhereHas('client', function ($q) use ($query) {
            $q->where('name', 'like', "%$query%")
              ->orWhere('email', 'like', "%$query%")
              ->orWhere('mobile', 'like', "%$query%")
              ->orWhere('company_name', 'like', "%$query%")
              ->orWhere('website', 'like', "%$query%")
              ->orWhere('country', 'like', "%$query%")
              ->orWhere('city', 'like', "%$query%");
        })
        ->get();
    break;



        case 'employee':
    $results = \DB::table('employee_details')
        ->join('users', 'employee_details.user_id', '=', 'users.id')
        ->leftJoin('designations', 'employee_details.designation_id', '=', 'designations.id')
        ->select(
            'employee_details.id as emp_detail_id',
            'employee_details.employee_id',
            'employee_details.status',
            'employee_details.gender',
            'employee_details.user_role',
            'users.id as user_id',
            'users.name',
            'users.email',
            'users.mobile',
            'designations.name as designation'
        )
        ->where(function ($q) use ($query) {
            $q->where('users.name', 'like', "%{$query}%")
              ->orWhere('users.email', 'like', "%{$query}%")
              ->orWhere('users.mobile', 'like', "%{$query}%")
              ->orWhere('designations.name', 'like', "%{$query}%")
              // add employee_details fields also
              ->orWhere('employee_details.employee_id', 'like', "%{$query}%")
              ->orWhere('employee_details.status', 'like', "%{$query}%")
              ->orWhere('employee_details.gender', 'like', "%{$query}%")
              ->orWhere('employee_details.user_role', 'like', "%{$query}%");
        })
        ->get();
    break;


        case 'client':
            $results = Client::where('name', 'like', "%$query%")->get();
            break;
    }

    // ✅ Redirect if only one match is found
    if ($results->count() === 1) {
        $single = $results->first();

        switch ($type) {
            case 'ticket':
                return redirect()->route('tickets.show', $single->id);
            case 'task':
                return redirect()->route('tasks.show', $single->id);
            case 'project':
                return redirect()->route('projects.show', $single->id);
            case 'employee':
                return redirect()->route('employees.show', $single->user_id);
            case 'client':
                return redirect()->route('clients.show', $single->id);
        }
    }

    // ✅ Otherwise, show result list
    return view('search-results', compact('results', 'type', 'query'));
}



   public function clockIn()
{
    $now = now(); // automatically in IST if app timezone is set
    $today = $now->toDateString();
    $userId = auth()->id();

    $existing = Attendance::where('user_id', $userId)
        ->where('date', $today)
        ->first();

    if ($existing) {
        return back()->with('error', 'You have already clocked in today.');
    }

    $setting = \App\Models\AttendanceSetting::first();
    $clockInTime = $now->format('H:i');
    $status = 'present';

    if ($setting && $clockInTime > $setting->late_time) {
        $status = 'late';
    }

    Attendance::create([
        'user_id'  => $userId,
        'date'     => $today,
        'clock_in' => $clockInTime,
        'status'   => $status,
    ]);

    return back()->with('success', 'Clocked in at ' . $now->format('h:i A'));
}


   public function clockOut()
{
    $now = now();
    $today = $now->toDateString();
    $userId = auth()->id();

    $attendance = Attendance::where('user_id', $userId)
        ->where('date', $today)
        ->first();

    if (!$attendance) {
        return back()->with('error', 'You need to clock in first.');
    }

    if ($attendance->clock_out) {
        return back()->with('error', 'You have already clocked out today.');
    }

    $attendance->update([
        'clock_out' => $now->format('H:i')
    ]);

    return back()->with('success', 'Clocked out at ' . $now->format('h:i A'));
}


public function project(Request $request)
{
    // Default to today if no filter
    $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date)->startOfDay() : now()->startOfDay();
    $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date)->endOfDay() : now()->endOfDay();

    // Apply filters to all queries where applicable
    $totalProjects = Project::whereBetween('created_at', [$startDate, $endDate])->count();

    $overdueProjects = Project::whereBetween('created_at', [$startDate, $endDate])
        ->where('deadline', '<', now())
        ->where('status', '!=', 'Completed')
        ->count();

    $pendingMilestones = ProjectMilestone::whereHas('project', function ($query) use ($startDate, $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        })
        ->where('status', 'Pending')
        ->with('project')
        ->get();

    $statusWiseCounts = Project::whereBetween('created_at', [$startDate, $endDate])
        ->select('status', DB::raw('count(*) as total'))
        ->groupBy('status')
        ->pluck('total', 'status')
        ->toArray();

    return view('dasboard-project', compact(
        'totalProjects',
        'overdueProjects',
        'pendingMilestones',
        'statusWiseCounts',
        'startDate',
        'endDate'
    ));
}




public function clientDashboard(Request $request)
{
    $startDate = $request->filled('start_date')
        ? Carbon::parse($request->start_date)->startOfDay()
        : now()->startOfDay();

    $endDate = $request->filled('end_date')
        ? Carbon::parse($request->end_date)->endOfDay()
        : now()->endOfDay();

    // Filter clients created between start and end dates
    $totalClients = Client::whereBetween('created_at', [$startDate, $endDate])->count();

    // Example placeholders for charts
    $timelogChartData = [];
    $earningsData = [];

    return view('dashboard-client', compact(
        'totalClients',
        'startDate',
        'endDate'
    ));
}
// public function ticketDashboard()
// {
//     return view('dashboard-ticket', [
//         'unresolved' => Ticket::where('status', '!=', 'resolved')->count(),
//         'resolved' => Ticket::where('status', 'resolved')->count(),
//         'unassigned' => Ticket::whereNull('agent_id')->count(),
//         'typeWiseData' => Ticket::selectRaw('type_id, COUNT(*) as count')->groupBy('type_id')->pluck('count', 'type_id')->toArray(),
//         'statusWiseData' => Ticket::selectRaw('status, COUNT(*) as count')->groupBy('status')->pluck('count', 'status')->toArray(),
//         'channelWiseData' => Ticket::selectRaw('channel, COUNT(*) as count')->groupBy('channel')->pluck('count', 'channel')->toArray(),
//         'openTickets' => Ticket::where('status', 'open')->orderBy('created_at', 'desc')->limit(5)->get()
//     ]);
// }

public function ticketDashboard(Request $request)
{
    $startDate = $request->filled('start_date')
        ? Carbon::parse($request->start_date)->startOfDay()
        : now()->startOfDay();

    $endDate = $request->filled('end_date')
        ? Carbon::parse($request->end_date)->endOfDay()
        : now()->endOfDay();

    // Base query for date filtering
    $ticketQuery = Ticket::whereBetween('created_at', [$startDate, $endDate]);

    return view('dashboard-ticket', [
        'unresolved' => (clone $ticketQuery)->where('status', '!=', 'resolved')->count(),
        'resolved' => (clone $ticketQuery)->where('status', 'resolved')->count(),
        'unassigned' => (clone $ticketQuery)->whereNull('agent_id')->count(),
        'typeWiseData' => (clone $ticketQuery)
                            ->selectRaw('type_id, COUNT(*) as count')
                            ->groupBy('type_id')
                            ->pluck('count', 'type_id')
                            ->toArray(),
        'statusWiseData' => (clone $ticketQuery)
                            ->selectRaw('status, COUNT(*) as count')
                            ->groupBy('status')
                            ->pluck('count', 'status')
                            ->toArray(),
        'channelWiseData' => (clone $ticketQuery)
                            ->selectRaw('channel, COUNT(*) as count')
                            ->groupBy('channel')
                            ->pluck('count', 'channel')
                            ->toArray(),
        'openTickets' => (clone $ticketQuery)
                            ->where('status', 'open')
                            ->orderBy('created_at', 'desc')
                            ->limit(5)
                            ->get(),
        'startDate' => $startDate,
        'endDate' => $endDate,
    ]);
}

public function hrindex(Request $request)
{
        $today = Carbon::today();
        // Parse date range
       $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date)->startOfDay() : now()->startOfMonth();
       $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date)->endOfDay() : now()->endOfMonth();


        $totalEmployees = EmployeeDetail::count();
        $activeEmployees = EmployeeDetail::where('status', 'Active')->count();
        $departmentsCount = Department::count();
        $designationsCount = Designation::count();
        $presentToday = Attendance::where('date', $today)->where('status', 'present')->count();
        $onLeaveToday = Leave::whereDate('start_date', '<=', $today)
                            ->whereDate('end_date', '>=', $today)
                            ->where('status', 'approved')
                            ->count();
        $pendingLeaves = Leave::where('status', 'pending')->count();

        $newEmployees = EmployeeDetail::whereBetween('joining_date', [$startDate, $endDate])->count();
          $exits = EmployeeDetail::whereBetween('exit_date', [$startDate, $endDate])->count();
         $approvedLeaves = Leave::where('status', 'Approved')
                        ->whereBetween('start_date', [$startDate, $endDate])
                        ->count();

        $todayPresent = Attendance::whereDate('date', today())->count();
        $averageAttendance = ($totalEmployees > 0) ? round(($todayPresent / $totalEmployees) * 100, 2) : 0;

        $pendingTasks = Task::where('status', 'Pending Task')->count();

        $monthlyHeadcount = EmployeeDetail::selectRaw('MONTH(joining_date) as month, COUNT(*) as count')
            ->groupBy('month')->pluck('count', 'month');

        $monthlyJoinings = EmployeeDetail::whereBetween('joining_date', [$startDate, $endDate])
                        ->selectRaw('MONTH(joining_date) as month, COUNT(*) as joinings')
                        ->groupBy('month')
                        ->pluck('joinings', 'month');

        $monthlyAttrition = EmployeeDetail::whereBetween('exit_date', [$startDate, $endDate])
                        ->selectRaw('MONTH(exit_date) as month, COUNT(*) as attritions')
                        ->groupBy('month')
                        ->pluck('attritions', 'month');

         $lateAttendances = Attendance::with('employee.employeeDetail.designation')
            ->where('status', 'Late')
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('user_id')
            ->get()
            ->groupBy('user_id');

              $leavesTaken = Leave::with(['user.employeeDetail.designation'])
            ->where('status', 'Approved')
            ->whereBetween('start_date', [$startDate, $endDate])
            ->select('user_id', DB::raw('count(*) as total'))
            ->groupBy('user_id')
            ->get();



        $departmentWise = DB::table('employee_details')
            ->leftJoin('departments', 'employee_details.department_id', '=', 'departments.id')
            ->select('departments.dpt_name as department_name', DB::raw('COUNT(*) as total'))
            ->groupBy('departments.dpt_name')
            ->get();

            // echo'<pre>';print_r($departmentWise);die;

        // Designation-wise employee count
        $designationWise = EmployeeDetail::with('designation')
            ->selectRaw('designation_id, COUNT(*) as total')
            ->groupBy('designation_id')
            ->get();

        $genderCounts = EmployeeDetail::select('gender')
        ->selectRaw('COUNT(*) as total')
        ->groupBy('gender')
        ->pluck('total', 'gender');

       $roleCounts = User::select('role')
        ->selectRaw('COUNT(*) as total')
        ->groupBy('role')
        ->pluck('total', 'role');

        return view('dashboard-hr', compact(
            'totalEmployees',
            'activeEmployees',
            'departmentsCount',
            'designationsCount',
            'presentToday',
            'onLeaveToday',
            'pendingLeaves',
            'newEmployees',
            'exits',
            'approvedLeaves',
            'todayPresent',
            'averageAttendance',
            'pendingTasks',
            'monthlyHeadcount',
            'monthlyJoinings',
            'monthlyAttrition',
            'lateAttendances',
            'leavesTaken',
            'departmentWise',
            'designationWise',
            'genderCounts',
            'roleCounts'
        ));
    }








}
