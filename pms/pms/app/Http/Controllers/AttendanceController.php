<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use App\Models\AttendanceSetting;
use App\Models\User;
use App\Models\Designation;
use App\Models\EmployeeDetail;
use App\Models\Holiday;
use App\Models\Department;
use App\Models\Leave;
use App\Models\CompanyAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Notifications\ClockInNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AttendanceController extends Controller
{
    /**
     * Convert seconds to HH:MM or HH:MM:SS
     */
    private function secondsToHhmm(int $seconds, bool $showSeconds = false): string
    {
        $seconds = max(0, $seconds);
        $h = intdiv($seconds, 3600);
        $m = intdiv($seconds % 3600, 60);
        $s = $seconds % 60;

        if ($showSeconds) {
            return sprintf('%02d:%02d:%02d', $h, $m, $s);
        }

        return sprintf('%02d:%02d', $h, $m);
    }

    /**
     * Build a Carbon datetime from a separate date (Y-m-d) and a time value (H:i or H:i:s or Carbon)
     * Returns null on failure.
     */
    private function buildDateTimeFromDateAndTime(?string $date, $timeValue): ?\Carbon\Carbon
    {
        if (empty($date) || $timeValue === null || $timeValue === '') {
            return null;
        }

        // Normalize time to H:i:s
        if (is_object($timeValue) && method_exists($timeValue, 'format')) {
            $timeStr = $timeValue->format('H:i:s');
        } else {
            $timeStr = trim((string)$timeValue);
            if (preg_match('/^\d{1,2}:\d{2}$/', $timeStr)) {
                $timeStr .= ':00';
            }
        }

        try {
            return Carbon::createFromFormat('Y-m-d H:i:s', $date . ' ' . $timeStr);
        } catch (\Throwable $e) {
            try {
                return Carbon::parse($date . ' ' . $timeStr);
            } catch (\Throwable $_) {
                return null;
            }
        }
    }

    /**
     * Calculate period totals (seconds, HH:MM, HH:MM:SS, decimal hours) for given users
     */
    private function calculatePeriodTotals($users, array $attendanceMap, Carbon $startDate, Carbon $endDate): array
    {
        $result = [];

        $userIds = [];
        if ($users instanceof \Illuminate\Support\Collection) {
            $userIds = $users->pluck('id')->toArray();
        } elseif (is_array($users)) {
            if (! empty($users) && is_object($users[0]) && property_exists($users[0], 'id')) {
                $userIds = array_map(fn($u) => $u->id, $users);
            } else {
                $userIds = $users;
            }
        } elseif (is_object($users) && property_exists($users, 'id')) {
            $userIds = [$users->id];
        }

        foreach ($userIds as $uid) {
            $periodSeconds = 0;

            $d = $startDate->copy();
            for (; $d->lte($endDate); $d->addDay()) {
                $dateKey = $d->format('Y-m-d');

                $cell = ($attendanceMap[$uid][$dateKey] ?? null);

                if ($cell instanceof \App\Models\Attendance) {
                    $periodSeconds += (int) ($cell->total_seconds ?? 0);
                    continue;
                }

                if (is_array($cell) || $cell instanceof \Illuminate\Support\Collection) {
                    foreach ($cell as $c) {
                        if ($c instanceof \App\Models\Attendance) {
                            $periodSeconds += (int) ($c->total_seconds ?? 0);
                        }
                    }
                }
            }

            $secs = max(0, (int) $periodSeconds);
            $hhmm = $this->secondsToHhmm($secs, false);
            $hhmmss = $this->secondsToHhmm($secs, true);
            $decimal = round($secs / 3600, 2);

            $result[$uid] = [
                'seconds' => $secs,
                'hhmm'    => $hhmm,
                'hhmmss'  => $hhmmss,
                'decimal' => $decimal,
            ];
        }

        return $result;
    }

    /**
     * Export Multi PDF - ADMIN ONLY
     */
    public function exportMultiPdf(Request $request)
    {
        $user = Auth::user();

        // Only admin can export
        if ($user->role !== 'admin') {
            return redirect()->route('attendance.index')
                ->with('error', 'You do not have permission to export data.');
        }

        $month = (int) ($request->month ?? now()->month);
        $year  = (int) ($request->year ?? now()->year);

        $userQuery = User::query();

        // Apply all filters
        if ($request->has('user_ids') && is_array($request->user_ids) && count($request->user_ids) > 0) {
            $userQuery->whereIn('id', $request->user_ids);
        }

        // Apply department filter
        if ($request->has('department_id') && $request->department_id) {
            $userQuery->whereHas('employeeDetail', function($q) use ($request) {
                $q->where('department_id', $request->department_id);
            });
        }

        // Apply designation filter
        if ($request->has('designation_id') && $request->designation_id) {
            $userQuery->whereHas('employeeDetail', function($q) use ($request) {
                $q->where('designation_id', $request->designation_id);
            });
        }

        $users = $userQuery->orderBy('name')->get();

        $daysInMonth = Carbon::create($year, $month, 1)->daysInMonth;
        $startDate = Carbon::create($year, $month, 1)->toDateString();
        $endDate   = Carbon::create($year, $month, $daysInMonth)->toDateString();

        // Build attendance query with same filters
        $attendanceQuery = Attendance::whereBetween('date', [$startDate, $endDate]);

        if ($request->has('user_ids') && is_array($request->user_ids) && count($request->user_ids)) {
            $attendanceQuery->whereIn('user_id', $request->user_ids);
        }

        if ($request->has('department_id') && $request->department_id) {
            $attendanceQuery->where('department_id', $request->department_id);
        }

        $attendanceRows = $attendanceQuery->get();

        // ensure model accessors are available in the view
        $attendanceRows->each->append(['total_seconds','total_duration','clock_in_datetime','clock_out_datetime']);

        // build map $attendanceMap[$userId][$Y-m-d] = record
        $attendanceMap = [];
        foreach ($attendanceRows as $r) {
            $dateKey = Carbon::parse($r->date)->format('Y-m-d');
            $attendanceMap[$r->user_id][$dateKey] = $r;
        }

        // Build filter info for title
        $filterInfo = '';
        if ($request->department_id) {
            $dept = Department::find($request->department_id);
            $filterInfo .= $dept ? $dept->name : 'Department ' . $request->department_id;
        }
        if ($request->designation_id) {
            $desig = Designation::find($request->designation_id);
            $filterInfo .= $desig ? ' - ' . $desig->name : ' - Designation ' . $request->designation_id;
        }

        $data = [
            'users' => $users,
            'attendanceMap' => $attendanceMap,
            'daysInMonth' => $daysInMonth,
            'month' => $month,
            'year' => $year,
            'generated_at' => now()->format('d-M-Y H:i'),
            'chunkDays' => 15,
            'filterInfo' => $filterInfo
        ];

        $pdf = Pdf::loadView('admin.attendance.report_pdf', $data)
            ->setPaper('a4', 'landscape')
            ->setWarnings(false);

        $fileName = sprintf('Attendance_Multi_%02d_%04d%s.pdf',
            $month,
            $year,
            $filterInfo ? '_' . Str::slug($filterInfo) : ''
        );

        return $pdf->download($fileName);
    }

    /**
     * Main Attendance Index - ROLE BASED
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Check if user has access
        if (!in_array($user->role, ['admin', 'employee'])) {
            abort(403, 'Unauthorized access');
        }

        $month = (int) ($request->month ?? now()->month);
        $year = (int) ($request->year ?? now()->year);
        $daysInMonth = Carbon::createFromDate($year, $month)->daysInMonth;

        $startDate = Carbon::create($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();

        // Step 1: holidays
        $holidays = Holiday::whereBetween('date', [$startDate, $endDate])
            ->get()
            ->mapWithKeys(function ($holiday) {
                return [
                    Carbon::parse($holiday->date)->format('Y-m-d') => $holiday->occassion ?? $holiday->title ?? 'Holiday',
                ];
            })
            ->toArray();

        // Step 2: load users and attendances based on role
        if ($user->role === 'admin') {
            $users = User::where('role', 'employee')->get();

            $attendances = Attendance::whereMonth('date', $month)
                ->whereYear('date', $year)
                ->get();
        } else {
            // Employee can only see their own data
            $users = collect([$user]);

            $attendances = Attendance::where('user_id', $user->id)
                ->whereMonth('date', $month)
                ->whereYear('date', $year)
                ->get();
        }

        // ensure model accessors are appended so view can use total_seconds/total_duration
        $attendances->each->append(['total_seconds','total_duration','clock_in_datetime','clock_out_datetime']);

        $leaves = Leave::whereMonth('date', $month)
            ->whereYear('date', $year)
            ->where('status', 'approved')
            ->get();

        // Step 3: initialize attendanceMap and mark holidays
        $attendanceMap = [];
        foreach ($users as $u) {
            for ($d = $startDate->copy(); $d->lte($endDate); $d->addDay()) {
                $formattedDate = $d->format('Y-m-d');
                $attendanceMap[$u->id][$formattedDate] = null;

                if (array_key_exists($formattedDate, $holidays)) {
                    $attendanceMap[$u->id][$formattedDate] = (object)[
                        'status' => 'holiday',
                        'occassion' => $holidays[$formattedDate]
                    ];
                }
            }
        }

        // Step 4: merge attendance records (Eloquent models)
        foreach ($attendances as $att) {
            $dateKey = Carbon::parse($att->date)->format('Y-m-d');
            $attendanceMap[$att->user_id][$dateKey] = $att;
        }

        // Step 5: merge approved leaves
        foreach ($leaves as $leave) {
            $dateKey = Carbon::parse($leave->date)->format('Y-m-d');
            $attendanceMap[$leave->user_id][$dateKey] = (object)[
                'status' => 'leave',
                'leave_type' => $leave->type,
                'duration' => $leave->duration,
                'reason' => $leave->reason ?? null,
            ];
        }

        // Step 6: fill remaining as absent
        foreach ($users as $u) {
            for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
                $formattedDate = $date->format('Y-m-d');

                if (!isset($attendanceMap[$u->id][$formattedDate])) {
                    $attendanceMap[$u->id][$formattedDate] = (object)[ 'status' => 'absent' ];
                }
            }
        }

        // Only admin gets these data
        $departments = $user->role == 'admin' ? Department::get() : collect();
        $designations = $user->role == 'admin' ? Designation::all() : collect();

        // calculate period totals for the displayed period and users
        $periodTotals = $this->calculatePeriodTotals($users, $attendanceMap, $startDate, $endDate);

        return view('admin.attendance.index', compact(
            'users',
            'attendanceMap',
            'daysInMonth',
            'month',
            'year',
            'departments',
            'designations',
            'periodTotals'
        ));
    }

    /**
     * Mark Attendance - ADMIN ONLY
     */
    public function markAttendance(Request $request)
    {
        $user = Auth::user();

        // Only admin can mark attendance
        if ($user->role !== 'admin') {
            return back()->with('error', 'You do not have permission to mark attendance.');
        }

        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'date' => 'required|date',
            'status' => 'required|in:present,absent,holiday,late,half_day,leave',
            'clock_in' => 'nullable|date_format:H:i',
        ]);

        Attendance::updateOrCreate(
            ['user_id' => $data['user_id'], 'date' => $data['date']],
            ['status' => $data['status'], 'clock_in' => $data['clock_in'] ? Carbon::createFromFormat('H:i', $data['clock_in'])->format('H:i:s') : null]
        );

        return back()->with('success', 'Attendance updated');
    }

    /**
     * Settings - ADMIN ONLY
     */
    public function settings()
    {
        $user = Auth::user();

        // Only admin can access settings
        if ($user->role !== 'admin') {
            return redirect()->route('attendance.index')
                ->with('error', 'You do not have permission to access settings.');
        }

        $setting = AttendanceSetting::firstOrCreate([], ['office_start_time' => '10:00', 'late_time' => '10:15']);
        return view('attendance.settings', compact('setting'));
    }

    /**
     * Update Settings - ADMIN ONLY
     */
    public function updateSettings(Request $request)
    {
        $user = Auth::user();

        // Only admin can update settings
        if ($user->role !== 'admin') {
            return back()->with('error', 'You do not have permission to update settings.');
        }

        $data = $request->validate([
            'office_start_time' => 'required|date_format:H:i',
            'late_time' => 'required|date_format:H:i',
        ]);

        AttendanceSetting::updateOrCreate([], $data);
        return back()->with('success', 'Settings updated');
    }

    /**
     * Filter Attendance - ROLE BASED
     */
    public function filter(Request $request)
    {
        $user = Auth::user();

        if (!in_array($user->role, ['admin', 'employee'])) {
            abort(403, 'Unauthorized');
        }

        $month = (int) ($request->input('month', now()->month));
        $year  = (int) ($request->input('year', now()->year));
        $userId = $request->input('user_id');
        $department_id = $request->input('department_id');
        $designation_id = $request->input('designation_id');

        $startDate = Carbon::createFromDate($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();

        // load holidays in month
        $holidays = Holiday::whereBetween('date', [$startDate, $endDate])
            ->get()
            ->mapWithKeys(function ($holiday) {
                return [Carbon::parse($holiday->date)->format('Y-m-d') => $holiday->occassion ?? $holiday->title ?? 'Holiday'];
            })->toArray();

        // base users query
        $usersQuery = User::with('employeeDetail')->where('role', 'employee');

        if ($user->role === 'employee') {
            // Employee can only see their own data
            $usersQuery->where('id', $user->id);
        } elseif ($userId && $user->role === 'admin') {
            // Admin can filter by specific user
            $usersQuery->where('id', (int) $userId);
        }

        // Department filter - only for admin
        if ($department_id && $user->role === 'admin') {
            $usersQuery->whereHas('employeeDetail', function ($q) use ($department_id) {
                $q->where('department_id', $department_id);
            });
        }

        // Designation filter - only for admin
        if ($designation_id && $user->role === 'admin') {
            $usersQuery->whereHas('employeeDetail', function ($q) use ($designation_id) {
                $q->where('designation_id', $designation_id);
            });
        }

        $users = $usersQuery->orderBy('name')->get();

        $daysInMonth = Carbon::createFromDate($year, $month, 1)->daysInMonth;

        // if no users, still return empty table
        if ($users->isEmpty()) {
            $attendanceMap = [];
            $designations = $user->role === 'admin' ? Designation::all() : collect();

            $html = view('admin.attendance.table', compact(
                'users',
                'designations',
                'attendanceMap',
                'daysInMonth',
                'month',
                'year'
            ))->render();

            return response()->json(['html' => $html]);
        }

        $userIds = $users->pluck('id')->toArray();

        $attendances = Attendance::whereIn('user_id', $userIds)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->get();

        $attendances->each->append(['total_seconds','total_duration','clock_in_datetime','clock_out_datetime']);

        // map attendance rows
        $attendanceMap = [];
        foreach ($attendances as $att) {
            $dateKey = Carbon::parse($att->date)->format('Y-m-d');
            // support multiple punches per day: store collection
            if (! isset($attendanceMap[$att->user_id][$dateKey])) {
                $attendanceMap[$att->user_id][$dateKey] = collect();
            }
            $attendanceMap[$att->user_id][$dateKey]->push($att);
        }

        // merge approved leaves
        $leaves = Leave::whereIn('user_id', $userIds)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->where('status', 'approved')
            ->get();

        foreach ($leaves as $leave) {
            $dateKey = Carbon::parse($leave->date)->format('Y-m-d');
            $attendanceMap[$leave->user_id][$dateKey] = (object)[
                'status' => 'leave',
                'leave_type' => $leave->type ?? null,
                'duration' => $leave->duration ?? null,
                'reason' => $leave->reason ?? null,
            ];
        }

        // initialize empty dates and apply holidays
        foreach ($users as $u) {
            for ($d = $startDate->copy(); $d->lte($endDate); $d->addDay()) {
                $formattedDate = $d->format('Y-m-d');

                if (! isset($attendanceMap[$u->id][$formattedDate])) {
                    // holiday?
                    if (array_key_exists($formattedDate, $holidays)) {
                        $attendanceMap[$u->id][$formattedDate] = (object)[
                            'status' => 'holiday',
                            'occassion' => $holidays[$formattedDate]
                        ];
                    } else {
                        $attendanceMap[$u->id][$formattedDate] = (object)[ 'status' => 'absent' ];
                    }
                }
            }
        }

        $designations = $user->role === 'admin' ? Designation::all() : collect();

        // calculate period totals and pass them to the view used by AJAX
        $periodTotals = $this->calculatePeriodTotals($users, $attendanceMap, $startDate, $endDate);

        $html = view('admin.attendance.table', compact(
            'users',
            'designations',
            'attendanceMap',
            'daysInMonth',
            'month',
            'year',
            'periodTotals'
        ))->render();

        return response()->json(['html' => $html]);
    }

    /**
     * By Hour View - ADMIN ONLY
     */
    public function byHour(Request $request)
    {
        $user = Auth::user();

        // Only admin can access by hour view
        if ($user->role !== 'admin') {
            return redirect()->route('attendance.index')
                ->with('error', 'You do not have permission to access this page.');
        }

        $monthRaw = $request->input('month', now()->month);
        $yearRaw  = $request->input('year', now()->year);
        $userId   = $request->input('user_id', null);

        // Normalize month -> integer 1..12
        try {
            if (is_numeric($monthRaw)) {
                $month = (int) $monthRaw;
            } else {
                $parsed = Carbon::parse($monthRaw);
                $month = (int) $parsed->month;
            }
        } catch (\Throwable $e) {
            $month = (int) now()->month;
        }

        $year = is_numeric($yearRaw) ? (int)$yearRaw : (int) now()->year;

        // pick users list
        $users = User::where('role', 'employee')->get();

        // Build a query that will find attendances matching either:
        // - month/year of clock_in (preferred) OR
        // - month/year of date (fallback)
        $attendanceQuery = Attendance::query();

        // if user filter provided
        if ($userId) {
            $attendanceQuery->where('user_id', (int)$userId);
        }

        $attendanceQuery->where(function($q) use ($month, $year) {
            $q->where(function($q2) use ($month, $year) {
                // clock_in is TIME column: build datetime via CONCAT(date,' ',COALESCE(clock_in,'00:00:00'))
                $q2->whereRaw("MONTH(CONCAT(`date`, ' ', COALESCE(`clock_in`, '00:00:00'))) = ?", [$month])
                   ->whereRaw("YEAR(CONCAT(`date`, ' ', COALESCE(`clock_in`, '00:00:00'))) = ?", [$year]);
            })->orWhere(function($q3) use ($month, $year) {
                // fallback: attendance->date column
                $q3->whereMonth('date', $month)
                   ->whereYear('date', $year);
            });
        });

        // eager load user relation for display
        $attendances = $attendanceQuery->with('user')->get();

        // append accessors expected by views
        $attendances->each->append(['total_seconds','total_duration','clock_in_datetime','clock_out_datetime']);

        // build attendanceMap: userId -> date -> collection(attendance)
        $attendanceMap = [];
        foreach ($attendances as $att) {
            $dateKey = Carbon::parse($att->date)->format('Y-m-d');
            if (! isset($attendanceMap[$att->user_id])) $attendanceMap[$att->user_id] = [];
            if (! isset($attendanceMap[$att->user_id][$dateKey])) $attendanceMap[$att->user_id][$dateKey] = collect();
            $attendanceMap[$att->user_id][$dateKey]->push($att);
        }

        // compute dayTotals: [user_id][Y-m-d] => seconds
        $dayTotals = [];
        foreach ($attendances as $att) {
            $uid = $att->user_id;
            $dateKey = Carbon::parse($att->date)->format('Y-m-d');

            // use model accessor total_seconds (handles overnight internally)
            $secs = (int) ($att->total_seconds ?? 0);

            if (! isset($dayTotals[$uid])) $dayTotals[$uid] = [];
            if (! isset($dayTotals[$uid][$dateKey])) $dayTotals[$uid][$dateKey] = 0;
            $dayTotals[$uid][$dateKey] += $secs;
        }

        // prepare days count for selected month/year
        $daysInMonth = Carbon::createFromDate($year, $month, 1)->daysInMonth;

        // compute periodTotals
        $startDate = Carbon::createFromDate($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();

        $periodTotals = $this->calculatePeriodTotals($users, $attendanceMap, $startDate, $endDate);

        return view('admin.attendance.by-hour', compact('attendances', 'users', 'month', 'year', 'attendanceMap', 'daysInMonth', 'periodTotals', 'dayTotals'));
    }

    /**
     * Create Attendance - ADMIN ONLY
     */
    public function create()
    {
        $user = Auth::user();

        // Only admin can create attendance
        if ($user->role !== 'admin') {
            return redirect()->route('attendance.index')
                ->with('error', 'You do not have permission to add attendance.');
        }

        $departments = Department::get();
        $users = \App\Models\User::where('role', 'employee')->get();
        $year = now()->format('Y');
        $month = now()->format('m');
        $location = CompanyAddress::all();

        return view('admin.attendance.create', compact('users','departments','year','month','location'));
    }

    /**
     * Store Attendance - ADMIN ONLY
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        // Only admin can store attendance
        if ($user->role !== 'admin') {
            return redirect()->route('attendance.index')
                ->with('error', 'You do not have permission to add attendance.');
        }

        $request->validate([
            'user_id'   => 'required',
            'user_id.*' => 'sometimes|exists:users,id',
            'clock_in'  => ['nullable', function ($attribute, $value, $fail) {
                if (!preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $value)) {
                    $fail('Clock In must be in HH:MM format (00–23 hours, 00–59 minutes).');
                }
            }],
            'clock_out' => ['nullable', function ($attribute, $value, $fail) use ($request) {
                if (!preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $value)) {
                    $fail('Clock Out must be in HH:MM format (00–23 hours, 00–59 minutes).');
                }
                if ($request->clock_in && $value && $value <= $request->clock_in) {
                    $fail('Clock Out must be after Clock In.');
                }
            }],
        ]);

        // Normalize user_id to array (accept both single id and array)
        $userIds = $request->input('user_id', []);
        if (!is_array($userIds)) $userIds = [$userIds];

        // If user selected mark by date-range (bulk)
        if ($request->mark_attendance_by === 'date' && $request->date_range) {
            [$start, $end] = explode(' - ', $request->date_range);

            $startDate = Carbon::createFromFormat('m/d/Y', trim($start));
            $endDate   = Carbon::createFromFormat('m/d/Y', trim($end));

            $period = CarbonPeriod::create($startDate, $endDate);

            foreach ($userIds as $userId) {
                foreach ($period as $date) {

                    // store time-only strings for DB TIME columns (H:i:s)
                    $clockInVal = $request->clock_in ? Carbon::createFromFormat('H:i', $request->clock_in)->format('H:i:s') : null;
                    $clockOutVal = $request->clock_out ? Carbon::createFromFormat('H:i', $request->clock_out)->format('H:i:s') : null;

                    $record = Attendance::updateOrCreate(
                        [
                            'user_id' => $userId,
                            'date'    => $date->toDateString(),
                        ],
                        [
                            'department_id'  => $request->department_id,
                            'status'         => $request->status,
                            'working_from'   => $request->working_from,
                            'location_id'    => $request->location_id,
                            'work_from_type' => $request->work_from_type,
                            'late'           => ($request->late == 'yes') ? 'yes' : 'no',
                            'half_day'       => ($request->half_day == 'yes') ? 'yes' : 'no',
                            'half_day_type'  => ($request->half_day == 'yes') ? $request->half_day_duration : null,
                            'clock_in'       => $clockInVal,
                            'clock_out'      => $clockOutVal,
                        ]
                    );

                    // prepare a Carbon instance for the notification (combine date + time)
                    $record->clocked_at = $this->buildDateTimeFromDateAndTime($record->date, $record->clock_in) ?? Carbon::now();

                    // notify the user (database channel)
                    $user = \App\Models\User::find($userId);
                    if ($user) {
                        try {
                            $user->notify(new ClockInNotification($record));
                        } catch (\Throwable $e) {
                            Log::error('ClockInNotification failed (bulk)', ['user' => $userId, 'error' => $e->getMessage()]);
                        }
                    }
                }
            }

        } else {
            // Single-date branch
            foreach ($userIds as $userId) {
                $existing = Attendance::where('user_id', $userId)
                    ->whereDate('date', $request->date)
                    ->first();

                if ($existing && !$request->has('overwrite_attendance')) {
                    continue;
                }

                $clockInVal = $request->clock_in ? Carbon::createFromFormat('H:i', $request->clock_in)->format('H:i:s') : null;
                $clockOutVal = $request->clock_out ? Carbon::createFromFormat('H:i', $request->clock_out)->format('H:i:s') : null;

                $record = Attendance::updateOrCreate(
                    [
                        'user_id' => $userId,
                        'date'    => $request->date,
                    ],
                    [
                        'department_id'  => $request->department_id,
                        'status'         => $request->status,
                        'working_from'   => $request->working_from,
                        'location_id'    => $request->location_id,
                        'work_from_type' => $request->work_from_type,
                        'late'           => ($request->late == 'yes') ? 'yes' : 'no',
                        'half_day'       => ($request->half_day == 'yes') ? 'yes' : 'no',
                        'half_day_type'  => ($request->half_day == 'yes') ? $request->half_day_duration : null,
                        'clock_in'       => $clockInVal,
                        'clock_out'      => $clockOutVal,
                    ]
                );

                // prepare a Carbon instance for the notification
                $record->clocked_at = $this->buildDateTimeFromDateAndTime($record->date, $record->clock_in) ?? Carbon::now();

                // notify the user
                $user = \App\Models\User::find($userId);
                if ($user) {
                    try {
                        $user->notify(new ClockInNotification($record));
                    } catch (\Throwable $e) {
                        Log::error('ClockInNotification failed (single)', ['user' => $userId, 'error' => $e->getMessage()]);
                    }
                }
            }
        }

        return redirect()->route('attendance.index')->with('success', 'Attendance saved successfully.');
    }

    /**
     * Attendance Report - ADMIN ONLY
     */
    public function attendanceReport(Request $request)
    {
        $user = Auth::user();

        // Only admin can access reports
        if ($user->role !== 'admin') {
            return redirect()->route('attendance.index')
                ->with('error', 'You do not have permission to access reports.');
        }

        $month = (int) ($request->month ?? now()->month);
        $year  = (int) ($request->year ?? now()->year);
        $daysInMonth = Carbon::create($year, $month)->daysInMonth;

        $users = User::where('role', 'employee')->get();

        $input = $request->user_id;

        if (is_array($input)) {
            $selectedIds = array_map('intval', $input);
        } elseif (!empty($input)) {
            $selectedIds = [ (int) $input ];
        } else {
            $selectedIds = [];
        }

        $selectedUser = count($selectedIds) === 1
            ? User::find($selectedIds[0])
            : null;

        $startDate = Carbon::create($year, $month, 1)->toDateString();
        $endDate   = Carbon::create($year, $month, $daysInMonth)->toDateString();

        $attRecords = !empty($selectedIds)
            ? Attendance::whereIn('user_id', $selectedIds)
                ->whereBetween('date', [$startDate, $endDate])
                ->get()
            : collect();

        if ($attRecords instanceof \Illuminate\Support\Collection && $attRecords->isNotEmpty()) {
            $attRecords->each->append(['total_seconds','total_duration','clock_in_datetime','clock_out_datetime']);
        }

        $grouped = $attRecords->groupBy(function ($a) {
            $day = Carbon::parse($a->date)->day;
            return "{$a->user_id}|{$day}";
        });

        $attendances = [];
        $targetUserIds = !empty($selectedIds)
            ? $selectedIds
            : $users->pluck('id')->toArray();

        for ($i = 0; $i < count($targetUserIds); $i++) {
            $uid = $targetUserIds[$i];
            for ($d = 1; $d <= $daysInMonth; $d++) {
                $key = "{$uid}|{$d}";
                $attendances[$uid][$d] = $grouped[$key][0] ?? null;
            }
        }

        $summary = null;

        if ($selectedUser) {
            $summary = [
                'present' => Attendance::where('user_id', $selectedUser->id)->whereMonth('date', $month)->whereYear('date', $year)->where('status', 'present')->count(),
                'late'    => Attendance::where('user_id', $selectedUser->id)->whereMonth('date', $month)->whereYear('date', $year)->where('status', 'late')->count(),
                'absent'  => Attendance::where('user_id', $selectedUser->id)->whereMonth('date', $month)->whereYear('date', $year)->where('status', 'absent')->count(),
                'leave'   => Attendance::where('user_id', $selectedUser->id)->whereMonth('date', $month)->whereYear('date', $year)->where('status', 'leave')->count(),
            ];
        } elseif (count($selectedIds) > 1) {
            $summary = [];

            foreach ($selectedIds as $uid) {
                $summary[$uid] = [
                    'present' => Attendance::where('user_id', $uid)->whereMonth('date', $month)->whereYear('date', $year)->where('status', 'present')->count(),
                    'late'    => Attendance::where('user_id', $uid)->whereMonth('date', $month)->whereYear('date', $year)->where('status', 'late')->count(),
                    'absent'  => Attendance::where('user_id', $uid)->whereMonth('date', $month)->whereYear('date', $year)->where('status', 'absent')->count(),
                    'leave'   => Attendance::where('user_id', $uid)->whereMonth('date', $month)->whereYear('date', $year)->where('status', 'leave')->count(),
                ];
            }
        }

        // Build attendanceMap for totals (userId -> date -> attendance model or null)
        $attendanceMap = [];
        foreach ($attRecords as $r) {
            $dateKey = Carbon::parse($r->date)->format('Y-m-d');
            if (! isset($attendanceMap[$r->user_id])) $attendanceMap[$r->user_id] = [];
            // if multiple records for same day, keep as collection
            if (! isset($attendanceMap[$r->user_id][$dateKey])) {
                $attendanceMap[$r->user_id][$dateKey] = [];
            }
            $attendanceMap[$r->user_id][$dateKey][] = $r;
        }

        // ensure we have full date keys for each user (fill nulls)
        foreach ($targetUserIds as $uid) {
            for ($d = 1; $d <= $daysInMonth; $d++) {
                $dateKey = Carbon::create($year, $month, $d)->format('Y-m-d');
                if (! isset($attendanceMap[$uid][$dateKey])) {
                    $attendanceMap[$uid][$dateKey] = null;
                }
            }
        }

        $periodTotals = $this->calculatePeriodTotals($targetUserIds, $attendanceMap, Carbon::create($year,$month,1), Carbon::create($year,$month,1)->endOfMonth());

        return view('admin.attendance.report', compact(
            'users',
            'attendances',
            'month',
            'year',
            'daysInMonth',
            'selectedUser',
            'summary',
            'selectedIds',
            'periodTotals'
        ));
    }

    /**
     * Export Excel - ADMIN ONLY
     */
    public function exportExcel(Request $request)
    {
        $user = Auth::user();

        // Only admin can export
        if ($user->role !== 'admin') {
            abort(403, 'Unauthorized access');
        }

        // Fixed: Use Excel::download() instead of calling non-existent method
        $filters = $request->all();
        $fileName = 'attendance-report-' . date('Y-m-d') . '.xlsx';

        return Excel::download(
            new \App\Exports\AttendanceExport($filters),
            $fileName
        );
    }

    /**
     * Export PDF - ADMIN ONLY
     */
    public function exportPdf(Request $request)
    {
        $user = Auth::user();

        // Only admin can export
        if ($user->role !== 'admin') {
            abort(403, 'Unauthorized access');
        }

        // Fixed: Use the existing exportMultiPdf method instead
        return $this->exportMultiPdf($request);
    }

    /**
     * By Member View - ADMIN ONLY
     */
    public function byMember(Request $request)
    {
        $user = Auth::user();

        // Only admin can access by member view
        if ($user->role !== 'admin') {
            return redirect()->route('attendance.index')
                ->with('error', 'You do not have permission to access this page.');
        }

        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);
        $userId = $request->input('user_id');

        $users = User::where('role', 'employee')
            ->when($userId, function ($q) use ($userId) {
                return $q->where('id', (int) $userId);
            })
            ->get();

        $attendances = Attendance::query()
            ->when($userId, function ($query) use ($userId) {
                return $query->where('user_id', (int) $userId);
            })
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->with('user')
            ->get();

        // append computed accessors
        $attendances->each->append(['total_seconds','total_duration','clock_in_datetime','clock_out_datetime']);

        $attendanceMap = [];
        foreach ($attendances as $attendance) {
            $dateKey = Carbon::parse($attendance->date)->format('Y-m-d');
            if (! isset($attendanceMap[$attendance->user_id])) $attendanceMap[$attendance->user_id] = [];
            if (! isset($attendanceMap[$attendance->user_id][$dateKey])) $attendanceMap[$attendance->user_id][$dateKey] = collect();
            $attendanceMap[$attendance->user_id][$dateKey]->push($attendance);
        }

        $daysInMonth = Carbon::createFromDate($year, $month, 1)->daysInMonth;

        // ensure each date key exists for each user
        $startDate = Carbon::createFromDate($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();
        foreach ($users as $u) {
            for ($d = $startDate->copy(); $d->lte($endDate); $d->addDay()) {
                $dateKey = $d->format('Y-m-d');
                if (! isset($attendanceMap[$u->id][$dateKey])) {
                    $attendanceMap[$u->id][$dateKey] = null;
                }
            }
        }

        $periodTotals = $this->calculatePeriodTotals($users, $attendanceMap, $startDate, $endDate);

        return view('admin.attendance.by_member', compact(
            'month', 'year', 'userId', 'users', 'attendanceMap', 'daysInMonth', 'periodTotals'
        ));
    }

    /**
     * Location View - ADMIN ONLY
     */
    public function todayAttendanceByMap(Request $request)
    {
        $user = Auth::user();

        // Only admin can access location view
        if ($user->role !== 'admin') {
            return redirect()->route('attendance.index')
                ->with('error', 'You do not have permission to access this page.');
        }

        $requestedDate = $request->input('date');
        $month = (int) ($request->input('month', now()->month));
        $year  = (int) ($request->input('year', now()->year));

        $query = Attendance::with([
            'user.employeeDetail.designation',
            'user.employeeDetail.department'
        ]);

        if ($requestedDate) {
            $query->whereDate('date', $requestedDate);
            $viewDate = $requestedDate;
        } elseif ($request->has('month') || $request->has('year')) {
            $query->whereMonth('date', $month)->whereYear('date', $year);
            $viewDate = Carbon::createFromDate($year, $month, 1)->toDateString();
        } else {
            $viewDate = now()->toDateString();
            $query->whereDate('date', $viewDate);
        }

        $attendances = $query->get();

        // Append accessors and calculate total hours
        $attendances->each(function ($attendance) {
            $attendance->append(['total_seconds','total_duration','clock_in_datetime','clock_out_datetime']);

            // Calculate total hours if clock_in and clock_out exist
            if ($attendance->clock_in && $attendance->clock_out) {
                $clockIn = Carbon::parse($attendance->clock_in);
                $clockOut = Carbon::parse($attendance->clock_out);
                $attendance->total_hours_calculated = $clockOut->diffInHours($clockIn);
            } else {
                $attendance->total_hours_calculated = null;
            }
        });

        // Enhanced debug data with both clock-in and clock-out info
        $debugData = $attendances->map(function ($att) {
            $user = $att->user;
            $empDetail = $user?->employeeDetail ?? null;

            // Check if location columns exist in database
            $hasClockInLocation = isset($att->clock_in_latitude) && $att->clock_in_latitude !== null;
            $hasClockOutLocation = isset($att->clock_out_latitude) && $att->clock_out_latitude !== null;

            return [
                'attendance_id' => $att->id,
                'user' => $user ? [
                    'id'     => $user->id,
                    'name'   => $user->name,
                    'avatar' => $user->profile_image_url ?? ($user->profile_image ?? null),
                    'email'  => $user->email,
                ] : null,
                'designation' => $empDetail?->designation?->name ?? null,
                'department'  => $empDetail?->department ? [
                    'id'   => $empDetail->department->id,
                    'name' => $empDetail->department->dpt_name ?? $empDetail->department->name ?? null,
                ] : null,
                // Clock-in data
                'clock_in_time' => $att->clock_in,
                'clock_in_latitude' => $hasClockInLocation ? $att->clock_in_latitude : null,
                'clock_in_longitude' => $hasClockInLocation ? $att->clock_in_longitude : null,
                'clock_in_address' => $hasClockInLocation ? ($att->clock_in_address ?? 'Coordinates recorded') : null,
                // Clock-out data
                'clock_out_time' => $att->clock_out,
                'clock_out_latitude' => $hasClockOutLocation ? $att->clock_out_latitude : null,
                'clock_out_longitude' => $hasClockOutLocation ? $att->clock_out_longitude : null,
                'clock_out_address' => $hasClockOutLocation ? ($att->clock_out_address ?? 'Coordinates recorded') : null,
                // Additional info
                'late' => in_array(strtolower((string)($att->late ?? '')), ['1', 'yes', 'true'], true),
                'status' => $att->status,
                'work_from_type' => $att->work_from_type ?? 'office',
                'working_from' => $att->working_from,
                'total_hours' => $att->total_hours_calculated ?? $att->total_hours ?? null,
                'date' => $att->date,
                'has_location_change' => $hasClockInLocation && $hasClockOutLocation &&
                    ($att->clock_in_latitude != $att->clock_out_latitude ||
                     $att->clock_in_longitude != $att->clock_out_longitude),
                'has_location_data' => $hasClockInLocation || $hasClockOutLocation,
            ];
        })->values();

        // Filter for map points (only show records with location data)
        $mapPoints = $debugData->filter(function ($a) {
            return !empty($a['user']) && $a['has_location_data'];
        })->values();

        $employees = $debugData->pluck('user')->filter()->unique('id')->values();
        $departments = $debugData->pluck('department')->filter()->unique('id')->values();

        if ($employees->isEmpty()) {
            $employees = User::where('role', 'employee')
                ->select('id', 'name')
                ->get()
                ->map(fn($u) => ['id' => $u->id, 'name' => $u->name]);
        }

        if ($departments->isEmpty()) {
            $departments = Department::query();
            if (\Illuminate\Support\Facades\Schema::hasColumn('departments', 'dpt_name')) {
                $departments->select('id', 'dpt_name as name');
            } else {
                $departments->select('id', 'name');
            }
            $departments = $departments->get();
        }

        return view('admin.attendance.by_map_location', [
            'attendanceData' => $mapPoints,
            'debugData'      => $debugData,
            'employees'      => $employees,
            'departments'    => $departments,
            'date'           => $viewDate,
            'month'          => $month,
            'year'           => $year,
        ]);
    }

    /**
     * Get employee locations - ROLE BASED (Admin can see all, employee only themselves)
     */
    public function getEmployeeLocations(Request $request)
    {
        try {
            $user = Auth::user();

            $request->validate([
                'user_id' => 'required|exists:users,id',
                'date' => 'required|date'
            ]);

            $requestedUserId = $request->input('user_id');

            // Check permission: Employee can only request their own data
            if ($user->role === 'employee' && $requestedUserId != $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only view your own location data.'
                ], 403);
            }

            $date = $request->input('date');

            // Get attendance records for the specific date
            $attendances = Attendance::where('user_id', $requestedUserId)
                ->whereDate('date', $date)
                ->with(['user.employeeDetail.department', 'user.employeeDetail.designation'])
                ->orderBy('clock_in', 'asc')
                ->get();

            $attendanceData = $attendances->map(function ($record) {
                // Calculate total hours
                $totalHours = null;
                if ($record->clock_in && $record->clock_out) {
                    $clockIn = Carbon::parse($record->clock_in);
                    $clockOut = Carbon::parse($record->clock_out);
                    $totalHours = round($clockOut->diffInMinutes($clockIn) / 60, 2);
                }

                $hasClockInLocation = !empty($record->clock_in_latitude) && !empty($record->clock_in_longitude);
                $hasClockOutLocation = !empty($record->clock_out_latitude) && !empty($record->clock_out_longitude);
                $hasLocationChange = $hasClockInLocation && $hasClockOutLocation &&
                    ($record->clock_in_latitude != $record->clock_out_latitude ||
                     $record->clock_in_longitude != $record->clock_out_longitude);

                return [
                    'id' => $record->id,
                    'date' => $record->date,
                    'clock_in_time' => $record->clock_in ? Carbon::parse($record->clock_in)->format('h:i A') : null,
                    'clock_out_time' => $record->clock_out ? Carbon::parse($record->clock_out)->format('h:i A') : null,
                    'clock_in_latitude' => $hasClockInLocation ? (float)$record->clock_in_latitude : null,
                    'clock_in_longitude' => $hasClockInLocation ? (float)$record->clock_in_longitude : null,
                    'clock_in_address' => $hasClockInLocation ? ($record->clock_in_address ?? 'Location recorded') : null,
                    'clock_out_latitude' => $hasClockOutLocation ? (float)$record->clock_out_latitude : null,
                    'clock_out_longitude' => $hasClockOutLocation ? (float)$record->clock_out_longitude : null,
                    'clock_out_address' => $hasClockOutLocation ? ($record->clock_out_address ?? 'Location recorded') : null,
                    'late' => $record->late === 'yes',
                    'status' => $record->status,
                    'work_from_type' => $record->work_from_type ?? 'office',
                    'working_from' => $record->working_from,
                    'total_hours' => $totalHours,
                    'has_location_change' => $hasLocationChange,
                    'has_location_data' => $hasClockInLocation || $hasClockOutLocation,
                ];
            });

            return response()->json([
                'success' => true,
                'attendance' => $attendanceData,
                'date' => Carbon::parse($date)->format('F d, Y'),
            ]);

        } catch (\Exception $e) {
            \Log::error('Error in getEmployeeLocations: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching employee locations'
            ], 500);
        }
    }

    /**
     * Get employee timeline - ROLE BASED
     */
    public function getEmployeeTimeline(Request $request)
    {
        try {
            $user = Auth::user();
            $requestedUserId = $request->user_id;
            $date = $request->date;

            if (!$requestedUserId || !$date) {
                return response()->json([
                    'success' => false,
                    'message' => 'User ID and Date are required'
                ], 400);
            }

            // Check permission: Employee can only request their own timeline
            if ($user->role === 'employee' && $requestedUserId != $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only view your own timeline.'
                ], 403);
            }

            $userRecord = User::with(['employeeDetail.department', 'employeeDetail.designation'])->find($requestedUserId);

            if (!$userRecord) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            // Get attendance records
            $attendances = Attendance::where('user_id', $requestedUserId)
                ->whereDate('date', $date)
                ->orderBy('clock_in', 'asc')
                ->get();

            $activities = [];
            $totalHours = 0;

            foreach ($attendances as $index => $attendance) {
                $clockIn = $attendance->clock_in ? Carbon::parse($attendance->clock_in)->format('h:i A') : 'N/A';
                $clockOut = $attendance->clock_out ? Carbon::parse($attendance->clock_out)->format('h:i A') : 'N/A';

                // Calculate duration
                $duration = 0;
                if ($attendance->clock_in && $attendance->clock_out) {
                    $clockInTime = Carbon::parse($attendance->clock_in);
                    $clockOutTime = Carbon::parse($attendance->clock_out);
                    $duration = $clockOutTime->diffInHours($clockInTime);
                    $totalHours += $duration;
                }

                $activities[] = [
                    'index' => $index + 1,
                    'clock_in' => $clockIn,
                    'clock_out' => $clockOut,
                    'duration' => $duration > 0 ? number_format($duration, 2) . ' hours' : 'Not completed',
                    'work_mode' => $attendance->work_from_type ?? 'office',
                    'status' => $attendance->status,
                    'late' => $attendance->late === 'yes',
                    'clock_in_location' => $attendance->clock_in_address ?? 'No location data',
                    'clock_out_location' => $attendance->clock_out_address ?? 'No location data',
                ];
            }

            // Create simple HTML response
            $html = '<div class="timeline-container">';
            $html .= '<div class="summary-card mb-4">';
            $html .= '<div class="row">';
            $html .= '<div class="col-md-8">';
            $html .= '<h5 class="mb-3 fw-bold" style="color: #2c3e50;">';
            $html .= '<i class="fas fa-user me-2"></i>' . $userRecord->name . ' - Attendance Timeline';
            $html .= '</h5>';
            $html .= '<div class="d-flex flex-wrap gap-3">';
            $html .= '<div><span class="text-muted">Date:</span> <strong class="ms-2">' . Carbon::parse($date)->format('F d, Y') . '</strong></div>';
            $html .= '<div><span class="text-muted">Designation:</span> <strong class="ms-2">' . ($userRecord->employeeDetail?->designation?->name ?? 'N/A') . '</strong></div>';
            $html .= '<div><span class="text-muted">Department:</span> <strong class="ms-2">' . ($userRecord->employeeDetail?->department?->name ?? 'N/A') . '</strong></div>';
            $html .= '</div>';
            $html .= '</div>';
            $html .= '<div class="col-md-4 text-end">';
            $html .= '<div class="bg-light p-3 rounded">';
            $html .= '<h6 class="mb-2">Total Hours Worked</h6>';
            $html .= '<h4 class="text-primary mb-0">' . number_format($totalHours, 2) . ' hours</h4>';
            $html .= '</div>';
            $html .= '</div>';
            $html .= '</div>';
            $html .= '</div>';

            if (count($activities) > 0) {
                $html .= '<div class="timeline">';
                foreach ($activities as $activity) {
                    $html .= '<div class="timeline-item">';
                    $html .= '<div class="timeline-marker">';
                    $html .= '<div class="marker-icon ' . ($activity['late'] ? 'late' : 'present') . '"></div>';
                    $html .= '</div>';
                    $html .= '<div class="timeline-content">';
                    $html .= '<div class="timeline-header">';
                    $html .= '<div>';
                    $html .= '<h6 class="mb-1"><i class="fas fa-clock me-2"></i>Session ' . $activity['index'] . '</h6>';
                    $html .= '<div class="small text-muted">';
                    $html .= '<span class="me-3"><i class="fas fa-sign-in-alt me-1"></i>' . $activity['clock_in'] . '</span>';
                    $html .= '<span><i class="fas fa-sign-out-alt me-1"></i>' . $activity['clock_out'] . '</span>';
                    $html .= '</div>';
                    $html .= '</div>';
                    $html .= '<div>';
                    $html .= '<span class="badge bg-' . ($activity['late'] ? 'warning' : 'success') . '">';
                    $html .= $activity['late'] ? 'Late' : 'On Time';
                    $html .= '</span>';
                    $html .= '</div>';
                    $html .= '</div>';
                    $html .= '<div class="mt-3">';
                    $html .= '<div class="location-info">';
                    $html .= '<strong><i class="fas fa-map-marker-alt me-2"></i>Locations:</strong>';
                    $html .= '<div class="mt-2">';
                    $html .= '<div class="mb-1"><i class="fas fa-sign-in-alt text-success me-2"></i>' . $activity['clock_in_location'] . '</div>';
                    $html .= '<div><i class="fas fa-sign-out-alt text-primary me-2"></i>' . $activity['clock_out_location'] . '</div>';
                    $html .= '</div>';
                    $html .= '</div>';
                    $html .= '</div>';
                    $html .= '<div class="mt-2 text-end">';
                    $html .= '<small class="text-muted">Duration: ' . $activity['duration'] . '</small>';
                    $html .= '</div>';
                    $html .= '</div>';
                    $html .= '</div>';
                }
                $html .= '</div>';
            } else {
                $html .= '<div class="text-center py-5">';
                $html .= '<i class="fas fa-history fa-3x text-muted mb-3"></i>';
                $html .= '<p class="text-muted">No attendance records found for this date</p>';
                $html .= '</div>';
            }

            $html .= '</div>';

            return response()->json([
                'success' => true,
                'html' => $html
            ]);

        } catch (\Exception $e) {
            \Log::error('Error in getEmployeeTimeline: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching timeline details'
            ], 500);
        }
    }

    /**
     * Show Attendance Details - ROLE BASED
     */
    public function showAttendanceDetails(Request $request)
    {
        try {
            $user = Auth::user();

            Log::info('showAttendanceDetails() called', [
                'attendance_id' => $request->attendance_id,
                'user_id'       => $request->user_id,
                'date'          => $request->date,
            ]);

            $attendanceId = $request->attendance_id;
            $requestedUserId = $request->user_id;
            $date = $request->date;

            // Check permission: Employee can only view their own details
            if ($user->role === 'employee' && $requestedUserId != $user->id) {
                return response('<div class="alert alert-danger">You can only view your own attendance details.</div>', 403);
            }

            $attendance = Attendance::with('user', 'user.employeeDetail')->find($attendanceId);

            if (!$attendance) {
                Log::warning('Attendance record not found', ['attendance_id' => $attendanceId]);
                return response('<div class="alert alert-danger">Attendance record not found.</div>', 404);
            }

            // Double-check permission
            if ($user->role === 'employee' && $attendance->user_id != $user->id) {
                return response('<div class="alert alert-danger">You can only view your own attendance details.</div>', 403);
            }

            // Rest of your existing showAttendanceDetails method...
            // ... [keep your existing showAttendanceDetails method code]

            return view('admin.attendance.attendance_details', [
                'attendance' => $attendance,
                'attendanceActivity' => $normalizedActivities,
                'totalTimeFormatted' => $totalTimeFormatted,
                'firstClockIn' => $firstClockIn,
                'lastClockOut' => $lastClockOut,
                'notClockedOut' => $notClockedOut,
                'startTime' => $startTime,
                'endTime' => $endTime,
                'attendanceDate' => $attendanceDate,
                'attendanceSettings' => $attendanceSettings,
                'totalTimeSeconds' => $totalTimeSeconds,
                'companyTimezone' => $companyTimezone,
            ]);

        } catch (\Throwable $e) {
            Log::error('Error in showAttendanceDetails()', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response('<div class="alert alert-danger">Something went wrong: ' . e($e->getMessage()) . '</div>', 500);
        }
    }

    /**
     * Edit Attendance - ADMIN ONLY
     */
    public function edit(Request $request)
    {
        $user = Auth::user();

        // Only admin can edit attendance
        if ($user->role !== 'admin') {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have permission to edit attendance.'
            ], 403);
        }

        $attendanceId = $request->attendance_id;
        $userId       = $request->user_id;
        $date         = $request->date;

        if (!$userId || !$date) {
            return response()->json([
                'status' => 'error',
                'message' => 'Missing required parameters.'
            ]);
        }

        $attendance = Attendance::find($attendanceId);

        if ($attendanceId && !$attendance) {
            return response()->json([
                'status' => 'error',
                'message' => 'Attendance record not found.'
            ]);
        }

        $employee    = User::find($userId);
        $departments = Department::all();
        $location    = CompanyAddress::all();

        return view('admin.attendance.edit_form', compact(
            'attendance',
            'employee',
            'departments',
            'location',
            'userId',
            'date'
        ));
    }

    /**
     * Update Attendance - ADMIN ONLY
     */
    public function update(Request $request, Attendance $attendance)
    {
        $user = Auth::user();

        // Only admin can update attendance
        if ($user->role !== 'admin') {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have permission to update attendance.'
            ], 403);
        }

        $request->validate([
            'clock_in'  => 'nullable|date_format:H:i',
            'clock_out' => 'nullable|date_format:H:i|after:clock_in',
            'status'    => 'required|string'
        ]);

        $attendance->update([
            // store as time-only (H:i:s) because DB has TIME columns
            'clock_in' => $request->clock_in ? Carbon::createFromFormat('H:i', $request->clock_in)->format('H:i:s') : null,
            'clock_out'=> $request->clock_out ? Carbon::createFromFormat('H:i', $request->clock_out)->format('H:i:s') : null,
            'status'   => $request->status,
        ]);

        return response()->json(['success' => true, 'message' => 'Attendance updated successfully.']);
    }

    /**
     * Employee Index (for employee-only view) - EMPLOYEE ONLY
     */
    public function employeeIndex(Request $request)
    {
        $user = Auth::user();

        // Only employees can access this view
        if ($user->role !== 'employee') {
            return redirect()->route('attendance.index')
                ->with('error', 'You do not have permission to access this page.');
        }

        $month = $request->get('month', date('m'));
        $year = $request->get('year', date('Y'));

        // Get days in selected month
        $daysInMonth = Carbon::createFromDate($year, $month)->daysInMonth;

        // Get attendance records for logged in employee
        $attendanceRecords = Attendance::where('user_id', $user->id)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->orderBy('date', 'desc')
            ->get();

        // Calculate summary
        $summary = [
            'present' => $attendanceRecords->where('status', 'present')->count(),
            'absent' => $attendanceRecords->where('status', 'absent')->count(),
            'late' => $attendanceRecords->where('status', 'late')->count(),
            'leave' => $attendanceRecords->where('status', 'leave')->count(),
        ];

        return view('attendance.employee.index', compact(
            'user',
            'attendanceRecords',
            'daysInMonth',
            'month',
            'year',
            'summary'
        ));
    }

    /**
     * Clock In - AVAILABLE FOR BOTH ADMIN AND EMPLOYEE
     */
    public function clockIn(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            if ($request->expectsJson()) {
                return response()->json(['status' => false, 'message' => 'Unauthenticated'], 401);
            }
            return redirect()->route('login');
        }

        try {
            $response = DB::transaction(function () use ($user, $request) {
                $today = now()->toDateString();

                // find or create today's attendance row
                $attendance = Attendance::firstOrCreate(
                    ['user_id' => $user->id, 'date' => $today],
                    [
                        'status' => 'present',
                        'working_from' => 'office',
                        'work_from_type' => 'other'
                    ]
                );

                // set clock_in if not already set
                if (empty($attendance->clock_in)) {
                    $attendance->clock_in = now()->format('H:i:s');
                    $attendance->status = 'present';
                    $attendance->save();
                } else {
                    // leave existing clock_in intact; you can uncomment to overwrite
                    // $attendance->clock_in = now()->format('H:i:s'); $attendance->save();
                }

                // prepare a Carbon instance used by your notification formatting logic
                $attendance->clocked_at = $this->buildDateTimeFromDateAndTime($attendance->date, $attendance->clock_in) ?? Carbon::now();

                // debug log so we can trace whether notify() was attempted
                Log::info('ClockIn notification called for user '.$user->id.' attendance_id:'.($attendance->id ?? 'n/a'));

                // notify the user (this inserts into notifications table via toDatabase())
                try {
                    $user->notify(new ClockInNotification($attendance));
                } catch (\Throwable $e) {
                    // log error, but don't break the transaction for a notification failure
                    Log::error('ClockInNotification failed (api clockIn)', [
                        'user' => $user->id,
                        'attendance_id' => $attendance->id ?? null,
                        'error' => $e->getMessage()
                    ]);
                }

                // fetch the latest notification just created for immediate frontend use
                $latestNotification = $user->notifications()->latest('created_at')->first();

                return [
                    'attendance' => $attendance,
                    'notification' => $latestNotification ? $latestNotification->toArray() : null
                ];
            });

            // if frontend expects JSON (AJAX or API)
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => true,
                    'message' => 'Clock-in recorded',
                    'attendance' => $response['attendance'],
                    'notification' => $response['notification']
                ]);
            }

            // for normal web form usage, redirect back with a success message
            return redirect()->route('attendance.index')->with('success', 'Clock-in recorded successfully.');

        } catch (\Throwable $e) {
            Log::error('Error in clockIn()', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);

            if ($request->expectsJson()) {
                return response()->json(['status' => false, 'message' => 'Failed to record clock-in', 'error' => $e->getMessage()], 500);
            }

            return redirect()->back()->with('error', 'Failed to record clock-in: ' . $e->getMessage());
        }
    }
}
