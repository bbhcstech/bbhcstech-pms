@extends('admin.layout.app')

@section('title', 'Leaves')

@section('content')

@php
    // Quick fix: Fetch employee data if not passed from controller
    if (!isset($employee_data) && auth()->user()->role === 'admin') {
        $employee_data = \App\Models\User::orderBy('name')->get();
    }
@endphp
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h3 mb-2 fw-bold text-dark">
                @if(auth()->user()->role === 'admin')
                    Leave Management
                @else
                    My Leaves
                @endif
            </h1>
            <p class="text-muted mb-0">
                @if(auth()->user()->role === 'admin')
                    Manage employee leave requests and approvals
                @else
                    View and manage your leave requests
                @endif
            </p>
        </div>
        <div class="mt-3 mt-md-0">
            @if(auth()->user()->role === 'admin' || auth()->user()->role === 'employee')
                <a href="{{ route('leaves.create') }}" class="btn btn-primary px-4">
                    <i class="bi bi-plus-circle me-2"></i>New Leave Request
                </a>
                @if(auth()->user()->role === 'admin')
                <!-- Export Button -->
                <button type="button" class="btn btn-success px-4 ms-2" data-bs-toggle="modal" data-bs-target="#exportModal">
                    <i class="bi bi-download me-2"></i>Export
                </button>
                @endif
            @endif
        </div>
    </div>

    <!-- Success Alert -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            <div class="flex-grow-1">{{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- ===================== EMPLOYEE LEAVE SUMMARY ===================== -->
    @if(auth()->user()->role === 'employee' && isset($user_leave_summary))
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card border">
                <div class="card-header bg-transparent py-3">
                    <h5 class="card-title mb-0"><i class="bi bi-calendar-check me-2"></i>Your Leave Summary - {{ date('Y') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Allocated Leaves -->
                        <div class="col-md-3 mb-3">
                            <div class="text-center p-3 border rounded bg-primary bg-opacity-10">
                                <h2 class="text-primary mb-1">{{ $user_leave_summary['allocated'] }}</h2>
                                <p class="small text-muted mb-0">Total Allocated Leaves</p>
                                @if(auth()->user()->joining_date)
                                <small class="text-muted">(Joined: {{ date('d M, Y', strtotime(auth()->user()->joining_date)) }})</small>
                                @endif
                            </div>
                        </div>

                        <!-- Leaves Taken -->
                        <div class="col-md-3 mb-3">
                            <div class="text-center p-3 border rounded
                                @if($user_leave_summary['percentage'] >= 90) bg-danger bg-opacity-10
                                @elseif($user_leave_summary['percentage'] >= 75) bg-warning bg-opacity-10
                                @else bg-success bg-opacity-10 @endif">
                                <h2 class="@if($user_leave_summary['percentage'] >= 90) text-danger
                                          @elseif($user_leave_summary['percentage'] >= 75) text-warning
                                          @else text-success @endif mb-1">
                                    {{ $user_leave_summary['taken'] }}
                                </h2>
                                <p class="small text-muted mb-0">Leaves Taken</p>
                                <div class="progress mt-2" style="height: 6px;">
                                    <div class="progress-bar @if($user_leave_summary['percentage'] >= 90) bg-danger
                                           @elseif($user_leave_summary['percentage'] >= 75) bg-warning
                                           @else bg-success @endif"
                                         style="width: {{ $user_leave_summary['percentage'] }}%">
                                    </div>
                                </div>
                                <small class="text-muted">{{ round($user_leave_summary['percentage'], 1) }}% Utilized</small>
                            </div>
                        </div>

                        <!-- Remaining Leaves -->
                        <div class="col-md-3 mb-3">
                            <div class="text-center p-3 border rounded bg-info bg-opacity-10">
                                <h2 class="text-info mb-1">{{ $user_leave_summary['remaining'] }}</h2>
                                <p class="small text-muted mb-0">Leaves Remaining</p>
                                @if($user_leave_summary['remaining'] <= 0)
                                <small class="text-danger">Next leaves will be unpaid</small>
                                @elseif($user_leave_summary['remaining'] <= 3)
                                <small class="text-warning">Only {{ $user_leave_summary['remaining'] }} leaves left</small>
                                @endif
                            </div>
                        </div>

                        <!-- Monetary Value -->
                        <div class="col-md-3 mb-3">
                            <div class="text-center p-3 border rounded bg-success bg-opacity-10">
                                <h2 class="text-success mb-1">
                                    ₹{{ number_format($user_leave_summary['monetary_value'], 0) }}
                                </h2>
                                <p class="small text-muted mb-0">Monetary Value</p>
                                <small class="text-muted">@ ₹{{ number_format($policy->leave_monetary_value ?? 0, 0) }}/leave</small>
                            </div>
                        </div>
                    </div>

                    <!-- Warning for low leaves -->
                    @if($user_leave_summary['remaining'] <= 0)
                    <div class="alert alert-warning mt-3 mb-0">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        You have no paid leaves remaining. Any new leave requests will be marked as unpaid.
                    </div>
                    @elseif($user_leave_summary['remaining'] <= 3)
                    <div class="alert alert-info mt-3 mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        You have only {{ $user_leave_summary['remaining'] }} paid leave(s) remaining.
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- ===================== ADMIN VIEW ===================== -->
    @if(auth()->user()->role === 'admin')
        <!-- Company Leave Policy Card -->
        <div class="card border mb-4">
            <div class="card-header bg-transparent py-3 d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0"><i class="bi bi-building me-2"></i>Company Leave Policy</h5>
                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#policyModal">
                    <i class="bi bi-pencil me-1"></i>Edit Policy
                </button>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="text-center p-3 border rounded">
                            <h3 class="text-primary mb-1">{{ $policy->annual_leaves ?? 18 }}</h3>
                            <p class="small text-muted mb-0">Annual Leaves</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center p-3 border rounded">
                            <h3 class="mb-1">{{ $policy->pro_rate_enabled ? 'Yes' : 'No' }}</h3>
                            <p class="small text-muted mb-0">Pro-rate Enabled</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center p-3 border rounded">
                            <h3 class="mb-1">{{ $policy->allow_carry_forward ? 'Yes' : 'No' }}</h3>
                            <p class="small text-muted mb-0">Carry Forward</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center p-3 border rounded">
                            <h3 class="mb-1">₹{{ number_format($policy->leave_monetary_value ?? 0, 0) }}</h3>
                            <p class="small text-muted mb-0">Value per Leave</p>
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-6">
                        <small class="text-muted">Fiscal Year:
                            {{ date('d M, Y', strtotime($policy->fiscal_year_start ?? date('Y-04-01'))) }} -
                            {{ date('d M, Y', strtotime($policy->fiscal_year_end ?? date('Y-03-31'))) }}
                        </small>
                    </div>
                    <div class="col-md-6 text-end">
                        <small class="text-muted">Max Carry Forward: {{ $policy->max_carry_forward ?? 'Not allowed' }} leaves</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Employee Leave Summary Table -->
        <div class="card border mb-4">
            <div class="card-header bg-transparent py-3">
                <h5 class="card-title mb-0"><i class="bi bi-people me-2"></i>Employee Leave Summary</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr class="border-bottom">
                                <th class="py-3 fw-semibold text-dark">Employee</th>
                                <th class="py-3 fw-semibold text-dark">Joining Date</th>
                                <th class="py-3 fw-semibold text-dark">Allocated</th>
                                <th class="py-3 fw-semibold text-dark">Taken</th>
                                <th class="py-3 fw-semibold text-dark">Remaining</th>
                                <th class="py-3 fw-semibold text-dark">Utilization</th>
                                <th class="py-3 fw-semibold text-dark">Monetary Value</th>
                                <th class="py-3 fw-semibold text-dark text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($employee_data as $employee)
                            @if($employee->role === 'employee')
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <div class="rounded-circle bg-light border d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                                <i class="bi bi-person text-muted"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-2">
                                            <div class="fw-medium">{{ $employee->name }}</div>
                                            <div class="small text-muted">{{ $employee->designation ?? 'N/A' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    {{ $employee->joining_date ? date('d M, Y', strtotime($employee->joining_date)) : 'Not set' }}
                                </td>
                                <td>
                                    <span class="badge bg-primary bg-opacity-10 text-primary border">
                                        {{ $employee_summaries[$employee->id]['allocated'] ?? 0 }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge
                                        @if(($employee_summaries[$employee->id]['percentage'] ?? 0) >= 90) bg-danger bg-opacity-10 text-danger
                                        @elseif(($employee_summaries[$employee->id]['percentage'] ?? 0) >= 75) bg-warning bg-opacity-10 text-warning
                                        @else bg-success bg-opacity-10 text-success @endif border">
                                        {{ $employee_summaries[$employee->id]['taken'] ?? 0 }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge
                                        @if(($employee_summaries[$employee->id]['remaining'] ?? 0) <= 0) bg-danger bg-opacity-10 text-danger
                                        @elseif(($employee_summaries[$employee->id]['remaining'] ?? 0) <= 3) bg-warning bg-opacity-10 text-warning
                                        @else bg-success bg-opacity-10 text-success @endif border">
                                        {{ $employee_summaries[$employee->id]['remaining'] ?? 0 }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="progress flex-grow-1" style="height: 6px;">
                                            <div class="progress-bar
                                                @if(($employee_summaries[$employee->id]['percentage'] ?? 0) >= 90) bg-danger
                                                @elseif(($employee_summaries[$employee->id]['percentage'] ?? 0) >= 75) bg-warning
                                                @else bg-success @endif"
                                                style="width: {{ $employee_summaries[$employee->id]['percentage'] ?? 0 }}%">
                                            </div>
                                        </div>
                                        <span class="small text-muted ms-2">
                                            {{ round($employee_summaries[$employee->id]['percentage'] ?? 0, 1) }}%
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-success fw-medium">
                                        ₹{{ number_format($employee_summaries[$employee->id]['monetary_value'] ?? 0, 0) }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <form action="{{ route('leaves.reset-employee-leaves', $employee->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-secondary"
                                                onclick="return confirm('Reset leave balance for {{ $employee->name }}?')">
                                            <i class="bi bi-arrow-clockwise"></i> Reset
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Filters Card -->
        <div class="card border mb-4">
            <div class="card-body p-4">
                <h6 class="mb-3 fw-semibold text-dark">Filters</h6>
                <form method="GET" action="{{ route('leaves.index') }}" class="row g-3">
                    <!-- Duration Filter -->
                    <div class="col-lg-4 col-md-6">
                        <label for="duration" class="form-label small text-muted mb-1">Date Range</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="bi bi-calendar text-muted"></i>
                            </span>
                            <input type="text"
                                   name="duration"
                                   id="duration"
                                   class="form-control border-start-0"
                                   value="{{ request('duration') }}"
                                   placeholder="Select date range"
                                   autocomplete="off">
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="col-lg-8 col-md-12 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary btn-sm px-3">
                            <i class="bi bi-funnel me-1"></i>Apply Filters
                        </button>
                        <a href="{{ route('leaves.index') }}" class="btn btn-outline-secondary btn-sm px-3">
                            <i class="bi bi-x-circle me-1"></i>Clear
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Filters Card -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-transparent py-3">
                <h5 class="card-title mb-0"><i class="bi bi-funnel me-2"></i>Filter Employee</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('leaves.index') }}" class="row g-3">
                    <!-- Employee Filter -->
                    <div class="col-lg-3 col-md-6">
                        <label for="employee" class="form-label">
                            Employee
                            <i class="bi bi-info-circle text-muted ms-1"
                               data-bs-toggle="tooltip"
                               title="Filter by specific employee"></i>
                        </label>
                        <select id="employee" name="employee" class="form-select select2">
                            <option value="">All Employees</option>
                            @foreach($employee_data as $employee)
                                <option value="{{ $employee->id }}"
                                        {{ request('employee') == $employee->id ? 'selected' : '' }}>
                                    {{ $employee->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Leave Type Filter -->
                    <div class="col-lg-3 col-md-6">
                        <label for="leave_type" class="form-label">
                            Leave Type
                        </label>
                        <select id="leave_type" name="leave_type" class="form-select">
                            <option value="">All Types</option>
                            <option value="casual" {{ request('leave_type') == 'casual' ? 'selected' : '' }}>Casual Leave</option>
                            <option value="sick" {{ request('leave_type') == 'sick' ? 'selected' : '' }}>Sick Leave</option>
                            <option value="leave_without_pay" {{ request('leave_type') == 'leave_without_pay' ? 'selected' : '' }}>Leave Without Pay</option>
                            <option value="half_day" {{ request('leave_type') == 'half_day' ? 'selected' : '' }}>Half Day</option>
                        </select>
                    </div>

                    <!-- Status Filter -->
                    <div class="col-lg-3 col-md-6">
                        <label for="status" class="form-label">
                            Status
                        </label>
                        <select id="status" name="status" class="form-select">
                            <option value="">All Statuses</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </div>

                    <!-- Action Buttons -->
                    <div class="col-lg-3 col-md-6 d-flex align-items-end">
                        <div class="d-flex gap-2 w-100">
                            <button type="submit" class="btn btn-primary flex-fill">
                                <i class="bi bi-filter me-2"></i>Apply Filters
                            </button>
                            <a href="{{ route('leaves.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle"></i>
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Controls Card -->
        <div class="card border mb-4">
            <div class="card-body py-3">
                <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-3">
                    <!-- Bulk Actions -->
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <div class="form-check me-3 mb-0">
                            <input class="form-check-input" type="checkbox" id="select-all">
                            <label class="form-check-label small text-muted" for="select-all">Select All</label>
                        </div>

                        <div class="d-flex align-items-center gap-2">
                            <select id="bulk-action" class="form-select form-select-sm w-auto" disabled style="display: none; width: 140px;">
                                <option value="">Bulk Actions</option>
                                <option value="none">No Action</option>
                                <option value="change_status">Change Status</option>
                                <option value="delete">Delete Selected</option>
                            </select>

                            <select id="status-dropdown" class="form-select form-select-sm w-auto" style="display: none; width: 120px;" disabled>
                                <option value="">Select Status</option>
                                <option value="approved">Approved</option>
                                <option value="pending">Pending</option>
                                <option value="rejected">Rejected</option>
                            </select>

                            <button id="apply-action" class="btn btn-primary btn-sm ms-2" style="display: none;">
                                Apply
                            </button>

                            <button id="bulkDeleteBtn" class="btn btn-outline-danger btn-sm ms-2" disabled>
                                <i class="bi bi-trash me-1"></i>Delete
                            </button>
                        </div>
                    </div>

                    <!-- View Toggle Buttons -->
                    <div class="d-flex align-items-center gap-1">
                        <span class="small text-muted me-2">View:</span>
                        <div class="btn-group btn-group-sm" role="group">
                            <a href="{{ route('leaves.index') }}"
                               class="btn btn-outline-secondary {{ request()->routeIs('leaves.index') ? 'active' : '' }} px-3">
                                Table
                            </a>
                            <a href="{{ route('leaves.calendar') }}"
                               class="btn btn-outline-secondary {{ request()->routeIs('leaves.calendar') ? 'active' : '' }} px-3">
                                Calendar
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- ===================== EMPLOYEE SIMPLE VIEW ===================== -->
    @if(auth()->user()->role === 'employee')
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card border">
                    <div class="card-body py-3">
                        <div class="d-flex align-items-center">
                            <div class="bg-warning bg-opacity-10 rounded-circle p-2 me-3">
                                <i class="bi bi-clock-history text-warning"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-semibold">{{ $leaves->where('status', 'pending')->count() }}</h6>
                                <p class="small text-muted mb-0">Pending</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border">
                    <div class="card-body py-3">
                        <div class="d-flex align-items-center">
                            <div class="bg-success bg-opacity-10 rounded-circle p-2 me-3">
                                <i class="bi bi-check-circle text-success"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-semibold">{{ $leaves->where('status', 'approved')->count() }}</h6>
                                <p class="small text-muted mb-0">Approved</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border">
                    <div class="card-body py-3">
                        <div class="d-flex align-items-center">
                            <div class="bg-danger bg-opacity-10 rounded-circle p-2 me-3">
                                <i class="bi bi-x-circle text-danger"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-semibold">{{ $leaves->where('status', 'rejected')->count() }}</h6>
                                <p class="small text-muted mb-0">Rejected</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Leaves Table -->
    <div class="card border">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="leaveTable" class="table table-hover mb-0">
                    <thead>
                        <tr class="border-bottom">
                            @if(auth()->user()->role === 'admin')
                                <th width="40" class="ps-4">
                                    <input type="checkbox" id="select-all-main">
                                </th>
                            @endif
                            @if(auth()->user()->role === 'admin')
                                <th class="py-3 fw-semibold text-dark">Employee</th>
                            @endif
                            <th class="py-3 fw-semibold text-dark">Leave Date</th>
                            <th class="py-3 fw-semibold text-dark">Duration</th>
                            <th class="py-3 fw-semibold text-dark">Status</th>
                            <th class="py-3 fw-semibold text-dark">Type</th>
                            <th class="py-3 fw-semibold text-dark">Paid Status</th>
                            <th class="text-end pe-4 py-3 fw-semibold text-dark">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($leaves as $leave)
                            <tr class="border-bottom">
                                @if(auth()->user()->role === 'admin')
                                    <td class="ps-4">
                                        <input type="checkbox" class="leave-checkbox" value="{{ $leave->id }}">
                                    </td>
                                @endif

                                @if(auth()->user()->role === 'admin')
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0">
                                                <div class="rounded-circle bg-light border d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                                    <i class="bi bi-person text-muted"></i>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1 ms-2">
                                                <div class="fw-medium">{{ $leave->user?->name ?? 'N/A' }}</div>
                                                <div class="small text-muted">
                                                    Leaves: {{ $leave->user?->remaining_leaves ?? 0 }} remaining
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                @endif

                                <td>
                                    @if($leave->start_date && $leave->end_date)
                                        <div class="text-dark">{{ $leave->start_date }} to {{ $leave->end_date }}</div>
                                        <div class="small text-muted">
                                            {{ \Carbon\Carbon::parse($leave->start_date)->diffInDays(\Carbon\Carbon::parse($leave->end_date)) + 1 }} days
                                        </div>
                                    @else
                                        <div class="text-dark">{{ $leave->date }}</div>
                                        <div class="small text-muted">1 day</div>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        {{ ucfirst($leave->duration) }}
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $statusColors = [
                                            'approved' => ['bg' => 'success', 'class' => 'text-success'],
                                            'pending' => ['bg' => 'warning', 'class' => 'text-warning'],
                                            'rejected' => ['bg' => 'danger', 'class' => 'text-danger']
                                        ];
                                        $status = $statusColors[$leave->status] ?? ['bg' => 'secondary', 'class' => 'text-secondary'];
                                    @endphp
                                    <span class="badge bg-{{ $status['bg'] }} bg-opacity-10 {{ $status['class'] }} border border-{{ $status['bg'] }} border-opacity-25">
                                        {{ ucfirst($leave->status) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="text-dark">{{ ucfirst($leave->type) }}</span>
                                </td>
                                <td>
                                    @if(auth()->user()->role === 'employee')
                                        @if($leave->paid == 0)
                                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25">Unpaid</span>
                                        @elseif($leave->paid == 1)
                                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">Paid</span>
                                        @endif
                                    @else
                                        <!-- Admin sees dropdown -->
                                        <select class="form-select form-select-sm change-paid-status"
                                                data-leave-id="{{ $leave->id }}"
                                                style="width: 100px;">
                                            <option value="0" {{ $leave->paid == 0 ? 'selected' : '' }}>Unpaid</option>
                                            <option value="1" {{ $leave->paid == 1 ? 'selected' : '' }}>Paid</option>
                                        </select>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light border"
                                                type="button"
                                                data-bs-toggle="dropdown"
                                                aria-expanded="false">
                                            <i class="bi bi-three-dots"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border">
                                            <li>
                                                <a class="dropdown-item" href="{{ route('leaves.show', $leave->id) }}">
                                                    <i class="bi bi-eye text-muted me-2"></i>View Details
                                                </a>
                                            </li>

                                            @if(auth()->user()->role == 'admin')
                                                <!-- Admin actions -->
                                                @if($leave->status !== 'approved')
                                                <li>
                                                    <form action="{{ route('leaves.updateStatus', $leave->id) }}" method="POST">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="hidden" name="status" value="approved">
                                                        <button type="submit" class="dropdown-item">
                                                            <i class="bi bi-check-circle text-success me-2"></i>Approve
                                                        </button>
                                                    </form>
                                                </li>
                                                @endif

                                                @if($leave->status !== 'rejected')
                                                <li>
                                                    <form action="{{ route('leaves.updateStatus', $leave->id) }}" method="POST">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="hidden" name="status" value="rejected">
                                                        <button type="submit" class="dropdown-item">
                                                            <i class="bi bi-x-circle text-danger me-2"></i>Reject
                                                        </button>
                                                    </form>
                                                </li>
                                                @endif

                                                @if($leave->status !== 'rejected')
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('leaves.edit', $leave->id) }}">
                                                        <i class="bi bi-pencil text-primary me-2"></i>Edit
                                                    </a>
                                                </li>
                                                @endif

                                                <li><hr class="dropdown-divider"></li>

                                                <li>
                                                    <form action="{{ route('leaves.destroy', $leave->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this leave?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="dropdown-item text-danger">
                                                            <i class="bi bi-trash me-2"></i>Delete
                                                        </button>
                                                    </form>
                                                </li>
                                            @else
                                                <!-- Employee actions - only view and cancel pending leaves -->
                                                @if($leave->status === 'pending')
                                                <li>
                                                    <form action="{{ route('leaves.destroy', $leave->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to cancel this leave request?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="dropdown-item text-danger">
                                                            <i class="bi bi-x-circle me-2"></i>Cancel Request
                                                        </button>
                                                    </form>
                                                </li>
                                                @endif
                                            @endif
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Results Info -->
    <div class="d-flex justify-content-between align-items-center mt-3">
        <div class="text-muted small" id="dt-custom-info">
            @if(auth()->user()->role === 'admin')
                Showing {{ $leaves->count() }} records
            @else
                You have {{ $leaves->count() }} leave request(s)
            @endif
        </div>
    </div>
</div>

<!-- Leave Policy Modal -->
<div class="modal fade" id="policyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-gear me-2"></i>Edit Leave Policy</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('leaves.update-policy') }}">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Annual Leaves Per Employee</label>
                            <input type="number" name="annual_leaves" class="form-control"
                                   value="{{ $policy->annual_leaves ?? 18 }}" required min="1">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Monetary Value per Leave (₹)</label>
                            <input type="number" step="0.01" name="leave_monetary_value"
                                   class="form-control" value="{{ $policy->leave_monetary_value ?? 0 }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Fiscal Year Start</label>
                            <input type="date" name="fiscal_year_start" class="form-control"
                                   value="{{ $policy->fiscal_year_start ?? date('Y-04-01') }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Fiscal Year End</label>
                            <input type="date" name="fiscal_year_end" class="form-control"
                                   value="{{ $policy->fiscal_year_end ?? date('Y-03-31') }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="pro_rate_enabled"
                                       value="1" {{ $policy->pro_rate_enabled ? 'checked' : '' }}>
                                <label class="form-check-label">Enable Pro-rate Calculation</label>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="allow_carry_forward"
                                       value="1" {{ $policy->allow_carry_forward ? 'checked' : '' }}>
                                <label class="form-check-label">Allow Leave Carry Forward</label>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Max Carry Forward (leaves)</label>
                            <input type="number" name="max_carry_forward" class="form-control"
                                   value="{{ $policy->max_carry_forward ?? '' }}" placeholder="Leave empty for unlimited">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Policy</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Export Modal -->
<div class="modal fade" id="exportModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-download me-2"></i>Export Leaves Data</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="GET" action="{{ route('leaves.export') }}" target="_blank">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Export Format</label>
                        <select name="type" class="form-select" required>
                            <option value="excel">Excel (.xlsx)</option>
                            <option value="pdf">PDF (.pdf)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date Range (Optional)</label>
                        <div class="row">
                            <div class="col-md-6">
                                <input type="date" name="from" class="form-control" placeholder="From Date">
                            </div>
                            <div class="col-md-6">
                                <input type="date" name="to" class="form-control" placeholder="To Date">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Employee (Optional)</label>
                        <select name="employee" class="form-select">
                            <option value="">All Employees</option>
                            @foreach($employee_data as $employee)
                                <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Export</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Multiple Leaves Modal -->
@foreach($leaves as $leave)
@if($leave->duration === 'multiple')
<div class="modal fade" id="leaveModal{{ $leave->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border">
            <div class="modal-header border-bottom">
                <h5 class="modal-title">
                    <i class="bi bi-calendar-week me-2"></i>
                    Leave Details - {{ $leave->user?->name ?? 'Unknown User' }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Date</th>
                                <th>Day</th>
                                <th>Type</th>
                                <th>Paid</th>
                                <th>Status</th>
                                @if(auth()->user()->role === 'admin')
                                    <th class="text-end pe-3">Action</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $start = \Carbon\Carbon::parse($leave->start_date);
                                $end = \Carbon\Carbon::parse($leave->end_date);
                            @endphp

                            @for ($date = $start; $date->lte($end); $date->addDay())
                                <tr>
                                    <td class="ps-3">{{ $date->format('d-m-Y') }}</td>
                                    <td>{{ $date->format('l') }}</td>
                                    <td>
                                        <span class="text-dark">{{ ucfirst($leave->type) }}</span>
                                    </td>
                                    <td>
                                        @if($leave->paid == 1)
                                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">Paid</span>
                                        @else
                                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25">Unpaid</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $statusColors[$leave->status] ?? 'secondary' }} bg-opacity-10 text-{{ $statusColors[$leave->status] ?? 'secondary' }} border border-{{ $statusColors[$leave->status] ?? 'secondary' }} border-opacity-25">
                                            {{ ucfirst($leave->status) }}
                                        </span>
                                    </td>
                                    @if(auth()->user()->role === 'admin')
                                    <td class="text-end pe-3">
                                        <form action="{{ route('leaves.destroy', $leave->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this leave?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                    @endif
                                </tr>
                            @endfor
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
@endforeach

@push('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">
<style>
    .card {
        border-color: #e0e0e0;
    }
    .table th {
        font-weight: 600;
        font-size: 0.875rem;
        color: #495057;
        background-color: #f8f9fa;
    }
    .table td {
        vertical-align: middle;
        padding: 1rem 0.75rem;
    }
    .badge {
        font-size: 0.75rem;
        font-weight: 500;
        padding: 0.25rem 0.5rem;
    }
    .dropdown-menu {
        min-width: 180px;
        border-color: #e0e0e0;
    }
    .form-select-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
    .daterangepicker {
        z-index: 1055 !important;
        font-family: inherit;
    }
    .table-hover tbody tr:hover {
        background-color: #f8f9fa;
    }
    .btn-group .btn.active {
        background-color: #0d6efd;
        border-color: #0d6efd;
        color: white;
    }
    .border-bottom {
        border-bottom-color: #e0e0e0 !important;
    }
    .input-group-sm > .form-control,
    .input-group-sm > .form-select {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
    .bg-opacity-10 {
        --bs-bg-opacity: 0.1;
    }
    .border-opacity-25 {
        --bs-border-opacity: 0.25;
    }
    .form-check-input:checked {
        background-color: #0d6efd;
        border-color: #0d6efd;
    }
    .text-dark {
        color: #212529 !important;
    }
    .fw-semibold {
        font-weight: 600 !important;
    }
    .fw-medium {
        font-weight: 500 !important;
    }
    /* New styles for leave summary */
    .progress {
        background-color: #e9ecef;
    }
    .progress-bar {
        transition: width 0.6s ease;
    }
</style>
@endpush

@push('js')
<script src="https://cdn.jsdelivr.net/npm/moment@2.29.1/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

@if(auth()->user()->role === 'admin')
<script>
$(function () {
    const predefinedRanges = {
        'Today': [moment(), moment()],
        'Last 7 Days': [moment().subtract(6, 'days'), moment()],
        'Last 30 Days': [moment().subtract(29, 'days'), moment()],
        'This Month': [moment().startOf('month'), moment().endOf('month')],
        'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
    };

    $('#duration').daterangepicker({
        autoUpdateInput: false,
        showDropdowns: true,
        opens: 'left',
        locale: {
            format: 'YYYY-MM-DD',
            cancelLabel: 'Clear'
        },
        ranges: predefinedRanges
    });

    $('#duration').on('apply.daterangepicker', function (ev, picker) {
        $(this).val(
            picker.startDate.format('YYYY-MM-DD') + ' to ' + picker.endDate.format('YYYY-MM-DD')
        );
    });

    $('#duration').on('cancel.daterangepicker', function () {
        $(this).val('');
    });
});
</script>
@endif

@if(auth()->user()->role === 'admin')
<script>
$(document).on('change', '.change-paid-status', function () {
    let leaveId = $(this).data('leave-id');
    let status = $(this).val();

    $.ajax({
        url: "{{ route('leaves.updatePaidStatus') }}",
        type: "POST",
        data: {
            _token: "{{ csrf_token() }}",
            leave_id: leaveId,
            paid: status
        },
        success: function (response) {
            if (response.success) {
                // Update the visual feedback
                $(this).closest('td').find('.badge').remove();
                var badgeClass = status == 1 ? 'success' : 'danger';
                var badgeText = status == 1 ? 'Paid' : 'Unpaid';
                $(this).after(`<span class="badge bg-${badgeClass} bg-opacity-10 text-${badgeClass} border border-${badgeClass} border-opacity-25 ms-2">${badgeText}</span>`);

                // Show success toast/alert
                showAlert('Status updated successfully!', 'success');
            } else {
                showAlert('Failed to update status!', 'danger');
            }
        }.bind(this),
        error: function () {
            showAlert('An error occurred!', 'danger');
        }
    });
});
</script>
@endif

<script>
function showAlert(message, type) {
    var alertHtml = `<div class="alert alert-${type} alert-dismissible fade show" role="alert">
        <i class="bi ${type === 'success' ? 'bi-check-circle' : 'bi-exclamation-circle'} me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>`;

    $('.container-fluid').prepend(alertHtml);

    // Auto remove after 3 seconds
    setTimeout(() => {
        $('.alert').alert('close');
    }, 3000);
}
</script>

@if(auth()->user()->role === 'admin')
<script>
$(document).ready(function () {
    // FIX: Prevent DataTables reinitialization error
    if ($.fn.DataTable.isDataTable('#leaveTable')) {
        $('#leaveTable').DataTable().clear().destroy();
        $('#leaveTable').removeAttr('style');
    }

    // Initialize DataTable for leaves table
    var table = $('#leaveTable').DataTable({
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>tip',
        responsive: true,
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],
        language: {
            search: "",
            searchPlaceholder: "Search leaves...",
            lengthMenu: "_MENU_ records per page",
            info: "Showing _START_ to _END_ of _TOTAL_ entries",
            infoEmpty: "Showing 0 to 0 of 0 entries",
            infoFiltered: "(filtered from _MAX_ total entries)"
        },
        columnDefs: [
            { orderable: false, targets: [0, 7] }
        ],
        order: [[2, 'desc']],
        destroy: true,
        retrieve: true
    });

    // Update custom info
    function updateCustomInfo() {
        var info = table.page.info();
        var start = (info.recordsDisplay === 0) ? 0 : info.start + 1;
        var end = info.end;
        var total = info.recordsDisplay;
        $('#dt-custom-info').text('Showing ' + start + ' to ' + end + ' of ' + total + ' entries');
    }
    updateCustomInfo();
    table.on('draw', updateCustomInfo);

    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Select all functionality
    $('#select-all, #select-all-main').on('change', function () {
        var checked = $(this).prop('checked');
        $('.leave-checkbox').prop('checked', checked).trigger('change');
    });

    // Toggle bulk actions based on checkbox selection
    function toggleBulkAction() {
        let anyChecked = $('.leave-checkbox:checked').length > 0;

        if (anyChecked) {
            $('#bulk-action, #bulk-action-main').show().prop('disabled', false);
            $('#apply-action').show();
            $('#bulkDeleteBtn').prop('disabled', false);
        } else {
            $('#bulk-action, #bulk-action-main').hide().prop('disabled', true).val('');
            $('#status-dropdown').hide().prop('disabled', true).val('');
            $('#apply-action').hide();
            $('#bulkDeleteBtn').prop('disabled', true);
        }
    }

    // When row checkboxes change
    $(document).on('change', '.leave-checkbox', function () {
        // Toggle bulk actions
        toggleBulkAction();

        // Sync the select-all checkbox
        var totalCheckboxes = $('.leave-checkbox').length;
        var checkedCount = $('.leave-checkbox:checked').length;
        $('#select-all, #select-all-main').prop('checked', totalCheckboxes === checkedCount);
    });

    // Bulk action dropdown change
    $(document).on('change', '#bulk-action', function () {
        if ($(this).val() === 'change_status') {
            $('#status-dropdown').show().prop('disabled', false);
        } else {
            $('#status-dropdown').hide().prop('disabled', true).val('');
        }
    });

    // Apply bulk action
    $('#apply-action').on('click', function () {
        let action = $('#bulk-action').val();
        let status = $('#status-dropdown').val();
        let selected = $('.leave-checkbox:checked').map(function () {
            return $(this).val();
        }).get();

        if (selected.length === 0) {
            showAlert('Please select at least one leave.', 'warning');
            return;
        }

        if (action === '') {
            showAlert('Please select an action.', 'warning');
            return;
        }

        if (action === 'change_status' && !status) {
            showAlert('Please select a status.', 'warning');
            return;
        }

        let confirmMessage = action === 'delete'
            ? `Are you sure you want to delete ${selected.length} selected leave(s)? This action cannot be undone.`
            : `Are you sure you want to update status for ${selected.length} selected leave(s)?`;

        if (!confirm(confirmMessage)) return;

        // Disable button during processing
        $(this).prop('disabled', true).html('<i class="bi bi-hourglass-split me-1"></i>Processing...');

        $.ajax({
            url: "{{ route('leaves.bulkAction') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                action: action,
                status: status,
                ids: selected
            },
            success: function (response) {
                showAlert(response.message, 'success');
                setTimeout(() => location.reload(), 1500);
            },
            error: function (xhr) {
                var msg = xhr.responseJSON?.message || 'An error occurred';
                showAlert(msg, 'danger');
                $('#apply-action').prop('disabled', false).html('<i class="bi bi-check-lg me-1"></i>Apply');
            }
        });
    });

    // Bulk delete
    $('#bulkDeleteBtn').on('click', function (e) {
        e.preventDefault();

        var selected = $('.leave-checkbox:checked').map(function () { return $(this).val(); }).get();

        if (selected.length === 0) {
            showAlert('Please select at least one leave.', 'warning');
            return;
        }

        if (!confirm(`Are you sure you want to delete ${selected.length} selected leave(s)? This action cannot be undone.`)) {
            return;
        }

        // Disable button during processing
        $(this).prop('disabled', true).html('<i class="bi bi-hourglass-split me-1"></i>Deleting...');

        $.ajax({
            url: "{{ route('leaves.bulk-delete') }}",
            method: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                ids: selected
            },
            success: function (res) {
                if (res.success ?? true) {
                    showAlert(res.message ?? 'Deleted successfully', 'success');
                    // Remove rows from DataTable
                    table.rows( $('.leave-checkbox:checked').closest('tr') ).remove().draw();
                    toggleBulkAction();
                } else {
                    showAlert(res.message || 'Something went wrong', 'danger');
                }
                $('#bulkDeleteBtn').prop('disabled', false).html('<i class="bi bi-trash me-1"></i>Delete');
            },
            error: function (xhr) {
                var msg = 'Error: ' + (xhr.responseJSON?.message || xhr.statusText);
                showAlert(msg, 'danger');
                $('#bulkDeleteBtn').prop('disabled', false).html('<i class="bi bi-trash me-1"></i>Delete');
            }
        });
    });

    // Initial toggle
    toggleBulkAction();
});
</script>
@else
<!-- Simple JavaScript for Employee View -->
<script>
$(document).ready(function () {
    // Initialize simple table for employees (no DataTables)
    $('#leaveTable').addClass('table-striped');

    // Simple search functionality for employees
    $('input[type="search"]').on('keyup', function() {
        var value = $(this).val().toLowerCase();
        $('#leaveTable tbody tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });
});
</script>
@endif

<!-- Initialize Select2 for dropdowns -->
<script>
$(document).ready(function() {
    $('.select2').select2({
        placeholder: "Select an employee",
        allowClear: true
    });
});
</script>
</script>
@endpush

@endsection
