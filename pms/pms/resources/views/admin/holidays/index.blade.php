@extends('admin.layout.app')
@section('title', 'Holiday List')
@section('content')
<main class="main">
    <div class="container py-4">
        @php
            $isAdmin = auth()->user()->role === 'admin';
        @endphp

        @if(!$isAdmin)
            <div class="alert alert-info mb-3">
                <i class="bi bi-info-circle me-2"></i>
                You are viewing in read-only mode. Only administrators can manage holidays.
            </div>
        @endif

        <h4 class="fw-bold mb-3">Holiday List</h4>

        <!-- Filter Section -->
        <form method="GET" action="{{ route('holidays.index') }}" class="row g-2 mb-3">
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

            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-filter me-1"></i> Filter
                </button>
                <a href="{{ route('holidays.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-clockwise me-1"></i> Reset
                </a>
            </div>
        </form>

        <!-- Header with actions -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <!-- Left side: Admin actions or employee message -->
            @if($isAdmin)
            <div class="d-flex gap-2">
                <a href="{{ route('holidays.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i> Add Holiday
                </a>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#holidayModal">
                    <i class="bi bi-calendar-check me-1"></i> Mark Default
                </button>
            </div>
            @else
            <div class="text-muted">
                <i class="bi bi-eye me-1"></i> Read-only view
            </div>
            @endif

            <!-- Right side: View switcher and admin bulk actions -->
            <div class="d-flex align-items-center gap-2">
                @if($isAdmin)
                <div class="d-flex align-items-center gap-2 me-3">
                    <select class="form-select form-select-sm" id="quick-action-type" style="width: 150px;">
                        <option value="">No Action</option>
                        <option value="delete">Delete Selected</option>
                    </select>
                    <button class="btn btn-sm btn-primary" id="quick-action-apply" disabled>
                        <i class="bi bi-check-circle me-1"></i> Apply
                    </button>
                </div>
                @endif

                <!-- View switcher -->
                <div class="btn-group" role="group">
                    <a href="{{ route('holidays.index') }}"
                       class="btn btn-sm {{ request()->routeIs('holidays.index') ? 'btn-primary' : 'btn-outline-primary' }}">
                        <i class="bi bi-list-ul me-1"></i> List
                    </a>
                    <a href="{{ route('holidays.calendar') }}"
                       class="btn btn-sm {{ request()->routeIs('holidays.calendar') ? 'btn-primary' : 'btn-outline-primary' }}">
                        <i class="bi bi-calendar me-1"></i> Calendar
                    </a>
                </div>
            </div>
        </div>

        <!-- Success/Error Messages -->
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        <!-- Holiday Table -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="holidayTable" class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                @if($isAdmin)
                                <th width="50" class="text-center">
                                    <input type="checkbox" id="selectAll" class="form-check-input">
                                </th>
                                @endif
                                <th>Date</th>
                                <th>Day</th>
                                <th>Title</th>
                                <th>Type</th>
                                @if($isAdmin)
                                <th width="100">Actions</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($holidays as $holiday)
                                <tr>
                                    @if($isAdmin)
                                    <td class="text-center">
                                        <input type="checkbox" class="form-check-input holiday-checkbox" value="{{ $holiday->id }}">
                                    </td>
                                    @endif
                                    <td>
                                        <i class="bi bi-calendar3 text-primary me-2"></i>
                                        {{ \Carbon\Carbon::parse($holiday->date)->format('d M, Y') }}
                                    </td>
                                    <td>
                                        <span class="badge bg-info">
                                            {{ \Carbon\Carbon::parse($holiday->date)->format('D') }}
                                        </span>
                                    </td>
                                    <td>
                                        <strong>{{ $holiday->title }}</strong>
                                        @if($holiday->occassion)
                                            <br><small class="text-muted">{{ $holiday->occassion }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($holiday->type === 'weekly_holiday')
                                            <span class="badge bg-success">Weekly Holiday</span>
                                        @else
                                            <span class="badge bg-primary">Special Holiday</span>
                                        @endif
                                    </td>
                                    @if($isAdmin)
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-light" type="button" data-bs-toggle="dropdown">
                                                <i class="bi bi-three-dots-vertical"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('holidays.edit', $holiday->id) }}">
                                                        <i class="bi bi-pencil me-2"></i> Edit
                                                    </a>
                                                </li>
                                                <li>
                                                    <form action="{{ route('holidays.destroy', $holiday->id) }}" method="POST"
                                                          onsubmit="return confirm('Are you sure you want to delete this holiday?');">
                                                        @csrf @method('DELETE')
                                                        <button class="dropdown-item text-danger" type="submit">
                                                            <i class="bi bi-trash me-2"></i> Delete
                                                        </button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $isAdmin ? 6 : 5 }}" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="bi bi-calendar-x display-6"></i>
                                            <p class="mt-3 mb-0">No holidays found for the selected period.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Admin Modal for Marking Default Holidays -->
    @if($isAdmin)
    <div class="modal fade" id="holidayModal" tabindex="-1" aria-labelledby="holidayModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="holidayModalLabel">
                        <i class="bi bi-calendar-check me-2"></i>Mark Default Holidays
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="save-mark-holiday-form" method="POST" action="{{ route('holidays.mark') }}">
                        @csrf
                        <div class="mb-4">
                            <label class="form-label fw-bold">Select Weekly Holidays for Current Year</label>
                            <div class="row">
                                @php
                                    $days = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
                                @endphp
                                @foreach($days as $i => $day)
                                    <div class="col-md-4 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="office_holiday_days[]"
                                                   id="day_{{ $i }}" value="{{ $i }}">
                                            <label class="form-check-label" for="day_{{ $i }}">
                                                {{ $day }}
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="occassion" class="form-label fw-bold">Occasion Name (Optional)</label>
                            <input type="text" class="form-control" name="occassion" id="occassion"
                                   placeholder="e.g., Weekend, Public Holiday, etc.">
                            <div class="form-text">If left blank, day name will be used as occasion.</div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i> Cancel
                    </button>
                    <button type="submit" form="save-mark-holiday-form" class="btn btn-primary">
                        <i class="bi bi-check-circle me-1"></i> Save Holidays
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</main>

@push('js')
<script>
    $(document).ready(function () {
        // Initialize DataTable
        $('#holidayTable').DataTable({
            dom: 'Bfrtip',
            buttons: ['copy', 'csv', 'excel', 'pdf', 'print'],
            responsive: true,
            pageLength: 10,
            lengthMenu: [10, 25, 50, 100],
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search holidays..."
            },
            order: [[ {{ $isAdmin ? 1 : 0 }}, 'asc' ]] // Sort by date
        });

        @if($isAdmin)
        // Bulk actions for admin
        $('#selectAll').on('change', function() {
            $('.holiday-checkbox').prop('checked', this.checked);
            toggleApplyButton();
        });

        $('.holiday-checkbox').on('change', function() {
            toggleApplyButton();
        });

        $('#quick-action-type').on('change', toggleApplyButton);

        function toggleApplyButton() {
            let anyChecked = $('.holiday-checkbox:checked').length > 0;
            let actionSelected = $('#quick-action-type').val() !== '';
            $('#quick-action-apply').prop('disabled', !(anyChecked && actionSelected));
        }

        $('#quick-action-apply').on('click', function() {
            let ids = $('.holiday-checkbox:checked').map(function(){
                return $(this).val();
            }).get();
            let action = $('#quick-action-type').val();

            if(ids.length === 0){
                alert('Please select at least one holiday.');
                return;
            }

            if(action === 'delete') {
                if(!confirm('Are you sure you want to delete ' + ids.length + ' selected holiday(s)?')) {
                    return;
                }

                $.ajax({
                    url: '{{ route("holiday.bulkAction") }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        holiday_ids: ids,
                        action: action
                    },
                    success: function(response){
                        alert(response.message);
                        location.reload();
                    },
                    error: function(xhr){
                        if(xhr.status === 403) {
                            alert('You are not authorized to perform this action.');
                        } else {
                            alert('Something went wrong. Please try again.');
                        }
                    }
                });
            }
        });
        @endif
    });
</script>
@endpush
@endsection
