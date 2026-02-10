<?php

namespace App\Http\Controllers;

use App\Models\Leave;
use App\Models\LeavePolicy;
use App\Models\LeaveBalance;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LeaveController extends Controller
{
    // Get current fiscal year
    private function getCurrentFiscalYear()
    {
        $currentMonth = date('m');
        $currentYear = date('Y');

        // Assuming fiscal year starts in April (04)
        if ($currentMonth >= 4) {
            return $currentYear . '-' . ($currentYear + 1);
        } else {
            return ($currentYear - 1) . '-' . $currentYear;
        }
    }

    // Get fiscal year dates
    private function getFiscalYearDates()
    {
        $currentMonth = date('m');
        $currentYear = date('Y');

        if ($currentMonth >= 4) {
            // April to March next year
            return [
                'start' => Carbon::create($currentYear, 4, 1)->startOfDay(),
                'end' => Carbon::create($currentYear + 1, 3, 31)->endOfDay()
            ];
        } else {
            // April previous year to March current year
            return [
                'start' => Carbon::create($currentYear - 1, 4, 1)->startOfDay(),
                'end' => Carbon::create($currentYear, 3, 31)->endOfDay()
            ];
        }
    }

    // Calculate pro-rated leaves for an employee
    private function calculateProRatedLeaves($employee, $annualLeaves = 18)
    {
        if (!$employee->joining_date) {
            return $annualLeaves;
        }

        $joinDate = Carbon::parse($employee->joining_date);
        $fiscalDates = $this->getFiscalYearDates();
        $fiscalStart = $fiscalDates['start'];
        $fiscalEnd = $fiscalDates['end'];

        // If employee joined after fiscal year started
        if ($joinDate->gt($fiscalStart)) {
            // Calculate months remaining in fiscal year
            $monthsRemaining = $joinDate->diffInMonths($fiscalEnd);

            if ($monthsRemaining > 0) {
                // Pro-rate calculation
                $proRatedLeaves = floor(($annualLeaves / 12) * $monthsRemaining);
                return max(1, $proRatedLeaves); // Minimum 1 leave
            }
            return 0;
        }

        // Employee joined before fiscal year started, gets full leaves
        return $annualLeaves;
    }

    // Initialize or update employee leave balance
    private function initializeEmployeeLeaveBalance($employee, $policy)
    {
        $annualLeaves = $policy->annual_leaves ?? 18;

        // Calculate pro-rated leaves if enabled
        if ($policy->pro_rate_enabled) {
            $allocatedLeaves = $this->calculateProRatedLeaves($employee, $annualLeaves);
        } else {
            $allocatedLeaves = $annualLeaves;
        }

        // Update employee record
        $employee->update([
            'annual_leave_balance' => $allocatedLeaves,
            'remaining_leaves' => $allocatedLeaves,
            'leaves_taken_this_year' => 0,
            'last_leave_reset' => now(),
        ]);

        // Create or update leave balance record for current year
        $currentYear = date('Y');
        LeaveBalance::updateOrCreate(
            [
                'user_id' => $employee->id,
                'year' => $currentYear
            ],
            [
                'allocated_leaves' => $allocatedLeaves,
                'remaining_leaves' => $allocatedLeaves,
                'used_leaves' => 0,
                'total_amount' => $allocatedLeaves * ($policy->leave_monetary_value ?? 0)
            ]
        );
    }

    // Update leave balance when leave is approved
    private function updateLeaveBalanceOnApproval($leave)
    {
        $employee = $leave->user;
        $policy = LeavePolicy::first();

        if (!$employee) return;

        // Calculate days taken
        $daysTaken = 1;
        if ($leave->duration === 'multiple' && $leave->start_date && $leave->end_date) {
            $start = Carbon::parse($leave->start_date);
            $end = Carbon::parse($leave->end_date);
            $daysTaken = $start->diffInDays($end) + 1;
        } elseif ($leave->duration === 'half_day') {
            $daysTaken = 0.5;
        }

        // Check if employee has enough leaves
        if ($employee->remaining_leaves >= $daysTaken) {
            // Deduct from paid leaves
            $employee->decrement('remaining_leaves', $daysTaken);
            $employee->increment('leaves_taken_this_year', $daysTaken);
            $leave->update(['paid' => 1]);
        } else {
            // Mark as unpaid leave
            $leave->update(['paid' => 0]);
        }

        // Update leave balance record for current year
        $currentYear = date('Y');
        $balance = LeaveBalance::where('user_id', $employee->id)
            ->where('year', $currentYear)
            ->first();

        if ($balance) {
            $balance->update([
                'used_leaves' => $employee->leaves_taken_this_year,
                'remaining_leaves' => $employee->remaining_leaves
            ]);
        }
    }

    public function index(Request $request)
    {
        // Get or create leave policy
        $policy = LeavePolicy::first();
        if (!$policy) {
            $policy = LeavePolicy::create([
                'annual_leaves' => 18,
                'pro_rate_enabled' => true,
                'fiscal_year_start' => date('Y') . '-04-01',
                'fiscal_year_end' => (date('Y') + 1) . '-03-31',
                'allow_carry_forward' => false,
                'leave_monetary_value' => 0
            ]);
        }

        // Get all leaves with filters
        $leaves = Leave::with('user');

        // If user is not admin, only show their own
        if (Auth::user()->role !== 'admin') {
            $leaves->where('user_id', Auth::id());
        }

        // Apply filters
        if ($request->filled('employee')) {
            $leaves->where('user_id', $request->employee);
        }

        if ($request->filled('leave_type')) {
            $leaves->where('type', $request->leave_type);
        }

        if ($request->filled('status')) {
            $leaves->where('status', $request->status);
        }

        // Duration filter
        if ($request->filled('duration')) {
            $duration = $request->duration;

            if (str_contains($duration, 'to')) {
                [$start, $end] = preg_split('/\s*to\s*/', $duration);
                try {
                    $startDate = Carbon::parse(trim($start))->startOfDay();
                    $endDate = Carbon::parse(trim($end))->endOfDay();

                    $leaves->where(function ($q) use ($startDate, $endDate) {
                        $q->whereBetween('start_date', [$startDate, $endDate])
                          ->orWhereBetween('end_date', [$startDate, $endDate])
                          ->orWhereBetween('date', [$startDate, $endDate]);
                    });
                } catch (\Exception $e) {
                    // Invalid format
                }
            } else {
                switch ($duration) {
                    case 'Today':
                        $startDate = Carbon::today();
                        $endDate = Carbon::today()->endOfDay();
                        break;
                    case 'Last 30 Days':
                        $startDate = Carbon::now()->subDays(29)->startOfDay();
                        $endDate = Carbon::now()->endOfDay();
                        break;
                    case 'This Month':
                        $startDate = Carbon::now()->startOfMonth();
                        $endDate = Carbon::now()->endOfMonth();
                        break;
                    case 'Last Month':
                        $startDate = Carbon::now()->subMonth()->startOfMonth();
                        $endDate = Carbon::now()->subMonth()->endOfMonth();
                        break;
                    case 'Last 90 Days':
                        $startDate = Carbon::now()->subDays(89)->startOfDay();
                        $endDate = Carbon::now()->endOfDay();
                        break;
                    case 'Last 6 Months':
                        $startDate = Carbon::now()->subMonths(6)->startOfMonth();
                        $endDate = Carbon::now()->endOfDay();
                        break;
                    case 'Last 1 Year':
                        $startDate = Carbon::now()->subYear()->startOfMonth();
                        $endDate = Carbon::now()->endOfDay();
                        break;
                    default:
                        $startDate = null;
                        $endDate = null;
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

        // Get employee data for filters
        $employee_data = [];
        if (Auth::user()->role === 'admin') {
            $employee_data = User::where('role', 'employee')->orderBy('name')->get();

            // Initialize leave balances for employees if not already done
            foreach ($employee_data as $employee) {
                if (!$employee->last_leave_reset) {
                    $this->initializeEmployeeLeaveBalance($employee, $policy);
                }
            }
        }

        // Calculate employee leave summaries for admin
        $employee_summaries = [];
        if (Auth::user()->role === 'admin') {
            foreach ($employee_data as $employee) {
                $employee_summaries[$employee->id] = [
                    'allocated' => $employee->annual_leave_balance,
                    'taken' => $employee->leaves_taken_this_year,
                    'remaining' => $employee->remaining_leaves,
                    'percentage' => $employee->annual_leave_balance > 0
                        ? round(($employee->leaves_taken_this_year / $employee->annual_leave_balance) * 100, 2)
                        : 0,
                    'monetary_value' => $employee->remaining_leaves * ($policy->leave_monetary_value ?? 0),
                ];
            }
        }

        // Get current user's leave summary for employee view
        $user_leave_summary = null;
        if (Auth::user()->role === 'employee') {
            $user = Auth::user();

            // Initialize leave balance if not already done
            if (!$user->last_leave_reset) {
                $this->initializeEmployeeLeaveBalance($user, $policy);
                $user->refresh(); // Refresh to get updated values
            }

            $user_leave_summary = [
                'allocated' => $user->annual_leave_balance,
                'taken' => $user->leaves_taken_this_year,
                'remaining' => $user->remaining_leaves,
                'percentage' => $user->annual_leave_balance > 0
                    ? round(($user->leaves_taken_this_year / $user->annual_leave_balance) * 100, 2)
                    : 0,
                'monetary_value' => $user->remaining_leaves * ($policy->leave_monetary_value ?? 0),
            ];
        }

        return view('admin.leaves.index', compact(
            'leaves',
            'employee_data',
            'policy',
            'employee_summaries',
            'user_leave_summary'
        ));
    }

    // NEW: Update leave policy
    public function updatePolicy(Request $request)
    {
        $validated = $request->validate([
            'annual_leaves' => 'required|integer|min:1',
            'pro_rate_enabled' => 'boolean',
            'fiscal_year_start' => 'required|date',
            'fiscal_year_end' => 'required|date|after:fiscal_year_start',
            'allow_carry_forward' => 'boolean',
            'max_carry_forward' => 'nullable|integer|min:0',
            'leave_monetary_value' => 'nullable|numeric|min:0',
        ]);

        $policy = LeavePolicy::first();
        if ($policy) {
            $policy->update($validated);
        } else {
            LeavePolicy::create($validated);
        }

        // Recalculate all employee leaves if pro-rate or annual leaves changed
        if (isset($validated['annual_leaves']) || isset($validated['pro_rate_enabled'])) {
            $employees = User::where('role', 'employee')->get();
            foreach ($employees as $employee) {
                $this->initializeEmployeeLeaveBalance($employee, $policy);
            }
        }

        return redirect()->route('leaves.index')->with('success', 'Leave policy updated successfully.');
    }

    // NEW: Reset employee leaves (admin can manually reset)
    public function resetEmployeeLeaves(Request $request, $id)
    {
        $employee = User::findOrFail($id);
        $policy = LeavePolicy::first();

        $this->initializeEmployeeLeaveBalance($employee, $policy);

        return back()->with('success', 'Leave balance reset for ' . $employee->name);
    }

    // NEW: Update leave status with automatic paid/unpaid logic
    public function updateStatus(Request $request, Leave $leave)
    {
        $oldStatus = $leave->status;
        $newStatus = $request->status;

        $leave->update(['status' => $newStatus]);

        // If status changed to approved, update leave balance
        if ($oldStatus !== 'approved' && $newStatus === 'approved') {
            $this->updateLeaveBalanceOnApproval($leave);
        }

        // If status changed from approved to something else, refund leaves
        if ($oldStatus === 'approved' && $newStatus !== 'approved' && $leave->paid == 1) {
            $employee = $leave->user;

            // Calculate days to refund
            $daysToRefund = 1;
            if ($leave->duration === 'multiple' && $leave->start_date && $leave->end_date) {
                $start = Carbon::parse($leave->start_date);
                $end = Carbon::parse($leave->end_date);
                $daysToRefund = $start->diffInDays($end) + 1;
            } elseif ($leave->duration === 'half_day') {
                $daysToRefund = 0.5;
            }

            // Refund leaves
            $employee->increment('remaining_leaves', $daysToRefund);
            $employee->decrement('leaves_taken_this_year', $daysToRefund);

            // Update leave balance record
            $currentYear = date('Y');
            $balance = LeaveBalance::where('user_id', $employee->id)
                ->where('year', $currentYear)
                ->first();

            if ($balance) {
                $balance->update([
                    'used_leaves' => $employee->leaves_taken_this_year,
                    'remaining_leaves' => $employee->remaining_leaves
                ]);
            }

            $leave->update(['paid' => 0]);
        }

        return back()->with('success', 'Leave status updated.');
    }

    // NEW: Export leave data
    public function export(Request $request)
    {
        $type = $request->type ?? 'excel';

        $query = Leave::with('user');

        // Apply filters if any
        if ($request->filled('employee')) {
            $query->where('user_id', $request->employee);
        }

        if ($request->filled('from') && $request->filled('to')) {
            $query->whereBetween('start_date', [$request->from, $request->to])
                  ->orWhereBetween('end_date', [$request->from, $request->to])
                  ->orWhereBetween('date', [$request->from, $request->to]);
        }

        $leaves = $query->get();

        if ($type === 'pdf') {
            // PDF export logic
            // You'll need to install a PDF package like barryvdh/laravel-dompdf
            return response()->streamDownload(function () use ($leaves) {
                echo view('admin.leaves.exports.pdf', compact('leaves'))->render();
            }, 'leaves-report-' . date('Y-m-d') . '.pdf');
        } else {
            // Excel export logic
            // You'll need to install a package like maatwebsite/excel
            return response()->streamDownload(function () use ($leaves) {
                echo view('admin.leaves.exports.excel', compact('leaves'))->render();
            }, 'leaves-report-' . date('Y-m-d') . '.xlsx');
        }
    }

    // NEW: Yearly reset of leaves (to be called via scheduled command)
    public function yearlyReset()
    {
        $employees = User::where('role', 'employee')->get();
        $policy = LeavePolicy::first();

        foreach ($employees as $employee) {
            // Calculate carry forward if enabled
            $carryForward = 0;
            if ($policy->allow_carry_forward && $employee->remaining_leaves > 0) {
                $carryForward = min($employee->remaining_leaves, $policy->max_carry_forward ?? 0);
            }

            // Store old year's balance
            $previousYear = date('Y') - 1;
            LeaveBalance::updateOrCreate(
                [
                    'user_id' => $employee->id,
                    'year' => $previousYear
                ],
                [
                    'allocated_leaves' => $employee->annual_leave_balance,
                    'used_leaves' => $employee->leaves_taken_this_year,
                    'remaining_leaves' => $employee->remaining_leaves,
                    'carried_forward' => $carryForward,
                    'total_amount' => $employee->remaining_leaves * ($policy->leave_monetary_value ?? 0)
                ]
            );

            // Calculate new year's leaves
            $newAnnualLeaves = $policy->annual_leaves + $carryForward;

            // Update employee with new year's leaves
            $employee->update([
                'annual_leave_balance' => $newAnnualLeaves,
                'remaining_leaves' => $newAnnualLeaves,
                'leaves_taken_this_year' => 0,
                'carry_forward_leaves' => $carryForward,
                'last_leave_reset' => now(),
            ]);

            // Create new year's balance record
            LeaveBalance::updateOrCreate(
                [
                    'user_id' => $employee->id,
                    'year' => date('Y')
                ],
                [
                    'allocated_leaves' => $newAnnualLeaves,
                    'remaining_leaves' => $newAnnualLeaves,
                    'used_leaves' => 0,
                    'carried_forward' => $carryForward,
                    'total_amount' => $newAnnualLeaves * ($policy->leave_monetary_value ?? 0)
                ]
            );
        }

        return response()->json(['message' => 'Yearly leave reset completed successfully.']);
    }

    // Keep all your existing methods below - they will work as before
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
            'date' => 'required_unless:duration,multiple|date|nullable',
            'start_date' => 'required_if:duration,multiple|date|nullable',
            'end_date' => 'required_if:duration,multiple|date|after_or_equal:start_date|nullable',
            'user_id' => auth()->user()->role === 'admin' ? 'required|exists:users,id' : '',
        ]);

        $userId = auth()->user()->role === 'admin' ? $request->user_id : auth()->id();

        $profileImagePath = null;

        // Handle file upload
        if ($request->hasFile('files')) {
            $image = $request->file('files');
            $imageName = time() . '-' . $image->getClientOriginalName();
            $image->move(public_path('admin/uploads/leave-file'), $imageName);
            $profileImagePath = 'admin/uploads/leave-file/' . $imageName;
        }

        // Check if employee has enough leaves (for employee requests)
        if (auth()->user()->role === 'employee') {
            $user = Auth::user();
            $daysRequested = 1;

            if ($request->duration === 'multiple') {
                $start = Carbon::parse($request->start_date);
                $end = Carbon::parse($request->end_date);
                $daysRequested = $start->diffInDays($end) + 1;
            } elseif ($request->duration === 'half_day') {
                $daysRequested = 0.5;
            }

            if ($user->remaining_leaves < $daysRequested) {
                return back()->with('error', 'You don\'t have enough leaves remaining. This will be marked as unpaid leave.');
            }
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

    // All other existing methods remain exactly the same
    public function edit(Leave $leave)
    {
        $users = User::all();
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

        // If approved paid leave, refund the leaves
        if ($leave->status === 'approved' && $leave->paid == 1) {
            $employee = $leave->user;

            // Calculate days to refund
            $daysToRefund = 1;
            if ($leave->duration === 'multiple' && $leave->start_date && $leave->end_date) {
                $start = Carbon::parse($leave->start_date);
                $end = Carbon::parse($leave->end_date);
                $daysToRefund = $start->diffInDays($end) + 1;
            } elseif ($leave->duration === 'half_day') {
                $daysToRefund = 0.5;
            }

            // Refund leaves
            $employee->increment('remaining_leaves', $daysToRefund);
            $employee->decrement('leaves_taken_this_year', $daysToRefund);
        }

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

    public function calendar()
    {
        if (auth()->user()->role === 'admin') {
            $employee_data = User::orderBy('name')->get();
        } else {
            $employee_data = User::where('id', auth()->id())->get();
        }

        return view('admin.leaves.calendar', compact('employee_data'));
    }

    public function calendarData(Request $request)
    {
        $query = Leave::with('user');

        if (auth()->user()->role !== 'admin') {
            $query->where('user_id', auth()->id());
        }

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

            $start = $leave->start_date ?? $leave->date ?? now()->format('Y-m-d');
            $end   = $leave->end_date ?? $leave->date ?? now()->format('Y-m-d');

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

        Leave::whereIn('id', $ids)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Selected leaves deleted successfully!'
        ]);
    }
}
