@extends('admin.layout.app')

@section('content')
<div class="container mt-4">

    {{-- Breadcrumb (inlined here so no separate partial required) --}}
    <nav aria-label="breadcrumb" class="mb-3">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active" aria-current="page">Employees</li>
      </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Employee List</h4>
        <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
            <!-- Quick Actions -->
            <div class="d-flex align-items-center gap-2">
                <select class="form-select" id="quick-action-type" style="min-width: 150px;">
                    <option value="">No Action</option>
                    <option value="change-status">Change Status</option>
                    <option value="delete">Delete</option>
                </select>

                <select class="form-select d-none" id="quick-action-status" style="min-width: 120px;">
                    <option value="Inactive">Inactive</option>
                    <option value="Active">Active</option>
                </select>

                <button class="btn btn-primary" id="quick-action-apply" disabled>Apply</button>
            </div>

            <!-- Add Employee -->
            <a href="{{ route('employees.create') }}" class="btn btn-primary">Add Employee</a>

            <!-- Invite Employee -->
            <button type="button" class="btn btn-secondary rounded f-14 p-2"
                    data-bs-toggle="modal" data-bs-target="#inviteModal">
                <i class="fa fa-plus me-1"></i> Invite Employee
            </button>

        </div>
    </div>

    <!-- Invite Modal -->
    <div class="modal fade" id="inviteModal" tabindex="-1" aria-labelledby="inviteModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <!-- Header -->
          <div class="modal-header">
            <h5 class="modal-title" id="inviteModalLabel">Invite member to Xinksoft Technologies Pvt. Ltd.</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>

          <!-- Body -->
          <div class="modal-body">
            <!-- Nav Tabs -->
            <ul class="nav nav-tabs" id="inviteTabs" role="tablist">
              <li class="nav-item" role="presentation">
                <button class="nav-link active" id="invite-email-tab" data-bs-toggle="tab" data-bs-target="#invite-email" type="button" role="tab">Invite by email</button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link" id="invite-link-tab" data-bs-toggle="tab" data-bs-target="#invite-link" type="button" role="tab">Invite by link</button>
              </li>
            </ul>

            <!-- Tab Content -->
            <div class="tab-content mt-3" id="inviteTabsContent">

              <!-- Invite by Email -->
              <div class="tab-pane fade show active" id="invite-email" role="tabpanel">
                <div class="alert alert-secondary">
                  <i class="fa fa-info-circle"></i> Employees will receive an email to log in and update their profile through the self-service portal.
                </div>

                <form id="inviteEmailForm" autocomplete="off">
                  @csrf
                  <div class="mb-3">
                    <label class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" name="email" id="inviteEmail" class="form-control" placeholder="e.g. johndoe@example.com" required>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Message</label>
                    <textarea name="message" id="inviteMessage" class="form-control" rows="3" placeholder="Add a message (optional)"></textarea>
                  </div>

                  <div class="d-flex gap-2">
                    <button type="submit" id="sendInviteBtn" class="btn btn-primary">
                      <span id="sendInviteSpinner" class="spinner-border spinner-border-sm me-2 d-none" role="status" aria-hidden="true"></span>
                      Send Invite
                    </button>

                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                  </div>

                  <div class="mt-3" id="inviteEmailAlert" style="display:none;"></div>
                </form>
              </div>

              <!-- Invite by Link -->
              <div class="tab-pane fade" id="invite-link" role="tabpanel">
                <p>Create an invite link for members to join.</p>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="linkOption" id="anyEmail" checked>
                  <label class="form-check-label" for="anyEmail">Allow any email address</label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="linkOption" id="domainEmail">
                  <label class="form-check-label" for="domainEmail">Only allow email addresses with domain <b>@xinksoft.com</b></label>
                </div>
                <button type="button" class="btn btn-primary mt-3" id="createLinkBtn"><i class="fa fa-link"></i> Create Link</button>

                <div class="mt-2" id="linkContainer" style="display:none;">
                  <div class="alert alert-success" id="linkAlert">Invitation link created successfully.</div>
                  <div class="input-group">
                    <input type="text" class="form-control" id="inviteLink" readonly>
                    <button class="btn btn-outline-secondary" type="button" id="copyLinkBtn">Copy</button>
                  </div>
                </div>
              </div>

            </div>
          </div>

        </div>
      </div>
    </div>

    <!-- Blocked delete info modal (one-time) -->
    <div class="modal fade" id="blockedDeleteModal" tabindex="-1" aria-labelledby="blockedDeleteModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="blockedDeleteModalLabel">Cannot delete employee</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <!-- injected by JS -->
          </div>
          <div class="modal-footer">
            <a href="#" id="blocked-view-subordinates" class="btn btn-outline-primary d-none">View reporting employees</a>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">OK</button>
          </div>
        </div>
      </div>
    </div>

    <form method="GET" action="{{ route('employees.index') }}" class="row g-3 mb-3">

      <!-- Employee ID -->
      <div class="col-md-3">
          <label class="form-label">Employee ID</label>
          <select name="employee_id" class="form-select select2">
              <option value="">All</option>
              @php
                // build dropdown options excluding notice/probation employees
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

      &nbsp;

      <!-- Designation -->
      <div class="col-md-3">
          <label class="form-label">Designation</label>
          <select name="designation_id" class="form-select select2">
              <option value="">All</option>
              @foreach($designations as $designation)
                  <option value="{{ $designation->id }}"
                          {{ request('designation_id') == $designation->id ? 'selected' : '' }}>
                      {{ $designation->name }}
                  </option>
              @endforeach
          </select>
      </div>

      <!-- Search Name/Email -->
      <div class="col-md-3">
          <label class="form-label">Search Name/Email</label>
          <select name="user_id" class="form-select select2">
              <option value="">All</option>
              @foreach($edOptions as $detail)
                  <option value="{{ $detail->user_id }}"
                          {{ request('user_id') == $detail->user_id ? 'selected' : '' }}>
                      {{ $detail->user->name ?? 'N/A' }} - {{ $detail->user->email ?? 'N/A' }}
                  </option>
              @endforeach
          </select>
      </div>

      <!-- Buttons -->
      <div class="col-md-3 d-flex align-items-end">
          <button type="submit" class="btn btn-primary me-2">Filter</button>
          <a href="{{ route('employees.index') }}" class="btn btn-secondary">Reset</a>
      </div>
    </form>

    &nbsp;

    {{-- Show delete/error messages ONLY when actions attempted --}}
    @if($errors->any())
    <div class="alert alert-danger">
        {{ $errors->first() }}
    </div>
    @endif

    @if(session('success'))
    <div class="alert alert-success" style="background-color: #28a745; color: white; border-color: #28a745;">
        {{ session('success') }}
    </div>
    @endif

    {{-- ========== NEW: Export Selected Rows Buttons ========== --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body py-2">
                    <div class="d-flex align-items-center gap-3 flex-wrap">
                        <h6 class="mb-0 text-primary fw-bold">Export Selected Rows:</h6>
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-outline-secondary" id="export-copy" disabled>
                                <i class="fas fa-copy me-1"></i> Copy
                            </button>
                            <button type="button" class="btn btn-outline-info" id="export-csv" disabled>
                                <i class="fas fa-file-csv me-1"></i> CSV
                            </button>
                            <button type="button" class="btn btn-outline-success" id="export-excel" disabled>
                                <i class="fas fa-file-excel me-1"></i> Excel
                            </button>
                            <button type="button" class="btn btn-outline-danger" id="export-pdf" disabled>
                                <i class="fas fa-file-pdf me-1"></i> PDF
                            </button>
                            <button type="button" class="btn btn-outline-primary" id="export-print" disabled>
                                <i class="fas fa-print me-1"></i> Print
                            </button>
                        </div>
                        <button type="button" class="btn btn-warning" id="export-all">
                            <i class="fas fa-download me-1"></i> Export All
                        </button>
                        <span id="export-selected-count" class="text-muted small">0 rows selected</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @php
      // Prepare the visible employees collection by filtering out notice/probation entries
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

    <div class="table-responsive">
        <table id="employeeTable" class="table table-bordered table-striped align-middle">
            <thead class="table-dark">
                <tr>
                    <th style="width: 50px;">
                        <input type="checkbox" id="selectAll" title="Select/Deselect All">
                    </th>
                    <th style="white-space: nowrap; width: 120px;">Employee ID</th>
                    <th style="width: 200px;">Name</th>
                    <th style="width: 200px;">Email</th>
                    <th style="width: 150px;">User Role</th>
                    <th style="white-space: nowrap; width: 150px;">Reporting To</th>
                    <th style="width: 100px;">Status</th>
                    <th style="width: 100px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @if($visibleEmployees->isEmpty())
                    <tr>
                        <td colspan="8" class="text-center py-4">No employees found.</td>
                    </tr>
                @else
                    @foreach($visibleEmployees as $employee)
                      <tr id="employee-row-{{ $employee->id }}"
                          @if(isset($employee->subordinate_count) && $employee->subordinate_count > 0) style="background-color: #ffffff;" @endif
                          data-employee-id="{{ $employee->id }}">

                            <td>
                                {{-- ===== REPORTING-TO INTEGRITY: disable checkbox if has subordinates ===== --}}
                                <input type="checkbox" class="employee-checkbox" value="{{ $employee->id }}"
                                       @if(isset($employee->subordinate_count) && $employee->subordinate_count > 0) disabled @endif
                                       data-employee-id="{{ $employee->employeeDetail?->employee_id ?? '-' }}"
                                       data-name="{{ htmlspecialchars($employee->name, ENT_QUOTES, 'UTF-8') }}"
                                       data-email="{{ $employee->email ?? '-' }}"
                                       data-designation="{{ htmlspecialchars($employee->employeeDetail?->designation?->name ?? '-', ENT_QUOTES, 'UTF-8') }}"
                                       data-reporting-to="{{ htmlspecialchars($employee->employeeDetail?->reportingTo?->name ?? 'N/A', ENT_QUOTES, 'UTF-8') }}"
                                       data-status="{{ $employee->employeeDetail?->status === 'Active' ? 'Active' : ($employee->employeeDetail?->status === 'Inactive' ? 'Inactive' : 'N/A') }}">
                            </td>

                            {{-- Employee ID --}}
                            <td>{{ $employee->employeeDetail?->employee_id ?? '-' }}</td>

                            {{-- Name, profile and badges --}}
                            <td style="white-space: nowrap;">
                                <div>
                                    @if(!empty($employee->profile_image))
                                        <img src="{{ asset($employee->profile_image) }}" alt="Profile Image" width="50" height="50" class="rounded-circle mb-2">
                                    @endif

                                    <strong>{{ $employee->name }}</strong><br>
                                    <small class="text-muted">{{ $employee->employeeDetail?->designation?->name ?? '-' }}</small><br>

                                    @php
                                        $detail = $employee->employeeDetail;
                                        $status = $detail?->employment_status ?? null;
                                        $designationName = strtolower($detail?->designation?->name ?? '');
                                        $probationEnd = $detail?->probation_end_date ? \Carbon\Carbon::parse($detail->probation_end_date) : null;
                                        $noticeEnd = $detail?->notice_end_date ? \Carbon\Carbon::parse($detail->notice_end_date) : null;
                                    @endphp

                                    <div class="mt-1">
                                        {{-- Priority: Notice -> Intern -> Employee --}}
                                        @if ($status === 'notice')
                                            <span class="badge rounded-pill" style="background-color:#ffc107; color:#212529;">
                                                On Notice Period
                                                @if($noticeEnd)
                                                    (Ends: {{ $noticeEnd->format('d M Y') }})
                                                @endif
                                            </span>

                                        @elseif (strpos($designationName, 'intern') !== false)
                                            <span class="badge rounded-pill" style="background-color:#0dcaf0; color:#000;">
                                                Intern
                                            </span>

                                        @else
                                            <span class="badge rounded-pill" style="background-color:#0d6efd; color:#fff;">
                                                Employee
                                            </span>

                                            @if ($status === 'probation' && $probationEnd)
                                                <span class="badge rounded-pill ms-1" style="background-color:#6c757d; color:#fff;">
                                                    Probation ends {{ $probationEnd->format('d M Y') }}
                                                </span>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            </td>

                            {{-- Email --}}
                            <td>{{ $employee->email ?? '-' }}</td>

                            {{-- User Role / Designation --}}
                            <td style="white-space: nowrap;">{{ $employee->employeeDetail?->designation?->name ?? '-' }}</td>

                            {{-- Reporting To --}}
                            <td style="white-space: nowrap;">{{ $employee->employeeDetail?->reportingTo?->name ?? 'N/A' }}</td>

                            {{-- Status --}}
                            <td>
                                @if($employee->employeeDetail?->status === 'Active')
                                    <span class="badge bg-success">Active</span>
                                @elseif($employee->employeeDetail?->status === 'Inactive')
                                    <span class="badge bg-danger">Inactive</span>
                                @else
                                    <span class="badge bg-secondary">N/A</span>
                                @endif
                            </td>

                            {{-- Actions --}}
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light" type="button" id="dropdownMenuButton{{ $employee->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>

                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton{{ $employee->id }}">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('employees.show', $employee->id) }}">
                                                <i class="bi bi-eye me-2"></i> View
                                            </a>
                                        </li>

                                        <li>
                                            <a class="dropdown-item" href="{{ route('employees.edit', $employee->id) }}">
                                                <i class="bi bi-pencil-square me-2"></i> Edit
                                            </a>
                                        </li>

                                        <li>
                                            {{-- ===== REPORTING-TO INTEGRITY: disabled-looking item opens info modal when clicked ===== --}}
                                            @if(isset($employee->subordinate_count) && $employee->subordinate_count > 0)
                                                <button class="dropdown-item text-muted blocked-delete-btn" type="button"
                                                    data-employee-name="{{ $employee->name }}"
                                                    data-employee-id="{{ $employee->id }}"
                                                    title="Cannot delete because employees report to this user">
                                                    <i class="bi bi-trash me-2"></i> Delete (Not Allowed)
                                                </button>
                                            @else
                                                <form action="{{ route('employees.destroy', $employee->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this employee?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="dropdown-item text-danger" type="submit">
                                                        <i class="bi bi-trash me-2"></i> Delete
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

        {{-- Bulk delete control placed under the table (near the "Showing x to y" area) --}}
        <div class="d-flex justify-content-between align-items-center mt-2">
            <div>
                <button id="btn-bulk-delete" class="btn btn-danger" disabled>
                    <i class="bi bi-trash me-1"></i> Delete All
                </button>
                <span id="bulk-selected-count" class="ms-2 text-muted">0 selected</span>
            </div>

            {{-- If you paginate in controller --}}
            @if(method_exists($employees, 'links'))
                <div>
                    {{ $employees->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

{{-- ========== FIXED: Custom CSS for Better UI ========== --}}
@section('styles')
<style>
    /* Enhanced UI for export buttons */
    .btn-group .btn {
        border-radius: 6px !important;
        margin: 2px;
        transition: all 0.3s ease;
        font-weight: 500;
        padding: 8px 16px;
    }

    .btn-group .btn:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    .btn-group .btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    /* Selected row styling */
    tr.selected {
        background-color: rgba(13, 110, 253, 0.1) !important;
        border-left: 3px solid #0d6efd !important;
    }

    /* Card styling for export section */
    .card {
        border-radius: 10px;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    }

    /* Table improvements */
    #employeeTable th {
        position: sticky;
        top: 0;
        z-index: 10;
        background-color: #2c3e50;
    }

    #employeeTable tbody tr:hover {
        background-color: rgba(0, 0, 0, 0.02);
        cursor: pointer;
    }

    /* Checkbox styling */
    .employee-checkbox:checked {
        background-color: #0d6efd;
        border-color: #0d6efd;
    }

    /* Status badges */
    .badge {
        font-size: 0.75em;
        padding: 4px 8px;
    }

    /* Select All checkbox */
    #selectAll {
        cursor: pointer;
    }
</style>
@append

@push('js')
{{-- ========== COMPLETELY FIXED JAVASCRIPT ========== --}}
<script>
$(document).ready(function () {
    // Store selected employee data
    let selectedEmployees = [];

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

    // ===== FIXED: CHECKBOX HANDLING =====

    // Select all checkboxes - COMPLETELY FIXED
    $('#selectAll').on('click', function() {
        const isChecked = $(this).prop('checked');

        // Get all enabled checkboxes
        const enabledCheckboxes = $('.employee-checkbox:not(:disabled)');

        if (isChecked) {
            // Select all enabled checkboxes
            enabledCheckboxes.prop('checked', true);
        } else {
            // Deselect all checkboxes (including disabled ones if somehow checked)
            $('.employee-checkbox').prop('checked', false);
        }

        // Trigger change event on all checkboxes
        enabledCheckboxes.trigger('change');
    });

    // Individual checkbox change - FIXED
    $(document).on('change', '.employee-checkbox', function() {
        updateSelectedEmployees();
        updateSelectAllCheckbox();
    });

    // Row click to select/deselect - FIXED
    $(document).on('click', 'tbody tr', function(e) {
        // Don't trigger if clicking on checkbox, dropdown, or link
        if ($(e.target).is('input[type="checkbox"]') ||
            $(e.target).closest('.dropdown, a, button, form').length ||
            $(e.target).is('a, button, .dropdown-item, img')) {
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
        const totalEmployees = $('.employee-checkbox:not(:disabled)').length;

        // Update UI counters
        $('#export-selected-count').text(selectedCount + ' row' + (selectedCount !== 1 ? 's' : '') + ' selected');
        $('#bulk-selected-count').text(selectedCount + ' selected');

        // Enable/disable export buttons
        const exportButtons = ['#export-copy', '#export-csv', '#export-excel', '#export-pdf', '#export-print'];
        exportButtons.forEach(btn => {
            $(btn).prop('disabled', selectedCount === 0);
        });

        // Enable/disable bulk delete button
        $('#btn-bulk-delete').prop('disabled', selectedCount === 0);
        $('#quick-action-apply').prop('disabled', selectedCount === 0 || $('#quick-action-type').val() === '');

        // Update row selection styling
        $('tbody tr').removeClass('selected');
        $('.employee-checkbox:checked:not(:disabled)').closest('tr').addClass('selected');
    }

    // ===== FIXED: EXPORT FUNCTIONS =====

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

    // Copy selected rows to clipboard - FIXED
    $('#export-copy').on('click', function() {
        if (selectedEmployees.length === 0) {
            alert('Please select at least one row to copy.');
            return;
        }

        const data = getDataForExport(false);
        const csvContent = data.map(row =>
            row.map(cell => `"${String(cell).replace(/"/g, '""')}"`).join('\t')
        ).join('\n');

        navigator.clipboard.writeText(csvContent)
            .then(() => {
                alert('Selected rows copied to clipboard!');
            })
            .catch(err => {
                console.error('Failed to copy:', err);
                alert('Failed to copy to clipboard. Please try again.');
            });
    });

    // Export to CSV - FIXED
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
        const url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', `selected_employees_${new Date().toISOString().split('T')[0]}.csv`);
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    });

    // Export to Excel with perfect alignment - FIXED
    $('#export-excel').on('click', function() {
        if (selectedEmployees.length === 0) {
            alert('Please select at least one row to export.');
            return;
        }

        const data = getDataForExport(false);

        // Create workbook and worksheet
        const wb = XLSX.utils.book_new();
        const ws = XLSX.utils.aoa_to_sheet(data);

        // Set column widths for perfect alignment
        const colWidths = [
            { wch: 15 }, // Employee ID
            { wch: 25 }, // Name
            { wch: 30 }, // Email
            { wch: 20 }, // Designation
            { wch: 20 }, // Reporting To
            { wch: 15 }  // Status
        ];
        ws['!cols'] = colWidths;

        // Center align all cells
        const range = XLSX.utils.decode_range(ws['!ref']);
        for (let R = range.s.r; R <= range.e.r; ++R) {
            for (let C = range.s.c; C <= range.e.c; ++C) {
                const cell_address = { c: C, r: R };
                const cell_ref = XLSX.utils.encode_cell(cell_address);
                if (!ws[cell_ref]) continue;

                // Style header row
                if (R === 0) {
                    ws[cell_ref].s = {
                        font: { bold: true, color: { rgb: "FFFFFF" } },
                        fill: { fgColor: { rgb: "4F81BD" } },
                        alignment: { horizontal: "center", vertical: "center" }
                    };
                } else {
                    ws[cell_ref].s = {
                        alignment: { horizontal: "center", vertical: "center" },
                        border: {
                            top: { style: "thin", color: { rgb: "000000" } },
                            bottom: { style: "thin", color: { rgb: "000000" } },
                            left: { style: "thin", color: { rgb: "000000" } },
                            right: { style: "thin", color: { rgb: "000000" } }
                        }
                    };
                }
            }
        }

        // Add worksheet to workbook
        XLSX.utils.book_append_sheet(wb, ws, "Selected Employees");

        // Generate Excel file
        XLSX.writeFile(wb, `selected_employees_${new Date().toISOString().split('T')[0]}.xlsx`);
    });

    // Export to PDF - FIXED
    $('#export-pdf').on('click', function() {
        if (selectedEmployees.length === 0) {
            alert('Please select at least one row to export.');
            return;
        }

        const data = getDataForExport(false);

        const { jsPDF } = window.jspdf;
        const doc = new jsPDF('landscape');

        // Add title
        doc.setFontSize(16);
        doc.text('Selected Employees - Xinksoft Technologies', 14, 15);
        doc.setFontSize(10);
        doc.text(`Generated: ${new Date().toLocaleDateString()}`, 14, 22);

        // Prepare table data
        const headers = [['Employee ID', 'Name', 'Email', 'Designation', 'Reporting To', 'Status']];
        const tableData = data.slice(1); // Remove headers

        // Create table
        doc.autoTable({
            head: headers,
            body: tableData,
            startY: 30,
            theme: 'grid',
            headStyles: { fillColor: [41, 128, 185], textColor: 255 },
            columnStyles: {
                0: { cellWidth: 25 },
                1: { cellWidth: 35 },
                2: { cellWidth: 45 },
                3: { cellWidth: 30 },
                4: { cellWidth: 35 },
                5: { cellWidth: 20 }
            },
            styles: { fontSize: 9, cellPadding: 3, textColor: [50, 50, 50] },
            margin: { left: 14 }
        });

        // Save PDF
        doc.save(`selected_employees_${new Date().toISOString().split('T')[0]}.pdf`);
    });

    // Print selected rows - FIXED
    $('#export-print').on('click', function() {
        if (selectedEmployees.length === 0) {
            alert('Please select at least one row to print.');
            return;
        }

        const data = getDataForExport(false);
        const tableData = data.slice(1); // Remove headers

        const printWindow = window.open('', '_blank');
        printWindow.document.write(`
            <html>
            <head>
                <title>Selected Employees - Xinksoft Technologies</title>
                <style>
                    body { font-family: Arial, sans-serif; margin: 20px; }
                    h1 { color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 10px; }
                    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                    th { background-color: #2c3e50; color: white; padding: 10px; text-align: left; }
                    td { padding: 8px; border-bottom: 1px solid #ddd; }
                    tr:nth-child(even) { background-color: #f2f2f2; }
                    .header-info { margin-bottom: 20px; color: #666; }
                    @media print {
                        body { margin: 0; }
                        .no-print { display: none; }
                    }
                </style>
            </head>
            <body>
                <h1>Selected Employees Report</h1>
                <div class="header-info">
                    <p><strong>Company:</strong> Xinksoft Technologies Pvt. Ltd.</p>
                    <p><strong>Generated:</strong> ${new Date().toLocaleDateString()}</p>
                    <p><strong>Total Selected:</strong> ${tableData.length} employee(s)</p>
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
                                <td>${row[5] || 'N/A'}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
                <div class="no-print" style="margin-top: 30px; text-align: center;">
                    <button onclick="window.print()" style="padding: 10px 20px; background: #3498db; color: white; border: none; border-radius: 4px; cursor: pointer;">
                        Print Report
                    </button>
                    <button onclick="window.close()" style="padding: 10px 20px; background: #e74c3c; color: white; border: none; border-radius: 4px; cursor: pointer; margin-left: 10px;">
                        Close Window
                    </button>
                </div>
            </body>
            </html>
        `);
        printWindow.document.close();
    });

    // Export ALL employees - FIXED
    $('#export-all').on('click', function() {
        const totalEmployees = $('.employee-checkbox:not(:disabled)').length;
        if (totalEmployees === 0) {
            alert('No employees available to export.');
            return;
        }

        if (!confirm(`Export ALL ${totalEmployees} employees from the table?`)) return;

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
    });

    // Initialize everything
    updateSelectedEmployees();
    updateSelectAllCheckbox();
});
</script>

{{-- ========== REQUIRED LIBRARIES ========== --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>

{{-- ========== EXISTING FUNCTIONALITY (UNCHANGED) ========== --}}
<script>
    $(document).ready(function() {
        $('.select2').select2({
            allowClear: true,
            width: '100%'
        });
    });
</script>

<script>
$(document).ready(function () {

  // ---------- Invite by Email AJAX ----------
  $('#inviteEmailForm').on('submit', function(e) {
    e.preventDefault();

    const email = $('#inviteEmail').val().trim();
    const message = $('#inviteMessage').val().trim();
    const $btn = $('#sendInviteBtn');
    const $spinner = $('#sendInviteSpinner');
    const $alert = $('#inviteEmailAlert');

    if (!email) {
      $alert.removeClass().addClass('alert alert-danger').text('Please enter a valid email.').show();
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
        $alert.removeClass().addClass('alert alert-success').text(res.message || 'Invitation sent successfully.').show();
        setTimeout(function() {
          $('#inviteModal').modal('hide');
          location.reload();
        }, 900);
      },
      error: function(xhr) {
        let errMsg = 'Failed to send invite.';
        if (xhr?.responseJSON?.error) errMsg = xhr.responseJSON.error;
        else if (xhr?.responseJSON?.message) errMsg = xhr.responseJSON.message;
        else if (xhr?.statusText) errMsg = xhr.statusText;

        $alert.removeClass().addClass('alert alert-danger').text(errMsg).show();
        console.error('Invite error:', xhr);
      },
      complete: function() {
        $btn.prop('disabled', false);
        $spinner.addClass('d-none');
      }
    });
  });


  // ---------- Invite by Link (client-side quick fallback) ----------
  function generateToken(length = 40) {
    const chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    let token = '';
    for (let i = 0; i < length; i++) {
      token += chars.charAt(Math.floor(Math.random() * Math.random() * chars.length));
    }
    return token;
  }

  $('#createLinkBtn').on('click', function() {
    const token = generateToken();
    const link = `{{ rtrim(url('/'), '/') }}` + '/register?invitation=' + token;

    $('#linkContainer').show();
    $('#inviteLink').val(link);
  });

  $('#copyLinkBtn').on('click', function() {
    const linkInput = document.getElementById('inviteLink');
    linkInput.select();
    linkInput.setSelectionRange(0, 99999);
    navigator.clipboard?.writeText(linkInput.value)
      .then(() => alert('Invitation link copied successfully!'))
      .catch(() => {
        document.execCommand('copy');
        alert('Invitation link copied successfully!');
      });
  });

});
</script>

<script>
$(document).ready(function() {

    // Show status dropdown if "Change Status" is selected
    $('#quick-action-type').on('change', function() {
        if ($(this).val() === 'change-status') {
            $('#quick-action-status').removeClass('d-none');
        } else {
            $('#quick-action-status').addClass('d-none');
        }
        toggleApplyButton();
    });

    // Enable apply button only if at least one employee is selected
    $(document).on('change', '.employee-checkbox, #quick-action-type, #quick-action-status', toggleApplyButton);

    function toggleApplyButton() {
        let anyChecked = $('.employee-checkbox:checked').length > 0;
        let actionSelected = $('#quick-action-type').val() !== '';
        $('#quick-action-apply').prop('disabled', !(anyChecked && actionSelected));
    }

    // Apply quick action
    $('#quick-action-apply').on('click', function() {
        let selectedIds = $('.employee-checkbox:checked').map(function() { return $(this).val(); }).get();
        let actionType = $('#quick-action-type').val();
        let status = $('#quick-action-status').val();

        if (!selectedIds.length) {
            alert('Please select at least one employee.');
            return;
        }

        if (actionType === 'change-status' && status) {
            $.ajax({
                url: '{{ route("employees.bulkUpdateStatus") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    employee_ids: selectedIds,
                    status: status
                },
                success: function(response) {
                    location.reload();
                },
                error: function(err) {
                    alert('Something went wrong!');
                }
            });
        }

        if (actionType === 'delete') {
            if (!confirm('Are you sure you want to delete the selected employees?')) return;

            $.ajax({
                url: '{{ route("employees.bulk.delete") }}',
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}',
                    employee_ids: selectedIds
                },
                success: function(response) {
                    location.reload();
                },
                error: function(err) {
                    alert('Failed to delete selected employees.');
                }
            });
        }
    });

    // ---------- Bulk Delete button (under the table) ----------
    $('#btn-bulk-delete').on('click', function() {
        let selectedIds = $('.employee-checkbox:checked').map(function() { return $(this).val(); }).get();

        if (!selectedIds.length) {
            alert('Please select at least one employee to delete.');
            return;
        }

        if (!confirm('Are you sure you want to permanently delete the selected employees?')) return;

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
                alert(res.message || 'Selected employees deleted successfully.');
                location.reload();
            },
            error: function(xhr) {
                let msg = 'Failed to delete selected employees.';
                if (xhr?.responseJSON?.message) msg = xhr.responseJSON.message;
                alert(msg);
                console.error('Bulk delete error:', xhr);
            },
            complete: function() {
                $('#bulkDeleteSpinner').remove();
                $btn.prop('disabled', false);
            }
        });
    });

    // Optional: prevent double form submissions for dropdown single-delete forms (existing)
    $(document).on('submit', 'form', function() {
        $(this).find('button[type="submit"]').prop('disabled', true);
    });

    // ===== Show friendly modal when admin clicks the disabled-looking Delete item =====
    $(document).on('click', '.blocked-delete-btn', function (e) {
        e.preventDefault();

        const name = $(this).data('employee-name') || 'this employee';
        const id = $(this).data('employee-id');

        const msg = `You cannot delete ${name} because other employees report to them.`;

        $('#blockedDeleteModal .modal-body').html('<p class="mb-0">' + msg + '</p>');

        // Provide a link to the employee show page (adjust if your route differs)
        const viewUrl = '{{ rtrim(url('/'), '/') }}' + '/employees/' + id;
        $('#blocked-view-subordinates').attr('href', viewUrl).removeClass('d-none');

        const modalEl = new bootstrap.Modal(document.getElementById('blockedDeleteModal'));
        modalEl.show();
    });

});
</script>
@endpush

@endsection
