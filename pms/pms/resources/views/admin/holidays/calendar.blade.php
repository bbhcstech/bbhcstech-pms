@extends('admin.layout.app')

@section('title', 'Holiday Calendar')

@section('content')
<main class="main">
    <div class="container py-4">
        
           <h4 class="fw-bold mb-0 me-3">Holidays</h4>
           &nbsp;
       <div class="d-flex justify-content-between align-items-center mb-3">
    <!-- Left Side -->
    <div class="d-flex align-items-center">
        
         
  
      
       @if(auth()->user()->role === 'admin')
            <a href="{{ route('holidays.create') }}" class="btn btn-primary me-2">
                Add Holiday
            </a>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#holidayModal">
                Mark Default Holidays
            </button>
            
            @endif
       
    </div>

    <!-- Right Side: Switcher -->
    <div class="btn-group" role="group" aria-label="Holiday View Switcher">
        <a href="{{ route('holidays.calendar') }}" 
           class="btn btn-sm btn-outline-primary {{ request()->routeIs('holidays.calendar') ? 'active' : '' }}" 
           data-bs-toggle="tooltip" title="Calendar">
            <i class="bi bi-calendar"></i>
        </a>
        <a href="{{ route('holidays.index') }}" 
           class="btn btn-sm btn-outline-primary {{ request()->routeIs('holidays.index') ? 'active' : '' }}" 
           data-bs-toggle="tooltip" title="Table View">
            <i class="bi bi-list-ul"></i>
        </a>
    </div>
</div>
 &nbsp;

        <div id='calendar'></div>
    </div>
    
    <!-- Unified Modal -->
<div class="modal fade" id="holidayModal" tabindex="-1" aria-labelledby="holidayModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title" id="holidayModalLabel">Mark Holiday</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <form id="save-mark-holiday-form" method="POST" action="{{ route('holidays.mark') }}">
          @csrf

          <!-- Default Weekly Holidays -->
          <div class="mb-3">
            <label class="form-label">Mark days for default Holidays for the current year</label>
            <div class="d-flex flex-wrap">
              @php
                $days = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
              @endphp
              @foreach($days as $i => $day)
                <div class="form-check me-3">
                  <input class="form-check-input" type="checkbox" name="office_holiday_days[]" id="day_{{ $i }}" value="{{ $i }}">
                  <label class="form-check-label" for="day_{{ $i }}">{{ $day }}</label>
                </div>
              @endforeach
            </div>
          </div>

          <!-- Occasion (for weekly holidays, fallback if none selected) -->
          <div class="mb-3">
            <label for="occassion" class="form-label">Occasion </label>
            <input type="text" class="form-control" name="occassion" id="occassion" placeholder="Occasion name">
          </div>

          <hr>

          <!-- Single Holiday -->
          <!--<h6>Add a specific holiday</h6>-->
          <!--<div class="row">-->
          <!--  <div class="col-md-6 mb-3">-->
          <!--    <label for="date" class="form-label">Holiday Date</label>-->
          <!--    <input type="date" name="date" id="date" class="form-control">-->
          <!--  </div>-->
          <!--  <div class="col-md-6 mb-3">-->
          <!--    <label for="occasion" class="form-label">Occasion</label>-->
          <!--    <input type="text" name="occasion" id="occasion" class="form-control">-->
          <!--  </div>-->
          <!--</div>-->

        </form>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" form="save-mark-holiday-form" class="btn btn-primary">Save</button>
      </div>

    </div>
  </div>
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
                height: 600,
                headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
            },

                events: {!! json_encode($holidays) !!},
                eventColor: '#0d6efd',
                eventTextColor: '#fff'
            });

            calendar.render();
        });
    </script>
@endsection
