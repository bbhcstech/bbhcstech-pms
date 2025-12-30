//hr dashboard

@extends('admin.layout.app')

@section('content')

@php
    $defaultStart = now()->format('Y-m-d');
    $defaultEnd = now()->format('Y-m-d');
    $startDate = request('start_date', $defaultStart);
    $endDate = request('end_date', $defaultEnd);
@endphp
<div class="container-fluid">

    <!-- Sub-navigation Tabs -->
     <ul class="nav nav-pills nav-fill mb-4 shadow-sm rounded border" id="dashboardTabs" role="tablist" style="background-color: #f8f9fa;">
    <li class="nav-item" role="presentation">
        <a class="nav-link fw-bold text-dark py-3 {{ request('tab') === 'project' ? 'active' : '' }}"
           href="{{ route('dashboard', ['tab' => 'project']) }}">
           <i class="bx bx-folder me-1"></i> Overview
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link fw-bold text-dark py-3 {{ Route::currentRouteName() === 'dashproject' ? 'active' : '' }}"
           href="{{ route('dashproject') }}">
           <i class="bx bx-folder me-1"></i> Project
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link fw-bold text-dark py-3 {{ Route::currentRouteName() === 'dashboard.client' ? 'active' : '' }}"
           href="{{ route('dashboard.client') }}">
           <i class="bx bx-folder me-1"></i> Clients
        </a>
    </li>

    <li class="nav-item" role="presentation">
        <a class="nav-link fw-bold text-dark py-3 {{ Route::currentRouteName() === 'hr.dashboard' ? 'active' : '' }}"
           href="{{ route('hr.dashboard') }}">
           <i class="bx bx-folder me-1"></i> HR
        </a>
    </li>

    <li class="nav-item" role="presentation">
        <a class="nav-link fw-bold text-dark py-3 {{ Route::currentRouteName() === 'dashboard.ticket' ? 'active' : '' }}"
           href="{{ route('dashboard.ticket') }}">
           <i class="bx bx-folder me-1"></i> Ticket
        </a>
    </li>
</ul>


    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold mb-0">HR Dashboard</h3>
       <form method="GET" class="d-flex align-items-center gap-2">
        <label class="mb-0 fw-semibold">Date Range:</label>
        <input type="date" name="start_date" class="form-control form-control-sm" value="{{ $startDate }}">
        <span class="fw-bold">to</span>
        <input type="date" name="end_date" class="form-control form-control-sm" value="{{ $endDate }}">
        <button type="submit" class="btn btn-sm btn-primary">Filter</button>
    </form>
    </div>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        @php
            $cards = [
                ['title' => 'Total Employees', 'value' => $totalEmployees],
                ['title' => 'New Employees', 'value' => $newEmployees],
                ['title' => 'Employee Exits', 'value' => $exits],
                ['title' => 'Approved Leaves', 'value' => $approvedLeaves],
                ['title' => 'Today Present', 'value' => $todayPresent],
                ['title' => 'Average Attendance', 'value' => $averageAttendance . '%'],
                ['title' => 'Pending Tasks', 'value' => $pendingTasks],
            ];
        @endphp

        @foreach ($cards as $card)
        <div class="col-sm-6 col-lg-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body text-center py-4">
                    <h6 class="text-muted">{{ $card['title'] }}</h6>
                    <h3 class="fw-bold mb-0">{{ $card['value'] }}</h3>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!--chart-->

   <div class="row mt-4">
    <!-- Department-wise Chart -->
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0 fw-semibold">Department-wise Employees</h6>
            </div>
            <div class="card-body" style="height: 320px;"> <!-- Fixed height -->
                <canvas id="departmentChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Designation-wise Chart -->
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0 fw-semibold">Designation-wise Employees</h6>
            </div>
            <div class="card-body" style="height: 320px;">
                <canvas id="designationChart"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <!-- Gender-wise Chart -->
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0 fw-semibold">Gender-wise Employees</h6>
            </div>
            <div class="card-body" style="height: 320px;">
                <canvas id="genderChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Role-wise Chart -->
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0 fw-semibold">Role-wise Employees</h6>
            </div>
            <div class="card-body" style="height: 320px;">
                <canvas id="roleChart"></canvas>
            </div>
        </div>
    </div>
</div>

    <!-- Charts -->
    <div class="row g-3">
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0 fw-semibold">Monthly Joinings</h6>
                </div>
                <div class="card-body">
                    <div style="height:250px">
                        <canvas id="joiningChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0 fw-semibold">Monthly Attritions</h6>
                </div>
                <div class="card-body">
                    <div style="height:250px">
                        <canvas id="exitChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Leaves Taken -->
<div class="row g-3 mt-3">
    <div class="col-md-6">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0 fw-semibold">Leaves Taken</h6>
            </div>
            <br>
            <div class="card-body">
                <ul class="list-group">
                    @forelse ($leavesTaken as $leave)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            {{ $leave->user->name }}<br>
                            <small>{{ $leave->user->employeeDetail->salutation ?? '' }} {{ $leave->user->employeeDetail->full_name ?? '' }}</small><br>
                            <small>{{ $leave->user->employeeDetail->designation->name ?? '-' }}</small>
                            <span class="badge bg-primary rounded-pill">{{ $leave->total }}</span>
                        </li>
                    @empty
                        <li class="list-group-item text-muted">- No leave records found. -</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

    <!-- Late Attendance -->
    <div class="col-md-6">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0 fw-semibold">Late Attendance</h6>
            </div>
            <br>
            <div class="card-body">
                <ul class="list-group">
                    @forelse ($lateAttendances as $userId => $attendances)
                        @php $user = $attendances->first()->employee->user ?? null; @endphp
                        @if ($user)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                {{ $user->name }}<br>
                                <small>{{ $user->employeeDetail->salutation ?? '' }} {{ $user->employeeDetail->full_name ?? '' }}</small><br>
                                <small>{{ $user->employeeDetail->designation->name ?? '-' }}</small>
                                <span class="badge bg-danger rounded-pill">{{ $attendances->count() }}</span>
                            </li>
                        @endif
                    @empty
                        <li class="list-group-item text-muted">- No late attendance found. -</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>


</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    const genderChart = new Chart(document.getElementById('genderChart'), {
        type: 'pie',
        data: {
            labels: {!! json_encode($genderCounts->keys()) !!},
            datasets: [{
                data: {!! json_encode($genderCounts->values()) !!},
                backgroundColor: ['#fcbf49', '#90be6d', '#577590']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });

    const roleChart = new Chart(document.getElementById('roleChart'), {
        type: 'pie',
        data: {
            labels: {!! json_encode($roleCounts->keys()) !!},
            datasets: [{
                data: {!! json_encode($roleCounts->values()) !!},
                backgroundColor: ['#ff6b6b', '#4ecdc4', '#1a535c']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
</script>

<script>
    const departmentCtx = document.getElementById('departmentChart').getContext('2d');
    const designationCtx = document.getElementById('designationChart').getContext('2d');

   const departmentChart = new Chart(document.getElementById('departmentChart'), {
    type: 'pie',
    data: {
        labels: {!! json_encode($departmentWise->map(fn($d) => $d->department_name ?? 'N/A')) !!},
        datasets: [{
            data: {!! json_encode($departmentWise->pluck('total')) !!},
            backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#8e44ad', '#2ecc71', '#e67e22']
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom' } }
    }
  });


    const designationChart = new Chart(designationCtx, {
        type: 'pie',
        data: {
            labels: {!! json_encode($designationWise->map(fn($d) => $d->designation->name ?? 'N/A')) !!},
            datasets: [{
                data: {!! json_encode($designationWise->pluck('total')) !!},
                backgroundColor: ['#FF9F40', '#36A2EB', '#FF6384', '#4BC0C0', '#9966FF', '#00a65a']
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'bottom' } }
        }
    });
</script>

<script>
    const months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
    const joiningData = {!! json_encode(array_values($monthlyJoinings->toArray())) !!};
    const exitData = {!! json_encode(array_values($monthlyAttrition->toArray())) !!};

    const chartOptions = {
        type: 'bar',
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1 }
                }
            }
        }
    };

    new Chart(document.getElementById('joiningChart').getContext('2d'), {
        ...chartOptions,
        data: {
            labels: months,
            datasets: [{
                label: 'Joinings',
                data: joiningData,
                backgroundColor: 'rgba(54, 162, 235, 0.7)'
            }]
        }
    });

    new Chart(document.getElementById('exitChart').getContext('2d'), {
        ...chartOptions,
        data: {
            labels: months,
            datasets: [{
                label: 'Attritions',
                data: exitData,
                backgroundColor: 'rgba(255, 99, 132, 0.7)'
            }]
        }
    });
</script>
@endsection
