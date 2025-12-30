@extends('admin.layout.app')

@section('content')
<main id="main" class="main">
    <div class="container">
        <div class="pagetitle mb-3">
            <br>
            <h5 class="mb-1">Task #{{ $task->task_short_code ?? $task->id }}</h5>
            <nav>
                <ol class="breadcrumb small mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('tasks.index') }}">Tasks</a></li>
                    <li class="breadcrumb-item active">Task #{{ $task->task_short_code ?? $task->id }}</li>
                </ol>
            </nav>
        </div>

        <section class="section">
            <div class="row">
                {{-- Left Main Details --}}
                <div class="col-md-9">
                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <h4 class="fw-bold text-primary">{{ $task->title }} ({{ $task->created_at->format('d-m-Y') }})</h4>
                                
                                {{-- Action Buttons --}}
                                <div class="d-flex align-items-center gap-2">
                                   <div class="d-flex gap-2 align-items-stretch">

                                    {{-- Mark Complete --}}
                                    <a href="{{ route('tasks.markComplete', $task->id) }}"
                                       class="btn btn-outline-success btn-sm d-flex align-items-center justify-content-center">
                                        <i class="bi bi-check-circle me-1"></i> Mark as Complete
                                    </a>
                                
                                    {{-- Timer Logic --}}
                                    @php
                                        $activeTimer = $task->activeTimer;
                                    @endphp
                                
                                    @if($activeTimer)
                                        @if($activeTimer->pause_time)
                                            {{-- Resume --}}
                                            <form method="POST" action="{{ route('task-timer.resume', $task->id) }}">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-info">
                                                    <i class="bi bi-play-circle-fill"></i> Resume
                                                </button>
                                            </form>
                                        @else
                                            {{-- Pause --}}
                                            <form method="POST" action="{{ route('task-timer.pause', $task->id) }}">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-warning">
                                                    <i class="bi bi-pause-circle-fill"></i> Pause
                                                </button>
                                            </form>
                                        @endif
                                
                                        {{-- Stop Timer Modal Trigger --}}
                                        <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#stopTimerModal-{{ $task->id }}">
                                            <i class="bi bi-stop-circle-fill"></i> Stop
                                        </button>
                                
                                        {{-- Timer Info --}}
                                        <div class="ms-2 small">
                                            <div>Started: {{ \Carbon\Carbon::parse($activeTimer->start_time)->format('h:i A') }}</div>
                                            @if($activeTimer->pause_time)
                                                <div class="text-muted">Paused: {{ \Carbon\Carbon::parse($activeTimer->pause_time)->format('h:i A') }}</div>
                                            @endif
                                        </div>
                                
                                        {{-- Stop Modal --}}
                                        <div class="modal fade" id="stopTimerModal-{{ $task->id }}" tabindex="-1">
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
                                                            <!--<p><strong>Start:</strong> {{ \Carbon\Carbon::parse($activeTimer->start_time)->format('h:i A') }}</p>-->
                                                            <!--<p><strong>End:</strong> {{ now()->format('h:i A') }}</p>-->
                                                            <p><strong>Start:</strong> {{ \Carbon\Carbon::parse($activeTimer->start_time)->format('h:i A') }}</p>
                                                            <p><strong>End:</strong> <span class="end-time" id="end-time-{{ $activeTimer->id }}"></span></p>

                                                            <p><strong>Total Time:</strong> {{ now()->diffInSeconds($activeTimer->start_time) }}s</p>
                                                            <div class="mb-3">
                                                                <label class="form-label">Memo *</label>
                                                                <textarea name="memo" class="form-control" rows="3" required></textarea>
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
                                        {{-- No Timer Active --}}
                                        <form method="POST" action="{{ route('task-timer.start', $task->id) }}">
                                            @csrf
                                            <button class="btn btn-sm btn-success">
                                                <i class="bi bi-play-circle"></i> Start Timer
                                            </button>
                                        </form>
                                    @endif
                                
                                </div>


                                
                                    {{-- Dropdown Menu --}}
                                    <div class="dropdown">
                                        <button class="btn btn-link text-dark p-0" type="button" data-bs-toggle="dropdown">
                                            <i class="bi bi-three-dots-vertical fs-5"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li><a class="dropdown-item" href="#">Send Reminder</a></li>
                                            <li><a class="dropdown-item" href="{{ route('tasks.edit', $task->id) }}">Edit Task</a></li>
                                            <li><a class="dropdown-item" href="#">Pin Task</a></li>
                                            <li><a class="dropdown-item" href="#">Copy Task Link</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            {{-- Info Rows --}}
                            <div class="row mt-3 g-3">
                                <div class="col-md-6">
                                    <table class="table table-borderless small w-100">
                                        <tr>
                                            <td class="text-muted text-start w-50">Project</td>
                                            <td class="text-dark fw-semibold text-start">{{ $task->project->name ?? '--' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted text-start">Priority</td>
                                            <td class="text-dark fw-semibold text-capitalize text-start">{{ $task->priority ?? 'medium' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted text-start">Assigned To</td>
                                            <td class="text-start">
                                                @if($task->assignee)
                                                    <img src="{{ $task->assignee->avatar ?? '/default.png' }}" class="rounded-circle" width="24" height="24" alt="">
                                                    <span class="ms-1">{{ $task->assignee->name }}</span>
                                                @else
                                                    --
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted text-start">Short Code</td>
                                            <td class="text-start">{{ $task->id }}</td>
                                        </tr>
                                    </table>
                                </div>
                            
                                <div class="col-md-6">
                                    <table class="table table-borderless small w-100">
                                        <tr>
                                            <td class="text-muted text-start w-50">Milestones</td>
                                            <td class="text-start">{{ $task->milestone->name ?? '--' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted text-start">Assigned By</td>
                                            <td class="text-start">
                                                @if($task->createdBy)
                                                    <img src="{{ $task->createdBy->avatar ?? '/default.png' }}" class="rounded-circle" width="24" height="24" alt="">
                                                    <span class="ms-1">{{ $task->createdBy->name }} <span class="badge bg-secondary">It's you</span></span>
                                                @else
                                                    N/A
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted text-start">Label</td>
                                            <td class="text-start">{{ $task->task_labels ?? '--' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted text-start">Task Category</td>
                                            <td class="text-start">{{ $task->category->category_name ?? 'No Category' }}</td>
                                        </tr>
                                        
                                        
                                    </table>
                                </div>
                            </div>


                            <div class="text-muted small mt-3">
                                <strong>Description:</strong> {{ $task->description ?? '--' }}
                            </div>
                           
                           <br>
                           
                           <div class="card shadow-sm mb-4">
    <div class="card-body">
        <div class="row">
            {{-- Make Private --}}
            <div class="col-md-6 col-lg-3 mb-3">
                <label class="form-label">Private Task</label>
                <p class="fw-semibold">{{ $task->is_private ? 'Yes' : 'No' }}</p>
            </div>

            {{-- Billable --}}
            <div class="col-md-6 col-lg-3 mb-3">
                <label class="form-label">Billable</label>
                <p class="fw-semibold">{{ $task->billable ? 'Yes' : 'No' }}</p>
            </div>

            {{-- Time Estimate --}}
            <div class="col-md-6 col-lg-3 mb-3">
                <label class="form-label">Time Estimate</label>
                @if(($task->estimate_hours ?? 0) > 0 || ($task->estimate_minutes ?? 0) > 0)
                    <p class="fw-semibold">{{ $task->estimate_hours }}h {{ $task->estimate_minutes }}m</p>
                @else
                    <p class="text-muted">--</p>
                @endif
            </div>

            {{-- Repeat Task --}}
            <div class="col-md-6 col-lg-3 mb-3">
                <label class="form-label">Repeats</label>
                @if($task->repeat)
                    <p class="fw-semibold">
                        Every {{ $task->repeat_count }} {{ Str::plural($task->repeat_type, $task->repeat_count) }},
                        {{ $task->repeat_cycles }} cycle(s)
                    </p>
                @else
                    <p class="text-muted">No</p>
                @endif
            </div>

            {{-- Dependent Task --}}
            <div class="col-md-6 col-lg-4 mb-3">
                <label class="form-label">Depends On</label>
                @if($task->dependentTask)
                    <p class="fw-semibold">
                        {{ $task->dependentTask->title }} 
                        
                       (Due: {{ $task->dependentTask->due_date ? \Carbon\Carbon::parse($task->dependentTask->due_date)->format('d-m-Y') : '--' }})

                    </p>
                @else
                    <p class="text-muted">None</p>
                @endif
            </div>

            {{-- File Upload --}}
            <div class="col-md-6 col-lg-4 mb-3">
                <label class="form-label">Attached File</label>
                @if($task->image_url)
                    <a href="{{ asset($task->image_url) }}" target="_blank">{{ basename($task->image_url) }}</a>
                @else
                    <p class="text-muted">No File Attached</p>
                @endif
            </div>
        </div>
    </div>
</div>

                        </div>
                    </div>
                </div>

                {{-- Right Status Card --}}
                <div class="col-md-3">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h6>
                                @php
                                    $badgeColors = [
                                        'Incomplete' => 'danger',
                                        'Completed' => 'success',
                                        'To Do' => 'secondary',
                                        'Doing' => 'info',
                                        'Waiting for Approval' => 'dark'
                                    ];
                                @endphp
                                <span class="badge bg-{{ $badgeColors[$task->status] ?? 'secondary' }}">
                                    {{ $task->status ?? 'Incomplete' }}
                                </span>
                            </h6>
                            <hr>
                            <p class="mb-1 small text-muted">Created On</p>
                            <p class="fw-semibold">{{ $task->created_at->format('d-m-Y h:i A') }}</p>

                            <p class="mb-1 small text-muted">Start Date</p>
                            <p class="fw-semibold">
                                {{ $task->start_date ? \Carbon\Carbon::parse($task->start_date)->format('d-m-Y') : '--' }}
                            </p>
                            
                            <p class="mb-1 small text-muted">Due Date</p>
                            <p class="fw-semibold">
                                {{ $task->due_date ? \Carbon\Carbon::parse($task->due_date)->format('d-m-Y') : '--' }}
                            </p>


                            <p class="mb-1 small text-muted">Hours Logged</p>
                            <p class="fw-semibold">{{ $task->total_logged_formatted ?? '0h 0m 0s' }}</p> {{-- Replace if dynamic --}}
                        </div>
                    </div>
                </div>
            </div>

    
                    {{-- Tabs --}}
                    <div class="mt-5">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">Task Details</h5>
                            {{-- Additional task action buttons (e.g., Complete/Timer) can go here if needed --}}
                        </div>
                    
                        <ul class="nav nav-pills mb-3" id="taskTab" role="tablist">
                            @foreach(['Files', 'Sub Tasks', 'Comments', 'Timesheet', 'Notes', 'History'] as $tab)
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link {{ $loop->first ? 'active' : '' }}"
                                            id="{{ strtolower(str_replace(' ', '-', $tab)) }}-tab"
                                            data-bs-toggle="pill"
                                            data-bs-target="#{{ strtolower(str_replace(' ', '-', $tab)) }}"
                                            type="button" role="tab">
                                        {{ $tab }}
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                    
                        <div class="tab-content p-3 border rounded bg-light" id="taskTabContent">
                            <div class="tab-pane fade show active" id="files" role="tabpanel">
                                @include('admin.tasks.files')
                    
                               
                            </div>
                    
                            <div class="tab-pane fade" id="sub-tasks" role="tabpanel">
                                @include('admin.tasks.subtasks')
                            </div>
                    
                            <div class="tab-pane fade" id="comments" role="tabpanel">
                                @include('admin.tasks.comments')
                               
                            </div>
                    
                            <div class="tab-pane fade" id="timesheet" role="tabpanel">
                                @include('admin.tasks.timesheet')
                            </div>
                    
                            <div class="tab-pane fade" id="notes" role="tabpanel">
                                @include('admin.tasks.notes')
                            </div>
                    
                            <div class="tab-pane fade" id="history" role="tabpanel">
                                @include('admin.tasks.history')
                            </div>
                        </div>
                    </div>

    
                </div>
            </div>
        </section>
    </div>
</main>
@endsection

@push('js')
<style>
    .nav-pills .nav-link.active {
    background-color: #0d6efd;
    color: #fff;
}

.btn-sm {
        padding-top: 6px;
        padding-bottom: 6px;
        font-size: 0.875rem;
        line-height: 1.25;
    }

    form button.btn {
        white-space: nowrap;
    }

    form {
        display: inline-block;
        margin: 0;
    }
    
     td {
        vertical-align: top;
    }
</style>




<script>
  console.log("✅ JS loaded successfully!");
</script>

<script>
  document.addEventListener("DOMContentLoaded", function () {
    // Toggle Sub-Tasks
    document.querySelectorAll('.toggle-subtasks').forEach(button => {
        button.addEventListener('click', function () {
            const id = this.getAttribute('data-id');
            const row = document.getElementById('subtasks-' + id);
            if (row.style.display === 'none') {
                row.style.display = '';
                this.innerText = 'Hide Sub-Tasks';
            } else {
                row.style.display = 'none';
                this.innerText = 'Show Sub-Tasks';
            }
        });
    });

    
  });
  
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
@endpush