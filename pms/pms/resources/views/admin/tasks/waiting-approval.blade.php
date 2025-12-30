@extends('admin.layout.app')

@section('title', 'Waiting for Approval Tasks')

@section('content')
<main class="main">
    <div class="container py-4">
        <h4 class="fw-bold mb-3">Tasks - Waiting for Approval</h4>

        <!-- Filters -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('tasks.waiting-approval') }}">
                    <div class="row g-3 align-items-end">
                        <!-- Duration Filter -->
                        <div class="col-md-3">
                            <label for="duration" class="form-label">Duration</label>
                           <input type="text"
                                   name="duration"
                                   id="duration"
                                   class="form-control"
                                   value="{{ request('duration') }}"
                                   placeholder="Start Date to End Date"
                                   autocomplete="off">
                        </div>

                        <!-- Search -->
                        <div class="col-md-3">
                            <label for="search" class="form-label">Search</label>
                            <input type="text"
                                   name="search"
                                   id="search"
                                   class="form-control"
                                   value="{{ request('search') }}"
                                   placeholder="Task title...">
                        </div>

                        <!-- Buttons -->
                        <div class="col-md-3 d-flex mt-3">
                            <button type="submit" class="btn btn-outline-primary w-100 me-2">
                                <i class="fas fa-filter me-1"></i> Filter
                            </button>
                            <a href="{{ route('tasks.waiting-approval') }}" class="btn btn-outline-secondary w-100">
                                <i class="fas fa-redo me-1"></i> Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Action buttons -->
        <div class="d-flex justify-content-end mb-3">
            <div class="btn-group" role="group">
                <a href="{{ route('tasks.index') }}" class="btn btn-secondary" data-toggle="tooltip" title="Tasks">
                   <i class="bi bi-list-ul"></i>
                </a>

                <a href="{{ route('users.tasks.board') }}" class="btn btn-secondary f-14" data-toggle="tooltip" title="Task Board">
                    <i class="bi bi-kanban"></i>
                </a>

                <a href="{{ route('tasks.calendar') }}" class="btn btn-secondary" data-toggle="tooltip" title="Calendar">
                   <i class="bi bi-calendar"></i>
                </a>

                <a href="{{ route('tasks.waiting-approval') }}" class="btn btn-secondary" data-toggle="tooltip" title="Waiting Approval">
                   <i class="bi bi-exclamation-triangle text-warning"></i>
                </a>
            </div>
        </div>

        &nbsp;

        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Task</th>
                        <th>Completed On</th>
                        <th>Start Date</th>
                        <th>Due Date</th>
                        <th>Estimated Time</th>
                        <th>Hours Logged</th>
                        <th>Assigned To</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>
                @forelse($tasks as $task)
                    @php
                        // title fallback (mapped object uses 'task', model uses 'heading' or 'title')
                        $taskTitle = $task->task ?? $task->heading ?? $task->title ?? '';

                        // code fallback
                        $taskCode = $task->code ?? $task->task_short_code ?? '';

                        // completed / dates formatting
                        $completedOn = null;
                        if (!empty($task->completed_on)) {
                            try {
                                $completedOn = \Carbon\Carbon::parse($task->completed_on)->format('Y-m-d');
                            } catch (\Throwable $e) {
                                $completedOn = $task->completed_on;
                            }
                        }

                        $startDate = null;
                        if (!empty($task->start_date)) {
                            try {
                                $startDate = \Carbon\Carbon::parse($task->start_date)->format('Y-m-d');
                            } catch (\Throwable $e) {
                                $startDate = $task->start_date;
                            }
                        }

                        $dueDate = null;
                        if (!empty($task->due_date)) {
                            try {
                                $dueDate = \Carbon\Carbon::parse($task->due_date)->format('Y-m-d');
                            } catch (\Throwable $e) {
                                $dueDate = $task->due_date;
                            }
                        }

                        // estimated time: prefer precomputed field, else compute from estimate_hours/minutes
                        if (!empty($task->estimated_time)) {
                            $estimated = $task->estimated_time;
                        } else {
                            $parts = [];
                            if (!empty($task->estimate_hours)) $parts[] = $task->estimate_hours . 'h';
                            if (!empty($task->estimate_minutes)) $parts[] = $task->estimate_minutes . 'm';
                            $estimated = count($parts) ? implode(' ', $parts) : null;
                        }

                        // hours logged: prefer precomputed field, else compute from timeLogged relation / tasktimeLogs
                        if (!empty($task->hours_logged)) {
                            $hoursLogged = $task->hours_logged;
                        } else {
                            $totalMinutes = 0;
                            // support both relation names in case of different codebases
                            $logs = $task->timeLogged ?? ($task->tasktimeLogs ?? null);
                            if ($logs && count($logs)) {
                                foreach ($logs as $log) {
                                    if (!empty($log->start_time) && !empty($log->end_time)) {
                                        $totalMinutes += \Carbon\Carbon::parse($log->end_time)->diffInMinutes(\Carbon\Carbon::parse($log->start_time));
                                    } elseif (!empty($log->start_time) && !empty($log->pause_time)) {
                                        $totalMinutes += \Carbon\Carbon::parse($log->pause_time)->diffInMinutes(\Carbon\Carbon::parse($log->start_time));
                                    }
                                }
                            }
                            $hoursLogged = $totalMinutes ? round($totalMinutes / 60, 2) : null;
                        }

                        // assigned to: check multiple possible places (mapped assigned_to string, assignee relation, users relation)
                        if (!empty($task->assigned_to) && is_string($task->assigned_to)) {
                            $assignedTo = $task->assigned_to;
                        } elseif (!empty($task->assignee) && is_object($task->assignee)) {
                            $assignedTo = $task->assignee->name ?? 'N/A';
                        } elseif (!empty($task->users) && count($task->users)) {
                            // users relation (belongsToMany)
                            $assignedTo = $task->users->pluck('name')->implode(', ');
                        } else {
                            $assignedTo = 'N/A';
                        }

                        $statusLabel = $task->status ?? '-';
                    @endphp

                    <tr>
                        <td>{{ $taskCode }}</td>
                        <td>{{ $taskTitle }}</td>
                        <td>{{ $completedOn ?? '-' }}</td>
                        <td>{{ $startDate ?? '-' }}</td>
                        <td>{{ $dueDate ?? '-' }}</td>
                        <td>{{ $estimated ?? '-' }}</td>
                        <td>{{ $hoursLogged ?? '-' }}</td>
                        <td>{{ $assignedTo }}</td>
                        <td><span class="badge bg-warning">{{ $statusLabel }}</span></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center">No tasks in Waiting for Approval</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</main>
@endsection

@push('js')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">
<script src="https://cdn.jsdelivr.net/npm/moment@2.29.1/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
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
@endpush
