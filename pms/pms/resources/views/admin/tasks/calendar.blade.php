@extends('admin.layout.app')

@section('title', 'Task Calendar')

@section('content')
<main class="main">
    <div class="container py-4">
       
            <h4 class="fw-bold mb-0">Task Calendar</h4>
            &nbsp;
            
            <form id="filter-form" method="GET" action="{{ route('tasks.calendar') }}" class="mb-3">
    <div class="d-flex flex-wrap bg-white p-3 shadow-sm rounded">
        
        <!-- Status Filter -->
        <div class="me-3 mb-2">
            <label class="fw-bold small">Status</label>
            <select name="status" class="form-select">
                <option value="all" {{ request('status')=='all' ? 'selected' : '' }}>All</option>
                <option value="not finished" {{ request('status')=='not finished' ? 'selected' : '' }}>Hide completed</option>
                <option value="Incomplete" {{ request('status')=='Incomplete' ? 'selected' : '' }}>Incomplete</option>
                <option value="To Do" {{ request('status')=='To Do' ? 'selected' : '' }}>To Do</option>
                <option value="Doing" {{ request('status')=='Doing' ? 'selected' : '' }}>Doing</option>
                <option value="Completed" {{ request('status')=='Completed' ? 'selected' : '' }}>Completed</option>
                <option value="Waiting Approval" {{ request('status')=='Waiting Approval' ? 'selected' : '' }}>Waiting Approval</option>
            </select>
        </div>

        <!-- Project Filter -->
        <div class="me-3 mb-2">
            <label class="fw-bold small">Project</label>
            <select name="project_id" class="form-select">
                <option value="all">All</option>
                @foreach($projects as $project)
                    <option value="{{ $project->id }}" {{ request('project_id') == $project->id ? 'selected' : '' }}>
                        {{ $project->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Search -->
        <div class="me-3 mb-2">
            <label class="fw-bold small">Search</label>
            <input type="text" name="search" class="form-control" placeholder="Search task..."
                   value="{{ request('search') }}">
        </div>

        <!-- Buttons -->
        <div class="align-self-end mb-2">
            <button type="submit" class="btn btn-primary me-2">Apply</button>
            <a href="{{ route('tasks.calendar') }}" class="btn btn-outline-secondary">Clear</a>
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
        
                <a href="{{ route('tasks.waiting-approval') }}" 
                   class="btn btn-secondary" 
                   data-toggle="tooltip" 
                   title="Waiting Approval">
                   <i class="bi bi-exclamation-triangle text-warning"></i>
                </a>
            </div>
        </div>
       

        <div id="calendar"></div>
    </div>
</main>
@endsection

@section('css')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet" />
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const calendarEl = document.getElementById('calendar');

        const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            height: 650,
           headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
            },

            events: {!! json_encode($tasks) !!},
            eventClick: function(info) {
                if (info.event.url) {
                    window.open(info.event.url, "_blank");
                    info.jsEvent.preventDefault();
                }
            }
        });

        calendar.render();
    });
</script>
@endsection
