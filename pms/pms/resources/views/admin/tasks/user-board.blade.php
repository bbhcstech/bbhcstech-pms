@extends('admin.layout.app')

@section('content')
<main class="main">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4>Task Board</h4>
        </div>
        
          &nbsp;
        <form id="filter-form" method="GET" action="{{ route('users.tasks.board', $user->id) }}">
    <div class="d-lg-flex d-md-flex d-block flex-wrap p-3 mb-4 bg-white shadow-sm rounded-lg align-items-end">

        <!-- Date Range -->
        <div class="me-3 mb-3 flex-grow-1">
            <label class="fw-bold mb-2">Duration</label>
            <div class="input-group input-daterange">
                <span class="input-group-text bg-light"><i class="bi bi-calendar"></i></span>
                <input type="text" name="start_date" class="form-control form-control-lg" 
                       placeholder="Start Date" value="{{ request('start_date') }}">
                <span class="input-group-text">to</span>
                <input type="text" name="end_date" class="form-control form-control-lg" 
                       placeholder="End Date" value="{{ request('end_date') }}">
            </div>
        </div>

        <!-- Search -->
        <div class="me-3 mb-3 flex-grow-1">
            <label class="fw-bold mb-2">Search</label>
            <div class="input-group">
                <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                <input type="text" name="search" class="form-control form-control-lg" 
                       placeholder="Type to search..." value="{{ request('search') }}">
            </div>
        </div>

        <!-- Buttons -->
        <div class="mb-3">
            <label class="fw-bold mb-2 d-block">&nbsp;</label>
            <div class="d-flex">
                <button type="submit" class="btn btn-primary btn-lg me-2">
                    <i class="bi bi-funnel"></i> Apply
                </button>
                <a href="{{ route('users.tasks.board', $user->id) }}" class="btn btn-outline-secondary btn-lg">
                    <i class="bi bi-x-circle"></i> Clear
                </a>
            </div>
        </div>
    </div>
</form>

  &nbsp;
            
      <div class="d-flex justify-content-between align-items-center mb-3">
            <!-- Left side buttons -->
            <div>
                <a href="{{ route('tasks.create') }}" 
                   class="btn btn-primary mr-2">
                    <i class="bi bi-plus-lg"></i> Add Task
                </a>
        
                <button type="button" 
                        class="btn btn-secondary mr-2" 
                        id="filter-my-task">
                    <i class="bi bi-person"></i> My Tasks
                </button>
            </div>
        
            <!-- Right side icons -->
            <div class="btn-group" role="group">
                <a href="{{ route('tasks.index') }}" 
                   class="btn btn-secondary" 
                   data-toggle="tooltip" 
                   title="Tasks">
                   <i class="bi bi-list-ul"></i>
                </a>
        
                <a href="{{ route('users.tasks.board') }}" 
           class="btn btn-secondary f-14" 
           data-toggle="tooltip" 
           data-original-title="Task Board">
            <i class="bi bi-kanban"></i>
        </a>
        
        
        
                <a href="{{ route('tasks.calendar') }}" 
                   class="btn btn-secondary" 
                   data-toggle="tooltip" 
                   title="Calendar">
                   <i class="bi bi-calendar"></i>
                </a>
        
                <a href="" 
                   class="btn btn-secondary" 
                   data-toggle="tooltip" 
                   title="Waiting Approval">
                   <i class="bi bi-exclamation-triangle text-warning"></i>
                </a>
            </div>
        </div>
        
        

  &nbsp;
  
  <!-- 🚨 Note Section -->
<div class="alert alert-warning mt-4">
    <i class="bi bi-exclamation-triangle-fill me-2"></i>
    <strong>Note:</strong> You cannot move the task to or from the 
    <strong>'Waiting for Approval'</strong> column. 
    To update the task status, please go to the <strong>Tasks</strong> menu.
</div>
  
   &nbsp;
     <div class="row">
    @foreach($statuses as $status)
        <div class="col-md-3 mb-4">
            <div class="card shadow">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong>{{ $status }}</strong>
                </div>
                <div class="card-body" style="min-height: 150px;">
                    @php $count = $tasks->where('status', $status)->count(); @endphp
                    <p><strong>{{ $count }}</strong> Task{{ $count !== 1 ? 's' : '' }}</p>

                    <div class="task-column">
                        @foreach($tasks->where('status', $status) as $task)
                            <div class="card mb-2 p-2 shadow-sm">
                                <strong>{{ $task->title }}</strong>
                                <div class="text-muted small mb-1">
                                    Project: {{ $task->project->name ?? 'N/A' }}
                                </div>
                                <div class="text-muted small">
                                    Created: {{ $task->created_at->format('d M, Y') }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
    </div>
</main>
@endsection


@push('css')
<link rel="stylesheet" 
      href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
      
      <style>
    .form-control-lg {
        border-radius: 0.75rem;
    }
    .input-group-text {
        font-size: 1.2rem;
    }
    .btn-lg {
        padding: 0.6rem 1.2rem;
        border-radius: 0.75rem;
    }
</style>
@endpush

@push('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
<script>
$(document).ready(function(){
    $('.input-daterange input').datepicker({
        format: 'yyyy-mm-dd',
        autoclose: true,
        todayHighlight: true,
        todayBtn: true,
        clearBtn: true,
        orientation: "bottom auto"
    });
});
</script>
@endpush

