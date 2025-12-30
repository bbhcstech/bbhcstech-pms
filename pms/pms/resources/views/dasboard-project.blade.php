@extends('admin.layout.app')

@section('content')

@php
    $startDateFormatted = \Carbon\Carbon::parse($startDate)->format('Y-m-d');
    $endDateFormatted = \Carbon\Carbon::parse($endDate)->format('Y-m-d');
@endphp

<div class="container-fluid py-4">
    <div class="container">

        {{-- Sub Navigation --}}
        <ul class="nav nav-pills nav-fill mb-4 shadow-sm rounded-4 border bg-light overflow-auto flex-nowrap"
            id="dashboardTabs" role="tablist">
            @php
                $tabs = [
                    ['route' => route('dashboard', ['tab' => 'project']), 'name' => 'Overview', 'active' => request('tab') === 'project'],
                    ['route' => route('dashproject'), 'name' => 'Project', 'active' => Route::currentRouteName() === 'dashproject'],
                    ['route' => route('dashboard.client'), 'name' => 'Clients', 'active' => Route::currentRouteName() === 'dashboard.client'],
                    ['route' => route('hr.dashboard'), 'name' => 'HR', 'active' => Route::currentRouteName() === 'hr.dashboard'],
                    ['route' => route('dashboard.ticket'), 'name' => 'Ticket', 'active' => Route::currentRouteName() === 'dashboard.ticket'],
                ];
            @endphp

            @foreach($tabs as $tab)
                <li class="nav-item">
                    <a class="nav-link fw-semibold px-4 py-3 {{ $tab['active'] ? 'active' : 'text-dark' }}"
                       href="{{ $tab['route'] }}">
                        <i class="bx bx-folder me-1"></i> {{ $tab['name'] }}
                    </a>
                </li>
            @endforeach
        </ul>

        {{-- Header + Filter --}}
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
            <h3 class="fw-bold mb-0">Project Dashboard</h3>

            <form method="GET" class="d-flex flex-wrap align-items-center gap-2">
                <span class="fw-semibold">Date Range:</span>
                <input type="date" name="start_date" class="form-control form-control-sm"
                       value="{{ $startDateFormatted }}">
                <span class="fw-bold">to</span>
                <input type="date" name="end_date" class="form-control form-control-sm"
                       value="{{ $endDateFormatted }}">
                <button type="submit" class="btn btn-sm btn-primary px-3">
                    Filter
                </button>
            </form>
        </div>

        {{-- Top Cards --}}
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="card shadow-sm rounded-4 text-center h-100 hover-shadow">
                    <div class="card-body">
                        <h6 class="text-muted">Total Projects</h6>
                        <h2 class="fw-bold text-primary">{{ $totalProjects }}</h2>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow-sm rounded-4 text-center h-100 hover-shadow">
                    <div class="card-body">
                        <h6 class="text-muted">Overdue Projects</h6>
                        <h2 class="fw-bold text-danger">{{ $overdueProjects }}</h2>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow-sm rounded-4 text-center h-100 hover-shadow">
                    <div class="card-body">
                        <h6 class="text-muted">Hours Logged</h6>
                        <h2 class="fw-bold">0</h2>
                    </div>
                </div>
            </div>
        </div>

        {{-- Chart + Table --}}
        <div class="row g-4">
            <div class="col-lg-6">
                <div class="card shadow-sm rounded-4 h-100">
                    <div class="card-header bg-transparent border-0">
                        <h6 class="fw-semibold mb-0">Status Wise Projects</h6>
                    </div>
                    <div class="card-body d-flex justify-content-center align-items-center">
                        <canvas id="projectStatusChart" style="max-height: 250px;"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card shadow-sm rounded-4 h-100">
                    <div class="card-header bg-transparent border-0">
                        <h6 class="fw-semibold mb-0">Pending Milestones</h6>
                    </div>
                    <div class="card-body table-responsive">
                        @if($pendingMilestones->isEmpty())
                            <p class="text-muted text-center my-4">— No record found —</p>
                        @else
                            <table class="table align-middle table-bordered mb-0">
                                <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Milestone Title</th>
                                    <th>Cost</th>
                                    <th>Project</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($pendingMilestones as $index => $milestone)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td class="fw-medium">{{ $milestone->title }}</td>
                                        <td>{{ $milestone->cost }}</td>
                                        <td>{{ $milestone->project->name ?? 'N/A' }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('projectStatusChart');
    new Chart(ctx, {
        type: 'pie',
        data: {
            labels: {!! json_encode(array_keys($statusWiseCounts)) !!},
            datasets: [{
                data: {!! json_encode(array_values($statusWiseCounts)) !!},
                backgroundColor: ['#0d6efd', '#ffc107', '#198754', '#dc3545', '#6c757d'],
                borderWidth: 1
            }]
        }
    });
</script>
@endpush
