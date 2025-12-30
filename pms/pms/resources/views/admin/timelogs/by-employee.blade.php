@extends('admin.layout.app')

@section('title', 'Employee TimeLogs')

@section('content')
<main class="main">
    <div class="container py-4">
        <h4>Timesheet</h4>

        <form method="GET" action="{{ route('timelogs.byEmployee') }}" class="row mb-3">
            <!-- Date Range -->
            <div class="col-md-4">
                <label class="form-label">Duration</label>
                <input type="text" name="daterange" id="daterange" class="form-control"
                       value="{{ request('start_date') && request('end_date') 
                                ? request('start_date').' To '.request('end_date') : '' }}">
                <input type="hidden" name="start_date" id="start_date" value="{{ request('start_date') }}">
                <input type="hidden" name="end_date" id="end_date" value="{{ request('end_date') }}">
            </div>

            <!-- Employee Dropdown -->
            <div class="col-md-3">
                <label class="form-label">Employee</label>
                <select name="user_id" class="form-select">
                    <option value="">All</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" 
                            {{ request('user_id', auth()->id()) == $emp->id ? 'selected' : '' }}>
                            {{ $emp->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Search -->
            <div class="col-md-2">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" 
                       placeholder="Start typing to search..."
                       value="{{ request('search') }}">
            </div>

            <!-- Submit -->
            
             <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">Filter</button>
                    <a href="{{ route('timelogs.byEmployee') }}" class="btn btn-secondary">Reset</a>
                </div>
        </form>
        
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

        <!-- Results -->
        <div class="card">
            <div class="card-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Project</th>
                            <th>Task</th>
                            <th>Start</th>
                            <th>End</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            <tr>
                                <td>{{ $log->user->name ?? '-' }}</td>
                                <td>{{ $log->project->project_code ?? '-' }}</td>
                                <td>{{ $log->task->title ?? '-' }}</td>
                                <td>{{ $log->start_time }}</td>
                                <td>{{ $log->end_time }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center">No logs found</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>
@endsection

@section('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/moment/min/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script>
    $(function() {
        $('#daterange').daterangepicker({
            autoUpdateInput: false,
            locale: { cancelLabel: 'Clear' },
            ranges: {
                'Today': [moment(), moment()],
                'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                'Last 90 Days': [moment().subtract(89, 'days'), moment()],
                'Last 6 Months': [moment().subtract(6, 'months').startOf('month'), moment()],
                'Last 1 Year': [moment().subtract(1, 'year').startOf('day'), moment()],
                'Custom Range': []
            }
        });

        $('#daterange').on('apply.daterangepicker', function(ev, picker) {
            $('#start_date').val(picker.startDate.format('YYYY-MM-DD'));
            $('#end_date').val(picker.endDate.format('YYYY-MM-DD'));
            $(this).val(picker.startDate.format('DD-MM-YYYY') + ' To ' + picker.endDate.format('DD-MM-YYYY'));
        });

        $('#daterange').on('cancel.daterangepicker', function() {
            $(this).val('');
            $('#start_date').val('');
            $('#end_date').val('');
        });
    });
</script>
@endsection
