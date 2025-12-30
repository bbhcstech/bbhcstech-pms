@extends('admin.layout.app')

@section('title', 'Admin Dashboard')

@section('content')

<style>
    /* General improvements */
    .small-chart {
        max-height: 200px !important;
    }

    .card {
        border-radius: 0.75rem;
        box-shadow: 0 0.25rem 0.75rem rgba(0,0,0,0.05);
        transition: transform 0.2s;
    }

    .card:hover {
        transform: translateY(-2px);
    }

    .nav-pills .nav-link.active {
        background-color: #0d6efd !important;
        color: #fff !important;
    }

    .badge {
        font-size: 0.85em;
    }

    /* Responsive adjustments */
    @media (max-width: 767px) {
        .card-body img {
            height: auto;
            max-width: 100%;
        }
        .content-wrapper {
            padding: 0.5rem !important;
        }
    }
</style>

<main id="main" class="main">

    <div class="content-wrapper">

        <!-- Dashboard Tabs -->
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

        <!-- Welcome Card -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card d-flex flex-wrap align-items-center">
                    <div class="col-md-7 card-body">
                        <h5 class="card-title text-primary mb-3">Welcome to PMS Admin Dashboard</h5>
                        <p>Manage employee attendance, tasks, and performance all in one place.</p>
                    </div>
                    <div class="col-md-5 text-center card-body">
                        <img src="{{ asset('admin/assets/img/illustrations/man-with-laptop.png')}}" class="img-fluid" height="175" alt="Dashboard Illustration"/>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row g-3 mb-4">
            <!-- Total Employees -->
            <div class="col-md-6 col-lg-3">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div class="avatar flex-shrink-0">
                                <img src="{{ asset('admin/assets/img/icons/unicons/chart-success.png')}}" alt="Employees" class="rounded" />
                            </div>
                            <div class="dropdown">
                                <button class="btn p-0" type="button" data-bs-toggle="dropdown">
                                    <i class="bx bx-dots-vertical-rounded"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <a class="dropdown-item" href="{{ route('employees.index') }}">View More</a>
                                </div>
                            </div>
                        </div>
                        <p class="mb-1"><a href="{{ route('employees.index') }}" class="text-decoration-none text-dark">Total Employees</a></p>
                        <h4>{{ $totalEmployees ?? 0 }}</h4>
                        <small class="text-success fw-medium"><i class="bx bx-up-arrow-alt"></i> {{ $totalEmployees ?? 0 }}</small>
                    </div>
                </div>
            </div>

            <!-- Total Attendance -->
            <div class="col-md-6 col-lg-3">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div class="avatar flex-shrink-0">
                                <img src="{{ asset('admin/assets/img/icons/unicons/wallet-info.png')}}" alt="Attendance" class="rounded" />
                            </div>
                            <div class="dropdown">
                                <button class="btn p-0" type="button" data-bs-toggle="dropdown">
                                    <i class="bx bx-dots-vertical-rounded"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <a class="dropdown-item" href="{{ route('attendance.report') }}">View More</a>
                                </div>
                            </div>
                        </div>
                        <p class="mb-1"><a href="{{ route('attendance.report') }}" class="text-decoration-none text-dark">Total Attendance</a></p>
                        <h4>{{ $presentCount ?? 0 }}/{{ $totalEmployees ?? 0 }}</h4>
                        <small class="text-success fw-medium"><i class="bx bx-up-arrow-alt"></i>{{ $presentCount ?? 0 }}/{{ $totalEmployees ?? 0 }}</small>
                    </div>
                </div>
            </div>

            <!-- Total Clients -->
            <div class="col-md-6 col-lg-3">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div class="avatar flex-shrink-0">
                                <img src="{{ asset('admin/assets/img/icons/unicons/chart-success.png')}}" alt="Clients" class="rounded" />
                            </div>
                            <div class="dropdown">
                                <button class="btn p-0" type="button" data-bs-toggle="dropdown">
                                    <i class="bx bx-dots-vertical-rounded"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <a class="dropdown-item" href="{{ route('clients.index') }}">View More</a>
                                </div>
                            </div>
                        </div>
                        <p class="mb-1"><a href="{{ route('clients.index') }}" class="text-decoration-none text-dark">Total Clients</a></p>
                        <h4>{{ $totalClient ?? 0 }}</h4>
                        <small class="text-success fw-medium"><i class="bx bx-up-arrow-alt"></i>{{ $totalClient ?? 0 }}</small>
                    </div>
                </div>
            </div>

            <!-- Total Projects -->
            <div class="col-md-6 col-lg-3">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div class="avatar flex-shrink-0">
                                <img src="{{ asset('admin/assets/img/icons/unicons/wallet-info.png')}}" alt="Projects" class="rounded" />
                            </div>
                            <div class="dropdown">
                                <button class="btn p-0" type="button" data-bs-toggle="dropdown">
                                    <i class="bx bx-dots-vertical-rounded"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <a class="dropdown-item" href="{{ route('projects.index') }}">View More</a>
                                </div>
                            </div>
                        </div>
                        <p class="mb-1"><a href="{{ route('projects.index') }}" class="text-decoration-none text-dark">Total Projects</a></p>
                        <h4>{{ $totalProject ?? 0 }}</h4>
                        <small class="text-success fw-medium"><i class="bx bx-up-arrow-alt"></i>{{ $totalProject ?? 0 }}</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Open Tickets & Pending Tasks Section -->
        <div class="row g-3">
            <!-- Open Tickets -->
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Open Tickets</h5>
                        <a href="{{ route('tickets.index', ['status' => 'open']) }}" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="card-body">
                        @forelse($openTickets as $ticket)
                            <div class="mb-3 pb-2 border-bottom">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">{{ $ticket->subject ?? 'No Subject' }}</h6>
                                        <small class="text-muted d-block">{{ $ticket->requester_name ?? 'Unknown' }} – {{ $ticket->project?->name ?? 'No Project' }}</small>
                                    </div>
                                    <div class="text-end">
                                        <small class="d-block text-muted">{{ \Carbon\Carbon::parse($ticket->created_at)->format('d-m-Y') }}</small>
                                        <span class="badge bg-label-warning">{{ ucfirst($ticket->priority ?? 'Low') }}</span>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-muted text-center py-4">
                                <i class="bx bx-error-circle fs-1 mb-2 d-block"></i>
                                No open tickets found.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Pending Tasks -->
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Pending Tasks</h5>
                        <a href="{{ route('tasks.index', ['exclude_completed' => true]) }}" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="card-body">
                        @forelse($pendingTasksTotal as $task)
                            <div class="mb-3 pb-2 border-bottom">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="mb-1">{{ $task->title ?? 'N/A' }}</h6>
                                        <small class="text-muted d-block">{{ $task->project->name ?? 'N/A' }} – {{ \Carbon\Carbon::parse($task->start_date)->format('d-m-Y') }}</small>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-{{ match($task->status) {
                                            'To Do' => 'secondary',
                                            'Doing' => 'info',
                                            'Incomplete' => 'danger',
                                            'Waiting for Approval' => 'dark',
                                            default => 'light'
                                        } }}">{{ $task->status ?? 'Pending' }}</span>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-muted py-4">
                                <i class="bi bi-check-circle fs-1 d-block mb-2"></i>
                                No pending tasks found.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Project Activities -->
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Project Activity Timeline</h5>
                        <a href="" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="card-body">
                        @forelse($activities as $activity)
                            <div class="d-flex align-items-start mb-3">
                                <div class="text-center me-3">
                                    <div class="text-primary fw-bold">{{ \Carbon\Carbon::parse($activity->created_at)->format('M') }}</div>
                                    <div class="h4 mb-0">{{ \Carbon\Carbon::parse($activity->created_at)->format('d') }}</div>
                                </div>
                                <div>
                                    <div>{{ $activity->activity ?? 'No activity' }}</div>
                                    <div class="text-muted small">
                                        <strong>{{ $activity->project_name ?? 'N/A' }}</strong><br>
                                        {{ \Carbon\Carbon::parse($activity->created_at)->format('h:i A') }}
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="text-muted">No recent activities.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

    </div> <!-- /content-wrapper -->

</main>
@endsection
