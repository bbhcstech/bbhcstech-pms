@extends('admin.layout.app')

@section('content')
<div class="employee-dashboard">

    {{-- Breadcrumb with Animation --}}
    <div class="breadcrumb-wrapper animate-slideDown">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('dashboard') }}">
                        <i class="fas fa-home me-1"></i> Dashboard
                    </a>
                </li>
                <li class="breadcrumb-item active">
                    <i class="fas fa-users me-1"></i> Employees
                </li>
            </ol>
        </nav>
    </div>

    @php
        // Calculate employee counts for statistics - FUNCTIONALITY UNCHANGED
        $totalEmployees = $employees->count();
        $activeEmployees = $employees->where('employeeDetail.status', 'Active')->count();
        $inactiveEmployees = $employees->where('employeeDetail.status', 'Inactive')->count();
        $noticeEmployees = $employees->filter(function($emp) {
            return $emp->employeeDetail?->employment_status === 'notice';
        })->count();
        $internEmployees = $employees->filter(function($emp) {
            $designation = strtolower($emp->employeeDetail?->designation?->name ?? '');
            return strpos($designation, 'intern') !== false;
        })->count();

        // Filter visible employees - FUNCTIONALITY UNCHANGED
        $visibleEmployees = $employees->filter(function($emp) {
            $detail = $emp->employeeDetail ?? null;
            $status = $detail?->employment_status ?? null;
            $hasNotice = !empty($detail?->notice_end_date);
            $hasProbation = !empty($detail?->probation_end_date);

            if (in_array($status, ['notice','probation'])) return false;
            if ($hasNotice || $hasProbation) return false;
            return true;
        });
    @endphp

    {{-- Header Section --}}
    <div class="header-section animate-fadeIn">
        <div class="header-content">
            <div class="title-wrapper">
                <div class="icon-circle animate-float">
                    <i class="fas fa-users"></i>
                </div>
                <div>
                    <h1>Employee Management</h1>
                    <p class="subtitle">Manage all employees, their details, and permissions</p>
                </div>
            </div>
            <div class="action-buttons">
                <!-- Quick Actions Dropdown -->
                <div class="quick-actions">
                    <div class="dropdown">
                        <button class="btn btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-bolt me-2"></i>Quick Actions
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#" id="bulkDeleteTrigger"><i class="fas fa-trash-alt me-2 text-danger"></i>Bulk Delete</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <!-- <li><a class="dropdown-item" href="#" id="exportSelectedTrigger"><i class="fas fa-download me-2 text-info"></i>Export Selected</a></li> -->
                        </ul>
                    </div>
                </div>

                <!-- Add Employee Button -->
                <a href="{{ route('employees.create') }}" class="btn btn-add animate-pulse">
                    <i class="fas fa-user-plus me-2"></i>
                    <span>Add Employee</span>
                </a>

                <!-- Invite Button -->
                <button type="button" class="btn btn-invite animate-pulse" data-bs-toggle="modal" data-bs-target="#inviteModal">
                    <i class="fas fa-envelope me-2"></i>
                    <span>Invite</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Stats Cards - 5 Cards Perfect Alignment with Animation --}}
    <div class="stats-grid">
        <!-- Total Employees Card -->
        <div class="stat-card total animate-slideUp" style="animation-delay: 0.1s;">
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-content">
                <span class="stat-label">Total Employees</span>
                <span class="stat-value">{{ $totalEmployees }}</span>
            </div>
            <div class="stat-glow"></div>
        </div>

        <!-- Active Employees Card -->
        <div class="stat-card active animate-slideUp" style="animation-delay: 0.2s;">
            <div class="stat-icon">
                <i class="fas fa-user-check"></i>
            </div>
            <div class="stat-content">
                <span class="stat-label">Active</span>
                <span class="stat-value">{{ $activeEmployees }}</span>
            </div>
            <div class="stat-glow"></div>
        </div>

        <!-- Inactive Employees Card -->
        <div class="stat-card inactive animate-slideUp" style="animation-delay: 0.3s;">
            <div class="stat-icon">
                <i class="fas fa-user-slash"></i>
            </div>
            <div class="stat-content">
                <span class="stat-label">Inactive</span>
                <span class="stat-value">{{ $inactiveEmployees }}</span>
            </div>
            <div class="stat-glow"></div>
        </div>

        <!-- On Notice Card -->
        <div class="stat-card notice animate-slideUp" style="animation-delay: 0.4s;">
            <div class="stat-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-content">
                <span class="stat-label">On Notice</span>
                <span class="stat-value">{{ $noticeEmployees }}</span>
            </div>
            <div class="stat-glow"></div>
        </div>

        <!-- Interns Card -->
        <div class="stat-card interns animate-slideUp" style="animation-delay: 0.5s;">
            <div class="stat-icon">
                <i class="fas fa-user-graduate"></i>
            </div>
            <div class="stat-content">
                <span class="stat-label">Interns</span>
                <span class="stat-value">{{ $internEmployees }}</span>
            </div>
            <div class="stat-glow"></div>
        </div>
    </div>

    {{-- Invite Modal - FUNCTIONALITY UNCHANGED --}}
    <div class="modal fade" id="inviteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-paper-plane me-2"></i>
                        Invite to Xinksoft Technologies
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Nav Tabs -->
                    <ul class="nav nav-tabs" id="inviteTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="invite-email-tab" data-bs-toggle="tab" data-bs-target="#invite-email" type="button" role="tab">
                                <i class="fas fa-envelope me-2"></i>Invite by Email
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="invite-link-tab" data-bs-toggle="tab" data-bs-target="#invite-link" type="button" role="tab">
                                <i class="fas fa-link me-2"></i>Invite by Link
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content" id="inviteTabsContent">
                        <!-- Invite by Email -->
                        <div class="tab-pane fade show active" id="invite-email" role="tabpanel">
                            <div class="alert alert-info bg-light">
                                <i class="fas fa-info-circle text-primary me-2"></i>
                                Employees will receive an email to log in and update their profile.
                            </div>

                            <form id="inviteEmailForm">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Email Address <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-at"></i></span>
                                        <input type="email" name="email" id="inviteEmail" class="form-control" placeholder="e.g. johndoe@xinksoft.com" required>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label fw-bold">Personal Message <span class="text-muted">(Optional)</span></label>
                                    <textarea name="message" id="inviteMessage" class="form-control" rows="3" placeholder="Add a welcome message..."></textarea>
                                </div>
                            </form>
                        </div>

                        <!-- Invite by Link -->
                        <div class="tab-pane fade" id="invite-link" role="tabpanel">
                            <div class="alert alert-light border">
                                <i class="fas fa-link text-primary me-2"></i>
                                Create an invitation link that can be shared with multiple people.
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold mb-3">Link Restrictions:</label>
                                <div class="list-group">
                                    <label class="list-group-item border-0 bg-light">
                                        <input class="form-check-input me-2" type="radio" name="linkOption" id="anyEmail" checked>
                                        <span class="fw-medium">Allow any email address</span>
                                        <small class="text-muted d-block mt-1">Anyone with the link can join</small>
                                    </label>
                                    <label class="list-group-item border-0 bg-light mt-2">
                                        <input class="form-check-input me-2" type="radio" name="linkOption" id="domainEmail">
                                        <span class="fw-medium">Restrict to company domain</span>
                                        <small class="text-muted d-block mt-1">Only @xinksoft.com emails allowed</small>
                                    </label>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end">
                                <button type="button" class="btn btn-primary px-4" id="createLinkBtn">
                                    <i class="fas fa-plus-circle me-2"></i>Generate Invite Link
                                </button>
                            </div>

                            <div class="mt-4" id="linkContainer" style="display:none;">
                                <div class="alert alert-success border-0 bg-success bg-opacity-10">
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    <span class="fw-medium">Invitation link created successfully!</span>
                                    <small class="d-block mt-1">This link will expire in 7 days</small>
                                </div>

                                <div class="input-group">
                                    <input type="text" class="form-control border-end-0" id="inviteLink" readonly>
                                    <button class="btn btn-outline-primary" type="button" id="copyLinkBtn">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                    <button class="btn btn-outline-success" type="button" id="shareLinkBtn" title="Share via Email">
                                        <i class="fas fa-share-alt"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" form="inviteEmailForm" id="sendInviteBtn" class="btn btn-primary">
                        <span id="sendInviteSpinner" class="spinner-border spinner-border-sm me-2 d-none"></span>
                        <i class="fas fa-paper-plane me-2"></i>Send Invitation
                    </button>
                </div>
                <div class="mt-3 px-3" id="inviteEmailAlert" style="display:none;"></div>
            </div>
        </div>
    </div>

    {{-- Blocked Delete Modal - FUNCTIONALITY UNCHANGED --}}
    <div class="modal fade" id="blockedDeleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Delete Restricted
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <i class="fas fa-users-slash fa-4x text-warning mb-3"></i>
                    <h5 id="blockedEmployeeName" class="fw-bold mb-2"></h5>
                    <p class="text-muted mb-0" id="blockedEmployeeReason"></p>
                </div>
                <div class="modal-footer border-0 justify-content-center">
                    <a href="#" id="blocked-view-subordinates" class="btn btn-outline-primary">
                        <i class="fas fa-eye me-2"></i>View Team Members
                    </a>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Alerts Section - FUNCTIONALITY UNCHANGED --}}
    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show mb-4 animate-shake" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>
        {{ $errors->first() }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4 animate-slideDown" role="alert">
        <i class="fas fa-check-circle me-2"></i>
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    {{-- Filter & Export Section - PERFECT ALIGNMENT --}}
    <div class="filter-export-card animate-fadeIn">
        <div class="filter-header">
            <div class="header-left">
                <div class="header-icon-wrapper">
                    <i class="fas fa-sliders-h"></i>
                </div>
                <div class="header-text">
                    <h3>Filter Employees</h3>
                    <p>Narrow down your employee list</p>
                </div>
            </div>
            <div class="header-right">
                <span id="export-selected-count" class="selected-badge">0 selected</span>
                <button type="button" class="btn btn-export-all" id="export-all">
                    <i class="fas fa-download me-2"></i>Export All
                </button>
                <div class="btn-group">
                    <button type="button" class="btn btn-outline-secondary" id="export-copy" disabled title="Copy">
                        <i class="fas fa-copy"></i>
                    </button>
                    <button type="button" class="btn btn-outline-success" id="export-csv" disabled title="CSV">
                        <i class="fas fa-file-csv"></i>
                    </button>
                    <button type="button" class="btn btn-outline-info" id="export-excel" disabled title="Excel">
                        <i class="fas fa-file-excel"></i>
                    </button>
                    <button type="button" class="btn btn-outline-danger" id="export-pdf" disabled title="PDF">
                        <i class="fas fa-file-pdf"></i>
                    </button>
                    <button type="button" class="btn btn-outline-secondary" id="export-print" disabled title="Print">
                        <i class="fas fa-print"></i>
                    </button>
                </div>
            </div>
        </div>
        <div class="filter-body">
            <form method="GET" action="{{ route('employees.index') }}" class="filter-form">
                <div class="filter-grid">
                    <!-- Employee ID -->
                    <div class="filter-item">
                        <label class="filter-label">
                            <i class="fas fa-id-card me-1"></i>Employee ID
                        </label>
                        <select name="employee_id" class="form-select select2">
                            <option value="">All Employees</option>
                            @php
                                $edOptions = $employeeDetails->filter(function($d){
                                    $status = $d->employment_status ?? null;
                                    $hasNotice = !empty($d->notice_end_date);
                                    $hasProb = !empty($d->probation_end_date);
                                    if (in_array($status, ['notice','probation'])) return false;
                                    if ($hasNotice || $hasProb) return false;
                                    return true;
                                });
                            @endphp
                            @foreach($edOptions as $detail)
                                <option value="{{ $detail->employee_id }}" {{ request('employee_id') == $detail->employee_id ? 'selected' : '' }}>
                                    {{ $detail->employee_id }} - {{ $detail->user->name ?? 'N/A' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Designation -->
                    <div class="filter-item">
                        <label class="filter-label">
                            <i class="fas fa-briefcase me-1"></i>Designation
                        </label>
                        <select name="designation_id" class="form-select select2">
                            <option value="">All Designations</option>
                            @foreach($designations as $designation)
                                <option value="{{ $designation->id }}" {{ request('designation_id') == $designation->id ? 'selected' : '' }}>
                                    {{ $designation->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Search Name/Email -->
                    <div class="filter-item">
                        <label class="filter-label">
                            <i class="fas fa-user me-1"></i>Search Name/Email
                        </label>
                        <select name="user_id" class="form-select select2">
                            <option value="">Search Employee...</option>
                            @foreach($edOptions as $detail)
                                <option value="{{ $detail->user_id }}" {{ request('user_id') == $detail->user_id ? 'selected' : '' }}>
                                    {{ $detail->user->name ?? 'N/A' }} - {{ $detail->user->email ?? 'N/A' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Buttons -->
                    <div class="filter-actions">
                        <button type="submit" class="btn btn-apply">
                            <i class="fas fa-search me-2"></i>Apply Filters
                        </button>
                        <a href="{{ route('employees.index') }}" class="btn btn-reset">
                            <i class="fas fa-redo me-2"></i>Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Main Table Card - PERFECT ALIGNMENT --}}
    <div class="table-card animate-slideUp">
        <div class="table-header">
            <div class="table-title">
                <div class="title-icon-wrapper">
                    <i class="fas fa-list"></i>
                </div>
                <div class="title-text">
                    <h3>Employee List</h3>
                    <span class="employee-count">{{ $visibleEmployees->count() }} employees</span>
                </div>
            </div>
            <div class="table-actions">
                <button id="btn-bulk-delete" class="btn btn-bulk-delete" disabled>
                    <i class="fas fa-trash-alt me-2"></i>Delete Selected
                </button>
                <span id="bulk-selected-count" class="selected-badge">0 selected</span>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th width="50">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="selectAll">
                            </div>
                        </th>
                        <th width="100">Emp ID</th>
                        <th>Employee Name</th>
                        <th width="200">Email</th>
                        <th width="220">Role & Reporting</th>
                        <th width="120">Status</th>
                        <th width="80">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @if($visibleEmployees->isEmpty())
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="empty-state">
                                    <i class="fas fa-users fa-4x text-muted mb-3"></i>
                                    <h5 class="text-muted">No Employees Found</h5>
                                    <p class="text-muted mb-3">Try adjusting your filters or add new employees</p>
                                    <a href="{{ route('employees.create') }}" class="btn btn-primary">
                                        <i class="fas fa-user-plus me-2"></i>Add First Employee
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @else
                        @foreach($visibleEmployees as $employee)
                        <tr id="employee-row-{{ $employee->id }}"
                            class="@if(isset($employee->subordinate_count) && $employee->subordinate_count > 0) table-warning @endif">
                            <td>
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input employee-checkbox"
                                           value="{{ $employee->id }}"
                                           @if(isset($employee->subordinate_count) && $employee->subordinate_count > 0) disabled @endif
                                           data-employee-id="{{ $employee->employeeDetail?->employee_id ?? '-' }}"
                                           data-name="{{ htmlspecialchars($employee->name, ENT_QUOTES, 'UTF-8') }}"
                                           data-email="{{ $employee->email ?? '-' }}"
                                           data-designation="{{ htmlspecialchars($employee->employeeDetail?->designation?->name ?? '-', ENT_QUOTES, 'UTF-8') }}"
                                           data-reporting-to="{{ htmlspecialchars($employee->employeeDetail?->reportingTo?->name ?? 'N/A', ENT_QUOTES, 'UTF-8') }}"
                                           data-status="{{ $employee->employeeDetail?->status === 'Active' ? 'Active' : ($employee->employeeDetail?->status === 'Inactive' ? 'Inactive' : 'N/A') }}">
                                </div>
                            </td>
                            <td>
                                <span class="employee-id-badge">
                                    {{ $employee->employeeDetail?->employee_id ?? '-' }}
                                </span>
                            </td>
                            <td>
                                <div class="employee-info">
                                    @if(!empty($employee->profile_image))
                                        <img src="{{ asset($employee->profile_image) }}" alt="Profile" class="profile-image">
                                    @else
                                        <div class="profile-placeholder">
                                            <i class="fas fa-user"></i>
                                        </div>
                                    @endif
                                    <div class="employee-details">
                                        <h6 class="employee-name">{{ $employee->name }}</h6>
                                        <span class="employee-designation">{{ $employee->employeeDetail?->designation?->name ?? '-' }}</span>
                                        <div class="employee-badges">
                                            @php
                                                $detail = $employee->employeeDetail;
                                                $status = $detail?->employment_status ?? null;
                                                $designationName = strtolower($detail?->designation?->name ?? '');
                                            @endphp

                                            @if ($status === 'notice')
                                                <span class="badge bg-warning text-dark">
                                                    <i class="fas fa-clock me-1"></i> Notice
                                                </span>
                                            @elseif (strpos($designationName, 'intern') !== false)
                                                <span class="badge bg-info">
                                                    <i class="fas fa-graduation-cap me-1"></i> Intern
                                                </span>
                                            @else
                                                <span class="badge bg-primary">
                                                    <i class="fas fa-user-tie me-1"></i> Employee
                                                </span>
                                            @endif

                                            @if(isset($employee->subordinate_count) && $employee->subordinate_count > 0)
                                                <span class="badge bg-warning text-dark ms-1">
                                                    <i class="fas fa-users me-1"></i> Lead
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="email-wrapper">
                                    <i class="fas fa-envelope email-icon"></i>
                                    <a href="mailto:{{ $employee->email }}" class="email-link">
                                        {{ Str::limit($employee->email, 25) }}
                                    </a>
                                </div>
                            </td>
                            <td>
                                <div class="role-info">
                                    <span class="role-name">{{ $employee->employeeDetail?->designation?->name ?? '-' }}</span>
                                    <span class="reports-to">
                                        <i class="fas fa-user-friends"></i>
                                        {{ $employee->employeeDetail?->reportingTo?->name ?? 'N/A' }}
                                    </span>
                                </div>
                            </td>
                            <td>
                                @if($employee->employeeDetail?->status === 'Active')
                                    <span class="status-badge status-active">
                                        <span class="status-dot"></span>
                                        Active
                                    </span>
                                @elseif($employee->employeeDetail?->status === 'Inactive')
                                    <span class="status-badge status-inactive">
                                        <span class="status-dot"></span>
                                        Inactive
                                    </span>
                                @else
                                    <span class="status-badge status-unknown">N/A</span>
                                @endif
                            </td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-icon" type="button" data-bs-toggle="dropdown">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('employees.show', $employee->id) }}">
                                                <i class="fas fa-eye text-primary me-2"></i> View Details
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('employees.edit', $employee->id) }}">
                                                <i class="fas fa-edit text-info me-2"></i> Edit Profile
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            @if(isset($employee->subordinate_count) && $employee->subordinate_count > 0)
                                                <button class="dropdown-item text-warning blocked-delete-btn" type="button"
                                                    data-employee-name="{{ $employee->name }}"
                                                    data-subordinate-count="{{ $employee->subordinate_count }}"
                                                    data-employee-id="{{ $employee->id }}">
                                                    <i class="fas fa-ban me-2"></i> Delete Restricted
                                                </button>
                                            @else
                                                <form action="{{ route('employees.destroy', $employee->id) }}" method="POST"
                                                      onsubmit="return confirm('Are you sure you want to delete this employee?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="dropdown-item text-danger" type="submit">
                                                        <i class="fas fa-trash-alt me-2"></i> Delete Employee
                                                    </button>
                                                </form>
                                            @endif
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>

        {{-- Pagination & Show Entries - PERFECT ALIGNMENT --}}
        @if(!$visibleEmployees->isEmpty())
        <div class="table-footer">
            <div class="footer-left">
                <div class="show-entries">
                    <span>Show</span>
                    <select class="form-select form-select-sm" id="showEntries">
                        <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                        <option value="20" {{ request('per_page') == 20 ? 'selected' : '' }}>20</option>
                        <option value="30" {{ request('per_page') == 30 ? 'selected' : '' }}>30</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                    </select>
                    <span>entries</span>
                </div>
            </div>

            @if(method_exists($employees, 'total'))
            <div class="footer-center">
                <span class="pagination-info">
                    Showing {{ ($employees->currentPage() - 1) * $employees->perPage() + 1 }}
                    - {{ min($employees->currentPage() * $employees->perPage(), $employees->total()) }}
                    of {{ $employees->total() }}
                </span>
            </div>
            @endif

            @if(method_exists($employees, 'currentPage'))
            <div class="footer-right">
                <div class="pagination">
                    @if($employees->onFirstPage())
                        <button class="page-btn disabled">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                    @else
                        <a href="{{ $employees->previousPageUrl() . (request('per_page') ? '&per_page=' . request('per_page') : '') }}"
                           class="page-btn">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    @endif

                    @php
                        $currentPage = $employees->currentPage();
                        $lastPage = $employees->lastPage();
                        $start = max(1, $currentPage - 2);
                        $end = min($lastPage, $currentPage + 2);
                    @endphp

                    @for ($i = $start; $i <= $end; $i++)
                        @if ($i == $currentPage)
                            <span class="page-btn active">{{ $i }}</span>
                        @else
                            <a href="{{ $employees->url($i) . (request('per_page') ? '&per_page=' . request('per_page') : '') }}"
                               class="page-btn">{{ $i }}</a>
                        @endif
                    @endfor

                    @if($lastPage > $end)
                        <span class="page-dots">...</span>
                        <a href="{{ $employees->url($lastPage) . (request('per_page') ? '&per_page=' . request('per_page') : '') }}"
                           class="page-btn">{{ $lastPage }}</a>
                    @endif

                    @if($employees->hasMorePages())
                        <a href="{{ $employees->nextPageUrl() . (request('per_page') ? '&per_page=' . request('per_page') : '') }}"
                           class="page-btn">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    @else
                        <button class="page-btn disabled">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    @endif
                </div>
            </div>
            @endif
        </div>
        @endif
    </div>
</div>

{{-- LIGHT PURPLE THEME - SOFT & ANIMATED --}}
<style>
    /* ===== LIGHT PURPLE THEME - SOFT, ANIMATED & EYE-FRIENDLY ===== */

    /* Main Background - Light Purple Gradient */
    .employee-dashboard {
        padding: 30px 35px;
        background: linear-gradient(145deg, #f9f5ff 0%, #f3ebff 50%, #f1e7ff 100%);
        min-height: 100vh;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        position: relative;
        overflow-x: hidden;
    }

    /* Soft Purple Overlay Effect */
    .employee-dashboard::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: radial-gradient(circle at 10% 20%, rgba(170, 140, 250, 0.08) 0%, transparent 40%),
                    radial-gradient(circle at 90% 70%, rgba(150, 120, 250, 0.08) 0%, transparent 40%),
                    radial-gradient(circle at 30% 80%, rgba(180, 150, 255, 0.06) 0%, transparent 45%);
        pointer-events: none;
    }

    /* ===== ANIMATIONS ===== */
    @keyframes slideDown {
        0% { opacity: 0; transform: translateY(-20px); }
        100% { opacity: 1; transform: translateY(0); }
    }

    @keyframes slideUp {
        0% { opacity: 0; transform: translateY(30px); }
        100% { opacity: 1; transform: translateY(0); }
    }

    @keyframes fadeIn {
        0% { opacity: 0; }
        100% { opacity: 1; }
    }

    @keyframes float {
        0% { transform: translateY(0px); }
        50% { transform: translateY(-5px); }
        100% { transform: translateY(0px); }
    }

    @keyframes pulse {
        0% { box-shadow: 0 0 0 0 rgba(139, 92, 246, 0.4); }
        70% { box-shadow: 0 0 0 10px rgba(139, 92, 246, 0); }
        100% { box-shadow: 0 0 0 0 rgba(139, 92, 246, 0); }
    }

    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        10%, 30%, 50%, 70%, 90% { transform: translateX(-2px); }
        20%, 40%, 60%, 80% { transform: translateX(2px); }
    }

    .animate-slideDown { animation: slideDown 0.6s ease forwards; }
    .animate-slideUp { animation: slideUp 0.6s ease forwards; }
    .animate-fadeIn { animation: fadeIn 0.8s ease forwards; }
    .animate-float { animation: float 4s ease-in-out infinite; }
    .animate-pulse { animation: pulse 2s infinite; }
    .animate-shake { animation: shake 0.5s ease forwards; }

    /* Breadcrumb - Light Glassmorphism */
    .breadcrumb-wrapper {
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid rgba(139, 92, 246, 0.15);
        border-radius: 16px;
        padding: 16px 24px;
        margin-bottom: 30px;
        box-shadow: 0 4px 15px rgba(139, 92, 246, 0.05);
    }

    .breadcrumb {
        display: flex;
        flex-wrap: wrap;
        padding: 0;
        margin-bottom: 0;
        list-style: none;
        background: transparent;
    }

    .breadcrumb-item {
        font-size: 0.95rem;
        font-weight: 500;
    }

    .breadcrumb-item a {
        color: #6d28d9;
        text-decoration: none;
        display: flex;
        align-items: center;
        transition: all 0.3s ease;
    }

    .breadcrumb-item a:hover {
        color: #5b21b6;
    }

    .breadcrumb-item.active {
        color: #4c1d95;
        font-weight: 600;
    }

    .breadcrumb-item + .breadcrumb-item::before {
        content: "›";
        color: #a78bfa;
        font-size: 1.2rem;
        line-height: 1;
        margin: 0 10px;
    }

    /* Header Section - Light Glassmorphism */
    .header-section {
        background: rgba(255, 255, 255, 0.8);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid rgba(139, 92, 246, 0.2);
        border-radius: 20px;
        padding: 25px 30px;
        margin-bottom: 35px;
        box-shadow: 0 8px 25px rgba(139, 92, 246, 0.08);
    }

    .header-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 20px;
    }

    .title-wrapper {
        display: flex;
        align-items: center;
        gap: 18px;
    }

    .icon-circle {
        width: 65px;
        height: 65px;
        background: linear-gradient(145deg, #c4b5fd, #a78bfa);
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.8rem;
        box-shadow: 0 10px 20px rgba(167, 139, 250, 0.25);
    }

    .title-wrapper h1 {
        font-size: 1.9rem;
        font-weight: 700;
        color: #2d1b4e;
        letter-spacing: -0.02em;
        margin: 0;
    }

    .subtitle {
        color: #6b4e9e;
        font-size: 0.95rem;
        margin-top: 5px;
    }

    /* Action Buttons */
    .action-buttons {
        display: flex;
        gap: 12px;
        align-items: center;
    }

    .btn {
        padding: 12px 24px;
        border-radius: 12px;
        font-weight: 600;
        font-size: 0.95rem;
        display: flex;
        align-items: center;
        border: none;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .btn-outline-light {
        background: white;
        border: 1px solid #e0d7ff;
        color: #5b4b7a;
    }

    .btn-outline-light:hover {
        background: #f5f0ff;
        border-color: #a78bfa;
        color: #6d28d9;
        transform: translateY(-2px);
        box-shadow: 0 8px 15px rgba(167, 139, 250, 0.15);
    }

    .btn-add {
        background: linear-gradient(145deg, #34d399, #10b981);
        color: white;
        box-shadow: 0 8px 18px rgba(16, 185, 129, 0.2);
    }

    .btn-add:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 25px rgba(16, 185, 129, 0.3);
    }

    .btn-invite {
        background: linear-gradient(145deg, #c084fc, #a78bfa);
        color: white;
        box-shadow: 0 8px 18px rgba(167, 139, 250, 0.25);
    }

    .btn-invite:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 25px rgba(167, 139, 250, 0.35);
    }

    /* Stats Grid - PERFECT 5 COLUMN ALIGNMENT */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 22px;
        margin-bottom: 35px;
    }

    .stat-card {
        background: white;
        border: 1px solid rgba(167, 139, 250, 0.2);
        border-radius: 20px;
        padding: 22px 18px;
        display: flex;
        align-items: center;
        gap: 16px;
        box-shadow: 0 8px 20px rgba(139, 92, 246, 0.06);
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        position: relative;
        overflow: hidden;
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 5px;
        height: 100%;
        background: linear-gradient(to bottom, #c084fc, #a78bfa);
    }

    .stat-card.total::before { background: linear-gradient(to bottom, #818cf8, #6366f1); }
    .stat-card.active::before { background: linear-gradient(to bottom, #34d399, #10b981); }
    .stat-card.inactive::before { background: linear-gradient(to bottom, #f87171, #ef4444); }
    .stat-card.notice::before { background: linear-gradient(to bottom, #fbbf24, #f59e0b); }
    .stat-card.interns::before { background: linear-gradient(to bottom, #60a5fa, #3b82f6); }

    .stat-card:hover {
        transform: translateY(-6px) scale(1.02);
        box-shadow: 0 15px 35px rgba(139, 92, 246, 0.12);
        border-color: rgba(167, 139, 250, 0.4);
    }

    .stat-icon {
        width: 55px;
        height: 55px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.6rem;
        flex-shrink: 0;
    }

    .stat-card.total .stat-icon {
        background: #e0e7ff;
        color: #4f46e5;
    }

    .stat-card.active .stat-icon {
        background: #d1fae5;
        color: #059669;
    }

    .stat-card.inactive .stat-icon {
        background: #fee2e2;
        color: #b91c1c;
    }

    .stat-card.notice .stat-icon {
        background: #fef3c7;
        color: #d97706;
    }

    .stat-card.interns .stat-icon {
        background: #dbeafe;
        color: #2563eb;
    }

    .stat-content {
        display: flex;
        flex-direction: column;
        flex: 1;
    }

    .stat-label {
        font-size: 0.8rem;
        font-weight: 600;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 4px;
    }

    .stat-value {
        font-size: 2rem;
        font-weight: 700;
        color: #1f2937;
        line-height: 1;
    }

    .stat-glow {
        position: absolute;
        top: -20px;
        right: -20px;
        width: 100px;
        height: 100px;
        border-radius: 50%;
        filter: blur(30px);
        opacity: 0.1;
        pointer-events: none;
    }

    .stat-card.total .stat-glow { background: #6366f1; }
    .stat-card.active .stat-glow { background: #10b981; }
    .stat-card.inactive .stat-glow { background: #ef4444; }
    .stat-card.notice .stat-glow { background: #f59e0b; }
    .stat-card.interns .stat-glow { background: #3b82f6; }

    /* Filter & Export Card - PERFECT ALIGNMENT */
    .filter-export-card {
        background: white;
        border: 1px solid rgba(167, 139, 250, 0.2);
        border-radius: 20px;
        margin-bottom: 30px;
        overflow: hidden;
        box-shadow: 0 8px 25px rgba(139, 92, 246, 0.06);
        transition: all 0.3s ease;
    }

    .filter-export-card:hover {
        box-shadow: 0 12px 35px rgba(139, 92, 246, 0.1);
        border-color: rgba(167, 139, 250, 0.3);
    }

    .filter-header {
        padding: 18px 25px;
        background: #faf7ff;
        border-bottom: 1px solid rgba(167, 139, 250, 0.15);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
    }

    .header-left {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .header-icon-wrapper {
        width: 44px;
        height: 44px;
        background: #ede9fe;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #8b5cf6;
        font-size: 1.2rem;
    }

    .header-text h3 {
        font-size: 1.1rem;
        font-weight: 600;
        color: #2d1b4e;
        margin: 0;
    }

    .header-text p {
        font-size: 0.8rem;
        color: #6b4e9e;
        margin: 2px 0 0;
    }

    .header-right {
        display: flex;
        align-items: center;
        gap: 15px;
        flex-wrap: wrap;
    }

    .selected-badge {
        padding: 6px 14px;
        background: #ede9fe;
        color: #6d28d9;
        border-radius: 100px;
        font-size: 0.85rem;
        font-weight: 600;
    }

    .btn-export-all {
        background: linear-gradient(145deg, #c084fc, #a78bfa);
        color: white;
        padding: 8px 20px;
        border-radius: 10px;
        font-size: 0.9rem;
        border: none;
        box-shadow: 0 4px 12px rgba(167, 139, 250, 0.2);
    }

    .btn-export-all:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(167, 139, 250, 0.3);
    }

    .btn-group {
        display: flex;
        gap: 5px;
        background: white;
        padding: 5px;
        border-radius: 12px;
        border: 1px solid rgba(167, 139, 250, 0.2);
    }

    .btn-group .btn {
        width: 36px;
        height: 36px;
        padding: 0;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: transparent;
        border: none;
        color: #6b7280;
    }

    .btn-group .btn:hover:not(:disabled) {
        background: #f5f0ff;
        color: #8b5cf6;
        transform: scale(1.1);
    }

    .btn-group .btn:disabled {
        opacity: 0.4;
        cursor: not-allowed;
    }

    .filter-body {
        padding: 25px;
    }

    .filter-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        align-items: flex-end;
    }

    .filter-item {
        display: flex;
        flex-direction: column;
    }

    .filter-label {
        font-size: 0.9rem;
        font-weight: 600;
        color: #4b5563;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
    }

    .filter-select {
        width: 100%;
        height: 44px;
        background: white;
        border: 1px solid #e0d7ff;
        border-radius: 10px;
        padding: 0 16px;
        font-size: 0.9rem;
        color: #1f2937;
        transition: all 0.2s ease;
    }

    .filter-select:focus {
        border-color: #a78bfa;
        box-shadow: 0 0 0 3px rgba(167, 139, 250, 0.1);
        outline: none;
    }

    .filter-actions {
        display: flex;
        gap: 10px;
        align-items: center;
    }

    .btn-apply {
        background: linear-gradient(145deg, #8b5cf6, #7c3aed);
        color: white;
        padding: 12px 20px;
        border-radius: 10px;
        font-weight: 600;
        flex: 1;
        border: none;
    }

    .btn-apply:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(124, 58, 237, 0.25);
    }

    .btn-reset {
        background: white;
        color: #6b7280;
        padding: 12px 20px;
        border-radius: 10px;
        font-weight: 600;
        border: 1px solid #e0d7ff;
        flex: 1;
        text-decoration: none;
        text-align: center;
    }

    .btn-reset:hover {
        background: #f9fafb;
        color: #1f2937;
        border-color: #a78bfa;
    }

    /* Table Card - PERFECT ALIGNMENT */
    .table-card {
        background: white;
        border: 1px solid rgba(167, 139, 250, 0.2);
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 8px 25px rgba(139, 92, 246, 0.06);
        transition: all 0.3s ease;
    }

    .table-card:hover {
        box-shadow: 0 12px 35px rgba(139, 92, 246, 0.1);
        border-color: rgba(167, 139, 250, 0.3);
    }

    .table-header {
        padding: 18px 25px;
        background: #faf7ff;
        border-bottom: 1px solid rgba(167, 139, 250, 0.15);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
    }

    .table-title {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .title-icon-wrapper {
        width: 44px;
        height: 44px;
        background: #f3e8ff;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #9b5de5;
        font-size: 1.2rem;
    }

    .title-text h3 {
        font-size: 1.1rem;
        font-weight: 600;
        color: #2d1b4e;
        margin: 0;
    }

    .employee-count {
        font-size: 0.8rem;
        color: #6b4e9e;
    }

    .table-actions {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .btn-bulk-delete {
        background: #fee2e2;
        color: #b91c1c;
        padding: 8px 20px;
        border-radius: 10px;
        font-size: 0.9rem;
        border: 1px solid #fecaca;
    }

    .btn-bulk-delete:hover:not(:disabled) {
        background: #fecaca;
        color: #991b1b;
        transform: translateY(-2px);
    }

    .btn-bulk-delete:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    /* Table Styles */
    .table-responsive {
        overflow-x: auto;
    }

    .table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 0;
    }

    .table thead th {
        background: #faf7ff;
        color: #5b4b7a;
        font-weight: 600;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 18px 16px;
        border-bottom: 2px solid rgba(167, 139, 250, 0.2);
        text-align: left;
        white-space: nowrap;
    }

    .table tbody td {
        padding: 18px 16px;
        border-bottom: 1px solid rgba(167, 139, 250, 0.1);
        color: #374151;
        vertical-align: middle;
    }

    .table tbody tr {
        transition: all 0.3s ease;
    }

    .table tbody tr:hover {
        background: #fcf9ff;
    }

    .table tbody tr.selected {
        background: #f5f0ff;
        border-left: 3px solid #8b5cf6;
    }

    .table-warning {
        background-color: #fffbeb !important;
    }

    .table-warning:hover {
        background-color: #fef3c7 !important;
    }

    /* Employee ID Badge */
    .employee-id-badge {
        display: inline-block;
        padding: 6px 12px;
        background: #f3f4f6;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        font-size: 0.8rem;
        font-weight: 600;
        color: #4b5563;
    }

    /* Employee Info */
    .employee-info {
        display: flex;
        align-items: flex-start;
        gap: 12px;
    }

    .profile-image {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        object-fit: cover;
        border: 2px solid white;
        box-shadow: 0 4px 10px rgba(139, 92, 246, 0.1);
    }

    .profile-placeholder {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: #ede9fe;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #8b5cf6;
        font-size: 1.1rem;
        border: 2px solid white;
        box-shadow: 0 4px 10px rgba(139, 92, 246, 0.1);
    }

    .employee-details {
        flex: 1;
    }

    .employee-name {
        font-size: 0.95rem;
        font-weight: 600;
        color: #1f2937;
        margin: 0 0 4px;
    }

    .employee-designation {
        font-size: 0.8rem;
        color: #6b7280;
        display: block;
        margin-bottom: 6px;
    }

    .employee-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
    }

    .badge {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        border-radius: 100px;
        font-size: 0.7rem;
        font-weight: 600;
    }

    .badge.bg-primary {
        background: #ede9fe !important;
        color: #6d28d9;
    }

    .badge.bg-info {
        background: #dbeafe !important;
        color: #2563eb;
    }

    .badge.bg-warning {
        background: #fef3c7 !important;
        color: #b45309;
    }

    /* Email */
    .email-wrapper {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .email-icon {
        color: #8b5cf6;
        font-size: 0.9rem;
    }

    .email-link {
        color: #6b7280;
        text-decoration: none;
        font-size: 0.9rem;
        transition: all 0.2s ease;
    }

    .email-link:hover {
        color: #6d28d9;
        text-decoration: underline;
    }

    /* Role Info */
    .role-info {
        display: flex;
        flex-direction: column;
    }

    .role-name {
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 4px;
        font-size: 0.9rem;
    }

    .reports-to {
        font-size: 0.8rem;
        color: #6b7280;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    /* Status Badge */
    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 6px 14px;
        border-radius: 100px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .status-active {
        background: #d1fae5;
        color: #065f46;
        border: 1px solid #a7f3d0;
    }

    .status-inactive {
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid #fecaca;
    }

    .status-unknown {
        background: #f3f4f6;
        color: #4b5563;
        border: 1px solid #e5e7eb;
    }

    .status-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        margin-right: 8px;
    }

    .status-active .status-dot {
        background: #10b981;
        box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2);
    }

    .status-inactive .status-dot {
        background: #ef4444;
        box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.2);
    }

    /* Action Dropdown */
    .btn-icon {
        width: 36px;
        height: 36px;
        padding: 0;
        border-radius: 10px;
        background: #f3f4f6;
        border: 1px solid #e5e7eb;
        color: #6b7280;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
    }

    .btn-icon:hover {
        background: #ede9fe;
        border-color: #a78bfa;
        color: #6d28d9;
        transform: scale(1.1);
    }

    .dropdown-menu {
        background: white;
        border: 1px solid rgba(167, 139, 250, 0.3);
        border-radius: 14px;
        padding: 8px;
        box-shadow: 0 20px 40px rgba(139, 92, 246, 0.1);
    }

    .dropdown-item {
        padding: 10px 16px;
        color: #374151;
        font-size: 0.9rem;
        border-radius: 8px;
        transition: all 0.2s ease;
    }

    .dropdown-item i {
        width: 20px;
        margin-right: 10px;
    }

    .dropdown-item:hover {
        background: #f5f0ff;
        color: #6d28d9;
    }

    .dropdown-divider {
        border-top: 1px solid #f0e7ff;
        margin: 8px 0;
    }

    /* Empty State */
    .empty-state {
        padding: 60px 20px;
        text-align: center;
    }

    .empty-state i {
        color: #d4c2ff;
    }

    .empty-state h5 {
        color: #6b4e9e;
        font-weight: 600;
    }

    .empty-state p {
        color: #9ca3af;
    }

    /* Table Footer - PERFECT ALIGNMENT */
    .table-footer {
        padding: 18px 25px;
        background: #faf7ff;
        border-top: 1px solid rgba(167, 139, 250, 0.15);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
    }

    .footer-left, .footer-center, .footer-right {
        display: flex;
        align-items: center;
    }

    .show-entries {
        display: flex;
        align-items: center;
        gap: 10px;
        color: #6b7280;
        font-size: 0.9rem;
    }

    .show-entries select {
        background: white;
        border: 1px solid #e0d7ff;
        border-radius: 8px;
        color: #1f2937;
        padding: 6px 12px;
        width: 70px;
    }

    .show-entries select:focus {
        border-color: #a78bfa;
        box-shadow: 0 0 0 3px rgba(167, 139, 250, 0.1);
    }

    .pagination-info {
        color: #6b7280;
        font-size: 0.9rem;
    }

    .pagination {
        display: flex;
        gap: 5px;
        align-items: center;
    }

    .page-btn {
        min-width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        background: white;
        border: 1px solid #e0d7ff;
        color: #6b7280;
        font-size: 0.9rem;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .page-btn:hover:not(.disabled) {
        background: #ede9fe;
        border-color: #a78bfa;
        color: #6d28d9;
        transform: scale(1.05);
    }

    .page-btn.active {
        background: #8b5cf6;
        border-color: #8b5cf6;
        color: white;
    }

    .page-btn.disabled {
        background: #f3f4f6;
        color: #9ca3af;
        border-color: #e5e7eb;
        cursor: not-allowed;
    }

    .page-dots {
        color: #9ca3af;
        padding: 0 5px;
    }

    /* Modal Styles */
    .modal-content {
        background: white;
        border: 1px solid rgba(167, 139, 250, 0.3);
        border-radius: 20px;
    }

    .modal-header {
        background: #faf7ff;
        border-bottom: 1px solid rgba(167, 139, 250, 0.2);
        padding: 20px 25px;
    }

    .modal-header.bg-warning {
        background: #fffbeb !important;
    }

    .modal-title {
        color: #2d1b4e;
        font-weight: 600;
    }

    .modal-body {
        padding: 25px;
    }

    .modal-footer {
        border-top: 1px solid rgba(167, 139, 250, 0.2);
        padding: 20px 25px;
    }

    .form-control, .input-group-text {
        background: white;
        border: 1px solid #e0d7ff;
        color: #1f2937;
    }

    .form-control:focus {
        border-color: #a78bfa;
        box-shadow: 0 0 0 3px rgba(167, 139, 250, 0.1);
    }

    .input-group-text {
        background: #f5f0ff;
        color: #8b5cf6;
    }

    .nav-tabs {
        border-bottom: 1px solid #e0d7ff;
    }

    .nav-tabs .nav-link {
        color: #6b7280;
        border: none;
        padding: 12px 20px;
    }

    .nav-tabs .nav-link.active {
        background: transparent;
        color: #6d28d9;
        border-bottom: 2px solid #8b5cf6;
    }

    .list-group-item {
        background: white;
        border: 1px solid #e0d7ff;
        color: #374151;
    }

    /* Form Check */
    .form-check-input {
        background: white;
        border: 2px solid #d4c2ff;
    }

    .form-check-input:checked {
        background-color: #8b5cf6;
        border-color: #8b5cf6;
    }

    .form-check-input:focus {
        box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);
        border-color: #8b5cf6;
    }

    .form-check-input:disabled {
        background: #f3f4f6;
        border-color: #e5e7eb;
    }

    /* Select2 Customization */
    .select2-container--default .select2-selection--single {
        background: white;
        border: 1px solid #e0d7ff;
        border-radius: 10px;
        height: 44px;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #1f2937;
        line-height: 44px;
        padding-left: 16px;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 42px;
    }

    .select2-dropdown {
        background: white;
        border: 1px solid #e0d7ff;
    }

    .select2-results__option {
        color: #1f2937;
    }

    .select2-results__option--highlighted {
        background: #f5f0ff !important;
        color: #6d28d9 !important;
    }

    /* Responsive - PERFECT ALIGNMENT */
    @media (max-width: 1400px) {
        .stats-grid {
            grid-template-columns: repeat(3, 1fr);
        }

        .filter-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 1200px) {
        .employee-dashboard {
            padding: 25px 30px;
        }
    }

    @media (max-width: 992px) {
        .employee-dashboard {
            padding: 20px 25px;
        }

        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .filter-grid {
            grid-template-columns: 1fr;
        }

        .header-content {
            flex-direction: column;
            align-items: flex-start;
        }

        .action-buttons {
            width: 100%;
            justify-content: flex-start;
        }
    }

    @media (max-width: 768px) {
        .employee-dashboard {
            padding: 15px 20px;
        }

        .stats-grid {
            grid-template-columns: 1fr;
        }

        .table thead {
            display: none;
        }

        .table tbody tr {
            display: block;
            margin-bottom: 20px;
            border: 1px solid rgba(167, 139, 250, 0.2);
            border-radius: 16px;
            padding: 20px;
        }

        .table tbody td {
            display: block;
            padding: 10px 0;
            border: none;
            position: relative;
            padding-left: 45%;
        }

        .table tbody td:before {
            content: attr(data-label);
            position: absolute;
            left: 0;
            width: 40%;
            font-weight: 600;
            color: #5b4b7a;
        }

        .table-footer {
            flex-direction: column;
            align-items: center;
        }

        .footer-left, .footer-center, .footer-right {
            width: 100%;
            justify-content: center;
        }
    }

    /* Custom Scrollbar */
    ::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }

    ::-webkit-scrollbar-track {
        background: #f5f0ff;
    }

    ::-webkit-scrollbar-thumb {
        background: #d4c2ff;
        border-radius: 10px;
    }

    ::-webkit-scrollbar-thumb:hover {
        background: #b79aff;
    }
</style>

{{-- JavaScript - COMPLETELY UNCHANGED FUNCTIONALITY --}}
@push('js')
<script>
$(document).ready(function () {
    // ===== ALL EXISTING FUNCTIONALITY - 100% UNCHANGED =====
    // Store selected employee data
    let selectedEmployees = [];
    let showEntries = localStorage.getItem('employeeShowEntries') || '10';

    // Set initial show entries value
    $('#showEntries').val(showEntries);

    // Show entries change
    $('#showEntries').on('change', function() {
        const value = $(this).val();
        localStorage.setItem('employeeShowEntries', value);
        const url = new URL(window.location.href);
        url.searchParams.set('per_page', value);
        window.location.href = url.toString();
    });

    // Select all checkboxes
    $('#selectAll').on('click', function() {
        const isChecked = $(this).prop('checked');
        const enabledCheckboxes = $('.employee-checkbox:not(:disabled)');
        enabledCheckboxes.prop('checked', isChecked).trigger('change');
    });

    // Individual checkbox change
    $(document).on('change', '.employee-checkbox', function() {
        updateSelectedEmployees();
        updateSelectAllCheckbox();
    });

    // Row click to select
    $(document).on('click', 'tbody tr', function(e) {
        if ($(e.target).is('input[type="checkbox"]') ||
            $(e.target).closest('.dropdown, a, button, form').length ||
            $(e.target).is('a, button, .dropdown-item, img, i')) {
            return;
        }
        const checkbox = $(this).find('.employee-checkbox:not(:disabled)');
        if (checkbox.length) {
            checkbox.prop('checked', !checkbox.prop('checked')).trigger('change');
        }
    });

    // Update select all checkbox
    function updateSelectAllCheckbox() {
        const totalCheckboxes = $('.employee-checkbox:not(:disabled)').length;
        const checkedCheckboxes = $('.employee-checkbox:checked:not(:disabled)').length;
        const selectAll = $('#selectAll');

        if (totalCheckboxes === 0) {
            selectAll.prop('checked', false).prop('indeterminate', false);
            return;
        }

        if (checkedCheckboxes === 0) {
            selectAll.prop('checked', false).prop('indeterminate', false);
        } else if (checkedCheckboxes === totalCheckboxes) {
            selectAll.prop('checked', true).prop('indeterminate', false);
        } else {
            selectAll.prop('checked', false).prop('indeterminate', true);
        }
    }

    // Update selected employees
    function updateSelectedEmployees() {
        selectedEmployees = [];
        $('.employee-checkbox:checked:not(:disabled)').each(function() {
            selectedEmployees.push({
                id: $(this).val(),
                data: {
                    employee_id: $(this).data('employee-id') || '-',
                    name: cleanHTML($(this).data('name')) || '-',
                    email: $(this).data('email') || '-',
                    designation: cleanHTML($(this).data('designation')) || '-',
                    reporting_to: cleanHTML($(this).data('reporting-to')) || 'N/A',
                    status: $(this).data('status') || 'N/A'
                }
            });
        });

        const selectedCount = selectedEmployees.length;
        $('#export-selected-count').text(selectedCount + ' selected');
        $('#bulk-selected-count').text(selectedCount + ' selected');

        const exportButtons = ['#export-copy', '#export-csv', '#export-excel', '#export-pdf', '#export-print'];
        exportButtons.forEach(btn => {
            $(btn).prop('disabled', selectedCount === 0);
        });

        $('#btn-bulk-delete').prop('disabled', selectedCount === 0);
        $('tbody tr').removeClass('selected');
        $('.employee-checkbox:checked:not(:disabled)').closest('tr').addClass('selected');
    }

    // Clean HTML function
    function cleanHTML(text) {
        if (!text) return '';
        return String(text).replace(/<[^>]*>/g, '').replace(/&nbsp;/g, ' ').trim();
    }

    // Export functions
    function getDataForExport(exportAll = false) {
        const data = [];
        const headers = ['Employee ID', 'Name', 'Email', 'Designation', 'Reporting To', 'Status'];
        data.push(headers);

        if (exportAll) {
            $('.employee-checkbox:not(:disabled)').each(function() {
                data.push([
                    $(this).data('employee-id') || '-',
                    cleanHTML($(this).data('name')) || '-',
                    $(this).data('email') || '-',
                    cleanHTML($(this).data('designation')) || '-',
                    cleanHTML($(this).data('reporting-to')) || 'N/A',
                    $(this).data('status') || 'N/A'
                ]);
            });
        } else {
            selectedEmployees.forEach(emp => {
                data.push([
                    emp.data.employee_id,
                    emp.data.name,
                    emp.data.email,
                    emp.data.designation,
                    emp.data.reporting_to,
                    emp.data.status
                ]);
            });
        }
        return data;
    }

    // Copy
    $('#export-copy').on('click', function() {
        if (selectedEmployees.length === 0) {
            alert('Please select at least one row to copy.');
            return;
        }
        const data = getDataForExport(false);
        const csvContent = data.map(row =>
            row.map(cell => `"${String(cell).replace(/"/g, '""')}"`).join('\t')
        ).join('\n');
        navigator.clipboard.writeText(csvContent).then(() => {
            alert('Selected rows copied to clipboard!');
        });
    });

    // CSV
    $('#export-csv').on('click', function() {
        if (selectedEmployees.length === 0) {
            alert('Please select at least one row to export.');
            return;
        }
        const data = getDataForExport(false);
        const csvContent = data.map(row =>
            row.map(cell => `"${String(cell).replace(/"/g, '""')}"`).join(',')
        ).join('\n');
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = `employees_${new Date().toISOString().split('T')[0]}.csv`;
        link.click();
    });

    // Excel
    $('#export-excel').on('click', function() {
        if (selectedEmployees.length === 0) {
            alert('Please select at least one row to export.');
            return;
        }
        const data = getDataForExport(false);
        const wb = XLSX.utils.book_new();
        const ws = XLSX.utils.aoa_to_sheet(data);
        XLSX.utils.book_append_sheet(wb, ws, "Employees");
        XLSX.writeFile(wb, `employees_${new Date().toISOString().split('T')[0]}.xlsx`);
    });

    // PDF
    $('#export-pdf').on('click', function() {
        if (selectedEmployees.length === 0) {
            alert('Please select at least one row to export.');
            return;
        }
        const data = getDataForExport(false);
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF('landscape');
        doc.text('Employee List', 14, 15);
        doc.autoTable({
            head: [data[0]],
            body: data.slice(1),
            startY: 30,
            theme: 'grid',
            headStyles: { fillColor: [124, 58, 237] }
        });
        doc.save(`employees_${new Date().toISOString().split('T')[0]}.pdf`);
    });

    // Print
    $('#export-print').on('click', function() {
        if (selectedEmployees.length === 0) {
            alert('Please select at least one row to print.');
            return;
        }
        window.print();
    });

    // Export all
    $('#export-all').on('click', function() {
        const totalEmployees = $('.employee-checkbox:not(:disabled)').length;
        if (totalEmployees === 0) {
            alert('No employees available to export.');
            return;
        }
        if (!confirm(`Export ALL ${totalEmployees} employees to Excel?`)) return;
        const data = getDataForExport(true);
        const wb = XLSX.utils.book_new();
        const ws = XLSX.utils.aoa_to_sheet(data);
        XLSX.utils.book_append_sheet(wb, ws, "All Employees");
        XLSX.writeFile(wb, `all_employees_${new Date().toISOString().split('T')[0]}.xlsx`);
    });

    // Bulk delete
    $('#btn-bulk-delete, #bulkDeleteTrigger').on('click', function() {
        let selectedIds = $('.employee-checkbox:checked:not(:disabled)').map(function() {
            return $(this).val();
        }).get();

        if (!selectedIds.length) {
            alert('Please select at least one employee to delete.');
            return;
        }

        if (!confirm(`Permanently delete ${selectedIds.length} selected employee(s)?`)) return;

        $.ajax({
            url: '{{ route("employees.bulk.delete") }}',
            type: 'DELETE',
            data: {
                _token: '{{ csrf_token() }}',
                employee_ids: selectedIds
            },
            success: function() {
                alert('Employees deleted successfully');
                location.reload();
            },
            error: function() {
                alert('Failed to delete employees.');
            }
        });
    });

    // Invite by email
    $('#inviteEmailForm').on('submit', function(e) {
        e.preventDefault();
        const email = $('#inviteEmail').val().trim();
        const message = $('#inviteMessage').val().trim();

        $.ajax({
            url: '{{ route("employees.sendInvite") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                email: email,
                message: message
            },
            success: function(res) {
                alert('Invitation sent successfully!');
                $('#inviteModal').modal('hide');
                $('#inviteEmailForm')[0].reset();
            },
            error: function(xhr) {
                alert(xhr?.responseJSON?.message || 'Failed to send invite.');
            }
        });
    });

    // Generate invite link
    $('#createLinkBtn').on('click', function() {
        const token = Math.random().toString(36).substring(2, 15);
        const domainRestricted = $('#domainEmail').is(':checked');
        const link = `{{ url('/') }}/register?invitation=${token}&restrict=${domainRestricted}`;
        $('#linkContainer').show();
        $('#inviteLink').val(link);
    });

    // Copy link
    $('#copyLinkBtn').on('click', function() {
        $('#inviteLink').select();
        document.execCommand('copy');
        alert('Link copied to clipboard!');
    });

    // Share link
    $('#shareLinkBtn').on('click', function() {
        const link = $('#inviteLink').val();
        window.location.href = `mailto:?subject=Join Xinksoft Technologies&body=${encodeURIComponent(link)}`;
    });

    // Blocked delete
    $(document).on('click', '.blocked-delete-btn', function (e) {
        e.preventDefault();
        const name = $(this).data('employee-name');
        const id = $(this).data('employee-id');
        const subordinateCount = $(this).data('subordinate-count');
        $('#blockedEmployeeName').text(name);
        $('#blockedEmployeeReason').text(`Cannot delete because ${subordinateCount} team member(s) report to this employee.`);
        $('#blocked-view-subordinates').attr('href', '{{ url("/") }}/employees/' + id);
        new bootstrap.Modal(document.getElementById('blockedDeleteModal')).show();
    });

    // Initialize
    updateSelectedEmployees();
    updateSelectAllCheckbox();

    // Mobile labels
    if ($(window).width() < 768) {
        $('#employeeTable thead th').each(function(index) {
            const headerText = $(this).text().trim();
            $('#employeeTable tbody tr').each(function() {
                $(this).find('td').eq(index).attr('data-label', headerText);
            });
        });
    }
});
</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
@endpush

@endsection
