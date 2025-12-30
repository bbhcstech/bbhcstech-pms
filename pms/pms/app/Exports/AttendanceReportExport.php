<?php

namespace App\Exports;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Carbon\Carbon;

class AttendanceReportExport implements FromView
{
    protected $user;
    protected $month;
    protected $year;

    public function __construct(User $user, $month, $year)
    {
        $this->user = $user;
        $this->month = $month;
        $this->year = $year;
    }

    public function view(): View
    {
        $daysInMonth = Carbon::createFromDate($this->year, $this->month)->daysInMonth;

        $attendances = Attendance::where('user_id', $this->user->id)
            ->whereMonth('date', $this->month)
            ->whereYear('date', $this->year)
            ->get();

        $attendanceMap = [];

        foreach ($attendances as $attendance) {
            $dateKey = Carbon::parse($attendance->date)->format('Y-m-d');
            $attendanceMap[$dateKey] = $attendance;
        }

        return view('exports.attendance', [
            'user' => $this->user,
            'daysInMonth' => $daysInMonth,
            'month' => $this->month,
            'year' => $this->year,
            'attendanceMap' => $attendanceMap,
        ]);
    }
}
