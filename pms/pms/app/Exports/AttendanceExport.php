<?php

namespace App\Exports;

use App\Models\Attendance;
use App\Models\User;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Holiday;
use App\Models\Leave;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;

class AttendanceExport
{
    /**
     * EXPORT EXCEL
     */
    public function exportExcel(Request $request)
    {
        $filters = $this->normalizeFilters($request);
        $users = $this->getUsersForExport($filters);
        $matrix = $this->buildMatrix($users, $filters['month'], $filters['year'], $filters);

        $excelData = $matrix['rows']->toArray();

        $export = new class($excelData) implements FromArray, ShouldAutoSize {
            private $data;
            public function __construct(array $data) { $this->data = $data; }
            public function array(): array { return $this->data; }
        };

        $filename = sprintf('attendance-%04d-%02d-%s.xlsx',
            $filters['year'],
            $filters['month'],
            Str::slug($filters['title_suffix'] ?? 'full-report')
        );

        return Excel::download($export, $filename);
    }

    /**
     * EXPORT PDF
     */
    public function exportPdf(Request $request)
    {
        $filters = $this->normalizeFilters($request);
        $users = $this->getUsersForExport($filters);
        $matrix = $this->buildMatrix($users, $filters['month'], $filters['year'], $filters);

        $data = [
            'title'    => "Attendance Report — " . Carbon::create($filters['year'], $filters['month'])->format('F Y') .
                         ($filters['title_suffix'] ? " — {$filters['title_suffix']}" : ' - Full Report'),
            'headings' => $matrix['headings'],
            'rows'     => $matrix['rows'],
            'filters'  => $filters,
            'summary'  => $matrix['summary'] ?? [],
            'month'    => $filters['month'],
            'year'     => $filters['year'],
            'daysInMonth' => Carbon::create($filters['year'], $filters['month'])->daysInMonth,
            'totalEmployees' => $users->count(),
        ];

        $pdf = Pdf::loadView('exports.attendance_matrix', $data)
                 ->setPaper('a4', 'landscape');

        $filename = sprintf('attendance-%04d-%02d-%s.pdf',
            $filters['year'],
            $filters['month'],
            Str::slug($filters['title_suffix'] ?? 'full-report')
        );

        return $pdf->download($filename);
    }

    /**
     * NORMALIZE FILTERS (month, year, department, employee, designation)
     */
    protected function normalizeFilters(Request $request): array
    {
        $now = Carbon::now();

        $month = $request->month ? (int) $request->month : $now->month;
        $year  = $request->year  ? (int) $request->year  : $now->year;

        // normalize employee: supports single ID, array, or CSV
        $employee = $request->employee ?? $request->user_id;

        if (is_string($employee) && str_contains($employee, ',')) {
            $employee = array_map('intval', explode(',', $employee));
        } elseif (is_array($employee)) {
            $employee = array_map('intval', $employee);
        } elseif ($employee) {
            $employee = intval($employee);
        }

        // Build title suffix for filename
        $titleSuffix = '';
        if ($request->department && $request->department !== 'all') {
            $dept = Department::find($request->department);
            $titleSuffix .= $dept ? $dept->dpt_name : 'Dept-' . $request->department;
        }
        if ($request->designation && $request->designation !== 'all') {
            $desig = Designation::find($request->designation);
            $titleSuffix .= $desig ? ' ' . $desig->name : ' Desig-' . $request->designation;
        }
        if ($employee) {
            $titleSuffix .= ' Employees';
        }

        return [
            'month'        => $month,
            'year'         => $year,
            'department'   => $request->department,
            'employee'     => $employee ?: null,
            'designation'  => $request->designation,
            'title_suffix' => trim($titleSuffix) ?: 'Full-Report',
        ];
    }

    /**
     * GET USERS LIST FOR EXPORT WITH FILTERS - uses employeeDetail relationship for dept/desig
     */
    protected function getUsersForExport(array $filters)
    {
        $month = $filters['month'];
        $year = $filters['year'];
        $departmentId = $filters['department'];
        $employee = $filters['employee'];
        $designation = $filters['designation'];

        $query = User::where('role', 'employee')->with('employeeDetail.designation', 'employeeDetail.department');

        // If specific employee filter provided
        if ($employee) {
            $query->when(is_array($employee),
                fn($q) => $q->whereIn('id', $employee),
                fn($q) => $q->where('id', $employee)
            );
        }

        // Apply department filter using relation if provided and not 'all'
        if ($departmentId && $departmentId !== 'all') {
            $query->whereHas('employeeDetail', function ($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }

        // Apply designation filter using relation if provided and not 'all'
        if ($designation && $designation !== 'all') {
            $query->whereHas('employeeDetail', function ($q) use ($designation) {
                $q->where('designation_id', $designation);
            });
        }

        return $query->orderBy('name')->get();
    }

    /**
     * BUILD MATRIX - ENHANCED WITH HOLIDAYS AND LEAVES
     */
    protected function buildMatrix(Collection $users, int $month, int $year, array $filters = []): array
    {
        $days = Carbon::create($year, $month)->daysInMonth;
        $start = Carbon::create($year, $month)->startOfMonth();
        $end   = Carbon::create($year, $month)->endOfMonth();

        // Get holidays for the month
        $holidays = Holiday::whereBetween('date', [$start, $end])
            ->get()
            ->keyBy(function($holiday) {
                return Carbon::parse($holiday->date)->format('Y-m-d');
            });

        // Get approved leaves for the month, grouped by user
        $leaves = Leave::where('status', 'approved')
            ->whereBetween('date', [$start, $end])
            ->get()
            ->groupBy('user_id');

        $headings = array_merge(
            ['#', 'Employee', 'Designation', 'Department'],
            range(1, $days),
            ['Present', 'Absent', 'Leave', 'Holiday', 'Late', 'Half Days', 'Total Hours', 'Working Days'] // Added Half Days column
        );

        // Apply same filters to attendance query
        $attendanceQuery = Attendance::whereBetween('date', [$start, $end]);

        if ($filters['department'] && $filters['department'] !== 'all') {
            // attendance may have department_id; if not, we still restrict by user relation handled earlier
            $attendanceQuery->where('department_id', $filters['department']);
        }

        if ($filters['employee'] ?? null) {
            $attendanceQuery->when(is_array($filters['employee']),
                fn($q) => $q->whereIn('user_id', $filters['employee']),
                fn($q) => $q->where('user_id', $filters['employee'])
            );
        }

        $attendance = $attendanceQuery->get()->groupBy('user_id');

        $rows = collect();
        $i = 1;
        $summary = [
            'total_present' => 0,
            'total_absent' => 0,
            'total_leave' => 0,
            'total_holiday' => 0,
            'total_late' => 0,
            'total_half_days' => 0,
            'total_hours' => 0,
            'total_working_days' => 0,
        ];

        foreach ($users as $user) {
            // employeeDetail safe access
            $designationName = $user->employeeDetail->designation->name ?? ($user->designation ?? null) ?? 'N/A';
            $departmentName = $user->employeeDetail->department->dpt_name ?? ($user->department->dpt_name ?? null) ?? 'N/A';

            $row = [
                $i,
                $user->name,
                $designationName,
                $departmentName
            ];

            $userStats = [
                'present' => 0,
                'absent' => 0,
                'leave' => 0,
                'holiday' => 0,
                'late' => 0,
                'half_days' => 0,
                'seconds' => 0,
                'working_days' => 0,
            ];

            $userAttendance = $attendance->get($user->id, collect());
            $userLeaves = $leaves->get($user->id, collect());

            for ($d = 1; $d <= $days; $d++) {
                $date = Carbon::create($year, $month, $d);
                $dateStr = $date->toDateString();

                // Check for holiday first
                if ($holidays->has($dateStr)) {
                    $status = 'H'; // Holiday
                    $userStats['holiday']++;
                    $summary['total_holiday']++;
                }
                // Check for leave
                elseif ($userLeaves->contains('date', $dateStr)) {
                    $status = 'L'; // Leave
                    $userStats['leave']++;
                    $summary['total_leave']++;
                }
                // Check for attendance (may be multiple records per day)
                else {
                    $attForDay = $userAttendance->filter(function ($a) use ($dateStr) {
                        // Ensure we compare in Y-m-d
                        return Carbon::parse($a->date)->format('Y-m-d') === $dateStr;
                    });

                    if ($attForDay->isNotEmpty()) {
                        // Determine aggregate status precedence:
                        // if any 'present' or 'late' or 'half_day' exist, treat accordingly.
                        $statuses = $attForDay->pluck('status')->map(fn($s) => strtolower((string)$s))->unique()->values()->toArray();

                        // default: assume present if any present/late/half_day found
                        if (in_array('present', $statuses)) {
                            $effectiveStatus = 'present';
                        } elseif (in_array('late', $statuses)) {
                            $effectiveStatus = 'late';
                        } elseif (in_array('half_day', $statuses) || in_array('hd', $statuses)) {
                            $effectiveStatus = 'half_day';
                        } else {
                            // fallback to first status
                            $effectiveStatus = $statuses[0] ?? 'present';
                        }

                        // Sum seconds for all attendance rows that day
                        $daySeconds = 0;
                        foreach ($attForDay as $att) {
                            $daySeconds += $this->calculateTotalSeconds($att);
                        }

                        if ($effectiveStatus === 'present') {
                            $status = 'P';
                            $userStats['present']++;
                            $summary['total_present']++;
                            $userStats['working_days']++;
                            $userStats['seconds'] += $daySeconds;
                        } elseif ($effectiveStatus === 'late') {
                            $status = 'Late';
                            $userStats['late']++;
                            $summary['total_late']++;
                            $userStats['working_days']++;
                            $userStats['seconds'] += $daySeconds;
                        } elseif ($effectiveStatus === 'half_day') {
                            $status = 'HD';
                            $userStats['present']++; // count as presence
                            $userStats['half_days']++;
                            $summary['total_present']++;
                            $summary['total_half_days']++;
                            $userStats['working_days'] += 0.5;
                            // prefer recorded seconds if present, else default 4 hours
                            $userStats['seconds'] += $daySeconds ?: (4 * 3600);
                        } else {
                            // treat anything else as absent for counting purposes
                            $status = 'A';
                            $userStats['absent']++;
                            $summary['total_absent']++;
                        }
                    } else {
                        // no attendance rows for the day => Absent
                        $status = 'A';
                        $userStats['absent']++;
                        $summary['total_absent']++;
                    }
                }

                $row[] = $status;
            }

            // Add summary columns - UPDATED ORDER
            $row[] = $userStats['present']; // Present days
            $row[] = $userStats['absent'];  // Absent days
            $row[] = $userStats['leave'];   // Leave days
            $row[] = $userStats['holiday']; // Holiday days
            $row[] = $userStats['late'];    // Late days
            $row[] = $userStats['half_days']; // Half days
            $row[] = $this->secondsToHms($userStats['seconds']); // Total Hours
            $row[] = $userStats['working_days']; // Working Days

            $summary['total_hours'] += $userStats['seconds'] / 3600;
            $summary['total_working_days'] += $userStats['working_days'];

            $rows->push($row);
            $i++;
        }

        $rows->prepend($headings);

        return [
            'headings' => $headings,
            'rows' => $rows,
            'summary' => $summary,
            'daysInMonth' => $days,
            'totalEmployees' => $users->count(),
        ];
    }

    /**
     * Calculate total seconds from attendance record - robust version
     *
     * - Uses total_seconds if present
     * - Parses clock_in/clock_out safely (uses attendance->date when time-only provided)
     * - Handles overnight (clock_out earlier than clock_in) by adding a day
     * - Falls back to sensible defaults (8h for present/late, 0 otherwise)
     */
    protected function calculateTotalSeconds($attendance)
    {
        // If model appended total_seconds and it's > 0, prefer that
        if (isset($attendance->total_seconds) && (int)$attendance->total_seconds > 0) {
            return (int) $attendance->total_seconds;
        }

        $clockInRaw = $attendance->clock_in ?? null;
        $clockOutRaw = $attendance->clock_out ?? null;
        $attDate = isset($attendance->date) ? Carbon::parse($attendance->date)->format('Y-m-d') : null;

        // Try to parse both values into Carbon instances
        $inDt = null;
        $outDt = null;

        try {
            if ($clockInRaw) {
                $inStr = (string)$clockInRaw;
                // if looks like time-only (H:i or H:i:s) or lacks date, combine with attendance date
                if (preg_match('/^\d{1,2}:\d{2}(:\d{2})?(\s?[AP]M)?$/i', trim($inStr)) && $attDate) {
                    // normalize to H:i:s if needed
                    if (preg_match('/^\d{1,2}:\d{2}$/', $inStr)) $inStr .= ':00';
                    $inDt = Carbon::parse($attDate . ' ' . $inStr);
                } else {
                    $inDt = Carbon::parse($inStr);
                }
            }
        } catch (\Throwable $e) {
            $inDt = null;
        }

        try {
            if ($clockOutRaw) {
                $outStr = (string)$clockOutRaw;
                if (preg_match('/^\d{1,2}:\d{2}(:\d{2})?(\s?[AP]M)?$/i', trim($outStr)) && $attDate) {
                    if (preg_match('/^\d{1,2}:\d{2}$/', $outStr)) $outStr .= ':00';
                    $outDt = Carbon::parse($attDate . ' ' . $outStr);
                } else {
                    $outDt = Carbon::parse($outStr);
                }
            }
        } catch (\Throwable $e) {
            $outDt = null;
        }

        if ($inDt && $outDt) {
            // handle overnight: if out is before in, assume next day
            if ($outDt->lt($inDt)) {
                $outDt = $outDt->copy()->addDay();
            }
            $secs = max(0, $outDt->getTimestamp() - $inDt->getTimestamp());
            return (int) $secs;
        }

        // If present/late but no timestamps, fallback to 8 hours
        $status = strtolower((string)($attendance->status ?? ''));
        if (in_array($status, ['present','late'])) {
            return 8 * 3600;
        }

        // If half day status, fallback to 4 hours
        if (in_array($status, ['half_day','hd'])) {
            return 4 * 3600;
        }

        // no useful data -> 0
        return 0;
    }

    protected function secondsToHms($seconds)
    {
        if ($seconds <= 0) return '00:00:00';

        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $seconds = $seconds % 60;

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }
}
