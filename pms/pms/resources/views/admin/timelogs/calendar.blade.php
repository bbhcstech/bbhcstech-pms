@extends('admin.layout.app')

@section('title', 'TimeLog Calendar')

@section('content')
<main class="main">
    <div class="container py-4">
        
        <h4 class="fw-bold mb-0 me-3">Timesheet</h4>

         &nbsp;

         <div class="d-flex justify-content-between align-items-center mb-3">
    <!-- Left side -->
    <div class="d-flex align-items-center">
       
        <a href="{{ route('timelogs.create') }}" class="btn btn-primary">Log Time</a>
    </div>

    <!-- Right side icons -->
    <div class="btn-group" role="group">
        <a href="{{ route('timelogs.index') }}" class="btn btn-sm btn-outline-primary {{ request()->routeIs('timelogs.index') ? 'active' : '' }}" 
           data-toggle="tooltip" data-original-title="Timesheet">
            <i class="side-icon bi bi-list-ul"></i>
        </a>

        <a href="{{ route('timelogs.calendar') }}" class="btn btn-sm btn-outline-primary {{ request()->routeIs('timelogs.calendar') ? 'active' : '' }}"  
           data-toggle="tooltip" data-original-title="Calendar">
            <i class="side-icon bi bi-calendar"></i>
        </a>

        <a href="{{ route('timelogs.byEmployee')}}" class="btn btn-sm btn-outline-primary {{ request()->routeIs('timelogs.byEmployee') ? 'active' : '' }}" 
           data-toggle="tooltip" data-original-title="Employee TimeLogs">
            <i class="side-icon bi bi-person"></i>
        </a>

        <a href="javascript:;" 
                   class="btn btn-secondary f-14" 
                   data-bs-toggle="modal" 
                   data-bs-target="#howItWorksModal"
                   data-bs-toggle="tooltip" 
                   title="How It Works">
                    <i class="side-icon bi bi-question-circle"></i>
                </a>
    </div>
</div>
&nbsp;

<!-- Modal -->
<div class="modal fade" id="howItWorksModal" tabindex="-1" aria-labelledby="howItWorksLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title" id="howItWorksLabel">Timesheet Lifecycle</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body text-center">
        <img src="{{ asset('timesheet-lifecycle.png') }}" alt="Timesheet Lifecycle" class="img-fluid">
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>

    </div>
  </div>
</div>

        <div id='calendar'></div>
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
                    right: 'dayGridMonth,timeGridWeek,listMonth'
                },
                events: {!! json_encode($timelogs) !!},
                eventColor: '#198754',
                eventTextColor: '#fff'
            });

            calendar.render();
        });
    </script>
@endsection
