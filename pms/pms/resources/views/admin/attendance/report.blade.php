@extends('admin.layout.app')

@section('title', 'Attendance Report')

@section('content')
<main class="main">
    <div class="content-wrapper py-4 px-3" style="background-color: #f5f7fa; min-height: 100vh;">
        <div class="container-fluid">
            <h4 class="fw-bold mb-3">Attendance Report</h4>

            <form method="GET" class="row g-2 mb-4">
                <div class="col-md-3">
                    <select name="user_id" class="form-select">
                        <option value="">-- Select Employee --</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="month" class="form-select">
                        @foreach(range(1, 12) as $m)
                            <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::createFromDate(null, $m)->format('F') }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="year" class="form-select">
                        @foreach(range(date('Y') - 2, date('Y') + 1) as $y)
                            <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary">Filter</button>
                </div>
            </form>

            @if($selectedUser)
                <div class="card mb-4">
                    <div class="card-body">
                        <h6 class="fw-bold mb-3">Summary for {{ $selectedUser->name }}</h6>
                        <div class="row text-center">
                            <div class="col">✔️ Present: {{ $summary['present'] }}</div>
                            <div class="col">⚠️ Late: {{ $summary['late'] }}</div>
                            <div class="col">❌ Absent: {{ $summary['absent'] }}</div>
                            <div class="col">🛫 Leave: {{ $summary['leave'] }}</div>
                        </div>
                    </div>
                </div>

                @if($selectedUser)
                    <div class="mb-3">
                        <form method="GET" action="{{ route('attendance.export.excel') }}" class="d-inline">
                            <input type="hidden" name="user_id" value="{{ $selectedUser->id }}">
                            <input type="hidden" name="month" value="{{ $month }}">
                            <input type="hidden" name="year" value="{{ $year }}">
                            <button class="btn btn-success btn-sm">⬇️ Export Excel</button>
                        </form>

                        <form method="GET" action="{{ route('attendance.export.pdf') }}" class="d-inline ms-2">
                            <input type="hidden" name="user_id" value="{{ $selectedUser->id }}">
                            <input type="hidden" name="month" value="{{ $month }}">
                            <input type="hidden" name="year" value="{{ $year }}">
                            <button class="btn btn-danger btn-sm">⬇️ Export PDF</button>
                        </form>
                    </div>
                @endif


                <div class="table-responsive">
                    <table class="table table-bordered text-center">
                        <thead class="table-light">
                            <tr>
                                @for($i = 1; $i <= $daysInMonth; $i++)
                                    @php
                                        $date = \Carbon\Carbon::create($year, $month, $i);
                                    @endphp
                                    <th style="font-size: 12px;">
                                        {{ $i }}<br><small>{{ $date->format('D') }}</small>
                                    </th>
                                @endfor
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                @for($i = 1; $i <= $daysInMonth; $i++)
                                    @php
                                        $record = $attendances[$selectedUser->id][$i] ?? null;
                                        $symbol = match($record->status ?? null) {
                                            'present' => '✔️',
                                            'absent' => '❌',
                                            'holiday' => '⭐',
                                            'late' => '⚠️',
                                            'half_day' => '⏳',
                                            'leave' => '🛫',
                                            default => '-'
                                        };
                                    @endphp
                                    <td>{{ $symbol }}</td>
                                @endfor
                            </tr>
                        </tbody>
                    </table>
                </div>
            @else
                <div class="alert alert-warning">Please select an employee to view report.</div>
            @endif
        </div>
    </div>
</main>
@endsection
