@extends('admin.layout.app')

@section('content')
<main id="main" class="main">
    <div class="container">
      &nbsp;
      <h4>Projects</h4>
       &nbsp;

        {{-- CSRF meta for ajax/fetch --}}
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <!-- Search/Filter Form -->
        <form method="GET" action="{{ route('projects.index') }}" class="row g-3 align-items-end mb-4">

            <div class="col-md-2">
                <label for="start_date" class="form-label">Start Date</label>
                <input type="date" name="start_date" id="start_date" class="form-control" value="{{ request('start_date') }}">
            </div>

            <div class="col-md-2">
                <label for="end_date" class="form-label">End Date</label>
                <input type="date" name="end_date" id="end_date" class="form-control" value="{{ request('end_date') }}">
            </div>

            <div class="col-md-2">
                <label for="status" class="form-label">Status</label>
                <select name="status" id="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="in progress" {{ request('status') == 'in progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                </select>
            </div>

           <div class="col-md-3">
                <label for="progress" class="form-label">Progress</label>
                <select class="form-select select2" multiple name="progress[]" id="progress" data-live-search="true" data-size="8">
                    <option value="0-20" {{ in_array('0-20', request('progress', [])) ? 'selected' : '' }}>0% - 20%</option>
                    <option value="21-40" {{ in_array('21-40', request('progress', [])) ? 'selected' : '' }}>21% - 40%</option>
                    <option value="41-60" {{ in_array('41-60', request('progress', [])) ? 'selected' : '' }}>41% - 60%</option>
                    <option value="61-80" {{ in_array('61-80', request('progress', [])) ? 'selected' : '' }}>61% - 80%</option>
                    <option value="81-99" {{ in_array('81-99', request('progress', [])) ? 'selected' : '' }}>81% - 99%</option>
                    <option value="100-100" {{ in_array('100-100', request('progress', [])) ? 'selected' : '' }}>100%</option>
                </select>
            </div>

            <div class="col-md-3">
                <label for="search" class="form-label">Project Name</label>
                <input type="text" name="search" id="search" class="form-control" placeholder="Search project..." value="{{ request('search') }}">
            </div>

            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">Filter</button>
                <a href="{{ route('projects.index') }}" class="btn btn-secondary">Reset</a>
            </div>
        </form>

&nbsp;
      <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">

    <!-- Left: Add Project Button (only for admin) -->
    @if(auth()->user()->role === 'admin')
        <div class="mb-2">
            <a href="{{ route('projects.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i> Add Project
            </a>
        </div>
    @else
        <div></div> <!-- Empty div to maintain spacing for employee -->
    @endif

    <!-- Right: Icon Buttons (always right-aligned for employee & admin) -->
    <div class="btn-group mb-2 ms-auto" role="group">

         <div class="d-flex align-items-center mb-3">
            <div class="btn-group align-items-center" role="group">
                <!-- Bulk status select (values match DB enum) -->
                <select id="bulkProjectStatus" class="form-select form-select-sm" disabled>
                    <option value="">Change Status</option>
                    <option value="not started">Not Started</option>
                    <option value="in progress">In Progress</option>
                    <option value="on hold">On Hold</option>
                    <option value="completed">Completed</option>
                </select>

                <!-- Apply button -->
                <button id="applyBulkProjectStatus" class="btn btn-primary btn-sm ms-2" disabled>Apply</button>
            </div>
        </div>

        &nbsp;
        <!-- Projects -->
        <a href="{{ route('projects.index') }}" class="btn btn-secondary f-14 btn-active projects"
           data-bs-toggle="tooltip" data-bs-placement="top" title="Projects">
           <i class="bi bi-list-ul"></i>
        </a>

        @if(auth()->user()->role === 'admin')
        <!-- Archive -->
        <a href="{{ route('projects.archive') }}" class="btn btn-secondary f-14"
           data-bs-toggle="tooltip" data-bs-placement="top" title="Archive">
           <i class="bi bi-archive"></i>
        </a>
        @endif

        <!-- Calendar -->
        <a href="{{ route('projects.calendar') }}" class="btn btn-secondary f-14"
           data-bs-toggle="tooltip" data-bs-placement="top" title="Calendar">
           <i class="bi bi-calendar"></i>
        </a>

        <!-- Pinned -->
        <a href="javascript:;" class="btn btn-secondary f-14 show-pinned"
           data-bs-toggle="tooltip" data-bs-placement="top" title="Pinned">
           <i class="bi bi-pin-angle"></i>
        </a>
    </div>

</div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

         <table id="projectTable" class="table table-bordered table-striped">
            <thead>
                <tr>
                     <th><input type="checkbox" id="selectAllProjects"></th>
                    <th>Code</th>
                    <th>Project</th>
                    <th>Client</th>
                    <th>Members</th>
                    <th style="white-space: nowrap;">Start Date</th>
                    <th>Deadline</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($projects as $project)
                <tr data-project-id="{{ $project->id }}">
                    <td><input type="checkbox" class="project-checkbox" value="{{ $project->id }}"></td>
                    <td>{{ $project->project_code }}</td>
                    <td>{{ $project->name }}</td>
                    <td style="white-space: nowrap;">{{ optional($project->client)->name ?? '-' }}</td>
                   <td style="white-space: nowrap;" >
    @foreach($project->users as $user)
        <span class="badge bg-primary me-1">
            {{ $user->name }}
            @if(!empty($user->employeeDetail->employee_id))
                ({{ $user->employeeDetail->employee_id }})
            @endif
        </span>
    @endforeach
</td>

                    <td style="white-space: nowrap;">{{ $project->start_date }}</td>
                    <td style="white-space: nowrap;">{{ $project->deadline ? $project->deadline : 'No deadline' }}</td>

                    <td style="white-space: nowrap;" class="status-cell">
                        @if(auth()->user()->role === 'admin')
                            <select class="form-select form-select-sm project-status-select" data-project-id="{{ $project->id }}">
                                <option value="not started" {{ $project->status === 'not started' ? 'selected' : '' }}>Not Started</option>
                                <option value="in progress" {{ $project->status === 'in progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="on hold" {{ $project->status === 'on hold' ? 'selected' : '' }}>On Hold</option>
                                <option value="completed" {{ $project->status === 'completed' ? 'selected' : '' }}>Completed</option>
                            </select>
                        @else
                            {{ ucfirst($project->status) }}
                        @endif
                    </td>

                    <td>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-light" type="button" id="dropdownMenuButton{{ $project->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>

                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton{{ $project->id }}">
                                <!-- View -->
                                <li>
                                    <a class="dropdown-item" href="{{ route('projects.show', $project->id) }}">
                                        <i class="bi bi-eye me-2"></i> View
                                    </a>
                                </li>

                                @if(auth()->user()->role === 'admin')
                                <!-- Edit -->
                                <li>
                                    <a class="dropdown-item" href="{{ route('projects.edit', $project->id) }}">
                                        <i class="bi bi-pencil-square me-2"></i> Edit
                                    </a>
                                </li>

                                <!-- Duplicate -->
                                <li>
                                    <button class="dropdown-item" type="button"
                                            data-bs-toggle="modal"
                                            data-bs-target="#duplicateProjectModal{{ $project->id }}">
                                        <i class="bi bi-files me-2"></i> Duplicate
                                    </button>
                                </li>

                                <!-- Gantt Chart -->
                                <li>
                                    <a class="dropdown-item" href="{{ route('projects.gantt', $project->id) }}">
                                        <i class="bi bi-diagram-3 me-2"></i> Gantt Chart
                                    </a>
                                </li>

                                <li>
                                    <a class="dropdown-item" href="{{ route('projects.public-gantt', $project->id) }}">
                                        <i class="bi bi-diagram-3 me-2"></i>Public Gantt Chart
                                    </a>
                                </li>

                                <!-- Public Task Board -->
                                <li>
                                    <a class="dropdown-item" href="{{ route('projects.tasks.board', $project->id) }}">
                                        <i class="bi bi-kanban me-2"></i> Public Task Board
                                    </a>
                                </li>

                                <li>
                                    <form action="{{ route('projects.archive.action', $project->id) }}" method="POST" onsubmit="return confirmArchive();" class="d-inline-block w-100">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-warning">
                                            <i class="bi bi-archive me-2"></i> Archive
                                        </button>
                                    </form>
                                </li>

                                <!-- Delete -->
                                <li>
                                    <form action="{{ route('projects.destroy', $project->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this project?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="dropdown-item text-danger" type="submit">
                                            <i class="bi bi-trash me-2"></i> Delete
                                        </button>
                                    </form>
                                </li>
                                @endif
                            </ul>
                        </div>
                    </td>

                </tr>

                <!-- Duplicate Project Modal -->
               <div class="modal fade" id="duplicateProjectModal{{ $project->id }}" tabindex="-1" aria-labelledby="duplicateProjectLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-centered">
                        <div class="modal-content">

                            <!-- Header -->
                            <div class="modal-header">
                                <h5 class="modal-title" id="duplicateProjectLabel">Copy Project</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>

                            <!-- Body -->
                            <div class="modal-body">
                                <form method="POST" action="{{ route('projects.duplicate', $project->id) }}">
                                    @csrf

                                    <!-- Hidden input for project -->
                                    <input type="hidden" name="duplicateProjectID" value="{{ $project->id }}">

                                    <div class="row">
                                        <!-- Options -->
                                        <div class="col-md-12 mb-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="task" id="task{{ $project->id }}">
                                                <label class="form-check-label" for="task{{ $project->id }}">Tasks</label>
                                            </div>

                                            <div class="ms-4 mt-2 d-none" id="taskOptions{{ $project->id }}">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="sub_task" id="subTask{{ $project->id }}">
                                                    <label class="form-check-label" for="subTask{{ $project->id }}">Copy Sub Tasks</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="same_assignee" id="sameAssignee{{ $project->id }}">
                                                    <label class="form-check-label" for="sameAssignee{{ $project->id }}">Keep Same Assignees</label>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Milestones -->
                                        <div class="col-md-12 mb-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="milestone" id="milestone{{ $project->id }}">
                                                <label class="form-check-label" for="milestone{{ $project->id }}">Milestones</label>
                                            </div>
                                        </div>

                                        <!-- Files -->
                                        <div class="col-md-12 mb-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="file" id="file{{ $project->id }}">
                                                <label class="form-check-label" for="file{{ $project->id }}">Files</label>
                                            </div>
                                        </div>

                                        <!-- Timesheet -->
                                        <div class="col-md-12 mb-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="note" id="timesheet{{ $project->id }}">
                                                <label class="form-check-label" for="timesheet{{ $project->id }}">Timesheet</label>
                                            </div>
                                        </div>

                                        <!-- Notes -->
                                        <div class="col-md-12 mb-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="note" id="note{{ $project->id }}">
                                                <label class="form-check-label" for="note{{ $project->id }}">Notes</label>
                                            </div>
                                        </div>
                                    </div>

                                     <div class="row mt-3">
                                        <div class="col-md-12 mb-2">
                                       <label for="project_code{{ $project->id }}">Short Code</label>

                                         <input type="text" class="form-control" name="project_code" id="project_code{{ $project->id }}" value="{{ $project->project_code}} Copy">

                                        </div>
                                    </div>

                                    <!-- Project Info -->
                                    <div class="row mt-3">
                                        <div class="col-md-12 mb-2">
                                            <label for="project_name{{ $project->id }}">Project Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="project_name" id="project_name{{ $project->id }}" value="{{ $project->name }} Copy">
                                        </div>

                                    </div>

                                  <div class="row">
                                    <!-- Start Date -->
                                    <div class="col-md-4 mb-3">
                                        <label>Start Date <sup class="text-danger">*</sup></label>
                                        <input type="date"
                                               name="start_date"
                                               class="form-control"
                                              value="{{ old('start_date', $project->start_date ? \Carbon\Carbon::parse($project->start_date)->format('Y-m-d') : '') }}"
                                               required>
                                    </div>

                                    <!-- Deadline -->
                                    <div class="col-md-4 mb-3 deadline-wrapper">
                                        <label>Deadline <sup class="text-danger">*</sup></label>
                                        <input type="date"
                                               name="deadline"
                                               class="form-control"
                                               id="deadline_input"
                                              value="{{ old('deadline', $project->deadline ? \Carbon\Carbon::parse($project->deadline)->format('Y-m-d') : '') }}"
                                               {{ is_null($project->deadline) ? 'disabled' : '' }}>
                                    </div>

                                    <!-- No Deadline -->
                                    <div class="col-md-4 mb-3 d-flex align-items-center">
                                        <div class="form-check mt-4">
                                            <input class="form-check-input"
                                                   type="checkbox"
                                                   name="without_deadline"
                                                   id="without_deadline"
                                                   {{ is_null($project->deadline) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="without_deadline">
                                                No deadline for this project
                                            </label>
                                        </div>
                                    </div>
                                </div>

                               <div class="row">
                                <!-- Public Project -->
                                <div class="col-md-12 mb-3">
                                    <div class="form-group">
                                        <div class="d-flex mt-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="public" id="is_public" value="1">
                                                <label class="form-check-label text-dark-grey pl-2 mr-4 cursor-pointer pt-1 text-wrap" for="is_public">
                                                    Create Public Project
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <!-- Department -->
                           <div class="mt-3">
                                <label>Department <sup class="text-danger">*</sup></label>

                          <select class="form-select form-select-sm select2"
        name="user_id[]"
        id="selectEmployee{{ $project->id }}"
        multiple
        required>
    @foreach($users as $employee)
        <option value="{{ $employee->id }}">
            {{ $employee->name }}
            @if(!empty($employee->employeeDetail->employee_id))
                ({{ $employee->employeeDetail->employee_id }})
            @endif
        </option>
    @endforeach
</select>

                            </div>

                                <!-- Members -->
                                  <div class="mt-3">
                                        <label for="selectEmployee{{ $project->id }}">Add Project Members <span class="text-danger">*</span></label>
                                        <select class="form-select form-select-sm select2"
                                                name="user_id[]"
                                                id="selectEmployee{{ $project->id }}"
                                                multiple
                                                required>
                                            @foreach($users as $employee)
                                                <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback">
                                            Please select at least one member.
                                        </div>
                                    </div>

                            </div>

                                   <!-- Client -->
                                 <div class="mt-3">
                                    <label>Client <sup class="text-danger">*</sup></label>
                                    <div class="input-group">
                                        <select name="client_id" id="client_id" class="form-select form-select-sm select2"  required>
                                            <option value="">Select</option>
                                            @foreach($clients as $client)
                                                <option value="{{ $client->id }}">{{ $client->name }}</option>
                                            @endforeach
                                        </select>

                                    </div>
                                </div>

                                    <!-- Submit -->
                                    <div class="mt-4 text-end">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary">Duplicate Project</button>
                                    </div>
                                </form>
                            </div>

                        </div>
                    </div>
                </div>
                @endforeach
            </tbody>
        </table>

    </div>

</main>

@push('js')
<!-- jQuery (required for Select2) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- SweetAlert2 JS (for toasts / alerts) -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>

<!-- JSZip & pdfmake for Excel/PDF export -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

<script>
$(function () {
    const predefinedRanges = {
        'Today': [moment(), moment()],
        'Last 30 Days': [moment().subtract(29, 'days'), moment()],
        'This Month': [moment().startOf('month'), moment().endOf('month')],
        'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
        'Last 90 Days': [moment().subtract(89, 'days'), moment()],
        'Last 6 Months': [moment().subtract(6, 'months').startOf('month'), moment()],
        'Last 1 Year': [moment().subtract(1, 'year').startOf('month'), moment()],
        'Custom Range': []
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
        if (picker.chosenLabel === 'Custom Range') {
            $(this).val(picker.startDate.format('YYYY-MM-DD') + ' to ' + picker.endDate.format('YYYY-MM-DD'));
        } else {
            $(this).val(picker.chosenLabel);
        }
    });

    $('#duration').on('cancel.daterangepicker', function () {
        $(this).val('');
    });
});
</script>

<script>
$(document).ready(function () {
    var table = $('#projectTable').DataTable({
        dom: 'Bfrtip', // Buttons, filter input, table, pagination
        buttons: [
            {
                extend: 'copy',
                text: 'Copy',
                exportOptions: {
                    columns: ':not(:first-child):not(:last-child)' // exclude first & last columns
                }
            },
            {
                extend: 'csv',
                text: 'CSV',
                exportOptions: {
                    columns: ':not(:first-child):not(:last-child)'
                }
            },
            {
                extend: 'excel',
                text: 'Excel',
                exportOptions: {
                    columns: ':not(:first-child):not(:last-child)'
                }
            },
            {
                extend: 'pdf',
                text: 'PDF',
                exportOptions: {
                    columns: ':not(:first-child):not(:last-child)'
                }
            },
            {
                extend: 'print',
                text: 'Print',
                exportOptions: {
                    columns: ':not(:first-child):not(:last-child)'
                }
            }
        ],
        responsive: true,
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search projects..."
        }
    });

    // Insert Bulk Delete directly under "Showing X to Y of Z entries"
    if (!$('#bulkDeleteContainer').length) {
        var info = $('#projectTable_wrapper .dataTables_info');

        $('<div id="bulkDeleteContainer" class="dt-bulk-delete mt-2">' +
            '<button id="bulkDeleteProjects" class="btn btn-danger btn-sm" disabled>Bulk Delete</button>' +
          '</div>').insertAfter(info);
    }
});
</script>

<script>
document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll('[id^="task"]').forEach(taskCheckbox => {
        taskCheckbox.addEventListener("change", function () {
            const projectId = this.id.replace("task", "");
            const taskOptions = document.getElementById("taskOptions" + projectId);
            if (this.checked) {
                taskOptions.classList.remove("d-none");
            } else {
                taskOptions.classList.add("d-none");
            }
        });
    });
});
</script>

<script>
document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll("form").forEach(form => {
        form.addEventListener("submit", function (e) {
            const select = form.querySelector("select[multiple][required]");
            if (select && select.selectedOptions.length === 0) {
                e.preventDefault();
                select.classList.add("is-invalid");
            } else if (select) {
                select.classList.remove("is-invalid");
            }
        });
    });
});
</script>

<script>
$(document).ready(function () {
    $('.select2').each(function () {
        let modalId = $(this).closest('.modal').attr('id');
        $(this).select2({
            dropdownParent: modalId ? $('#' + modalId) : $(document.body),
            placeholder: "Select members",
            allowClear: true,
            width: '100%'
        });
    });
});
</script>

<script>
function confirmArchive() {
    return confirm('Are you sure? Do you want to archive this project?');
}
</script>

<!-- Tooltip Initialization (Bootstrap 5) -->
<script>
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl)
    })
</script>

<script>
$(document).ready(function () {
    // Initialize select2
    $('#departmentSelect').select2({
        placeholder: "Select Departments",
        width: '100%',
        dropdownCssClass: "custom-select2-dropdown"
    });

    // Add custom Select All / Deselect All
    $('#departmentSelect').on('select2:open', function () {
        if (!$('.select2-dropdown .select-all-btns').length) {
            $('.select2-dropdown').prepend(`
                <div class="select-all-btns d-flex justify-content-between px-2 py-1">
                    <a href="javascript:void(0)" id="selectAllDept" class="text-primary small">Select All</a>
                    <a href="javascript:void(0)" id="deselectAllDept" class="text-secondary small">Deselect All</a>
                </div>
            `);

            // Select All
            $('#selectAllDept').on('click', function () {
                let allOptions = $('#departmentSelect option').map(function () {
                    return $(this).val();
                }).get();
                $('#departmentSelect').val(allOptions).trigger('change');
            });

            // Deselect All
            $('#deselectAllDept').on('click', function () {
                $('#departmentSelect').val(null).trigger('change');
            });
        }
    });
});
</script>

<script>
$(document).ready(function() {
    let pinnedVisible = true; // Track current state

    $('.show-pinned').click(function() {
        if (pinnedVisible) {
            // Hide all project rows
            $('#projectTable tbody tr').hide();
        } else {
            // Show all project rows
            $('#projectTable tbody tr').show();
        }
        pinnedVisible = !pinnedVisible; // Toggle state
        // redraw DataTable to keep internal state consistent
        if ($.fn.dataTable && $.fn.dataTable.isDataTable('#projectTable')) {
            $('#projectTable').DataTable().draw(false);
        }
    });
});
</script>

<!-- FINAL: Fixed Select All toggle + row sync + bulk control enabling -->
<script>
$(document).ready(function () {
  // Helper: all project checkboxes currently in DOM
  function allRowCheckboxes() {
    return $('input.project-checkbox');
  }

  // Helper: whether any checkbox is unchecked
  function anyUnchecked() {
    return allRowCheckboxes().filter(':not(:checked)').length > 0;
  }

  // Update header state (checked / indeterminate)
  function refreshHeaderState() {
    var $all = allRowCheckboxes();
    var $checked = $all.filter(':checked');
    var header = $('#selectAllProjects');

    if ($all.length === 0) {
      header.prop('checked', false).prop('indeterminate', false);
      return;
    }

    if ($checked.length === 0) {
      header.prop('checked', false).prop('indeterminate', false);
    } else if ($checked.length === $all.length) {
      header.prop('checked', true).prop('indeterminate', false);
    } else {
      header.prop('checked', false).prop('indeterminate', true);
    }
  }

  // Update bulk controls (enable when at least one is checked)
  function refreshBulkControls() {
    var any = allRowCheckboxes().filter(':checked').length > 0;
    $('#bulkProjectStatus, #applyBulkProjectStatus, #bulkDeleteProjects').prop('disabled', !any);
  }

  // Header click: if any row is unchecked -> select ALL, else -> unselect ALL
  $('#selectAllProjects').off('change.fixedSel').on('change.fixedSel', function () {
    var shouldCheck = anyUnchecked(); // true => select all, false => unselect all
    allRowCheckboxes().each(function () {
      $(this).prop('checked', shouldCheck).trigger('change.fixedSel');
    });
    // After changing, refresh visuals
    refreshHeaderState();
    refreshBulkControls();
  });

  // When any row checkbox changes, update header and bulk controls
  $('#projectTable').off('change.row').on('change.row', 'input.project-checkbox', function () {
    refreshHeaderState();
    refreshBulkControls();
  });

  // If DataTable redraws, ensure header and bulk controls are refreshed
  if ($.fn.dataTable && $.fn.dataTable.isDataTable('#projectTable')) {
    $('#projectTable').DataTable().on('draw', function () {
      // refresh states after redraw
      refreshHeaderState();
      refreshBulkControls();
    });
  }

  // Initial sync on load
  refreshHeaderState();
  refreshBulkControls();
});
</script>

<script>
$(document).ready(function () {
    // Setup CSRF header globally for all jQuery AJAX requests
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
});
</script>

<script>
$(document).ready(function () {
    // Apply bulk status
    $('#applyBulkProjectStatus').on('click', function () {
        var ids = $('input.project-checkbox:checked').map(function () {
            return $(this).val();
        }).get();

        let status = $('#bulkProjectStatus').val();

        if (!status) {
            alert('Please select a status.');
            return;
        }
        if (!ids.length) {
            alert('Please select projects first.');
            return;
        }

        // disable controls while processing
        $('#applyBulkProjectStatus').prop('disabled', true).text('Applying...');
        $('#bulkProjectStatus').prop('disabled', true);

        $.ajax({
            url: "{{ route('projects.bulk-status') }}",
            type: "POST",
            dataType: "json",
            data: {
                ids: ids,
                status: status
            },
            success: function (response) {
                if (response.success) {
                    // update only affected rows
                    ids.forEach(function(id) {
                        const $row = $('tr[data-project-id="' + id + '"]');
                        const $select = $row.find('.project-status-select');
                        if ($select.length) {
                            $select.val(status);
                        } else {
                            $row.find('.status-cell').text(status);
                        }
                        $row.find('.project-checkbox').prop('checked', false);
                    });

                    $('#bulkProjectStatus').val('');
                    $('#applyBulkProjectStatus').prop('disabled', true).text('Apply');
                    $('#bulkProjectStatus').prop('disabled', true);
                    $('#selectAllProjects').prop('checked', false).prop('indeterminate', false);

                    Swal.fire({ icon: 'success', title: 'Updated', text: 'Projects updated successfully', timer: 1400, showConfirmButton: false });

                    if ($.fn.dataTable && $.fn.dataTable.isDataTable('#projectTable')) {
                        $('#projectTable').DataTable().draw(false);
                    }
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: response.message || 'Something went wrong.' });
                    $('#applyBulkProjectStatus').prop('disabled', false).text('Apply');
                    $('#bulkProjectStatus').prop('disabled', false);
                }
            },
            error: function (xhr) {
                let title = 'Server error';
                let msg = 'Please try again.';
                try {
                    title = 'HTTP ' + xhr.status;
                    if (xhr.responseJSON) {
                        msg = xhr.responseJSON.message || msg;
                        if (xhr.responseJSON.errors) {
                            msg += ' — ' + Object.values(xhr.responseJSON.errors).flat().join(' | ');
                        }
                    } else if (xhr.responseText) {
                        msg = xhr.responseText;
                    }
                } catch (e) {}
                Swal.fire({ icon: 'error', title: title, text: msg });
                $('#applyBulkProjectStatus').prop('disabled', false).text('Apply');
                $('#bulkProjectStatus').prop('disabled', false);
            }
        });
    });
});
</script>

<style>
.custom-select2-dropdown .select-all-btns {
    border-bottom: 1px solid #eee;
    background: #f9f9f9;
}
.custom-select2-dropdown .select-all-btns a {
    cursor: pointer;
    text-decoration: none;
}
.custom-select2-dropdown .select-all-btns a:hover {
    text-decoration: underline;
}

/* make Bulk Delete appear on its own line under info */
.dataTables_wrapper .dt-bulk-delete {
    clear: both;      /* clears the info & paginate floats */
    text-align: left;
}
</style>

<!-- NEW: AJAX handler for single-row status change -->
<script>
$(document).ready(function () {
    $(document).on('change', '.project-status-select', function () {
        const $select = $(this);
        const projectId = $select.data('project-id');
        const newStatus = $select.val();

        $select.prop('disabled', true);

        $.ajax({
            url: "{{ url('admin/projects') }}/" + projectId + "/status",
            type: "POST",
            dataType: "json",
            data: { _method: 'PATCH', status: newStatus },
            success: function (res) {
                if (res.success) {
                    if (res.status) {
                        $select.val(res.status);
                    } else {
                        $select.val(newStatus);
                    }

                    Swal.fire({
                        icon: 'success',
                        title: 'Status updated',
                        text: res.message || 'Project status updated successfully',
                        timer: 1600,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: res.message || 'Failed to update status' });
                }
            },
            error: function (xhr) {
                let title = 'Server error';
                let msg = 'Please try again.';
                try {
                    title = 'HTTP ' + xhr.status;
                    if (xhr.responseJSON) {
                        msg = xhr.responseJSON.message || msg;
                        if (xhr.responseJSON.errors) {
                            msg += ' — ' + Object.values(xhr.responseJSON.errors).flat().join(' | ');
                        }
                    } else if (xhr.responseText) {
                        msg = xhr.responseText;
                    }
                } catch (e) {}
                Swal.fire({ icon: 'error', title: title, text: msg });
            },
            complete: function () {
                $select.prop('disabled', false);
                if ($.fn.dataTable && $.fn.dataTable.isDataTable('#projectTable')) {
                    $('#projectTable').DataTable().draw(false);
                }
            }
        });
    });
});
</script>

<!-- NEW: Bulk Delete AJAX handler -->
<script>
$(document).ready(function () {
    $(document).on('click', '#bulkDeleteProjects', function () {
        var ids = $('input.project-checkbox:checked').map(function () {
            return $(this).val();
        }).get();

        if (!ids.length) {
            alert('Please select at least one project.');
            return;
        }

        if (!confirm('Are you sure you want to delete the selected projects?')) {
            return;
        }

        $('#bulkDeleteProjects').prop('disabled', true).text('Deleting...');

        $.ajax({
            url: "{{ route('projects.bulk-delete') }}",
            type: "POST",
            dataType: "json",
            data: { ids: ids },
            success: function (res) {
                if (res.success) {
                    if ($.fn.dataTable && $.fn.dataTable.isDataTable('#projectTable')) {
                        var dt = $('#projectTable').DataTable();
                        ids.forEach(function (id) {
                            var $row = $('tr[data-project-id="' + id + '"]');
                            dt.row($row).remove();
                        });
                        dt.draw(false);
                    } else {
                        ids.forEach(function (id) {
                            $('tr[data-project-id="' + id + '"]').remove();
                        });
                    }

                    $('#selectAllProjects').prop('checked', false).prop('indeterminate', false);
                    $('#bulkProjectStatus, #applyBulkProjectStatus, #bulkDeleteProjects')
                        .prop('disabled', true).text('Bulk Delete');

                    Swal.fire({
                        icon: 'success',
                        title: 'Deleted',
                        text: res.deleted + ' project(s) deleted successfully.',
                        timer: 1600,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: res.message || 'Bulk delete failed.'
                    });
                    $('#bulkDeleteProjects').prop('disabled', false).text('Bulk Delete');
                }
            },
            error: function (xhr) {
                let title = 'Server error';
                let msg = 'Please try again.';
                try {
                    title = 'HTTP ' + xhr.status;
                    if (xhr.responseJSON) {
                        msg = xhr.responseJSON.message || msg;
                        if (xhr.responseJSON.errors) {
                            msg += ' — ' + Object.values(xhr.responseJSON.errors).flat().join(' | ');
                        }
                    } else if (xhr.responseText) {
                        msg = xhr.responseText;
                    }
                } catch (e) {}
                Swal.fire({ icon: 'error', title: title, text: msg });
                $('#bulkDeleteProjects').prop('disabled', false).text('Bulk Delete');
            }
        });
    });
});
</script>

@endpush

@endsection
