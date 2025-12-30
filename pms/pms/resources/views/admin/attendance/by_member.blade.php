@extends('admin.layout.app')

@section('content')
<div class="container-fluid">
    <br>
    {{-- ✅ Filter Form --}}
     <form method="GET" action="{{ route('attendance.byMember') }}" class="row g-3 mb-4">
    <div class="col-md-3">
        <label for="month" class="form-label">Month</label>
        <select name="month" id="month" class="form-select" required>
            @for($m = 1; $m <= 12; $m++)
                <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                    {{ \Carbon\Carbon::createFromDate(null, $m)->format('F') }}
                </option>
            @endfor
        </select>
    </div>

    <div class="col-md-3">
        <label for="year" class="form-label">Year</label>
        <select name="year" id="year" class="form-select" required>
            @for($y = now()->year - 3; $y <= now()->year + 1; $y++)
                <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
            @endfor
        </select>
    </div>

    @if(auth()->user()->role === 'admin')
        <div class="col-md-4">
            <label for="user_id" class="form-label">Employee</label>
            <select name="user_id" id="user_id" class="form-select">
                <option value="">All Employees</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ $userId == $user->id ? 'selected' : '' }}>
                        {{ $user->name }}
                    </option>
                @endforeach
            </select>
        </div>
    @endif

    <div class="col-md-2 d-flex align-items-end gap-2">
        <button type="submit" class="btn btn-success w-100">Filter</button>
        <a href="{{ route('attendance.byMember') }}" class="btn btn-secondary w-100">Reset</a>
    </div>
</form>

  <div class="d-flex justify-content-between align-items-center mb-3">
    {{-- ✅ Breadcrumbs (Left) --}}
    <div>
        <h4 class="fw-bold mb-0">Attendance by Member</h4>
        <nav style="--bs-breadcrumb-divider: '•';" aria-label="breadcrumb">
            <ol class="breadcrumb small text-muted mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('attendance.index') }}">Attendances</a></li>
                <li class="breadcrumb-item active" aria-current="page">Attendance by Member</li>
            </ol>
        </nav>
    </div>

    {{-- ✅ Tabs (Right) --}}
    <div class="btn-group mt-2" role="group">
        <a href="{{ route('attendance.index') }}" class="btn btn-secondary" title="Summary">
            <i class="bi bi-list-ul"></i>
        </a>
        <a href="{{ route('attendance.byMember') }}" class="btn btn-primary" title="Attendance by Member">
            <i class="bi bi-person"></i>
        </a>
        <a href="{{ route('attendance.byHour') }}" class="btn btn-secondary f-14" title="Attendance by Hour">
            <i class="bi bi-clock"></i>
        </a>
        {{-- ✅ New Button for Attendance by Location --}}
        <a href="{{ route('attendance.today.map', ['year' => $year, 'month' => $month]) }}" class="btn btn-secondary f-14" title="Attendance by Location">
            <i class="bi bi-geo-alt"></i>
        </a>
    </div>
</div>


 
    


    {{-- ✅ Attendance Table --}}
    <div class="table-responsive">
        <table class="table table-bordered text-center small">
            <thead class="table-light">
                <tr>
                    <th>User</th>
                    @for ($d = 1; $d <= $daysInMonth; $d++)
                        <th>{{ $d }}</th>
                    @endfor
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                    <tr>
                        <td class="text-start">{{ $user->name }}</td>
                        @for ($d = 1; $d <= $daysInMonth; $d++)
                            @php
                                $dateKey = \Carbon\Carbon::createFromDate($year, $month, $d)->format('Y-m-d');
                                $att = $attendanceMap[$user->id][$dateKey] ?? null;
                            @endphp
                            <td>
                                @if($att)
                                    <span class="badge bg-success">✔</span>
                                @else
                                    <span class="text-muted">–</span>
                                @endif
                            </td>
                        @endfor
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</div>
@endsection
