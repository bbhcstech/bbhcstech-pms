@extends('admin.layout.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            {{-- ✅ Page Header with Breadcrumb --}}
            <div class="page-header mb-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="fw-bold mb-2" style="color: #2c3e50;">
                            <i class="fas fa-user me-2" style="color: #3498db;"></i>
                            Attendance by Member
                        </h4>
                        <nav style="--bs-breadcrumb-divider: '›';" aria-label="breadcrumb">
                            <ol class="breadcrumb small mb-0">
                                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-muted">Home</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('attendance.index') }}" class="text-muted">Attendances</a></li>
                                <li class="breadcrumb-item active" style="color: #3498db;" aria-current="page">By Member</li>
                            </ol>
                        </nav>
                    </div>

                    {{-- ✅ Navigation Tabs --}}
                    <div class="btn-group shadow-sm" role="group">
                        <a href="{{ route('attendance.index') }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-list-ul me-1"></i> Summary
                        </a>
                        <a href="{{ route('attendance.byMember') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-user me-1"></i> By Member
                        </a>
                        <a href="{{ route('attendance.byHour') }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-clock me-1"></i> By Hour
                        </a>
                        <a href="{{ route('attendance.today.map', ['year' => $year, 'month' => $month]) }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-map-marker-alt me-1"></i> Location
                        </a>
                    </div>
                </div>
            </div>

            {{-- ✅ Filter Card --}}
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="m-0 font-weight-bold" style="color: #2c3e50;">
                        <i class="fas fa-filter me-2"></i>Filter Attendance
                    </h6>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('attendance.byMember') }}" class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label for="month" class="form-label small fw-semibold text-muted">Month</label>
                            <select name="month" id="month" class="form-select form-select-sm shadow-sm" required>
                                @for($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::createFromDate(null, $m)->format('F') }}
                                    </option>
                                @endfor
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label for="year" class="form-label small fw-semibold text-muted">Year</label>
                            <select name="year" id="year" class="form-select form-select-sm shadow-sm" required>
                                @for($y = now()->year - 3; $y <= now()->year + 1; $y++)
                                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>

                        @if(auth()->user()->role === 'admin')
                            <div class="col-md-4">
                                <label for="user_id" class="form-label small fw-semibold text-muted">Employee</label>
                                <select name="user_id" id="user_id" class="form-select form-select-sm shadow-sm">
                                    <option value="">All Employees</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ $userId == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div class="col-md-2">
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-sm shadow-sm">
                                    <i class="fas fa-search me-1"></i> Apply
                                </button>
                                <a href="{{ route('attendance.byMember') }}" class="btn btn-outline-secondary btn-sm shadow-sm">
                                    <i class="fas fa-redo me-1"></i> Reset
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- ✅ Table Card --}}
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h6 class="m-0 font-weight-bold" style="color: #2c3e50;">
                        <i class="fas fa-users me-2"></i>Monthly Attendance Overview
                        <span class="badge bg-primary ms-2">{{ $users->count() }} Employees</span>
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="py-3 px-4 text-start" style="background-color: #f8f9fa; min-width: 200px;">
                                        <i class="fas fa-user-circle me-2"></i>Employee Name
                                    </th>
                                    @for ($d = 1; $d <= $daysInMonth; $d++)
                                        @php
                                            $date = \Carbon\Carbon::createFromDate($year, $month, $d);
                                            $isWeekend = $date->isWeekend();
                                        @endphp
                                        <th class="py-3 text-center {{ $isWeekend ? 'bg-light' : '' }}"
                                            style="min-width: 40px; font-size: 12px;">
                                            {{ $d }}<br>
                                            <small class="text-muted">{{ $date->format('D') }}</small>
                                        </th>
                                    @endfor
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($users as $user)
                                    <tr>
                                        <td class="py-3 px-4 text-start fw-medium" style="border-right: 2px solid #f8f9fa;">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-placeholder me-3">
                                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center"
                                                         style="width: 32px; height: 32px; font-size: 14px;">
                                                        {{ substr($user->name, 0, 1) }}
                                                    </div>
                                                </div>
                                                <div>
                                                    {{ $user->name }}
                                                    <br>
                                                    <small class="text-muted">{{ $user->employee_id ?? 'N/A' }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        @for ($d = 1; $d <= $daysInMonth; $d++)
                                            @php
                                                $dateKey = \Carbon\Carbon::createFromDate($year, $month, $d)->format('Y-m-d');
                                                $att = $attendanceMap[$user->id][$dateKey] ?? null;
                                                $date = \Carbon\Carbon::createFromDate($year, $month, $d);
                                                $isWeekend = $date->isWeekend();
                                                $isToday = $date->isToday();
                                            @endphp
                                            <td class="py-2 text-center align-middle {{ $isWeekend ? 'bg-light' : '' }} {{ $isToday ? 'border border-primary' : '' }}">
                                                @if($att)
                                                    <span class="badge bg-success rounded-pill p-1"
                                                          style="width: 24px; height: 24px; line-height: 16px;"
                                                          title="Present on {{ $dateKey }}">
                                                        <i class="fas fa-check"></i>
                                                    </span>
                                                @else
                                                    <span class="text-muted opacity-50" title="Absent on {{ $dateKey }}">
                                                        –
                                                    </span>
                                                @endif
                                            </td>
                                        @endfor
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white py-3">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                <span class="badge bg-success rounded-circle p-1 me-2">
                                    <i class="fas fa-check" style="font-size: 10px;"></i>
                                </span>
                                <small class="text-muted">Present</small>
                                <span class="mx-2">•</span>
                                <span class="text-muted me-2">–</span>
                                <small class="text-muted">Absent</small>
                            </div>
                        </div>
                        <div class="col-md-6 text-end">
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                Showing {{ $users->count() }} employees for {{ \Carbon\Carbon::createFromDate(null, $month)->format('F') }} {{ $year }}
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ✅ Legend --}}
            <div class="mt-4">
                <div class="d-flex flex-wrap gap-3">
                    <div class="d-flex align-items-center">
                        <span class="badge bg-success rounded-circle p-1 me-2">
                            <i class="fas fa-check" style="font-size: 10px;"></i>
                        </span>
                        <small>Present</small>
                    </div>
                    <div class="d-flex align-items-center">
                        <span class="text-muted me-2">–</span>
                        <small>Absent</small>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="border border-primary px-2 py-1 me-2"></div>
                        <small>Today</small>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="bg-light px-2 py-1 me-2"></div>
                        <small>Weekend</small>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<style>
    .page-header {
        padding: 20px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        margin-bottom: 24px;
    }

    .card {
        border-radius: 12px;
        border: none;
        transition: all 0.3s ease;
    }

    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.1) !important;
    }

    .table th {
        background-color: #f8f9fa;
        font-weight: 600;
        color: #2c3e50;
        border-top: none;
        border-bottom: 2px solid #e9ecef;
    }

    .table td {
        vertical-align: middle;
        border-color: #f1f3f4;
    }

    .table tr:hover td {
        background-color: #f8f9fa;
    }

    .avatar-placeholder .rounded-circle {
        font-weight: 600;
    }

    .form-select {
        border-radius: 8px;
        border: 1px solid #ddd;
        transition: all 0.3s ease;
    }

    .form-select:focus {
        border-color: #3498db;
        box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
    }

    .btn-group .btn {
        border-radius: 8px !important;
        margin: 0 2px;
    }

    .breadcrumb {
        background: transparent;
        padding: 0;
        margin-bottom: 0;
    }

    .breadcrumb-item a {
        text-decoration: none;
        transition: color 0.3s ease;
    }

    .breadcrumb-item a:hover {
        color: #3498db !important;
    }

    @media (max-width: 768px) {
        .table-responsive {
            font-size: 12px;
        }

        .btn-group {
            flex-wrap: wrap;
            gap: 5px;
        }

        .btn-group .btn {
            flex: 1;
            min-width: 60px;
            margin: 2px;
        }
    }
</style>
@endsection
