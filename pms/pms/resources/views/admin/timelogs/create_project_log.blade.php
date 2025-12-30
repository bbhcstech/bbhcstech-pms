@extends('admin.layout.app')

@section('content')

<div class="container mt-4">
    
    <br>
        <a href="{{ route('projects.index') }}" class="btn btn-secondary mb-3">← Back to Projects</a>

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
                <a class="nav-link" href="{{ route('projects.timelogs.create', $project->id) }}">Timesheet</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#">Discussion</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#">Notes</a>
            </li>
        </ul>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4>Time Logs</h4>
            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addTimeLogModal">
                Add Time Log
            </button>
        </div>
        
        <div class="row mb-3">
            <form method="GET" action="{{ route('projects.timelogs.create', $project->id) }}">
            <div class="row mb-3">
                <div class="col-md-4">
                    <label>Employee</label>
                    <select name="employee_id" class="form-select">
                        <option value="">All Employees</option>
                        @foreach($employee_data as $emp)
                            <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>
                                {{ $emp->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <!--<div class="col-md-4">-->
                <!--    <label>Status</label>-->
                <!--    <select name="status" class="form-select">-->
                <!--        <option value="">All</option>-->
                <!--        <option value="Approved" {{ request('status') == 'Approved' ? 'selected' : '' }}>Approved</option>-->
                <!--        <option value="Pending" {{ request('status') == 'Pending' ? 'selected' : '' }}>Pending</option>-->
                <!--        <option value="Rejected" {{ request('status') == 'Rejected' ? 'selected' : '' }}>Rejected</option>-->
                <!--    </select>-->
                <!--</div>-->
                <div class="col-md-4">
                    <label>Invoice Generated</label>
                    <select name="invoice_id" class="form-select">
                        <option value="">All</option>
                        <option value="Yes" {{ request('invoice_id') == 'Yes' ? 'selected' : '' }}>Yes</option>
                        <option value="No" {{ request('invoice_id') == 'No' ? 'selected' : '' }}>No</option>
                    </select>

                </div>
                
                <div class="col-md-4">
                <label class="form-label d-none d-md-block">&nbsp;</label>
                <div class="input-group bg-grey rounded">
                    <span class="input-group-text bg-additional-grey">
                        <i class="fa fa-search text-dark-grey"></i>
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control f-14 p-1 height-35 border" placeholder="Search task or employee..." autocomplete="off">
                </div>
            </div>

            </div>
        
            <div class="row mb-3">
                
                <div class="col-md-3 text-end">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
            </div>
        </form>

        
        <div class="table-responsive">
            <table id="timeTable" class="table table-bordered table-striped">

                <thead class="table-light">
                    <tr>
                        <th>Id</th>
                        <th>Code</th>
                        <th>Project Name</th>
                        <th>Task</th>
                        <th>Employee</th>
                        <th>Start Time</th>
                        <th>End Time</th>
                        <th>Total Hours</th>
                        <th>Earnings</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr>
                        <td>{{ $log->id }}</td>
                        <td>{{ $log->project->project_code ?? '-' }}</td>
                        <td>{{ $log->project->name ?? '-' }}</td> 
                        <td>{{ $log->task->title ?? '-' }}</td>
                        <td>{{ $log->employee->name ?? 'N/A' }}</td>
                        <td>{{ $log->start_date }} {{ \Carbon\Carbon::parse($log->start_time)->format('h:i A') }}</td>
                        <td>{{ $log->end_date }} {{ \Carbon\Carbon::parse($log->end_time)->format('h:i A') }}</td>
                        <td>{{ number_format($log->total_hours, 2) }} hrs</td>
                        <td>₹{{ number_format($log->total_hours * 200, 2) }}</td> {{-- example: ₹200/hour --}}
                        <td>
                            <!--<a href="{{ route('timelogs.edit', $log->id) }}" class="btn btn-sm btn-warning">Edit</a>-->
                            <form action="{{ route('timelogs.destroy', $log->id) }}" method="POST" style="display:inline-block;">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-danger" onclick="return confirm('Delete this log?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center">No data available in table</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

</div>

<!-- Add Time Log Modal -->
<div class="modal fade" id="addTimeLogModal" tabindex="-1" aria-labelledby="addTimeLogModalLabel" aria-hidden="true">
    <div class="modal-dialog">
       <form action="{{ route('timelogs.store') }}" method="POST">
         @csrf
          <input type="hidden" name="redirect_to_project" value="1">
           <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Log Time</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
    
            <div class="modal-body">
                {{-- Project --}}
                 <div class="mb-3">
                    <label class="form-label">Project *</label>
                    @if(isset($projects))
                        <select name="project_id" id="project_id" class="form-select" required>
                            <option value="">Select Project</option>
                            @foreach($projects as $pro)
                                <option value="{{ $pro->id }}">{{ $pro->name }}</option>
                            @endforeach
                        </select>
                    @elseif(isset($project))
                        <select name="project_id" id="project_id" class="form-select" required>
                            <option value="{{ $project->id }}" selected>{{ $project->name }}</option>
                        </select>
                    @endif
                </div>
       

    
                {{-- Task (populated dynamically) --}}
                <div class="mb-3">
                    <label class="form-label">Task *</label>
                    @if(isset($tasks))
                    
                    <select name="task_id" id="task_id" class="form-select" required>
                        <option value="">Select Task</option>
                        @foreach($tasks as $task)
                            <option value="{{ $task->id }}">{{ $task->title }}</option>
                        @endforeach
                        </select>
                         @elseif(isset($tasks))
                        <select name="task_id" id="task_id" class="form-select" required>
                            <option value="">Select Task</option>
                             <option value="{{ $task->id }}" selected>{{ $task->title }}</option>
                        </select>
                         @endif
                </div>
    
                {{-- Employee (auto-filled based on task) --}}
                    <div class="mb-3">
                        <label class="form-label">Employee *</label>
                         @if(isset($tasks))
                         
                         <select name="employee_id" id="employee_id" class="form-select" required>
                            <option value="">Select Employee</option>
                            @foreach($employee_data as $employee)
                                <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                            @endforeach
                        </select>
                        @elseif(isset($tasks))
                        
                        <select name="employee_id" id="employee_id" class="form-select" required>
                            <option value="">Select Employee</option>
                            <option value="{{ $employee->id }}" selected>{{ $employee->name }}</option>
                           
                        </select>
                          @endif
                    </div>
    
                {{-- Date & Time --}}
                <div class="mb-3">
                    <label class="form-label">Start Date *</label>
                    <input type="date" name="start_date" id="start_date" class="form-control" required>
                </div>
    
                <div class="mb-3">
                    <label class="form-label">Start Time *</label>
                    <input type="time" name="start_time" id="start_time" class="form-control" required>
                </div>
    
                <div class="mb-3">
                    <label class="form-label">End Date *</label>
                    <input type="date" name="end_date" id="end_date" class="form-control" required>
                </div>
    
                <div class="mb-3">
                    <label class="form-label">End Time *</label>
                    <input type="time" name="end_time" id="end_time" class="form-control" required>
                </div>
    
                <div class="mb-3">
                    <label class="form-label">Memo</label>
                    <textarea name="memo" class="form-control" rows="2" placeholder="e.g. Working on new logo"></textarea>
                </div>
    
                {{-- Total Hours --}}
                <div class="mb-3">
                    <label class="form-label">Total Hours</label>
                    <input type="text" name="total_hours" id="total_hours" class="form-control" readonly>
                </div>
            </div>
    
            <div class="modal-footer">
                <button type="submit" class="btn btn-success">Add Time Log</button>
            </div>
        </div>
      </form>

    </div>
</div>

@push('js')
<script>
  $(document).ready(function () {
    $('#timeTable').DataTable({
        dom: 'Bfrtip',
        buttons: ['copy', 'csv', 'excel', 'pdf', 'print'],
        responsive: true,
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],
        language: {
                search: "_INPUT_",
                searchPlaceholder: "Search tickets..."
        }
    });
  });
</script>




<script>
$(document).ready(function () {
    // Load tasks based on selected project
    $('#project_id').on('change', function () {
    const projectId = $(this).val();
    $('#task_id').html('<option value="">Loading...</option>');
    $('#employee_id').html('<option value="">Select Employee</option>');

    if (projectId) {
        $.get(`/pms/public/project/${projectId}/tasks`, function (data) { // NOTE: now it's `/project/` not `/api/project/`
            let options = '<option value="">Select Task</option>';
            data.forEach(task => {
                options += `<option value="${task.id}">${task.title}</option>`;
            });
            $('#task_id').html(options);
        });
    }
});

    // Load employee based on selected task
        $('#task_id').on('change', function () {
        const taskId = $(this).val();
        $('#employee_id').html('<option value="">Loading...</option>');
    
        if (taskId) {
            $.get(`/pms/public/timelogs/get-task-employee/${taskId}`, function (employee) {
                if (employee && employee.id) {
                    $('#employee_id').html(`<option value="${employee.id}" selected>${employee.name}</option>`);
                } else {
                    $('#employee_id').html('<option value="">No employee assigned</option>');
                }
            });
        }
    });



    // Calculate total hours live
    $('#start_date, #start_time, #end_date, #end_time').on('change', function () {
        const start = new Date($('#start_date').val() + 'T' + $('#start_time').val());
        const end = new Date($('#end_date').val() + 'T' + $('#end_time').val());

        if (!isNaN(start.getTime()) && !isNaN(end.getTime()) && end > start) {
            const diffInHours = (end - start) / (1000 * 60 * 60);
            $('#total_hours').val(diffInHours.toFixed(2) + ' hrs');
        } else {
            $('#total_hours').val('0 hrs');
        }
    });
});
</script>
@endpush


@endsection

