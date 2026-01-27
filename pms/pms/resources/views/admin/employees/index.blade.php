
@extends('admin.layout.app')

@section('content')
<div class="container-fluid mt-4 px-3 px-md-4">

    {{-- Breadcrumb --}}
    <nav aria-label="breadcrumb" class="mb-4">
      <ol class="breadcrumb bg-light p-3 rounded">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none"><i class="fas fa-home me-1"></i> Dashboard</a></li>
        <li class="breadcrumb-item active" aria-current="page"><i class="fas fa-users me-1"></i> Employees</li>
      </ol>
    </nav>

    {{-- Pre-calculate counts for statistics --}}
    @php
        // Calculate employee counts for statistics cards
        $totalEmployees = $employees->count();
        $activeEmployees = $employees->where('employeeDetail.status', 'Active')->count();
        $noticeEmployees = $employees->filter(function($emp) {
            return $emp->employeeDetail?->employment_status === 'notice';
        })->count();
        $internEmployees = $employees->filter(function($emp) {
            $designation = strtolower($emp->employeeDetail?->designation?->name ?? '');
            return strpos($designation, 'intern') !== false;
        })->count();

        // Filter visible employees (for table display)
        $visibleEmployees = $employees->filter(function($emp) {
            $detail = $emp->employeeDetail ?? null;
            $status = $detail?->employment_status ?? null;
            $hasNotice = !empty($detail?->notice_end_date);
            $hasProbation = !empty($detail?->probation_end_date);

            if (in_array($status, ['notice','probation'])) {
                return false;
            }
            if ($hasNotice || $hasProbation) {
                return false;
            }
            return true;
        });
    @endphp

    {{-- Page Header with Actions --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div class="mb-3 mb-md-0">
            <h2 class="h4 fw-bold text-primary mb-1"><i class="fas fa-users me-2"></i>Employee Management</h2>
            <p class="text-muted mb-0">Manage all employees, their details, and permissions</p>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <!-- Quick Actions -->
            <div class="d-flex align-items-center gap-2 bg-light p-2 rounded">
                <select class="form-select form-select-sm border-0 bg-transparent" id="quick-action-type" style="min-width: 140px;">
                    <option value="">Quick Actions</option>
                    <option value="change-status">Change Status</option>
                    <option value="delete">Bulk Delete</option>
                </select>

                <select class="form-select form-select-sm border-0 bg-transparent d-none" id="quick-action-status" style="min-width: 110px;">
                    <option value="Active">Activate</option>
                    <option value="Inactive">Deactivate</option>
                </select>

                <button class="btn btn-sm btn-primary rounded-pill px-3" id="quick-action-apply" disabled>
                    <i class="fas fa-play me-1"></i> Apply
                </button>
            </div>

            <!-- Add Employee -->
            <a href="{{ route('employees.create') }}" class="btn btn-success d-flex align-items-center">
                <i class="fas fa-user-plus me-2"></i> Add Employee
            </a>

            <!-- Invite Employee -->
            <button type="button" class="btn btn-primary d-flex align-items-center"
                    data-bs-toggle="modal" data-bs-target="#inviteModal">
                <i class="fas fa-envelope me-2"></i> Invite
            </button>
        </div>
    </div>

    {{-- Stats Cards - FIXED TEXT VISIBILITY --}}
    <div class="row mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm bg-gradient-primary text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-subtitle mb-2 fw-bold" style="opacity: 0.95;">Total Employees</h6>
                            <h3 class="mb-0 fw-bold">{{ $totalEmployees }}</h3>
                        </div>
                        <i class="fas fa-users fa-2x" style="opacity: 0.8;"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm bg-gradient-success text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-subtitle mb-2 fw-bold" style="opacity: 0.95;">Active</h6>
                            <h3 class="mb-0 fw-bold">{{ $activeEmployees }}</h3>
                        </div>
                        <i class="fas fa-user-check fa-2x" style="opacity: 0.8;"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3 mt-3 mt-md-0">
            <div class="card border-0 shadow-sm bg-gradient-warning h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-subtitle mb-2 fw-bold text-dark">On Notice</h6>
                            <h3 class="mb-0 fw-bold text-dark">{{ $noticeEmployees }}</h3>
                        </div>
                        <i class="fas fa-clock fa-2x text-dark" style="opacity: 0.8;"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3 mt-3 mt-md-0">
            <div class="card border-0 shadow-sm bg-gradient-info text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-subtitle mb-2 fw-bold" style="opacity: 0.95;">Interns</h6>
                            <h3 class="mb-0 fw-bold">{{ $internEmployees }}</h3>
                        </div>
                        <i class="fas fa-user-graduate fa-2x" style="opacity: 0.8;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Invite Modal -->
    <div class="modal fade" id="inviteModal" tabindex="-1" aria-labelledby="inviteModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
          <!-- Header -->
          <div class="modal-header bg-primary text-white">
            <h5 class="modal-title" id="inviteModalLabel">
                <i class="fas fa-envelope me-2"></i>Invite to Xinksoft Technologies
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>

          <!-- Body -->
          <div class="modal-body p-4">
            <!-- Nav Tabs -->
            <ul class="nav nav-pills nav-fill mb-4" id="inviteTabs" role="tablist">
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

            <!-- Tab Content -->
            <div class="tab-content" id="inviteTabsContent">
              <!-- Invite by Email -->
              <div class="tab-pane fade show active" id="invite-email" role="tabpanel">
                <div class="alert alert-info bg-light border-0">
                  <i class="fas fa-info-circle text-primary"></i>
                  <span class="ms-2">Employees will receive an email to log in and update their profile through the self-service portal.</span>
                </div>

                <form id="inviteEmailForm" autocomplete="off">
                  @csrf
                  <div class="mb-3">
                    <label class="form-label fw-bold">Email Address <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-at"></i></span>
                        <input type="email" name="email" id="inviteEmail" class="form-control"
                               placeholder="e.g. johndoe@xinksoft.com" required>
                    </div>
                  </div>
                  <div class="mb-4">
                    <label class="form-label fw-bold">Personal Message <span class="text-muted">(Optional)</span></label>
                    <textarea name="message" id="inviteMessage" class="form-control" rows="3"
                              placeholder="Add a welcome message..."></textarea>
                  </div>

                  <div class="d-flex gap-2 justify-content-end">
                    <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" id="sendInviteBtn" class="btn btn-primary px-4">
                      <span id="sendInviteSpinner" class="spinner-border spinner-border-sm me-2 d-none" role="status"></span>
                      <i class="fas fa-paper-plane me-2"></i>Send Invitation
                    </button>
                  </div>

                  <div class="mt-3" id="inviteEmailAlert" style="display:none;"></div>
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
                    <input type="text" class="form-control border-end-0" id="inviteLink" readonly
                           placeholder="Generating link...">
                    <button class="btn btn-outline-primary" type="button" id="copyLinkBtn">
                      <i class="fas fa-copy"></i>
                    </button>
                    <button class="btn btn-outline-success" type="button" id="shareLinkBtn" title="Share via Email">
                      <i class="fas fa-share-alt"></i>
                    </button>
                  </div>

                  <small class="text-muted mt-2 d-block">
                    <i class="fas fa-info-circle me-1"></i>
                    Share this link with prospective employees
                  </small>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Blocked delete info modal -->
    <div class="modal fade" id="blockedDeleteModal" tabindex="-1" aria-labelledby="blockedDeleteModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
          <div class="modal-header bg-warning text-dark">
            <h5 class="modal-title" id="blockedDeleteModalLabel">
                <i class="fas fa-exclamation-triangle me-2"></i>Delete Restricted
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body py-4">
            <div class="text-center mb-3">
              <i class="fas fa-users-slash fa-3x text-warning mb-3"></i>
              <h5 class="fw-bold" id="blockedEmployeeName"></h5>
              <p class="text-muted" id="blockedEmployeeReason"></p>
            </div>
          </div>
          <div class="modal-footer border-0">
            <a href="#" id="blocked-view-subordinates" class="btn btn-outline-primary">
              <i class="fas fa-eye me-2"></i>View Team Members
            </a>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>

    {{-- Filter Section --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-light border-0 py-3">
            <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filter Employees</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('employees.index') }}" class="row g-3">
              <!-- Employee ID -->
              <div class="col-12 col-md-4 col-lg-3">
                  <label class="form-label fw-medium">Employee ID</label>
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
                          <option value="{{ $detail->employee_id }}"
                                  {{ request('employee_id') == $detail->employee_id ? 'selected' : '' }}>
                              {{ $detail->employee_id }} - {{ $detail->user->name ?? 'N/A' }}
                          </option>
                      @endforeach
                  </select>
              </div>

              <!-- Designation -->
              <div class="col-12 col-md-4 col-lg-3">
                  <label class="form-label fw-medium">Designation</label>
                  <select name="designation_id" class="form-select select2">
                      <option value="">All Designations</option>
                      @foreach($designations as $designation)
                          <option value="{{ $designation->id }}"
                                  {{ request('designation_id') == $designation->id ? 'selected' : '' }}>
                              {{ $designation->name }}
                          </option>
                      @endforeach
                  </select>
              </div>

              <!-- Search Name/Email -->
              <div class="col-12 col-md-4 col-lg-3">
                  <label class="form-label fw-medium">Search Name/Email</label>
                  <select name="user_id" class="form-select select2">
                      <option value="">Search Employee...</option>
                      @foreach($edOptions as $detail)
                          <option value="{{ $detail->user_id }}"
                                  {{ request('user_id') == $detail->user_id ? 'selected' : '' }}>
                              {{ $detail->user->name ?? 'N/A' }} - {{ $detail->user->email ?? 'N/A' }}
                          </option>
                      @endforeach
                  </select>
              </div>

              <!-- Buttons -->
              <div class="col-12 col-md-12 col-lg-3 d-flex align-items-end gap-2">
                  <button type="submit" class="btn btn-primary flex-fill d-flex align-items-center justify-content-center">
                      <i class="fas fa-search me-2"></i> Apply Filters
                  </button>
                  <a href="{{ route('employees.index') }}" class="btn btn-outline-secondary flex-fill d-flex align-items-center justify-content-center">
                      <i class="fas fa-redo me-2"></i> Reset
                  </a>
              </div>
            </form>
        </div>
    </div>

    {{-- Alerts --}}
    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>
        {{ $errors->first() }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
        <i class="fas fa-check-circle me-2"></i>
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    {{-- Export Section --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-light border-0 py-3 d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
            <div class="mb-2 mb-md-0">
                <h5 class="mb-0"><i class="fas fa-file-export me-2"></i>Export Options</h5>
                <small class="text-muted">Export selected or all employee data</small>
            </div>
            <div class="d-flex align-items-center">
                <span id="export-selected-count" class="badge bg-primary me-3">0 selected</span>
                <button type="button" class="btn btn-sm btn-warning" id="export-all">
                    <i class="fas fa-download me-1"></i> Export All
                </button>
            </div>
        </div>
        <div class="card-body py-3">
            <div class="d-flex flex-wrap align-items-center gap-2">
                <span class="fw-medium text-muted me-2">Export Selected:</span>
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-outline-primary btn-sm" id="export-copy" disabled>
                        <i class="fas fa-copy me-1"></i> Copy
                    </button>
                    <button type="button" class="btn btn-outline-success btn-sm" id="export-csv" disabled>
                        <i class="fas fa-file-csv me-1"></i> CSV
                    </button>
                    <button type="button" class="btn btn-outline-info btn-sm" id="export-excel" disabled>
                        <i class="fas fa-file-excel me-1"></i> Excel
                    </button>
                    <button type="button" class="btn btn-outline-danger btn-sm" id="export-pdf" disabled>
                        <i class="fas fa-file-pdf me-1"></i> PDF
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="export-print" disabled>
                        <i class="fas fa-print me-1"></i> Print
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-light border-0 py-3">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
                <h5 class="mb-2 mb-md-0"><i class="fas fa-list me-2"></i>Employee List</h5>
                <div class="d-flex align-items-center">
                    <button id="btn-bulk-delete" class="btn btn-danger btn-sm me-3" disabled>
                        <i class="fas fa-trash-alt me-1"></i> Delete Selected
                    </button>
                    <span id="bulk-selected-count" class="text-muted small">0 selected</span>
                </div>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="employeeTable" class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4" style="width: 50px;">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="selectAll" title="Select/Deselect All">
                                </div>
                            </th>
                            <th class="fw-semibold">Emp ID</th>
                            <th class="fw-semibold">Employee Details</th>
                            <th class="fw-semibold">Contact</th>
                            <th class="fw-semibold">Role & Reporting</th>
                            <th class="fw-semibold text-center">Status</th>
                            <th class="fw-semibold text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($visibleEmployees->isEmpty())
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="py-4">
                                        <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                        <h5 class="text-muted">No Employees Found</h5>
                                        <p class="text-muted">Try adjusting your filters or add new employees</p>
                                        <a href="{{ route('employees.create') }}" class="btn btn-primary mt-2">
                                            <i class="fas fa-user-plus me-2"></i> Add First Employee
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @else
                            @foreach($visibleEmployees as $employee)
                              <tr id="employee-row-{{ $employee->id }}"
                                  class="@if(isset($employee->subordinate_count) && $employee->subordinate_count > 0) table-warning @endif"
                                  data-employee-id="{{ $employee->id }}">

                                    {{-- Checkbox --}}
                                    <td class="ps-4">
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

                                    {{-- Employee ID --}}
                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            {{ $employee->employeeDetail?->employee_id ?? '-' }}
                                        </span>
                                    </td>

                                    {{-- Employee Details --}}
                                    <td>
                                        <div class="d-flex align-items-start">
                                            @if(!empty($employee->profile_image))
                                                <img src="{{ asset($employee->profile_image) }}" alt="Profile"
                                                     width="48" height="48" class="rounded-circle me-3 border">
                                            @else
                                                <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center me-3"
                                                     style="width: 48px; height: 48px;">
                                                    <i class="fas fa-user fs-5"></i>
                                                </div>
                                            @endif

                                            <div>
                                                <h6 class="mb-1 fw-medium">{{ $employee->name }}</h6>
                                                <small class="text-muted">{{ $employee->employeeDetail?->designation?->name ?? '-' }}</small>

                                                <div class="mt-2">
                                                    @php
                                                        $detail = $employee->employeeDetail;
                                                        $status = $detail?->employment_status ?? null;
                                                        $designationName = strtolower($detail?->designation?->name ?? '');
                                                        $probationEnd = $detail?->probation_end_date ? \Carbon\Carbon::parse($detail->probation_end_date) : null;
                                                        $noticeEnd = $detail?->notice_end_date ? \Carbon\Carbon::parse($detail->notice_end_date) : null;
                                                    @endphp

                                                    @if ($status === 'notice')
                                                        <span class="badge rounded-pill bg-warning text-dark">
                                                            <i class="fas fa-clock me-1"></i> Notice Period
                                                            @if($noticeEnd)
                                                                ({{ $noticeEnd->format('d M') }})
                                                            @endif
                                                        </span>
                                                    @elseif (strpos($designationName, 'intern') !== false)
                                                        <span class="badge rounded-pill bg-info text-white">
                                                            <i class="fas fa-graduation-cap me-1"></i> Intern
                                                        </span>
                                                    @else
                                                        <span class="badge rounded-pill bg-primary text-white">
                                                            <i class="fas fa-user-tie me-1"></i> Employee
                                                        </span>
                                                        @if ($status === 'probation' && $probationEnd)
                                                            <span class="badge rounded-pill bg-secondary text-white ms-1">
                                                                Probation ({{ $probationEnd->format('d M') }})
                                                            </span>
                                                        @endif
                                                    @endif

                                                    @if(isset($employee->subordinate_count) && $employee->subordinate_count > 0)
                                                        <span class="badge rounded-pill bg-warning text-dark ms-1"
                                                              title="Has {{ $employee->subordinate_count }} team members">
                                                            <i class="fas fa-users me-1"></i> Team Lead
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Contact --}}
                                    <td>
                                        <div class="d-flex flex-column">
                                            <a href="mailto:{{ $employee->email }}" class="text-decoration-none">
                                                <i class="fas fa-envelope text-primary me-2"></i>
                                                <small>{{ $employee->email ?? '-' }}</small>
                                            </a>
                                        </div>
                                    </td>

                                    {{-- Role & Reporting --}}
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="fw-medium">{{ $employee->employeeDetail?->designation?->name ?? '-' }}</span>
                                            <small class="text-muted">
                                                <i class="fas fa-user-friends me-1"></i>
                                                Reports to: {{ $employee->employeeDetail?->reportingTo?->name ?? 'N/A' }}
                                            </small>
                                        </div>
                                    </td>

                                    {{-- Status --}}
                                    <td class="text-center">
                                        @if($employee->employeeDetail?->status === 'Active')
                                            <span class="badge bg-success rounded-pill px-3 py-1">
                                                <i class="fas fa-circle me-1" style="font-size: 8px;"></i> Active
                                            </span>
                                        @elseif($employee->employeeDetail?->status === 'Inactive')
                                            <span class="badge bg-danger rounded-pill px-3 py-1">
                                                <i class="fas fa-circle me-1" style="font-size: 8px;"></i> Inactive
                                            </span>
                                        @else
                                            <span class="badge bg-secondary rounded-pill px-3 py-1">N/A</span>
                                        @endif
                                    </td>

                                    {{-- Actions --}}
                                    <td class="text-center">
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-light rounded-circle" type="button"
                                                    id="dropdownMenuButton{{ $employee->id }}"
                                                    data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow border-0"
                                                aria-labelledby="dropdownMenuButton{{ $employee->id }}">
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
        </div>

        {{-- Pagination and Show Entries --}}
        <div class="card-footer bg-light border-0 py-3">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center">
                {{-- Show Entries --}}
                <div class="mb-3 mb-md-0">
                    <div class="d-flex align-items-center">
                        <span class="text-muted me-2">Show:</span>
                        <select class="form-select form-select-sm" id="showEntries" style="width: auto;">
                            <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                            <option value="20" {{ request('per_page') == 20 ? 'selected' : '' }}>20</option>
                            <option value="30" {{ request('per_page') == 30 ? 'selected' : '' }}>30</option>
                            <option value="40" {{ request('per_page') == 40 ? 'selected' : '' }}>40</option>
                            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                        </select>
                        <span class="text-muted ms-2">entries</span>
                    </div>
                </div>

                {{-- Pagination Info --}}
                <div class="text-center mb-3 mb-md-0">
                    @if(method_exists($employees, 'total'))
                        <span class="text-muted">
                            Showing {{ ($employees->currentPage() - 1) * $employees->perPage() + 1 }}
                            to {{ min($employees->currentPage() * $employees->perPage(), $employees->total()) }}
                            of {{ $employees->total() }} entries
                        </span>
                    @endif
                </div>

                {{-- Pagination Buttons --}}
                <div class="d-flex align-items-center">
                    @if(method_exists($employees, 'currentPage'))
                        @if($employees->onFirstPage())
                            <button class="btn btn-outline-secondary btn-sm" disabled>
                                <i class="fas fa-chevron-left me-1"></i> Previous
                            </button>
                        @else
                            <a href="{{ $employees->previousPageUrl() . (request('per_page') ? '&per_page=' . request('per_page') : '') }}" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-chevron-left me-1"></i> Previous
                            </a>
                        @endif

                        <div class="mx-3">
                            @php
                                $currentPage = $employees->currentPage();
                                $lastPage = $employees->lastPage();
                                $start = max(1, $currentPage - 2);
                                $end = min($lastPage, $currentPage + 2);
                            @endphp

                            @for ($i = $start; $i <= $end; $i++)
                                @if ($i == $currentPage)
                                    <span class="btn btn-primary btn-sm mx-1">{{ $i }}</span>
                                @else
                                    <a href="{{ $employees->url($i) . (request('per_page') ? '&per_page=' . request('per_page') : '') }}" class="btn btn-outline-primary btn-sm mx-1">{{ $i }}</a>
                                @endif
                            @endfor

                            @if($lastPage > $end)
                                <span class="mx-1">...</span>
                                <a href="{{ $employees->url($lastPage) . (request('per_page') ? '&per_page=' . request('per_page') : '') }}" class="btn btn-outline-primary btn-sm mx-1">{{ $lastPage }}</a>
                            @endif
                        </div>

                        @if($employees->hasMorePages())
                            <a href="{{ $employees->nextPageUrl() . (request('per_page') ? '&per_page=' . request('per_page') : '') }}" class="btn btn-outline-primary btn-sm">
                                Next <i class="fas fa-chevron-right ms-1"></i>
                            </a>
                        @else
                            <button class="btn btn-outline-secondary btn-sm" disabled>
                                Next <i class="fas fa-chevron-right ms-1"></i>
                            </button>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Custom CSS --}}
@section('styles')
<style>
    /* ===== IMPROVED TEXT VISIBILITY ===== */

    /* Card header text - make darker */
    .card-header h5,
    .card-header h6 {
        color: #212529 !important; /* Dark black */
        font-weight: 600;
    }

    /* Stats cards text improvements */
    .bg-gradient-primary.text-white .card-subtitle,
    .bg-gradient-success.text-white .card-subtitle,
    .bg-gradient-info.text-white .card-subtitle {
        color: rgba(255, 255, 255, 0.9) !important; /* Brighter white for subtitles */
    }

    .bg-gradient-primary.text-white h3,
    .bg-gradient-success.text-white h3,
    .bg-gradient-info.text-white h3 {
        color: #ffffff !important; /* Pure white for numbers */
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2); /* Add shadow for contrast */
    }

    /* Warning card (yellow background) - make text darker */
    .bg-gradient-warning.text-dark .card-subtitle {
        color: #495057 !important; /* Dark gray */
    }

    .bg-gradient-warning.text-dark h3 {
        color: #212529 !important; /* Almost black */
        font-weight: 700;
    }

    /* Form labels - make darker */
    .form-label.fw-medium,
    .form-label.fw-bold {
        color: #343a40 !important; /* Dark gray */
    }

    /* Table text improvements */
    #employeeTable th.fw-semibold {
        color: #495057 !important; /* Darker gray for headers */
    }

    #employeeTable td {
        color: #212529 !important; /* Dark black for table content */
    }

    /* Dropdown text */
    .dropdown-item {
        color: #212529 !important; /* Dark text for dropdown items */
    }

    /* Badge text improvements */
    .badge.bg-light.text-dark {
        color: #212529 !important;
        font-weight: 500;
    }

    /* Text-muted improvements */
    .text-muted {
        color: #6c757d !important; /* Slightly darker muted text */
    }

    /* Breadcrumb text */
    .breadcrumb .breadcrumb-item a {
        color: #495057 !important;
    }

    .breadcrumb .breadcrumb-item.active {
        color: #212529 !important;
        font-weight: 500;
    }

    /* Modal text */
    .modal-title {
        color: #212529 !important;
    }

    .modal-body {
        color: #495057 !important;
    }

    /* Alert text */
    .alert {
        color: #212529 !important;
    }

    /* Button text */
    .btn-primary,
    .btn-success,
    .btn-danger,
    .btn-warning {
        color: white !important;
        font-weight: 500;
    }

    .btn-outline-primary,
    .btn-outline-secondary {
        font-weight: 500;
    }

    /* ===== SPECIFIC DARKENING FOR STATS CARDS ===== */

    /* Primary gradient card (purple) - improve text */
    .bg-gradient-primary.text-white {
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
    }

    /* Success gradient card (green) - improve text */
    .bg-gradient-success.text-white {
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
    }

    /* Warning gradient card (yellow/orange) - DARK TEXT */
    .bg-gradient-warning {
        background: linear-gradient(135deg, #f9a825 0%, #f57f17 100%) !important;
    }

    .bg-gradient-warning .card-subtitle {
        color: #5d4037 !important; /* Brownish dark */
        font-weight: 500;
    }

    .bg-gradient-warning h3 {
        color: #3e2723 !important; /* Dark brown */
        font-weight: 700;
        text-shadow: none !important;
    }

    .bg-gradient-warning i {
        color: #5d4037 !important; /* Match subtitle color */
    }

    /* Info gradient card (blue) - improve text */
    .bg-gradient-info.text-white {
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
    }

    /* ===== ADDITIONAL VISIBILITY IMPROVEMENTS ===== */

    /* Page headers */
    h2.text-primary {
        color: #0d47a1 !important; /* Darker blue */
    }

    /* Card titles */
    .card-header h5 {
        font-weight: 600;
        color: #212529 !important;
    }

    /* Filter section text */
    .card.border-0.shadow-sm .card-header {
        background-color: #f8f9fa !important;
        border-bottom: 1px solid #dee2e6;
    }

    /* Export section text */
    .card .card-header .text-muted {
        color: #6c757d !important;
        font-weight: 400;
    }

    /* Table hover improvements */
    #employeeTable tbody tr:hover td {
        color: #000000 !important; /* Pure black on hover */
    }

    /* Checkbox labels (if any) */
    .form-check-label {
        color: #495057 !important;
    }

    /* Input placeholder text */
    .form-control::placeholder {
        color: #6c757d !important;
        opacity: 0.8;
    }

    /* Select2 text */
    .select2-selection__placeholder {
        color: #6c757d !important;
    }

    .select2-selection__rendered {
        color: #495057 !important;
    }

    /* Pagination text */
    .btn-outline-primary {
        color: #0d6efd !important;
        border-color: #0d6efd;
    }

    .btn-outline-primary:hover {
        color: white !important;
    }

    /* ===== SPECIFIC FIX FOR GRADIENT CARDS ===== */

    /* Add dark overlay to gradient cards for better text visibility */
    .bg-gradient-primary.text-white::before,
    .bg-gradient-success.text-white::before,
    .bg-gradient-info.text-white::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(rgba(0, 0, 0, 0.1), rgba(0, 0, 0, 0.2));
        border-radius: inherit;
        z-index: 0;
    }

    .bg-gradient-primary.text-white > .card-body,
    .bg-gradient-success.text-white > .card-body,
    .bg-gradient-info.text-white > .card-body {
        position: relative;
        z-index: 1;
    }

    /* For warning card, use light overlay */
    .bg-gradient-warning.text-dark::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(rgba(255, 255, 255, 0.3), rgba(255, 255, 255, 0.4));
        border-radius: inherit;
        z-index: 0;
    }

    .bg-gradient-warning.text-dark > .card-body {
        position: relative;
        z-index: 1;
    }

    /* ===== RESPONSIVE TEXT SIZING ===== */

    @media (max-width: 768px) {
        .card-body h3 {
            font-size: 1.5rem; /* Smaller on mobile */
        }

        .card-subtitle {
            font-size: 0.8rem;
        }
    }

    /* Keep all existing styles below this line */
    /* Global Improvements */
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .card {
        border-radius: 12px;
        overflow: hidden;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.1) !important;
    }

    /* Gradient Backgrounds */
    .bg-gradient-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
    }

    .bg-gradient-success {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%) !important;
    }

    .bg-gradient-warning {
        background: linear-gradient(135deg, #f7971e 0%, #ffd200 100%) !important;
    }

    .bg-gradient-info {
        background: linear-gradient(135deg, #36d1dc 0%, #5b86e5 100%) !important;
    }

    /* Table Styling */
    #employeeTable {
        border-collapse: separate;
        border-spacing: 0;
    }

    #employeeTable th {
        background-color: #f8f9fa;
        font-weight: 600;
        border-bottom: 2px solid #dee2e6;
        padding: 16px 12px;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    #employeeTable td {
        padding: 16px 12px;
        border-bottom: 1px solid #f0f0f0;
        vertical-align: middle;
    }

    #employeeTable tbody tr {
        transition: all 0.2s ease;
    }

    #employeeTable tbody tr:hover {
        background-color: rgba(13, 110, 253, 0.05) !important;
        transform: scale(1.002);
    }

    #employeeTable tbody tr.selected {
        background-color: rgba(13, 110, 253, 0.08) !important;
        border-left: 3px solid #0d6efd !important;
    }

    /* Badge Improvements */
    .badge {
        font-weight: 500;
        letter-spacing: 0.3px;
    }

    /* Checkbox Styling */
    .form-check-input {
        width: 18px;
        height: 18px;
        cursor: pointer;
        border: 2px solid #dee2e6;
        transition: all 0.2s ease;
    }

    .form-check-input:checked {
        background-color: #0d6efd;
        border-color: #0d6efd;
        box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.2);
    }

    .form-check-input:disabled {
        background-color: #e9ecef;
        border-color: #dee2e6;
        opacity: 0.6;
        cursor: not-allowed;
    }

    /* Button Improvements */
    .btn {
        border-radius: 8px;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .btn-group .btn {
        border-radius: 6px;
        margin: 0 2px;
    }

    .btn:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    /* Dropdown Improvements */
    .dropdown-menu {
        border-radius: 10px;
        border: 1px solid rgba(0,0,0,0.08);
        box-shadow: 0 10px 40px rgba(0,0,0,0.1);
    }

    .dropdown-item {
        padding: 10px 16px;
        font-weight: 500;
        transition: all 0.2s ease;
    }

    .dropdown-item:hover {
        background-color: rgba(13, 110, 253, 0.08);
        padding-left: 20px;
    }

    /* Form Control Styling */
    .form-control, .form-select {
        border-radius: 8px;
        border: 1px solid #dee2e6;
        padding: 10px 16px;
        transition: all 0.3s ease;
    }

    .form-control:focus, .form-select:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.2);
    }

    /* Alert Improvements */
    .alert {
        border-radius: 10px;
        border: none;
        padding: 16px 20px;
    }

    /* Modal Styling */
    .modal-content {
        border-radius: 15px;
        border: none;
        box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .container-fluid {
            padding-left: 15px;
            padding-right: 15px;
        }

        .card-header, .card-footer {
            padding: 15px;
        }

        .table-responsive {
            border-radius: 0;
        }

        #employeeTable th, #employeeTable td {
            padding: 12px 8px;
            font-size: 0.85rem;
        }

        .btn-group {
            flex-wrap: wrap;
            gap: 5px;
        }

        .btn-group .btn {
            margin: 2px;
            flex: 1;
            min-width: 70px;
        }

        .d-flex.flex-wrap {
            gap: 10px;
        }

        /* Stack table columns on mobile */
        #employeeTable thead {
            display: none;
        }

        #employeeTable tbody tr {
            display: block;
            margin-bottom: 20px;
            border: 1px solid #dee2e6;
            border-radius: 10px;
            padding: 15px;
            background: white;
        }

        #employeeTable tbody td {
            display: block;
            text-align: left;
            border: none;
            padding: 8px 0;
        }

        #employeeTable tbody td::before {
            content: attr(data-label);
            font-weight: 600;
            display: block;
            margin-bottom: 4px;
            color: #666;
            font-size: 0.85rem;
        }
    }

    @media (max-width: 576px) {
        .d-flex.flex-md-row {
            flex-direction: column !important;
            gap: 15px;
        }

        .btn {
            width: 100%;
            margin-bottom: 5px;
        }

        .btn-group {
            width: 100%;
        }

        .select2-container {
            width: 100% !important;
        }
    }

    /* Custom Scrollbar */
    ::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }

    ::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }

    ::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 10px;
    }

    ::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }

    /* Loading Animation */
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .card {
        animation: fadeIn 0.5s ease-out;
    }

    /* Select All Checkbox Animation */
    #selectAll {
        transition: all 0.3s ease;
    }

    #selectAll:indeterminate {
        background-color: #0d6efd;
        border-color: #0d6efd;
        opacity: 0.7;
    }
</style>
@append

@push('js')
<script>
$(document).ready(function () {
    // Store selected employee data
    let selectedEmployees = [];
    let showEntries = localStorage.getItem('employeeShowEntries') || '10';

    // Set initial show entries value
    $('#showEntries').val(showEntries);

    // Function to clean HTML from text
    function cleanHTML(text) {
        if (!text || text === null || text === undefined) return '';
        if (typeof text !== 'string') return String(text);

        return String(text)
            .replace(/<br\s*\/?>/gi, '\n')
            .replace(/<[^>]*>/g, '')
            .replace(/&nbsp;/g, ' ')
            .replace(/&amp;/g, '&')
            .replace(/&lt;/g, '<')
            .replace(/&gt;/g, '>')
            .replace(/&quot;/g, '"')
            .replace(/\s+/g, ' ')
            .trim();
    }

    // ===== SHOW ENTRIES FUNCTIONALITY =====
    $('#showEntries').on('change', function() {
        const value = $(this).val();
        localStorage.setItem('employeeShowEntries', value);

        // Create URL with per_page parameter
        const url = new URL(window.location.href);
        url.searchParams.set('per_page', value);
        window.location.href = url.toString();
    });

    // ===== FIXED CHECKBOX HANDLING =====

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

    // Row click to select/deselect
    $(document).on('click', 'tbody tr', function(e) {
        // Don't trigger if clicking on checkbox, dropdown, or link
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

    // Update "Select All" checkbox state
    function updateSelectAllCheckbox() {
        const totalCheckboxes = $('.employee-checkbox:not(:disabled)').length;
        const checkedCheckboxes = $('.employee-checkbox:checked:not(:disabled)').length;
        const selectAll = $('#selectAll');

        if (totalCheckboxes === 0) {
            selectAll.prop('checked', false);
            selectAll.prop('indeterminate', false);
            return;
        }

        if (checkedCheckboxes === 0) {
            selectAll.prop('checked', false);
            selectAll.prop('indeterminate', false);
        } else if (checkedCheckboxes === totalCheckboxes) {
            selectAll.prop('checked', true);
            selectAll.prop('indeterminate', false);
        } else {
            selectAll.prop('checked', false);
            selectAll.prop('indeterminate', true);
        }
    }

    // Function to update selected employees array
    function updateSelectedEmployees() {
        selectedEmployees = [];

        // Get only checked AND enabled checkboxes
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

        // Update UI counters
        $('#export-selected-count').text(selectedCount + ' selected');
        $('#bulk-selected-count').text(selectedCount + ' selected');

        // Update export badge
        $('#export-selected-count').removeClass('bg-primary bg-success').addClass(selectedCount > 0 ? 'bg-success' : 'bg-primary');

        // Enable/disable export buttons
        const exportButtons = ['#export-copy', '#export-csv', '#export-excel', '#export-pdf', '#export-print'];
        exportButtons.forEach(btn => {
            $(btn).prop('disabled', selectedCount === 0);
            $(btn).toggleClass('disabled', selectedCount === 0);
        });

        // Enable/disable bulk delete button
        $('#btn-bulk-delete').prop('disabled', selectedCount === 0);
        $('#quick-action-apply').prop('disabled', selectedCount === 0 || $('#quick-action-type').val() === '');

        // Update row selection styling
        $('tbody tr').removeClass('selected');
        $('.employee-checkbox:checked:not(:disabled)').closest('tr').addClass('selected');
    }

    // ===== FIXED EXPORT FUNCTIONS =====

    // Function to get data for export
    function getDataForExport(exportAll = false) {
        const data = [];
        const headers = ['Employee ID', 'Name', 'Email', 'Designation', 'Reporting To', 'Status'];

        // Add headers
        data.push(headers);

        if (exportAll) {
            // Export ALL employees from table
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
            // Export only selected employees
            selectedEmployees.forEach(emp => {
                data.push([
                    emp.data.employee_id || '-',
                    emp.data.name || '-',
                    emp.data.email || '-',
                    emp.data.designation || '-',
                    emp.data.reporting_to || 'N/A',
                    emp.data.status || 'N/A'
                ]);
            });
        }

        return data;
    }

    // Copy selected rows to clipboard
    $('#export-copy').on('click', function() {
        if (selectedEmployees.length === 0) {
            showToast('Please select at least one row to copy.', 'warning');
            return;
        }

        const data = getDataForExport(false);
        const csvContent = data.map(row =>
            row.map(cell => `"${String(cell).replace(/"/g, '""')}"`).join('\t')
        ).join('\n');

        navigator.clipboard.writeText(csvContent)
            .then(() => {
                showToast('Selected rows copied to clipboard!', 'success');
            })
            .catch(err => {
                console.error('Failed to copy:', err);
                showToast('Failed to copy to clipboard. Please try again.', 'error');
            });
    });

    // Export to CSV
    $('#export-csv').on('click', function() {
        if (selectedEmployees.length === 0) {
            showToast('Please select at least one row to export.', 'warning');
            return;
        }

        const data = getDataForExport(false);
        const csvContent = data.map(row =>
            row.map(cell => `"${String(cell).replace(/"/g, '""')}"`).join(',')
        ).join('\n');

        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', `employees_${new Date().toISOString().split('T')[0]}.csv`);
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);

        showToast('CSV file downloaded successfully!', 'success');
    });

    // Export to Excel
    $('#export-excel').on('click', function() {
        if (selectedEmployees.length === 0) {
            showToast('Please select at least one row to export.', 'warning');
            return;
        }

        const data = getDataForExport(false);

        // Create workbook and worksheet
        const wb = XLSX.utils.book_new();
        const ws = XLSX.utils.aoa_to_sheet(data);

        // Set column widths
        const colWidths = [
            { wch: 15 }, { wch: 25 }, { wch: 30 },
            { wch: 20 }, { wch: 20 }, { wch: 15 }
        ];
        ws['!cols'] = colWidths;

        XLSX.utils.book_append_sheet(wb, ws, "Employees");
        XLSX.writeFile(wb, `employees_${new Date().toISOString().split('T')[0]}.xlsx`);

        showToast('Excel file downloaded successfully!', 'success');
    });

    // Export to PDF
    $('#export-pdf').on('click', function() {
        if (selectedEmployees.length === 0) {
            showToast('Please select at least one row to export.', 'warning');
            return;
        }

        const data = getDataForExport(false);

        const { jsPDF } = window.jspdf;
        const doc = new jsPDF('landscape');

        // Add title
        doc.setFontSize(16);
        doc.setTextColor(41, 128, 185);
        doc.text('Employee List - Xinksoft Technologies', 14, 15);
        doc.setFontSize(10);
        doc.setTextColor(100, 100, 100);
        doc.text(`Generated: ${new Date().toLocaleDateString()} | ${selectedEmployees.length} records`, 14, 22);

        // Prepare table data
        const headers = [['Employee ID', 'Name', 'Email', 'Designation', 'Reporting To', 'Status']];
        const tableData = data.slice(1);

        // Create table
        doc.autoTable({
            head: headers,
            body: tableData,
            startY: 30,
            theme: 'grid',
            headStyles: {
                fillColor: [41, 128, 185],
                textColor: 255,
                fontSize: 10,
                fontStyle: 'bold'
            },
            columnStyles: {
                0: { cellWidth: 25 },
                1: { cellWidth: 35 },
                2: { cellWidth: 45 },
                3: { cellWidth: 30 },
                4: { cellWidth: 35 },
                5: { cellWidth: 20 }
            },
            styles: {
                fontSize: 9,
                cellPadding: 3,
                textColor: [50, 50, 50],
                lineColor: [200, 200, 200],
                lineWidth: 0.1
            },
            margin: { left: 14, right: 14 }
        });

        // Add page numbers
        const pageCount = doc.internal.getNumberOfPages();
        for(let i = 1; i <= pageCount; i++) {
            doc.setPage(i);
            doc.setFontSize(8);
            doc.setTextColor(150, 150, 150);
            doc.text(`Page ${i} of ${pageCount}`, doc.internal.pageSize.width - 30, doc.internal.pageSize.height - 10);
        }

        // Save PDF
        doc.save(`employees_${new Date().toISOString().split('T')[0]}.pdf`);
        showToast('PDF file downloaded successfully!', 'success');
    });

    // Print selected rows
    $('#export-print').on('click', function() {
        if (selectedEmployees.length === 0) {
            showToast('Please select at least one row to print.', 'warning');
            return;
        }

        const data = getDataForExport(false);
        const tableData = data.slice(1);

        const printWindow = window.open('', '_blank');
        printWindow.document.write(`
            <html>
            <head>
                <title>Employee Report - Xinksoft Technologies</title>
                <style>
                    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap');
                    body {
                        font-family: 'Inter', sans-serif;
                        margin: 30px;
                        color: #333;
                        background: #f8f9fa;
                    }
                    .print-header {
                        text-align: center;
                        margin-bottom: 30px;
                        padding-bottom: 20px;
                        border-bottom: 2px solid #2c3e50;
                    }
                    .print-header h1 {
                        color: #2c3e50;
                        margin-bottom: 10px;
                        font-weight: 600;
                    }
                    .print-info {
                        background: white;
                        padding: 20px;
                        border-radius: 10px;
                        margin-bottom: 30px;
                        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
                    }
                    table {
                        width: 100%;
                        border-collapse: collapse;
                        margin-top: 20px;
                        background: white;
                        border-radius: 8px;
                        overflow: hidden;
                        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
                    }
                    th {
                        background-color: #2c3e50;
                        color: white;
                        padding: 12px 15px;
                        text-align: left;
                        font-weight: 500;
                        font-size: 14px;
                    }
                    td {
                        padding: 10px 15px;
                        border-bottom: 1px solid #eee;
                        font-size: 13px;
                    }
                    tr:nth-child(even) { background-color: #f8f9fa; }
                    tr:hover { background-color: #e9f7fe; }
                    .badge {
                        padding: 4px 10px;
                        border-radius: 12px;
                        font-size: 12px;
                        font-weight: 500;
                    }
                    .badge-active { background: #d4edda; color: #155724; }
                    .badge-inactive { background: #f8d7da; color: #721c24; }
                    .no-print { margin-top: 40px; text-align: center; }
                    .no-print button {
                        padding: 12px 30px;
                        background: #3498db;
                        color: white;
                        border: none;
                        border-radius: 6px;
                        cursor: pointer;
                        font-weight: 500;
                        transition: background 0.3s;
                        margin: 5px;
                    }
                    .no-print button:hover { background: #2980b9; }
                    @media print {
                        body { margin: 0; background: white; }
                        .no-print { display: none; }
                        .print-header { margin-top: 0; }
                    }
                </style>
            </head>
            <body>
                <div class="print-header">
                    <h1>Employee Report</h1>
                    <p><strong>Company:</strong> Xinksoft Technologies Pvt. Ltd.</p>
                    <p><strong>Generated:</strong> ${new Date().toLocaleDateString()} ${new Date().toLocaleTimeString()}</p>
                    <p><strong>Total Records:</strong> ${tableData.length}</p>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Employee ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Designation</th>
                            <th>Reporting To</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${tableData.map(row => `
                            <tr>
                                <td>${row[0] || '-'}</td>
                                <td>${row[1] || '-'}</td>
                                <td>${row[2] || '-'}</td>
                                <td>${row[3] || '-'}</td>
                                <td>${row[4] || 'N/A'}</td>
                                <td>
                                    <span class="badge ${row[5] === 'Active' ? 'badge-active' : 'badge-inactive'}">
                                        ${row[5] || 'N/A'}
                                    </span>
                                </td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
                <div class="no-print">
                    <button onclick="window.print()">Print Report</button>
                    <button onclick="window.close()" style="background: #e74c3c;">Close Window</button>
                </div>
            </body>
            </html>
        `);
        printWindow.document.close();
    });

    // Export ALL employees
    $('#export-all').on('click', function() {
        const totalEmployees = $('.employee-checkbox:not(:disabled)').length;
        if (totalEmployees === 0) {
            showToast('No employees available to export.', 'warning');
            return;
        }

        if (!confirm(`Export ALL ${totalEmployees} employees to Excel?`)) return;

        // Get ALL employee data
        const data = getDataForExport(true);

        // Create Excel file
        const wb = XLSX.utils.book_new();
        const ws = XLSX.utils.aoa_to_sheet(data);

        // Set column widths
        const colWidths = [
            { wch: 15 }, { wch: 25 }, { wch: 30 },
            { wch: 20 }, { wch: 20 }, { wch: 15 }
        ];
        ws['!cols'] = colWidths;

        XLSX.utils.book_append_sheet(wb, ws, "All Employees");
        XLSX.writeFile(wb, `all_employees_${new Date().toISOString().split('T')[0]}.xlsx`);

        showToast(`All ${totalEmployees} employees exported successfully!`, 'success');
    });

    // Toast notification function
    function showToast(message, type = 'info') {
        const toast = $(`
            <div class="toast align-items-center text-white bg-${type === 'success' ? 'success' : type === 'error' ? 'danger' : type === 'warning' ? 'warning' : 'primary'} border-0 position-fixed bottom-0 end-0 m-3" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : type === 'warning' ? 'exclamation-triangle' : 'info-circle'} me-2"></i>
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `);

        $('body').append(toast);
        const bsToast = new bootstrap.Toast(toast[0]);
        bsToast.show();

        toast.on('hidden.bs.toast', function () {
            $(this).remove();
        });
    }

    // ===== EXISTING FUNCTIONALITY =====

    // Initialize Select2
    $('.select2').select2({
        allowClear: true,
        width: '100%',
        placeholder: function() {
            $(this).data('placeholder');
        },
        theme: 'bootstrap-5'
    });

    // ---------- Invite by Email AJAX ----------
    $('#inviteEmailForm').on('submit', function(e) {
        e.preventDefault();

        const email = $('#inviteEmail').val().trim();
        const message = $('#inviteMessage').val().trim();
        const $btn = $('#sendInviteBtn');
        const $spinner = $('#sendInviteSpinner');
        const $alert = $('#inviteEmailAlert');

        if (!email) {
            $alert.removeClass().addClass('alert alert-danger alert-dismissible fade show')
                .html('<i class="fas fa-exclamation-circle me-2"></i>Please enter a valid email.')
                .show();
            return;
        }

        $alert.hide();
        $btn.prop('disabled', true);
        $spinner.removeClass('d-none');

        $.ajax({
            url: '{{ route("employees.sendInvite") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                email: email,
                message: message
            },
            success: function(res) {
                $alert.removeClass().addClass('alert alert-success alert-dismissible fade show')
                    .html(`<i class="fas fa-check-circle me-2"></i>${res.message || 'Invitation sent successfully!'}
                           <button type="button" class="btn-close" data-bs-dismiss="alert"></button>`)
                    .show();

                // Reset form
                $('#inviteEmailForm')[0].reset();

                setTimeout(function() {
                    $('#inviteModal').modal('hide');
                    showToast('Invitation sent successfully!', 'success');
                    setTimeout(() => location.reload(), 1000);
                }, 1500);
            },
            error: function(xhr) {
                let errMsg = 'Failed to send invite. Please try again.';
                if (xhr?.responseJSON?.error) errMsg = xhr.responseJSON.error;
                else if (xhr?.responseJSON?.message) errMsg = xhr.responseJSON.message;
                else if (xhr?.statusText) errMsg = xhr.statusText;

                $alert.removeClass().addClass('alert alert-danger alert-dismissible fade show')
                    .html(`<i class="fas fa-exclamation-circle me-2"></i>${errMsg}
                           <button type="button" class="btn-close" data-bs-dismiss="alert"></button>`)
                    .show();
            },
            complete: function() {
                $btn.prop('disabled', false);
                $spinner.addClass('d-none');
            }
        });
    });

    // ---------- Invite by Link ----------
    function generateToken(length = 40) {
        const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        let token = '';
        const array = new Uint8Array(length);
        window.crypto.getRandomValues(array);
        for (let i = 0; i < length; i++) {
            token += chars[array[i] % chars.length];
        }
        return token;
    }

    $('#createLinkBtn').on('click', function() {
        const token = generateToken();
        const domainRestricted = $('#domainEmail').is(':checked');
        const link = `{{ rtrim(url('/'), '/') }}/register?invitation=${token}&restrict=${domainRestricted}`;

        $('#linkContainer').slideDown();
        $('#inviteLink').val(link);

        // Show success message
        showToast('Invitation link generated successfully!', 'success');
    });

    $('#copyLinkBtn').on('click', function() {
        const linkInput = document.getElementById('inviteLink');
        linkInput.select();
        linkInput.setSelectionRange(0, 99999);

        navigator.clipboard?.writeText(linkInput.value)
            .then(() => showToast('Link copied to clipboard!', 'success'))
            .catch(() => {
                document.execCommand('copy');
                showToast('Link copied to clipboard!', 'success');
            });
    });

    $('#shareLinkBtn').on('click', function() {
        const link = $('#inviteLink').val();
        const subject = 'Join Xinksoft Technologies';
        const body = `You're invited to join Xinksoft Technologies! Click the link below to register:\n\n${link}`;

        window.location.href = `mailto:?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;
    });

    // ---------- Quick Actions ----------
    $('#quick-action-type').on('change', function() {
        if ($(this).val() === 'change-status') {
            $('#quick-action-status').removeClass('d-none').addClass('d-flex');
        } else {
            $('#quick-action-status').addClass('d-none').removeClass('d-flex');
        }
        toggleApplyButton();
    });

    function toggleApplyButton() {
        let anyChecked = $('.employee-checkbox:checked:not(:disabled)').length > 0;
        let actionSelected = $('#quick-action-type').val() !== '';
        $('#quick-action-apply').prop('disabled', !(anyChecked && actionSelected));
    }

    // Apply quick action
    $('#quick-action-apply').on('click', function() {
        let selectedIds = $('.employee-checkbox:checked:not(:disabled)').map(function() {
            return $(this).val();
        }).get();

        let actionType = $('#quick-action-type').val();
        let status = $('#quick-action-status').val();

        if (!selectedIds.length) {
            showToast('Please select at least one employee.', 'warning');
            return;
        }

        if (actionType === 'change-status' && status) {
            if (!confirm(`Change status of ${selectedIds.length} employee(s) to "${status}"?`)) return;

            $.ajax({
                url: '{{ route("employees.bulkUpdateStatus") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    employee_ids: selectedIds,
                    status: status
                },
                success: function(response) {
                    showToast(`Status updated for ${selectedIds.length} employee(s)`, 'success');
                    setTimeout(() => location.reload(), 1000);
                },
                error: function(err) {
                    showToast('Failed to update status. Please try again.', 'error');
                }
            });
        }

        if (actionType === 'delete') {
            if (!confirm(`Permanently delete ${selectedIds.length} selected employee(s)? This action cannot be undone.`)) return;

            $.ajax({
                url: '{{ route("employees.bulk.delete") }}',
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}',
                    employee_ids: selectedIds
                },
                success: function(response) {
                    showToast(`${selectedIds.length} employee(s) deleted successfully`, 'success');
                    setTimeout(() => location.reload(), 1000);
                },
                error: function(err) {
                    showToast('Failed to delete employees. Please try again.', 'error');
                }
            });
        }
    });

    // ---------- Bulk Delete button ----------
    $('#btn-bulk-delete').on('click', function() {
        let selectedIds = $('.employee-checkbox:checked:not(:disabled)').map(function() {
            return $(this).val();
        }).get();

        if (!selectedIds.length) {
            showToast('Please select at least one employee to delete.', 'warning');
            return;
        }

        if (!confirm(`Permanently delete ${selectedIds.length} selected employee(s)? This action cannot be undone.`)) return;

        const $btn = $(this);
        $btn.prop('disabled', true).prepend('<span id="bulkDeleteSpinner" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>');

        $.ajax({
            url: '{{ route("employees.bulk.delete") }}',
            type: 'DELETE',
            data: {
                _token: '{{ csrf_token() }}',
                employee_ids: selectedIds
            },
            success: function(res) {
                showToast(`${selectedIds.length} employee(s) deleted successfully`, 'success');
                setTimeout(() => location.reload(), 1000);
            },
            error: function(xhr) {
                let msg = 'Failed to delete selected employees.';
                if (xhr?.responseJSON?.message) msg = xhr.responseJSON.message;
                showToast(msg, 'error');
            },
            complete: function() {
                $('#bulkDeleteSpinner').remove();
                $btn.prop('disabled', false);
            }
        });
    });

    // Prevent double form submissions
    $(document).on('submit', 'form', function() {
        $(this).find('button[type="submit"]').prop('disabled', true);
    });

    // ===== Show friendly modal when admin clicks the disabled-looking Delete item =====
    $(document).on('click', '.blocked-delete-btn', function (e) {
        e.preventDefault();

        const name = $(this).data('employee-name') || 'this employee';
        const id = $(this).data('employee-id');
        const subordinateCount = $(this).data('subordinate-count') || 0;

        $('#blockedEmployeeName').text(name);
        $('#blockedEmployeeReason').text(`Cannot delete because ${subordinateCount} team member(s) report to this employee. Please reassign team members before deletion.`);

        const viewUrl = '{{ rtrim(url('/'), '/') }}' + '/employees/' + id;
        $('#blocked-view-subordinates').attr('href', viewUrl).removeClass('d-none');

        const modalEl = new bootstrap.Modal(document.getElementById('blockedDeleteModal'));
        modalEl.show();
    });

    // Add data labels for mobile view
    function addDataLabelsForMobile() {
        if ($(window).width() < 768) {
            $('#employeeTable tbody td').each(function(index) {
                let label = '';
                switch(index) {
                    case 0: label = 'Select'; break;
                    case 1: label = 'Employee ID'; break;
                    case 2: label = 'Employee Details'; break;
                    case 3: label = 'Contact'; break;
                    case 4: label = 'Role & Reporting'; break;
                    case 5: label = 'Status'; break;
                    case 6: label = 'Actions'; break;
                }
                $(this).attr('data-label', label);
            });
        }
    }

    // Initialize on load and resize
    $(window).on('load resize', function() {
        addDataLabelsForMobile();
    });

    // Initialize everything
    updateSelectedEmployees();
    updateSelectAllCheckbox();
    addDataLabelsForMobile();

    // Add keyboard shortcuts
    $(document).on('keydown', function(e) {
        // Ctrl+A to select all
        if (e.ctrlKey && e.key === 'a') {
            e.preventDefault();
            $('#selectAll').click();
        }

        // Escape to deselect all
        if (e.key === 'Escape') {
            $('.employee-checkbox:checked').prop('checked', false).trigger('change');
        }
    });
});
</script>

{{-- REQUIRED LIBRARIES --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>

@endpush

@endsection
