@extends('admin.layout.app')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

<main id="main" class="main">
    <div class="container">
     
        @if(isset($project))
            {{-- Sub-navigation bar --}}
            <ul class="nav nav-tabs mb-4">
                <li class="nav-item">
                    <a class="nav-link active" href="{{ route('projects.show', $project->id) }}">Overview</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('project-members.index', $project->id)}}">Members</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('project-files.index', $project->id)}}">Files</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('milestones.index', $project->id)}}">Milestones</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="{{ route('projects.tasks.index', $project->id)}}">Tasks</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('projects.tasks.board', $project->id) }}">Task Board</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('projects.gantt', $project->id) }}">Gantt Chart</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('projects.timelogs.index', $project->id) }}">Timesheet</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('expenses.index', $project->id) }}">Expenses</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">Notes</a>
                </li>
                {{-- Toggle Button --}}
                <li class="nav-item">
                    <a class="nav-link text-primary" href="#" id="toggle-more">More ▾</a>
                </li>
            </ul>
            
            {{-- Collapsible Extra Tabs --}}
            <ul class="nav nav-tabs mb-4 d-none" id="more-tabs">
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('projects.discussions.index', $project->id) }}">Discussion</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('projects.burndown', $project->id) }}">Burndown Chart</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('admin.activities.project', $project->id) }}">Activity</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('tickets.index', ['project_id' => $project->id]) }}">Ticket</a>
                </li>
            </ul>
        
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4>Tasks for Project: {{ $project->name }}</h4>
            </div>
        @else
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4>All Tasks</h4>
            </div>
        @endif
        
        <form method="GET" action="{{ $project ? route('projects.tasks.index', $project->id) : route('tasks.index') }}" class="row g-3 align-items-end mb-4">
            <div class="col-md-3">
                <label for="search" class="form-label">Search</label>
                <input type="text" name="search" id="search" class="form-control" placeholder="Task title or code" value="{{ request('search') }}">
            </div>

            <div class="col-md-2">
                <label for="status" class="form-label">Status</label>
                <select name="status" id="status" class="form-select">
                    <option value="">All Status</option>
                    @foreach(['Waiting for Approval', 'To Do', 'Doing', 'Incomplete', 'Completed'] as $status)
                        <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>{{ $status }}</option>
                    @endforeach
                </select>
            </div>
        
            <div class="col-md-2">
                <label for="start_date" class="form-label">Start Date (From)</label>
                <input type="date" name="start_date" id="start_date" class="form-control" value="{{ request('start_date') }}">
            </div>
        
            <div class="col-md-2">
                <label for="end_date" class="form-label">Due Date (To)</label>
                <input type="date" name="end_date" id="end_date" class="form-control" value="{{ request('end_date') }}">
            </div>
        
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">Filter</button>
                <a href="{{ $project ? route('projects.tasks.index', $project->id) : route('tasks.index') }}" class="btn btn-secondary">Reset</a>
            </div>
        </form>
       
        &nbsp;
      
        <div class="d-flex justify-content-between align-items-center mb-3">
            {{-- Left side buttons --}}
            <div>
                <a href="{{ route('tasks.create') }}" class="btn btn-primary mr-2">
                    <i class="bi bi-plus-lg"></i> Add Task
                </a>

                <button type="button" class="btn btn-secondary mr-2" id="filter-my-task">
                    <i class="bi bi-person"></i> My Tasks
                </button>
            </div>
            
            <div class="d-flex align-items-center mb-3 justify-content-between">
                {{-- Bulk status controls --}}
                <div class="btn-group align-items-center" role="group">
                    <select id="bulkStatus" class="form-select form-select-sm" disabled>
                        <option value="">Change Status</option>
                        <option value="Incomplete">Incomplete</option>
                        <option value="To Do">To Do</option>
                        <option value="Doing">Doing</option>
                        <option value="Completed">Completed</option>
                        <option value="Waiting for Approval">Waiting for Approval</option>
                    </select>

                    <button id="applyBulkStatus" class="btn btn-primary btn-sm ms-2" disabled>
                        Apply
                    </button>
                </div>
                
                &nbsp;

                {{-- Right side icons --}}
                <div class="btn-group" role="group">
                    <a href="{{ route('tasks.index') }}" class="btn btn-secondary" data-toggle="tooltip" title="Tasks">
                        <i class="bi bi-list-ul"></i>
                    </a>

                    <a href="{{ route('users.tasks.board') }}" class="btn btn-secondary f-14" data-toggle="tooltip" data-original-title="Task Board">
                        <i class="bi bi-kanban"></i>
                    </a>

                    <a href="{{ route('tasks.calendar') }}" class="btn btn-secondary" data-toggle="tooltip" title="Calendar">
                        <i class="bi bi-calendar"></i>
                    </a>
                    
                    <a href="javascript:;" class="btn btn-secondary f-14 show-pinned" data-toggle="tooltip" data-original-title="Pinned">
                        <i class="side-icon bi bi-pin-angle"></i>
                    </a>

                    <a href="{{ route('tasks.waiting-approval') }}" class="btn btn-secondary" data-toggle="tooltip" title="Waiting Approval">
                        <i class="bi bi-exclamation-triangle text-warning"></i>
                    </a>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <table id="taskTable" class="table table-bordered table-hover">
            <thead class="table-dark text-center align-middle">
                <tr>
                    <th style="width:40px;">
                        <input type="checkbox" id="selectAll">
                    </th>
                    <th style="white-space: nowrap;">Task Code</th>
                    <th>Timer</th>
                    <th>Task</th>
                    <th style="white-space: nowrap;">Completed On</th>
                    <th style="white-space: nowrap;">Start Date</th>
                    <th style="white-space: nowrap;">Due Date</th>
                    <th style="white-space: nowrap;">Estimated Time</th>
                    <th style="white-space: nowrap;">Hours Logged</th>
                    <th style="white-space: nowrap;">Assigned To</th>
                    <th>Status</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
            @foreach($tasks->where('parent_id', null) as $task)
                <tr data-task-id="{{ $task->id }}">
                    <td>
                        <input type="checkbox" class="row-checkbox" value="{{ $task->id }}">
                    </td>
                    <td>{{ $task->task_short_code ?? $task->id }}</td>
    
                    {{-- Timer: Show active/inactive icon or duration --}}
                    <td>
                        @php
                            $activeTimer = $task->activeTimer;
                        @endphp
                    
                        @if($activeTimer)
                            @if($activeTimer->pause_time)
                                {{-- Timer is Paused --}}
                                <form method="POST" action="{{ route('task-timer.resume', $task->id) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-info" title="Resume Timer">
                                        <i class="bi bi-play-circle-fill"></i> Resume
                                    </button>
                                </form>
                            @else
                                {{-- Timer is Running --}}
                                <form method="POST" action="{{ route('task-timer.pause', $task->id) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-warning" title="Pause Timer">
                                        <i class="bi bi-pause-circle-fill"></i> Pause
                                    </button>
                                </form>
                            @endif
                    
                            {{-- Stop Button --}}
                            <button class="btn btn-sm btn-danger mt-1" data-bs-toggle="modal" data-bs-target="#stopTimerModal-{{ $task->id }}">
                                <i class="bi bi-stop-circle-fill"></i> Stop
                            </button>
                    
                            <br>
                            <small>
                                Started: {{ \Carbon\Carbon::parse($activeTimer->start_time)->format('h:i A') }}
                                @if($activeTimer->pause_time)
                                    <br><span class="text-muted">Paused at: {{ \Carbon\Carbon::parse($activeTimer->pause_time)->format('h:i A') }}</span>
                                @endif
                            </small>
                    
                            {{-- Stop Modal --}}
                            <div class="modal fade" id="stopTimerModal-{{ $task->id }}" tabindex="-1" aria-labelledby="stopTimerModalLabel" aria-hidden="true">
                                <div class="modal-dialog">
                                    <form method="POST" action="{{ route('task-timer.stop', $task->id) }}">
                                        @csrf
                                        <input type="hidden" name="timer_id" value="{{ $activeTimer->id }}">
                                        <input type="hidden" name="project_id" value="{{ $task->project_id }}">
                                        <input type="hidden" name="start_date" value="{{ \Carbon\Carbon::parse($activeTimer->start_time)->format('Y-m-d') }}">
                                        <input type="hidden" name="end_date" value="{{ now()->format('Y-m-d') }}">
                                        
                                        
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Stop Timer</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p><strong>Start:</strong> {{ \Carbon\Carbon::parse($activeTimer->start_time)->format('h:i A') }}</p>
                                                <p><strong>End:</strong> <span class="end-time"></span></p>

                                                <p><strong>Total Time:</strong>
                                                    {{ now()->diffInSeconds($activeTimer->start_time) }}s
                                                </p>
                                                <div class="mb-3">
                                                    <label class="form-label">Memo <span class="text-danger">*</span></label>
                                                    <textarea name="memo" class="form-control" rows="3" maxlength="500" required 
                                                              oninput="checkMemoLength(this)"></textarea>
                                                    <small id="memoHelp" class="form-text text-muted">Max 500 characters.</small>
                                                    <div id="memoError" class="text-danger mt-1" style="display: none;">Maximum 500 characters allowed.</div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-success">Save</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        @else
                            {{-- No Active Timer --}}
                            <form method="POST" action="{{ route('task-timer.start', $task->id) }}">
                                @csrf
                                <button class="btn btn-sm btn-success" title="Start Timer">
                                    <i class="bi bi-play-circle"></i> Start
                                </button>
                            </form>
                        @endif
                    </td>
    
                    <td>
                        <strong>{{ $task->title }}</strong><br>
                        <small class="text-muted">{{ $task->project->name ?? 'N/A' }}</small>
                    </td>
    
                    <td>
                        @if($task->completed_on)
                            <span class="text-success">{{ \Carbon\Carbon::parse($task->completed_on)->format('d-m-Y h:i A') }}</span>
                        @else
                            <span class="text-muted">--</span>
                        @endif
                    </td>
    
    
                    <td style="white-space: nowrap;">{{ $task->start_date ? \Carbon\Carbon::parse($task->start_date)->format('d-m-Y') : '--' }}</td>
                    <td style="white-space: nowrap;">{{ $task->due_date ? \Carbon\Carbon::parse($task->due_date)->format('d-m-Y') : '--' }}</td>
    
    
                    <td>
                        {{ $task->estimate_hours ?? 0 }}h {{ $task->estimate_minutes ?? 0 }}m
                    </td>
    
                    <td>{{ $task->total_logged_formatted ?? '0h 0m 0s' }}</td>
    
                    @php
                        $assignedUsers = $task->assignees ?? collect();
                    @endphp
                    
                    <td style="white-space: nowrap;">
                        @if($assignedUsers->count())
                            @foreach($assignedUsers as $user)
                                <img src="{{ asset($user->profile_image ?? 'images/default-user.png') }}"
                                     alt="{{ $user->name }}"
                                     width="30"
                                     class="rounded-circle"
                                     title="{{ $user->name }}">
                                     {{ $user->name }}
                            @endforeach
                        @else
                            <span class="text-muted">Unassigned</span>
                        @endif
                    </td>
    
                    <td style="min-width: 220px;">
                        <div class="d-flex align-items-center gap-2">
                            {{-- Status Badge --}}
                            <span class="badge bg-{{ $badgeColors[$task->status] ?? 'secondary' }}">
                                {{ $task->status }}
                            </span>
                    
                            {{-- Status Dropdown --}}
                            <select class="form-select form-select-sm status-dropdown"
                                    data-task-id="{{ $task->id }}"
                                    style="width: auto; min-width: 140px;">
                                @foreach(['Waiting for Approval', 'To Do', 'Doing', 'Incomplete', 'Completed'] as $status)
                                    <option value="{{ $status }}" {{ $task->status === $status ? 'selected' : '' }}>
                                        {{ $status }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </td>
    
                    <td class="text-center">
                        <div class="dropdown">
                            <button class="btn btn-light btn-sm" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="{{ route('tasks.show', $task->id) }}"> <i class="bi bi-eye"></i> View </a></li>
                                <li><a class="dropdown-item" href="{{ route('tasks.edit', $task->id) }}"><i class="bi bi-pencil-square"></i> Edit</a></li>
                                <li><a class="dropdown-item" href="{{ route('tasks.create') }}?duplicate_id={{ $task->id }}"><i class="bi bi-files"></i> Duplicate</a></li>
                                <li>
                                    <form action="{{ route('tasks.destroy', $task->id) }}" method="POST">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Delete task?')">
                                            <i class="bi bi-trash"></i> Delete
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

    </div>
</main>
@endsection

@push('js')
<script>
$(document).ready(function () {
    var table = $('#taskTable').DataTable({
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'copy',
                exportOptions: {
                    columns: ':not(:first-child):not(:last-child)'
                }
            },
            {
                extend: 'csv',
                exportOptions: {
                    columns: ':not(:first-child):not(:last-child)'
                }
            },
            {
                extend: 'excel',
                exportOptions: {
                    columns: ':not(:first-child):not(:last-child)'
                }
            },
            {
                extend: 'pdf',
                exportOptions: {
                    columns: ':not(:first-child):not(:last-child)'
                }
            },
            {
                extend: 'print',
                exportOptions: {
                    columns: ':not(:first-child):not(:last-child)'
                }
            }
        ],
        responsive: true,
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],
        order: [],
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Start typing to search..."
        }
    });

    // Inject Bulk Delete button directly under "Showing X to Y of Z entries"
    if (!$('#taskBulkDeleteContainer').length) {
        var info = $('#taskTable_wrapper .dataTables_info');

        $('<div id="taskBulkDeleteContainer" class="dt-task-bulk-delete mt-2">' +
            '<button id="bulkDeleteTasks" class="btn btn-danger btn-sm" disabled>Bulk Delete</button>' +
          '</div>').insertAfter(info);
    }
});
</script>

<script>
  console.log("✅ JS loaded successfully!");
</script>

<script>
  $(document).ready(function () {
    $('.status-dropdown').each(function () {
        $(this).data('prev', $(this).val());
    });

    $('.status-dropdown').on('change', function () {
        const dropdown = $(this);
        const taskId = dropdown.data('task-id');
        const newStatus = dropdown.val();
        const previousStatus = dropdown.data('prev');

        if (!confirm(`Are you sure you want to change the status to "${newStatus}"?`)) {
            dropdown.val(previousStatus);
            return;
        }

        $.ajax({
            url: "{{ url('/tasks') }}/" + taskId + "/update-status",
            type: "POST",
            data: {
                status: newStatus,
                _token: "{{ csrf_token() }}"
            },
            success: function (response) {
                if (response.success) {
                    dropdown.data('prev', newStatus);

                    // Update badge inside same <td>
                    const badge = dropdown.closest('td').find('.badge');
                    const colorMap = {
                        'Incomplete': 'danger',
                        'Completed': 'success',
                        'To Do': 'secondary',
                        'Doing': 'info',
                        'Waiting for Approval': 'dark'
                    };

                    if (badge.length) {
                        badge.removeClass().addClass('badge bg-' + (colorMap[newStatus] || 'secondary')).text(newStatus);
                    }

                    alert('Status updated successfully.');
                } else {
                    alert(response.message || 'Failed to update status.');
                    dropdown.val(previousStatus);
                }
            },
            error: function (xhr) {
                alert(xhr.responseJSON?.message || 'Something went wrong!');
                dropdown.val(previousStatus);
            }
        });
    });
});
</script>

<script>
    document.getElementById('toggle-more').addEventListener('click', function(e) {
        e.preventDefault();
        const moreTabs = document.getElementById('more-tabs');
        if (moreTabs.classList.contains('d-none')) {
            moreTabs.classList.remove('d-none');
            this.innerHTML = 'Less ▴';
        } else {
            moreTabs.classList.add('d-none');
            this.innerHTML = 'More ▾';
        }
    });
</script>

<script>
function checkMemoLength(textarea) {
    const errorDiv = document.getElementById('memoError');
    if (textarea.value.length > 500) {
        errorDiv.style.display = 'block';
    } else {
        errorDiv.style.display = 'none';
    }
}

document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll('[id^="stopTimerModal-"]').forEach(modal => {
        let intervalId;

        modal.addEventListener('show.bs.modal', function () {
            const endTimeSpan = modal.querySelector('.end-time');
            intervalId = setInterval(() => {
                if (endTimeSpan) {
                    const now = new Date();
                    const options = { hour: '2-digit', minute: '2-digit', hour12: true };
                    endTimeSpan.textContent = now.toLocaleTimeString([], options);
                }
            }, 1000);
        });

        modal.addEventListener('hide.bs.modal', function () {
            clearInterval(intervalId);
        });
    });
});
</script>

<script>
document.addEventListener("DOMContentLoaded", function () {
    let pinnedVisible = true;

    document.querySelector('.show-pinned').addEventListener('click', function () {
        const tableBody = document.querySelector('#taskTable tbody');

        if (pinnedVisible) {
            tableBody.style.display = 'none';
            this.classList.add('active'); // highlight the button
        } else {
            tableBody.style.display = '';
            this.classList.remove('active');
        }

        pinnedVisible = !pinnedVisible;
    });
});


$(document).ready(function () {
    // Select/Deselect all
    $('#selectAll').on('click', function () {
        $('.row-checkbox').prop('checked', this.checked).trigger('change');
    });

    // Enable/Disable bulk controls
    $(document).on('change', '.row-checkbox', function () {
        var selected = $('.row-checkbox:checked').length;
        $('#bulkStatus, #applyBulkStatus, #bulkDeleteTasks').prop('disabled', selected === 0);
    });

    // Apply bulk status update
    $('#applyBulkStatus').on('click', function () {
        var status = $('#bulkStatus').val();
        var ids = $('.row-checkbox:checked').map(function () {
            return $(this).val();
        }).get();

        if (!status || ids.length === 0) {
            alert("Please select at least one task and a status.");
            return;
        }

        if (!confirm("Are you sure you want to change status of selected tasks to '" + status + "'?")) {
            return;
        }

        $.ajax({
            url: "{{ route('tasks.bulkStatusUpdate') }}",
            method: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                status: status,
                ids: ids
            },
            success: function (res) {
                location.reload();
            },
            error: function () {
                alert("Something went wrong!");
            }
        });
    });

    // Bulk Delete AJAX (matches Route::delete)
    $(document).on('click', '#bulkDeleteTasks', function () {
        var ids = $('.row-checkbox:checked').map(function () {
            return $(this).val();
        }).get();

        if (!ids.length) {
            alert('Please select at least one task.');
            return;
        }

        if (!confirm('Are you sure you want to delete the selected tasks?')) {
            return;
        }

        $('#bulkDeleteTasks').prop('disabled', true).text('Deleting...');

        $.ajax({
            url: "{{ route('tasks.bulkDelete') }}",
            type: "DELETE",                      // important: DELETE to match your route
            dataType: "json",
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}"  // CSRF for DELETE
            },
            data: {
                ids: ids
            },
            success: function (res) {
                if (res.success) {
                    if ($.fn.dataTable && $.fn.dataTable.isDataTable('#taskTable')) {
                        var dt = $('#taskTable').DataTable();
                        ids.forEach(function (id) {
                            var $row = $('tr[data-task-id="' + id + '"]');
                            dt.row($row).remove();
                        });
                        dt.draw(false);
                    } else {
                        ids.forEach(function (id) {
                            $('tr[data-task-id="' + id + '"]').remove();
                        });
                    }

                    $('#selectAll').prop('checked', false).prop('indeterminate', false);
                    $('#bulkStatus, #applyBulkStatus, #bulkDeleteTasks').prop('disabled', true).text('Bulk Delete');

                    alert(res.deleted + ' task(s) deleted successfully.');
                } else {
                    alert(res.message || 'Bulk delete failed.');
                    $('#bulkDeleteTasks').prop('disabled', false).text('Bulk Delete');
                }
            },
            error: function (xhr) {
                console.error('Bulk delete error:', xhr.status, xhr.responseText);
                alert('Server error. Please try again.');
                $('#bulkDeleteTasks').prop('disabled', false).text('Bulk Delete');
            }
        });
    });
});
</script>

<style>
/* Make Bulk Delete appear on its own line under info */
.dataTables_wrapper .dt-task-bulk-delete {
    clear: both;
    text-align: left;
}
</style>

@endpush
