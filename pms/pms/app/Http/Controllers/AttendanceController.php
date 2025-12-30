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

use App\Exports\AttendanceReportExport;
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
     *
     * @param \Illuminate\Support\Collection|array $users
     * @param array $attendanceMap  Map [$userId][$dateYmd] => Attendance model OR collection/array of Attendance
     * @param \Carbon\Carbon $startDate
     * @param \Carbon\Carbon $endDate
     * @return array keyed by user id => ['seconds' => int, 'hhmm' => string, 'hhmmss' => string, 'decimal' => float]
     */
    private function calculatePeriodTotals($users, array $attendanceMap, Carbon $startDate, Carbon $endDate): array
    {
        $result = [];

        // normalize users to id array
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

    public function exportMultiPdf(Request $request)
    {
        $month = (int) ($request->month ?? now()->month);
        $year  = (int) ($request->year ?? now()->year);

        $userQuery = User::query();

        // Apply all filters
        if ($request->has('user_ids') && is_array($request->user_ids) && count($request->user_ids) > 0) {
            $userQuery->whereIn('id', $request->user_ids);
        } elseif (auth()->user()->role === 'employee') {
            $userQuery->where('id', auth()->id());
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

        $pdf = Pdf::loadView('admin.attendance.report_table_pdf_chunked', $data)
            ->setPaper('a4', 'landscape')
            ->setWarnings(false);

        $fileName = sprintf('Attendance_Multi_%02d_%04d%s.pdf', 
            $month, 
            $year,
            $filterInfo ? '_' . Str::slug($filterInfo) : ''
        );

        return $pdf->download($fileName);
    }

    public function index(Request $request)
    {
        $user = Auth::user();

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

        // Step 2: load users and attendances
        if ($user->role === 'admin') {
            $users = User::where('role', 'employee')->get();

            $attendances = Attendance::whereMonth('date', $month)
                ->whereYear('date', $year)
                ->get();
        } elseif ($user->role === 'employee') {
            $users = collect([$user]);

            $attendances = Attendance::where('user_id', $user->id)
                ->whereMonth('date', $month)
                ->whereYear('date', $year)
                ->get();
        } else {
            abort(403, 'Unauthorized access');
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

        $departments = Department::get();
        $designations = Designation::all();

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

    public function markAttendance(Request $request)
    {
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

    public function settings()
    {
        $setting = AttendanceSetting::firstOrCreate([], ['office_start_time' => '10:00', 'late_time' => '10:15']);
        return view('attendance.settings', compact('setting'));
    }

    public function updateSettings(Request $request)
    {
        $data = $request->validate([
            'office_start_time' => 'required|date_format:H:i',
            'late_time' => 'required|date_format:H:i',
        ]);

        AttendanceSetting::updateOrCreate([], $data);
        return back()->with('success', 'Settings updated');
    }

    public function filter(Request $request)
    {
        $month = (int) ($request->input('month', now()->month));
        $year  = (int) ($request->input('year', now()->year));
        $userId = $request->input('user_id');
        $department_id = $request->input('department_id');
        $designation_id = $request->input('designation_id');

        $authUser = Auth::user();

        if (! in_array($authUser->role, ['admin', 'employee'])) {
            abort(403, 'Unauthorized');
        }

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

        if ($authUser->role === 'employee') {
            $usersQuery->where('id', $authUser->id);
        } elseif ($userId) {
            $usersQuery->where('id', (int) $userId);
        }

        if ($department_id) {
            $usersQuery->whereHas('employeeDetail', function ($q) use ($department_id) {
                $q->where('department_id', $department_id);
            });
        }

        if ($designation_id) {
            $usersQuery->whereHas('employeeDetail', function ($q) use ($designation_id) {
                $q->where('designation_id', $designation_id);
            });
        }

        $users = $usersQuery->orderBy('name')->get();

        $daysInMonth = Carbon::createFromDate($year, $month, 1)->daysInMonth;

        // if no users, still return empty table
        if ($users->isEmpty()) {
            $attendanceMap = [];
            $designations = Designation::all();

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

        $designations = Designation::all();

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

    public function byHour(Request $request)
    {
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

        $authUser = Auth::user();

        // pick users list
        if ($authUser->role === 'admin') {
            $users = User::where('role', 'employee')->get();
        } else {
            $users = collect([$authUser]);
            $userId = $authUser->id;
        }

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

        // -----------------------
        // NEW: compute dayTotals: [user_id][Y-m-d] => seconds
        // -----------------------
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

        // compute periodTotals using your helper if available; else fallback to manual sum
        $startDate = Carbon::createFromDate($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();

        if (method_exists($this, 'calculatePeriodTotals')) {
            $periodTotals = $this->calculatePeriodTotals($users, $attendanceMap, $startDate, $endDate);
        } else {
            $periodTotals = [];
            foreach ($users as $u) {
                $secs = 0;
                $userMap = $attendanceMap[$u->id] ?? [];
                foreach ($userMap as $dKey => $col) {
                    foreach ($col as $rec) {
                        $secs += (int)($rec->total_seconds ?? 0);
                    }
                }
                $periodTotals[$u->id] = [
                    'seconds' => $secs,
                    'hhmm' => intval($secs/3600) . ':' . str_pad(intval(($secs%3600)/60),2,'0',STR_PAD_LEFT),
                    'decimal' => round($secs/3600, 2)
                ];
            }
        }

        // If periodTotals is empty (edge-case), compute from dayTotals
        if (empty($periodTotals) || !is_array($periodTotals)) {
            $periodTotals = [];
            foreach ($users as $u) {
                $uid = $u->id;
                $sum = 0;
                if (! empty($dayTotals[$uid])) {
                    $sum = array_sum($dayTotals[$uid]);
                }
                $periodTotals[$uid] = [
                    'seconds' => (int) $sum,
                    'hhmm'    => intval($sum/3600) . ':' . str_pad(intval(($sum%3600)/60),2,'0',STR_PAD_LEFT),
                    'hhmmss'  => (function($s){ $h = intdiv($s,3600); $m = intdiv($s%3600,60); $sec = $s%60; return sprintf('%02d:%02d:%02d', $h,$m,$sec); })($sum),
                    'decimal' => round($sum/3600, 2)
                ];
            }
        }

        Log::info('byHour filter', [
            'user_filter' => $userId,
            'month' => $month,
            'year' => $year,
            'returned' => $attendances->count(),
            'dayTotals_sample' => array_slice($dayTotals, 0, 5),
        ]);

        return view('admin.attendance.by-hour', compact('attendances', 'users', 'month', 'year', 'attendanceMap', 'daysInMonth', 'periodTotals', 'dayTotals'));
    }

    public function create()
    {
        $departments = Department::get();
        $users = \App\Models\User::where('role', 'employee')->get();
        $year = now()->format('Y');
        $month = now()->format('m');
        $location = CompanyAddress::all();

        return view('admin.attendance.create', compact('users','departments','year','month','location'));
    }

    public function store(Request $request)
    {
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

    public function attendanceReport(Request $request)
    {
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

    public function exportExcel(Request $request)
    {
        return \App\Exports\AttendanceExport::exportToExcel($request);
    }

    public function exportPdf(Request $request)
    {
        return \App\Exports\AttendanceExport::exportToPdf($request);
    }

    public function byMember(Request $request)
    {
        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);
        $userId = $request->input('user_id');

        $authUser = auth()->user();
        $attendanceMap = [];

        if ($authUser->role === 'admin') {
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
        } else {
            $users = collect([$authUser]);

            $attendances = Attendance::where('user_id', $authUser->id)
                ->whereMonth('date', $month)
                ->whereYear('date', $year)
                ->with('user')
                ->get();
        }

        // append computed accessors
        $attendances->each->append(['total_seconds','total_duration','clock_in_datetime','clock_out_datetime']);

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

    public function todayAttendanceByMap(Request $request)
    {
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

        $authUser = auth()->user();
        if ($authUser->role === 'employee') {
            $query->where('user_id', $authUser->id);
        }

        $attendances = $query->get();

        // append accessors (useful for any further processing)
        $attendances->each->append(['total_seconds','total_duration','clock_in_datetime','clock_out_datetime']);

        Log::info('todayAttendanceByMap loaded', [
            'count' => $attendances->count(),
            'ids' => $attendances->pluck('id')->toArray(),
            'user_ids' => $attendances->pluck('user_id')->unique()->values()->toArray(),
        ]);

        $debugData = $attendances->map(function ($att) {
            $user = $att->user;
            $empDetail = $user?->employeeDetail ?? null;

            return [
                'attendance_id' => $att->id,
                'user' => $user ? [
                    'id'     => $user->id,
                    'name'   => $user->name,
                    'avatar' => $user->profile_image_url ?? ($user->profile_image ?? null),
                ] : null,
                'designation' => $empDetail?->designation?->name ?? null,
                'department'  => $empDetail?->department ? [
                    'id'   => $empDetail->department->id,
                    'name' => $empDetail->department->dpt_name ?? $empDetail->department->name ?? null,
                ] : null,
                'latitude'  => $att->latitude,
                'longitude' => $att->longitude,
                'late' => in_array(strtolower((string)($att->late ?? '')), ['1', 'yes', 'true'], true),
            ];
        })->values();

        $mapPoints = $debugData->filter(function ($a) {
            return !empty($a['user']) && $a['latitude'] !== null && $a['longitude'] !== null
                && $a['latitude'] !== '' && $a['longitude'] !== '';
        })->values();

        $employees = collect();
        $departments = collect();

        if ($authUser->role === 'admin') {
            $employees = $mapPoints->pluck('user')->filter()->unique('id')->values();

            $departments = $mapPoints->pluck('department')->filter()->unique('id')->values();

            if ($employees->isEmpty()) {
                $employees = \App\Models\User::where('role', 'employee')
                    ->select('id', 'name')
                    ->get()
                    ->map(fn($u) => ['id' => $u->id, 'name' => $u->name]);
            }

            if ($departments->isEmpty()) {
                if (\Illuminate\Support\Facades\Schema::hasColumn('departments', 'dpt_name')) {
                    $departments = \App\Models\Department::select('id', 'dpt_name')->get()
                        ->map(fn($d) => ['id' => $d->id, 'name' => $d->dpt_name]);
                } elseif (\Illuminate\Support\Facades\Schema::hasColumn('departments', 'name')) {
                    $departments = \App\Models\Department::select('id', 'name')->get()
                        ->map(fn($d) => ['id' => $d->id, 'name' => $d->name]);
                } else {
                    $departments = \App\Models\Department::all()->map(function ($d) {
                        return [
                            'id' => $d->id,
                            'name' => $d->dpt_name ?? ($d->name ?? 'Unknown'),
                        ];
                    });
                }
            }
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

    public function showAttendanceDetails(Request $request)
    {
        try {
            Log::info('showAttendanceDetails() called', [
                'attendance_id' => $request->attendance_id,
                'user_id'       => $request->user_id,
                'date'          => $request->date,
            ]);

            $attendanceId = $request->attendance_id;
            $userId = $request->user_id;
            $date = $request->date;

            $attendance = Attendance::with('user', 'user.employeeDetail')->find($attendanceId);

            if (! $attendance) {
                Log::warning('Attendance record not found', ['attendance_id' => $attendanceId]);
                return response('<div class="alert alert-danger">Attendance record not found.</div>', 404);
            }

            $parseAttendanceDatetime = function ($value, $attendanceDate) {
                if (empty($value) && $value !== '0') {
                    return null;
                }
                if ($value instanceof Carbon) {
                    return $value->copy();
                }
                if ($value instanceof \DateTime) {
                    return Carbon::instance($value);
                }

                $val = trim((string) $value);
                $attendanceDate = (string) $attendanceDate;

                try {
                    if (preg_match('/^\d{4}-\d{2}-\d{2}[ T]\d{2}:\d{2}(:\d{2})?$/', $val)) {
                        return Carbon::parse($val);
                    }

                    if (preg_match('/^[0-2]?\d:[0-5]\d(:[0-5]\d)?$/', $val)) {
                        if (strlen($val) === 5) $val .= ':00';
                        return Carbon::createFromFormat('Y-m-d H:i:s', $attendanceDate . ' ' . $val);
                    }

                    return Carbon::parse($attendanceDate . ' ' . $val);
                } catch (\Throwable $e) {
                    try {
                        return Carbon::parse($val);
                    } catch (\Throwable $_) {
                        return null;
                    }
                }
            };

            $attendanceActivity = Attendance::where('user_id', $userId)
                ->whereDate('date', $date)
                ->orderBy('clock_in', 'asc')
                ->get();

            // append accessors for each activity row
            $attendanceActivity->each->append(['total_seconds','total_duration','clock_in_datetime','clock_out_datetime']);

            Log::info('Attendance activities retrieved', ['count' => $attendanceActivity->count()]);

            $totalTimeSeconds = 0;
            $firstClockIn = null;
            $lastClockOut = null;
            $notClockedOut = false;
            $startTime = null;
            $endTime = null;
            $attendanceDate = null;

            $normalizedActivities = $attendanceActivity->map(function ($activity, $idx) use (
                $parseAttendanceDatetime,
                &$totalTimeSeconds,
                &$firstClockIn,
                &$lastClockOut,
                &$notClockedOut,
                &$startTime,
                &$endTime,
                &$attendanceDate
            ) {
                $inDt = $parseAttendanceDatetime($activity->clock_in, $activity->date);
                $outDt = $parseAttendanceDatetime($activity->clock_out, $activity->date);

                if ($idx === 0) {
                    $firstClockIn = $inDt;
                    $attendanceDate = $activity->date ? Carbon::parse($activity->date) : null;
                    $startTime = $inDt;
                }

                if ($outDt) {
                    $lastClockOut = $outDt;
                }

                $outForDuration = null;
                if ($inDt && $outDt) {
                    if ($outDt->lt($inDt)) {
                        $outForDuration = $outDt->copy()->addDay();
                    } else {
                        $outForDuration = $outDt;
                    }
                }

                $durationSeconds = null;
                if ($inDt && $outForDuration) {
                    $durationSeconds = max(0, $outForDuration->getTimestamp() - $inDt->getTimestamp());
                    $totalTimeSeconds += $durationSeconds;
                    $endTime = $outForDuration;
                } elseif ($inDt && ! $outDt) {
                    $notClockedOut = true;
                    $now = Carbon::now();
                    $interval = max(0, $now->getTimestamp() - $inDt->getTimestamp());
                    $durationSeconds = $interval;
                    $totalTimeSeconds += $durationSeconds;
                    $endTime = $now;
                }

                return (object) [
                    'id' => $activity->id,
                    'raw' => $activity,
                    'in_dt' => $inDt,
                    'out_dt' => $outDt,
                    'out_for_duration' => $outForDuration,
                    'duration_seconds' => $durationSeconds,
                    'duration_human' => $durationSeconds !== null ? \Carbon\CarbonInterval::seconds($durationSeconds)->cascade()->forHumans() : null,
                    'location' => $activity->location ?? 'Office',
                    'date' => $activity->date,
                ];
            })->values();

            $hours = (int) floor($totalTimeSeconds / 3600);
            $minutes = (int) floor(($totalTimeSeconds % 3600) / 60);
            $seconds = (int) ($totalTimeSeconds % 60);
            $totalTimeFormatted = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);

            Log::info('Attendance summary calculated', [
                'total_time'      => $totalTimeFormatted,
                'first_clock_in'  => $firstClockIn ? $firstClockIn->toDateTimeString() : null,
                'last_clock_out'  => $lastClockOut ? $lastClockOut->toDateTimeString() : null,
                'not_clocked_out' => $notClockedOut,
                'activity_count'  => $normalizedActivities->count(),
            ]);

            $attendanceSettings = AttendanceSetting::first();
            $companyTimezone = config('app.timezone') ?? 'Asia/Kolkata';

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

    public function edit(Request $request)
    {
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

    public function update(Request $request, Attendance $attendance)
    {
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
 * API / action endpoint to record clock-in for the authenticated user.
 * - Creates today's attendance row if missing
 * - Sets clock_in only if not already set (non-destructive)
 * - Prepares clocked_at for the notification payload
 * - Calls $user->notify(new ClockInNotification($attendance))
 * - Returns JSON when requested, otherwise redirects back with flash
 */
public function clockIn(Request $request)
{
    $user = $request->user();
    if (! $user) {
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
