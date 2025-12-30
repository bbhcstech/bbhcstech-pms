<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Attendance Report</title>
</head>
<body>
    <h2>Attendance Report</h2>
    <p><strong>Employee:</strong> {{ $user->name }}</p>
    <p><strong>Month:</strong> {{ \Carbon\Carbon::createFromDate(null, $month, 1)->format('F') }}</p>
    <p><strong>Year:</strong> {{ $year }}</p>

    <table border="1" cellpadding="5" cellspacing="0">
        <thead>
            <tr>
                <th>Date</th>
                <th>Status</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
            @for ($day = 1; $day <= $daysInMonth; $day++)
                @php
                    $date = \Carbon\Carbon::createFromDate($year, $month, $day)->format('Y-m-d');
                    $attendance = $attendanceMap[$date] ?? null;
                @endphp
                <tr>
                    <td>{{ $date }}</td>
                    <td>{{ $attendance->status ?? 'Absent' }}</td>
                    <td>{{ $attendance->remarks ?? '-' }}</td>
                </tr>
            @endfor
        </tbody>
    </table>
</body>
</html>

