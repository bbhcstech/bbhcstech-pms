<?php


namespace App\Http\Controllers;

use App\Models\Leave;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class LeaveController extends Controller
{
    // public function index()
    // {
    //     $leaves = Auth::user()->role == 'admin'
    //         ? Leave::with('user')->latest()->get()
    //         : Leave::where('user_id', Auth::id())->latest()->get();

    //     return view('admin.leaves.index', compact('leaves'));
    // }
    public function index(Request $request)
{
    $leaves = Leave::with('user');

    // 🔹 If user is not admin, only show their own
    if (Auth::user()->role !== 'admin') {
        $leaves->where('user_id', Auth::id());
    }

    // ✅ Duration filter
    if ($request->filled('duration')) {
        $duration = $request->duration;

        if (str_contains($duration, 'to')) {
    [$start, $end] = preg_split('/\s*to\s*/', $duration);  // handles spaces
    try {
        $startDate = Carbon::parse(trim($start))->startOfDay();
        $endDate   = Carbon::parse(trim($end))->endOfDay();

        $leaves->where(function ($q) use ($startDate, $endDate) {
            $q->whereBetween('start_date', [$startDate, $endDate])
              ->orWhereBetween('end_date', [$startDate, $endDate])
              ->orWhereBetween('date', [$startDate, $endDate]);
        });
    } catch (\Exception $e) {
        // Invalid format, ignore
    }
}
 else {
            switch ($duration) {
                case 'Today':
                    $startDate = Carbon::today();
                    $endDate   = Carbon::today()->endOfDay();
                    break;
                case 'Last 30 Days':
                    $startDate = Carbon::now()->subDays(29)->startOfDay();
                    $endDate   = Carbon::now()->endOfDay();
                    break;
                case 'This Month':
                    $startDate = Carbon::now()->startOfMonth();
                    $endDate   = Carbon::now()->endOfMonth();
                    break;
                case 'Last Month':
                    $startDate = Carbon::now()->subMonth()->startOfMonth();
                    $endDate   = Carbon::now()->subMonth()->endOfMonth();
                    break;
                case 'Last 90 Days':
                    $startDate = Carbon::now()->subDays(89)->startOfDay();
                    $endDate   = Carbon::now()->endOfDay();
                    break;
                case 'Last 6 Months':
                    $startDate = Carbon::now()->subMonths(6)->startOfMonth();
                    $endDate   = Carbon::now()->endOfDay();
                    break;
                case 'Last 1 Year':
                    $startDate = Carbon::now()->subYear()->startOfMonth();
                    $endDate   = Carbon::now()->endOfDay();
                    break;
                default:
                    $startDate = null;
                    $endDate   = null;
            }

            if ($startDate && $endDate) {
                $leaves->where(function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('start_date', [$startDate, $endDate])
                      ->orWhereBetween('end_date', [$startDate, $endDate])
                      ->orWhereBetween('date', [$startDate, $endDate]);
                });
            }
        }
    }

    $leaves = $leaves->latest()->get();

    return view('admin.leaves.index', compact('leaves'));
}

    public function create()
    {
         $users = \App\Models\User::where('role', 'employee')->get();
        return view('admin.leaves.create', compact('users'));
        
    }

    public function store(Request $request)
    {
          $request->validate([
            'type' => 'required|string',
            'duration' => 'required|string',
            'reason' => 'required|string',
            'files' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:2048',

            'status' => 'nullable|string',
        
            // Conditional fields
            'date' => 'required_unless:duration,multiple|date|nullable',
            'start_date' => 'required_if:duration,multiple|date|nullable',
            'end_date' => 'required_if:duration,multiple|date|after_or_equal:start_date|nullable',
        
            'user_id' => auth()->user()->role === 'admin' ? 'required|exists:users,id' : '',
        ]);


        $userId = auth()->user()->role === 'admin' ? $request->user_id : auth()->id();
        
            
   
    
    $profileImagePath = null;

    // Handle profile image upload
    if ($request->hasFile('files')) {
        $image = $request->file('files');
        $imageName = time() . '-' . $image->getClientOriginalName();
        $image->move(public_path('admin/uploads/leave-file'), $imageName);

        $profileImagePath = 'admin/uploads/leave-file/' . $imageName;
    }

        Leave::create([
            'user_id' => $userId,
            'type' => $request->type,
            'duration'=> $request->duration,
            'date' => $request->date,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'files'=> $profileImagePath,
            'reason' => $request->reason,
        ]);

        return redirect()->route('leaves.index')->with('success', 'Leave applied successfully.');
    }
    
    public function edit(Leave $leave)
    {
        $users = User::all(); // Only needed if admin needs to select a user
        return view('admin.leaves.edit', compact('leave', 'users'));
    }
    
    public function update(Request $request, Leave $leave)
{
    $request->validate([
        'type' => 'required|string',
        'duration' => 'required|string',
        'reason' => 'required|string',
        'files' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:2048',
        'status' => 'nullable|string',
        'date' => 'required_unless:duration,multiple|date|nullable',
        'start_date' => 'required_if:duration,multiple|date|nullable',
        'end_date' => 'required_if:duration,multiple|date|after_or_equal:start_date|nullable',
        'user_id' => auth()->user()->role === 'admin' ? 'required|exists:users,id' : '',
    ]);

    $userId = auth()->user()->role === 'admin' ? $request->user_id : auth()->id();

    // Handle file upload
    $profileImagePath = $leave->files;
    if ($request->hasFile('files')) {
        $file = $request->file('files');
        $fileName = time() . '-' . $file->getClientOriginalName();
        $file->move(public_path('admin/uploads/leave-file'), $fileName);
        $profileImagePath = 'admin/uploads/leave-file/' . $fileName;
    }

    $leave->update([
        'user_id' => $userId,
        'type' => $request->type,
        'duration' => $request->duration,
        'date' => $request->date,
        'start_date' => $request->start_date,
        'end_date' => $request->end_date,
        'reason' => $request->reason,
        'files' => $profileImagePath,
    ]);

    return redirect()->route('leaves.index')->with('success', 'Leave updated successfully.');
}




    public function updateStatus(Request $request, Leave $leave)
    {
        $leave->status = $request->status;
        $leave->save();

        return back()->with('success', 'Leave status updated.');
    }

    public function leaveReport(Request $request)
{
    $users = User::where('role', 'employee')->get();

    $query = Leave::with('user');

    if ($request->user_id) {
        $query->where('user_id', $request->user_id);
    }

    if ($request->type) {
        $query->where('type', $request->type);
    }

    if ($request->from && $request->to) {
        $query->whereBetween('start_date', [$request->from, $request->to]);
    }

    $leaves = $query->latest()->get();

    $summary = [
        'total' => $leaves->count(),
        'approved' => $leaves->where('status', 'approved')->count(),
        'pending' => $leaves->where('status', 'pending')->count(),
        'rejected' => $leaves->where('status', 'rejected')->count(),
    ];

    return view('admin.leaves.report', compact('users', 'leaves', 'summary'));
}

public function destroy($id)
{
    $leave = Leave::findOrFail($id);

    // Delete uploaded file if exists
    if ($leave->files && file_exists(public_path($leave->files))) {
        unlink(public_path($leave->files));
    }

    $leave->delete();

    return redirect()->back()->with('success', 'Leave deleted successfully.');
}

public function show($id)
{
    $leave = Leave::with('user')->findOrFail($id);

    return view('admin.leaves.show', compact('leave'));
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
    
    
    // Calendar View
public function calendar()
{
    // Get employees for the filter dropdown
    if (auth()->user()->role === 'admin') {
        // Admin can see all employees
        $employee_data = User::orderBy('name')->get();
    } else {
        // Non-admin sees only themselves
        $employee_data = User::where('id', auth()->id())->get();
    }

    // Pass only employee data to Blade
    return view('admin.leaves.calendar', compact('employee_data'));
}


public function calendarData(Request $request)
{
    $query = Leave::with('user');

    // Non-admin users see only their own leaves
    if (auth()->user()->role !== 'admin') {
        $query->where('user_id', auth()->id());
    }

    // Filters
    if ($request->employee) {
        $query->where('user_id', $request->employee);
    }
    if ($request->leave_type) {
        $query->where('type', $request->leave_type);
    }
    if ($request->status) {
        $query->where('status', $request->status);
    }

    $leaves = $query->get()->map(function ($leave) {
        $status = strtolower($leave->status);

        // Determine start and end dates
        $start = $leave->start_date ?? $leave->date ?? now()->format('Y-m-d');
        $end   = $leave->end_date ?? $leave->date ?? now()->format('Y-m-d');

        // FullCalendar treats 'end' as exclusive, so add 1 day
        $end = date('Y-m-d', strtotime($end . ' +1 day'));

        return [
            'title' => $leave->user ? $leave->user->name . ' - ' . ucfirst($status) : 'Unknown',
            'start' => $start,
            'end' => $end,
            'color' => $status === 'approved' ? '#28a745'
                        : ($status === 'rejected' ? '#dc3545' : '#ffc107'),
        ];
    });

    return response()->json($leaves);
}


public function bulkAction(Request $request)
{
    $ids = $request->ids;
    $action = $request->action;

    if (!$ids || count($ids) === 0) {
        return response()->json(['message' => 'No leaves selected'], 400);
    }

    if ($action === 'delete') {
        Leave::whereIn('id', $ids)->delete();
        return response()->json(['message' => 'Selected leaves deleted successfully!']);
    }

    if ($action === 'change_status') {
        $status = $request->status;
        if (!$status) {
            return response()->json(['message' => 'Please select a status'], 400);
        }
        Leave::whereIn('id', $ids)->update(['status' => $status]);
        return response()->json(['message' => "Selected leaves updated to {$status}"]);
    }

    return response()->json(['message' => 'No action performed']);
}

// public function bulkAction(Request $request)
// {
//     $request->validate([
//         'client_ids' => 'required|array',
//         'action' => 'required|string',
//     ]);

//     $ids = $request->client_ids;

//     if ($request->action === 'change-status' && $request->filled('status')) {
//         Client::whereIn('id', $ids)
//             ->update([
//                 'login_allowed' => $request->status === 'Active' ? 1 : 0,
//                 'status' => $request->status
//             ]);
//         return response()->json(['success' => true, 'message' => 'Status updated successfully']);
//     }

//     if ($request->action === 'delete') {
//         Client::whereIn('id', $ids)->delete();
//         return response()->json(['success' => true, 'message' => 'Clients deleted successfully']);
//     }

//     return response()->json(['success' => false, 'message' => 'Invalid action'], 400);
// }



public function bulkDelete(Request $request)
{
    $request->validate([
        'ids' => 'required|array',
    ]);

    $ids = $request->ids;

    // Delete related files
    $leaves = Leave::whereIn('id', $ids)->get();

    foreach ($leaves as $leave) {
        if ($leave->files && file_exists(public_path($leave->files))) {
            unlink(public_path($leave->files));
        }
    }

    // Delete leaves
    Leave::whereIn('id', $ids)->delete();

    return response()->json([
        'success' => true,
        'message' => 'Selected leaves deleted successfully!'
    ]);
}




}
