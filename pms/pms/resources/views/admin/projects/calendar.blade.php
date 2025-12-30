@extends('admin.layout.app')

@section('title', 'Project Calendar')

@section('content')
<main class="main">
    <div class="container py-4">

        <!-- Header -->
        <!--<div class="d-flex justify-content-between align-items-center mb-4">-->
        <!--    <h4 class="fw-bold mb-0">Project Calendar</h4>-->
        <!--    <a href="{{ url('account/projects') }}" class="btn btn-secondary">-->
        <!--        <i class="bi bi-list-ul"></i> Projects-->
        <!--    </a>-->
        <!--</div>-->
        
        @if(auth()->user()->role === 'admin')
        <a href="{{ route('projects.create') }}" class="btn btn-primary mb-2">
            <i class="bi bi-plus-circle me-1"></i> Add Project
        </a>
    @endif

        <!-- Buttons -->
       <div class="d-flex justify-content-end mb-3">
                <div class="btn-group" role="group">
                    <a href="{{ route('projects.index') }}" class="btn btn-secondary f-14">Projects</a>
                    <a href="{{ route('projects.archive') }}" class="btn btn-secondary f-14">Archive</a>
                    <a href="{{ route('projects.calendar') }}" class="btn btn-secondary f-14 btn-active">Calendar</a>
                </div>
            </div>



        <!-- Calendar Wrapper -->
        <div class="card">
            <div class="card-body">
                <div id="project-calendar" style="min-height: 650px;"></div>
            </div>
        </div>

    </div>
</main>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet" />
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('project-calendar');

    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        height: 650,
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        events: {!! json_encode($events) !!}, // Controller must pass $events
        eventColor: '#378006',
        eventTextColor: '#fff',
        navLinks: true,
        editable: false,
        dayMaxEvents: true
    });

    calendar.render();
});
</script>
@endpush
