@extends('admin.layout.app')
@section('title', 'Holiday List')
@section('content')
<main class="main">
    <div class="container py-4">
         <h4 class="fw-bold mb-0 me-3">List View</h4>
         <!-- Filter Section -->
        <form method="GET" action="{{ route('holidays.index') }}" class="row g-2 mb-3">
            <!-- Month Filter -->
            <div class="col-md-3">
                <select name="month" class="form-select">
                    <option value="">Select Month</option>
                    @foreach(range(1,12) as $m)
                        <option value="{{ $m }}" {{ $selectedMonth == $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                        </option>
                    @endforeach
                </select>
            </div>
        
            <!-- Year Filter -->
            <div class="col-md-3">
                <select name="year" class="form-select">
                    <option value="">Select Year</option>
                    @foreach(range(date('Y')-5, date('Y')+2) as $y)
                        <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>
                            {{ $y }}
                        </option>
                    @endforeach
                </select>
            </div>
        
            <!-- Buttons -->
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="{{ route('holidays.index') }}" class="btn btn-secondary">Reset</a>
            </div>
        </form>
        &nbsp;
     
     <div class="d-flex justify-content-between align-items-center mb-3">
         
        


    <!-- Left: Action Buttons -->
    <div>
        <a href="{{ route('holidays.create') }}" class="btn btn-primary">
            Add Holiday
        </a>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#holidayModal">
            Mark Default Holidays
        </button>
    </div>

    <!-- Right: Switcher -->
            <div class="btn-group" role="group" aria-label="Holiday View Switcher">
                
                
                <select class="form-select" id="quick-action-type" style="min-width: 180px;">
                <option value="">No Action</option>
                <option value="delete">Delete</option>
            </select>
            <button class="btn btn-primary" id="quick-action-apply" disabled>Apply</button>

        
        &nbsp;
                <a href="{{ route('holidays.calendar') }}" 
                   class="btn btn-sm btn-outline-primary {{ request()->routeIs('holidays.calendar') ? 'active' : '' }}" 
                   data-toggle="tooltip" title="Calendar">
                    <i class="bi bi-calendar"></i>
                </a>
                <a href="{{ route('holidays.index') }}" 
                   class="btn btn-sm btn-outline-primary {{ request()->routeIs('holidays.index') ? 'active' : '' }}" 
                   data-toggle="tooltip" title="Table View">
                    <i class="bi bi-list-ul"></i>
                </a>
            </div>
        
        </div>


          @if(session('success'))
        <div class="alert alert-success" style="background-color: #28a745; color: white; border-color: #28a745;">
            {{ session('success') }}
        </div>
    @endif  
    &nbsp;
     
         <table id="holidayTable" class="table table-bordered table-hover table-striped align-middle">
            <thead>
                <tr>
                   <th><input type="checkbox" id="selectAll"></th>
                    <th>Date</th>
                    <th>Title</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
               @foreach($holidays as $holiday)
                    @foreach(explode(',', $holiday->dates) as $date)
                        <tr>
                            <td><input type="checkbox" class="holiday-checkbox" value="{{ $holiday->id }}"></td>
                            <td>{{ \Carbon\Carbon::parse($holiday->date)->format('d M, Y (D)') }}</td>
                            <td>{{ $holiday->title }}</td>
                            <!--<td>-->
                            <!--    <a href="{{ route('holidays.edit', $holiday->id) }}" class="btn btn-sm btn-warning">Edit</a>-->
                            <!--    <form method="POST" action="{{ route('holidays.destroy', $holiday->id) }}" style="display:inline-block">-->
                            <!--        @csrf @method('DELETE')-->
                            <!--        <button class="btn btn-sm btn-danger" onclick="return confirm('Delete?')">Delete️</button>-->
                            <!--    </form>-->
                            <!--</td>-->
                            
                            <td>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-light" type="button" id="dropdownMenuButton{{ $holiday->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                        
                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton{{ $holiday->id }}">
                                    <!-- Edit -->
                                    <li>
                                        <a class="dropdown-item" href="{{ route('holidays.edit', $holiday->id) }}">
                                            <i class="bi bi-pencil-square me-2"></i> Edit
                                        </a>
                                    </li>
                        
                                    <!-- Delete -->
                                    <li>
                                        <form action="{{ route('holidays.destroy', $holiday->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this holiday?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="dropdown-item text-danger" type="submit">
                                                <i class="bi bi-trash me-2"></i> Delete
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </td>

                        </tr>
                    @endforeach
                @endforeach

            </tbody>
        </table>
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
@push('js')
<script>

     $(document).ready(function () {
  $('#holidayTable').DataTable({
        dom: 'Bfrtip',
        buttons: ['copy', 'csv', 'excel', 'pdf', 'print'],
        responsive: true,
        pageLength: 10,
        lengthMenu: [10,25,50,100],
        language: { search: "_INPUT_", searchPlaceholder: "Start typing to search..." }
    });

  });
</script>

<script>
$(document).ready(function() {
    $('#markHolidayForm').submit(function(e) {
        e.preventDefault();

        $.ajax({
            url: "{{ route('holidays.mark') }}",
            type: "POST",
            data: $(this).serialize(),
            success: function(response) {
                if(response.status === 'success') {
                    alert(response.message);
                    $('#markHolidayModal').modal('hide');
                    location.reload(); // or append new holiday to table dynamically
                }
            },
            error: function(xhr) {
                alert('Something went wrong.');
            }
        });
    });
});

$(document).ready(function () {
 
    // Select all checkbox
    $('#selectAll').on('change', function() {
        $('.holiday-checkbox').prop('checked', this.checked);
        toggleApplyButton();
    });

    // Individual checkboxes
    $('.holiday-checkbox, #quick-action-type').on('change', toggleApplyButton);

    function toggleApplyButton() {
        let anyChecked = $('.holiday-checkbox:checked').length > 0;
        let actionSelected = $('#quick-action-type').val() !== '';
        $('#quick-action-apply').prop('disabled', !(anyChecked && actionSelected));
    }

    // Apply bulk action
    $('#quick-action-apply').on('click', function() {
        let ids = $('.holiday-checkbox:checked').map(function(){ return $(this).val(); }).get();
        let action = $('#quick-action-type').val();

        if(ids.length === 0){ alert('Select at least one holiday.'); return; }
        if(action === 'delete' && !confirm('Are you sure you want to delete selected holidays?')) return;

        $.ajax({
            url: '{{ route("holiday.bulkAction") }}',
            type: 'POST',
            data: { _token: '{{ csrf_token() }}', holiday_ids: ids, action: action },
            success: function(res){ location.reload(); },
            error: function(){ alert('Something went wrong'); }
        });
    });
});
</script>
@endpush

@endsection
