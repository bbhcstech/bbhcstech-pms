@extends('admin.layout.app')

@section('title', 'Waiting for Approval Tasks')

@section('content')
<main class="main">
    <div class="container py-4">
        <h4 class="fw-bold mb-3">Tasks - Waiting for Approval</h4>
        
        
        <!-- Filters -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('tasks.waitingApproval') }}">
            <div class="row g-3 align-items-end">

                <!-- Project Filter -->
                <!--<div class="col-md-3">-->
                <!--    <label for="project_id" class="form-label">Project</label>-->
                <!--    <select name="project_id" id="project_id" class="form-select">-->
                <!--        <option value="">All Projects</option>-->
                <!--        @foreach($projects as $project)-->
                <!--            <option value="{{ $project->id }}" {{ request('project_id') == $project->id ? 'selected' : '' }}>-->
                <!--                {{ $project->name }}-->
                <!--            </option>-->
                <!--        @endforeach-->
                <!--    </select>-->
                <!--</div>-->

                <!-- Assigned To Filter -->
                <!--<div class="col-md-3">-->
                <!--    <label for="assigned_to" class="form-label">Assigned To</label>-->
                <!--    <select name="assigned_to" id="assigned_to" class="form-select">-->
                <!--        <option value="">All Employees</option>-->
                <!--        @foreach($employees as $employee)-->
                <!--            <option value="{{ $employee->id }}" {{ request('assigned_to') == $employee->id ? 'selected' : '' }}>-->
                <!--                {{ $employee->name }}-->
                <!--            </option>-->
                <!--        @endforeach-->
                <!--    </select>-->
                <!--</div>-->

                <!-- Duration Filter -->
                <div class="col-md-3">
                    <label for="duration" class="form-label">Duration</label>
                    <input type="text"
                           name="duration"
                           id="duration"
                           class="form-control"
                           value="{{ request('duration') }}"
                           placeholder="YYYY-MM-DD to YYYY-MM-DD"
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
                    <a href="{{ route('tasks.waitingApproval') }}" class="btn btn-outline-secondary w-100">
                        <i class="fas fa-redo me-1"></i> Reset
                    </a>
                </div>

            </div>
        </form>
    </div>
</div>


 <div class="d-flex justify-content-between align-items-center mb-3">
        <!-- Left Side: New Leave -->
        <a href="{{ route('leaves.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-circle"></i> &nbsp; New Leave
        </a>
    
        <!-- Right Side: Button Group -->
        <div class="btn-group" role="group" aria-label="Basic example">
            <a href="" 
               class="btn btn-secondary f-14" 
               data-toggle="tooltip" 
               data-original-title="Table View">
                <i class="side-icon bi bi-list-ul"></i>
            </a>
    
            <a href="{{ route('leaves.calendar') }}" 
       class="btn btn-secondary f-14 {{ request()->routeIs('leaves.calendar') ? 'btn-active' : '' }}"
       data-toggle="tooltip" data-original-title="Calendar">
        <i class="side-icon bi bi-calendar"></i>
    </a>
    
            <!--<a href="" -->
            <!--   class="btn btn-secondary f-14 btn-active" -->
            <!--   data-toggle="tooltip" -->
            <!--   data-original-title="My Leaves">-->
            <!--    <i class="side-icon bi bi-person"></i>-->
            <!--</a>-->
        </div>
    </div>
    
    &nbsp;

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
                    <tr>
                        <td>{{ $task->code }}</td>
                        <td>{{ $task->title }}</td>
                        <td>{{ $task->completed_on ?? '-' }}</td>
                        <td>{{ $task->start_date }}</td>
                        <td>{{ $task->due_date }}</td>
                        <td>{{ $task->estimated_time ?? '-' }}</td>
                        <td>{{ $task->hours_logged ?? '-' }}</td>
                        <td>{{ $task->assignee->name ?? 'N/A' }}</td>
                        <td><span class="badge bg-warning">{{ $task->status }}</span></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center">No tasks in Waiting for Approval</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</main>
@endsection
