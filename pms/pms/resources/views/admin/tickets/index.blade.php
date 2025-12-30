@extends('admin.layout.app')

@section('content')
<div class="container-fluid mt-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Tickets</h4>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('tickets.index') }}">
                <div class="row g-3 align-items-end">

                    <!-- Date Range -->
                    <div class="col-md-3">
                        <label for="duration" class="form-label mb-1 text-muted">Duration</label>
                        <input type="text"
                               class="form-control border rounded-2"
                               id="duration"
                               name="duration"
                               value="{{ request('duration') }}"
                               placeholder="Select Range"
                               autocomplete="off">
                    </div>

                    <!-- Status -->
                    <div class="col-md-2">
                        <label for="status" class="form-label mb-1 text-muted">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">All</option>
                            <option value="open" {{ request('status') == 'open' ? 'selected' : '' }}>Open</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="resolved" {{ request('status') == 'resolved' ? 'selected' : '' }}>Resolved</option>
                            <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Closed</option>
                        </select>
                    </div>

                    <!-- Button -->
                    <div class="col-md-2 d-flex">
                        <button type="submit" class="btn btn-outline-primary w-100 me-2">
                            <i class="fas fa-filter me-1"></i> Filter
                        </button>
                        <a href="{{ route('tickets.index') }}" class="btn btn-outline-secondary w-100">
                            <i class="fas fa-redo me-1"></i> Reset
                        </a>
                    </div>

                </div>
            </form>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-3">
        <div class="col-md-2 col-6 mb-2">
            <div class="card text-center bg-light border">
                <div class="card-body p-3">
                    <h6 class="text-muted mb-1">Total</h6>
                    <h5 class="mb-0 fw-semibold">{{ $tickets->count() }}</h5>
                </div>
            </div>
        </div>

        <div class="col-md-2 col-6 mb-2">
            <div class="card text-center bg-soft-success text-dark">
                <div class="card-body p-3">
                    <h6 class="mb-1">Open</h6>
                    <h5 class="mb-0 fw-semibold">{{ $tickets->where('status', 'open')->count() }}</h5>
                </div>
            </div>
        </div>

        <div class="col-md-2 col-6 mb-2">
            <div class="card text-center bg-soft-warning text-dark">
                <div class="card-body p-3">
                    <h6 class="mb-1">Pending</h6>
                    <h5 class="mb-0 fw-semibold">{{ $tickets->where('status', 'pending')->count() }}</h5>
                </div>
            </div>
        </div>

        <div class="col-md-2 col-6 mb-2">
            <div class="card text-center bg-soft-primary text-dark">
                <div class="card-body p-3">
                    <h6 class="mb-1">Resolved</h6>
                    <h5 class="mb-0 fw-semibold">{{ $tickets->where('status', 'resolved')->count() }}</h5>
                </div>
            </div>
        </div>

        <div class="col-md-2 col-6 mb-2">
            <div class="card text-center bg-soft-danger text-dark">
                <div class="card-body p-3">
                    <h6 class="mb-1">Closed</h6>
                    <h5 class="mb-0 fw-semibold">{{ $tickets->where('status', 'closed')->count() }}</h5>
                </div>
            </div>
        </div>
    </div>

    &nbsp;

    <!-- Top row: create + bulk action -->
    <div class="d-flex mb-2 justify-content-between align-items-center">
        <!-- Left side: Create Ticket -->
        <a href="{{ route('tickets.create') }}" class="btn btn-primary">Create Ticket</a>

        <!-- Right side: Bulk action (hidden by default) -->
        <div class="d-flex" id="bulk-action-div" style="display:none;">
            <select id="bulk-action" class="form-select w-auto me-2">
                <option value="">No Action</option>
                <option value="change_status">Change Status</option>
                <option value="delete">Delete</option>
            </select>
            <button id="apply-bulk" class="btn btn-primary">Apply</button>
        </div>
    </div>

    &nbsp;

    <!-- Flash Message -->
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <!-- Table -->
    <div class="card shadow-sm">
        <div class="card-body">
            <table class="table table-bordered table-hover table-striped" id="tickets-table">
                <thead class="table-light">
                    <tr>
                        <th><input type="checkbox" id="select-all"></th> <!-- Select all -->
                        <th>Ticket#</th>
                        <th style="white-space: nowrap;">Ticket Subject</th>
                        <th style="white-space: nowrap;">Requester Name</th>
                        <th style="white-space: nowrap;">Requested On</th>
                        <th>Others</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tickets as $ticket)
                        <tr>
                            <td>
                                <input type="checkbox"
                                       class="ticket-checkbox"
                                       value="{{ $ticket->id }}">
                            </td>
                            <td>{{ $ticket->id }}</td>
                            <td style="white-space: nowrap;">{{ $ticket->subject }}</td>
                            <td style="white-space: nowrap;">{{ $ticket->requester_name }}</td>
                            <td style="white-space: nowrap;">{{ $ticket->agent->name ?? '—' }}</td>
                            <td>
                                @php
                                    $badge = match($ticket->priority) {
                                        'low' => 'secondary',
                                        'medium' => 'info',
                                        'high' => 'warning',
                                        'critical' => 'danger',
                                        default => 'light'
                                    };
                                @endphp
                                <span class="badge bg-{{ $badge }}">Priority: {{ ucfirst($ticket->priority) }}</span>
                            </td>
                            <td>
                                <select name="status"
                                        class="form-select form-select-sm change-status w-25"
                                        data-ticket-id="{{ $ticket->id }}"
                                        style="min-width:150px;">
                                    <option value="open" {{ $ticket->status == 'open' ? 'selected' : '' }}>Open</option>
                                    <option value="pending" {{ $ticket->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="resolved" {{ $ticket->status == 'resolved' ? 'selected' : '' }}>Resolved</option>
                                    <option value="closed" {{ $ticket->status == 'closed' ? 'selected' : '' }}>Closed</option>
                                </select>
                            </td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light" type="button" data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('tickets.show', $ticket->id) }}">
                                                <i class="fas fa-eye me-2"></i>View
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('tickets.edit', $ticket->id) }}">
                                                <i class="fas fa-edit me-2"></i>Edit
                                            </a>
                                        </li>
                                        <li>
                                            <form action="{{ route('tickets.destroy', $ticket->id) }}"
                                                  method="POST"
                                                  onsubmit="return confirm('Are you sure?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item text-danger">
                                                    <i class="fas fa-trash me-2"></i>Delete
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>

            </table>
        </div>
    </div>
</div>
@endsection

@push('js')
<!-- Bootstrap Select CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta2/dist/css/bootstrap-select.min.css">

<!-- Font Awesome (for icons) -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">

<!-- Bootstrap Select JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta2/dist/js/bootstrap-select.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/moment@2.29.1/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

<!-- DataTables buttons -->
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.3.0-beta.1/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.3.0-beta.1/vfs_fonts.js"></script>

<script>
    $(document).ready(function() {
        $('.select-picker').selectpicker();
    });
</script>

<script>
    $(document).ready(function () {
        // init datatable
        let ticketsDatatable = $('#tickets-table').DataTable({
            dom: 'Bfrtip',
            buttons: [
                {
                    extend: 'copy',
                    exportOptions: {
                        columns: ':not(:first-child):not(:last-child)'
                    }
                },
                {
                    extend: 'csv',
                    exportOptions: {
                        columns: ':not(:first-child):not(:last-child)'
                    }
                },
                {
                    extend: 'excel',
                    exportOptions: {
                        columns: ':not(:first-child):not(:last-child)'
                    }
                },
                {
                    extend: 'pdf',
                    exportOptions: {
                        columns: ':not(:first-child):not(:last-child)'
                    }
                },
                {
                    extend: 'print',
                    exportOptions: {
                        columns: ':not(:first-child):not(:last-child)'
                    }
                }
            ],
            pageLength: 10,
            lengthMenu: [10, 25, 50, 100],
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Start typing to search..."
            },
            columnDefs: [
                { targets: [0, 7], searchable: false }, // disable search on checkbox & Actions
                { targets: '_all', searchable: true }
            ]
        });

        // add bottom bulk delete button near pagination/info
        let paginate = $('#tickets-table_wrapper .dataTables_paginate');
        $('<button id="bulk-delete-bottom" class="btn btn-danger btn-sm me-2" style="display:none;">Delete Selected</button>')
            .insertBefore(paginate);

        // Status change handler
        $('#tickets-table').on('change', '.change-status', function () {
            var ticketId = $(this).data('ticket-id');
            var status = $(this).val();
            var token = '{{ csrf_token() }}';

            $.ajax({
                url: '{{ route("tickets.change-status") }}',
                method: 'POST',
                data: {
                    _token: token,
                    ticketId: ticketId,
                    status: status
                },
                success: function (response) {
                    if (response.status === 'success') {
                        alert('Status updated successfully!');
                    }
                },
                error: function () {
                    alert('Error updating status');
                }
            });
        });
    });
</script>

<script>
    // daterange
    $(function () {
        const predefinedRanges = {
            'Today': [moment(), moment()],
            'Last 30 Days': [moment().subtract(29, 'days'), moment()],
            'This Month': [moment().startOf('month'), moment().endOf('month')],
            'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
            'Last 90 Days': [moment().subtract(89, 'days'), moment()],
            'Last 6 Months': [moment().subtract(6, 'months').startOf('month'), moment()],
            'Last 1 Year': [moment().subtract(1, 'year').startOf('month'), moment()],
            'Custom Range': []
        };

        $('#duration').daterangepicker({
            autoUpdateInput: false,
            showDropdowns: true,
            opens: 'left',
            locale: {
                format: 'YYYY-MM-DD',
                cancelLabel: 'Clear'
            },
            ranges: predefinedRanges
        });

        $('#duration').on('apply.daterangepicker', function (ev, picker) {
            if (picker.chosenLabel === 'Custom Range') {
                $(this).val(picker.startDate.format('YYYY-MM-DD') + ' to ' + picker.endDate.format('YYYY-MM-DD'));
            } else {
                $(this).val(picker.chosenLabel);
            }
        });

        $('#duration').on('cancel.daterangepicker', function () {
            $(this).val('');
        });
    });
</script>

<script>
    $(document).ready(function() {
        function toggleBulkUi() {
            const anyChecked = $('.ticket-checkbox:checked').length > 0;

            if (anyChecked) {
                $('#bulk-action-div').show();
                $('#bulk-delete-bottom').show();
            } else {
                $('#bulk-action-div').hide();
                $('#bulk-delete-bottom').hide();
                $('#bulk-action').val('');
            }
        }

        // checkbox change
        $('#tickets-table').on('change', '.ticket-checkbox', toggleBulkUi);

        // select all
        $('#select-all').on('click', function() {
            $('.ticket-checkbox').prop('checked', this.checked);
            toggleBulkUi();
        });

        // apply bulk action (includes delete)
        $('#apply-bulk').on('click', function() {
            var action = $('#bulk-action').val();
            var selected = $('.ticket-checkbox:checked').map(function() {
                return $(this).val();
            }).get();

            if (!action) { alert('Please select an action'); return; }
            if (selected.length === 0) { alert('Please select at least one ticket'); return; }
            if (action === 'delete' && !confirm('Are you sure you want to delete selected tickets?')) return;

            $.ajax({
                url: '{{ route("tickets.bulk-action") }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    action: action,
                    tickets: selected
                },
                success: function(response) {
                    if (response.status === 'success') location.reload();
                },
                error: function() {
                    alert('Error performing action');
                }
            });
        });

        // bottom delete button – reuse same bulk delete flow
        $(document).on('click', '#bulk-delete-bottom', function () {
            $('#bulk-action').val('delete');
            $('#apply-bulk').trigger('click');
        });
    });
</script>
@endpush
