{{-- resources/views/admin/clients/index.blade.php --}}
@extends('admin.layout.app')

@section('title', 'Clients')

@section('content')
<div class="container-fluid mt-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="btn-sm ms-5">Clients</h4>

        @if(session('success'))
            <div class="alert alert-success" style="background-color: #28a745; color: white; border-color: #28a745;">
                {{ session('success') }}
            </div>
        @endif
    </div>

    {{-- Filters Card --}}
    <div class="container-fluid mt-0">
        <div class="card shadow-sm border-0 mb-4">
            <form method="GET" action="{{ route('clients.index') }}">
                <div class="card-body">
                    <div class="row g-3">
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

                        <div class="col-md-3">
                            <label for="name" class="form-label mb-1 text-muted">Clients</label>
                            <select class="form-select select2" id="name" name="name" style="min-height: 45px; font-size: 15px;">
                                <option value="">All</option>
                                @foreach($clients as $c)
                                    <option value="{{ $c->id }}" {{ request('name') == $c->id ? 'selected' : '' }}>
                                        {{ $c->client_uid ?? $c->id }} - {{ $c->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">Filter</button>
                            <a href="{{ route('clients.index') }}" class="btn btn-secondary">Reset</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Actions top --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="{{ route('clients.create') }}" class="btn btn-primary btn-sm ms-5">
            <i class="bi bi-plus-circle"></i> &nbsp; Add Client
        </a>

        <div class="btn-group" role="group" aria-label="Basic example">
            <div class="d-flex align-items-center gap-2">
                <select class="form-select" id="quick-action-type" style="min-width: 180px;">
                    <option value="">No Action</option>
                    <option value="change-status">Change Status</option>
                    <option value="delete">Delete</option>
                </select>

                <select class="form-select d-none" id="quick-action-status" style="min-width: 150px;">
                    <option value="Active">Active</option>
                    <option value="Inactive">Inactive</option>
                </select>

                <button class="btn btn-primary" id="quick-action-apply" disabled>Apply</button>
            </div>

            &nbsp;

            <a href="{{ route('clients.index') }}" class="btn btn-secondary f-14" data-toggle="tooltip" data-original-title="Table View">
                <i class="side-icon bi bi-list-ul"></i>
            </a>

            <a href="{{ route('clients.pending') }}" class="btn btn-secondary f-14 show-unverified btn-active" data-toggle="tooltip" data-original-title="Account verification pending">
                <i class="side-icon bi bi-person-x"></i>
            </a>
        </div>
    </div>

    {{-- Table --}}
    <div class="container-fluid mt-2">
        <div class="card-body table-responsive">
            <table id="clientsTable" class="table table-bordered table-hover table-striped align-middle">
                <thead class="table-dark">
                    <tr>
                        <th style="width:40px;"><input type="checkbox" id="selectAll"></th>
                        <th>Client ID</th>
                        <th style="white-space: nowrap;">Name</th>
                        <th>Email</th>
                        <th style="white-space: nowrap;">Company</th>
                        <th>Status</th>
                        <th style="white-space: nowrap;">Created</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($clients as $client)
                        <tr data-id="{{ $client->id }}">
                            <td><input type="checkbox" class="client-checkbox" value="{{ $client->id }}"></td>
                            <td>{{ $client->client_uid ?? '—' }}</td>
                            <td style="white-space: nowrap;">
                                <strong>{{ $client->name }}</strong><br>
                                @if($client->salutation)
                                    <small>{{ $client->salutation }} {{ $client->name }}</small>
                                @endif
                            </td>
                            <td>{{ $client->email }}</td>
                            <td style="white-space: nowrap;">{{ $client->company_name ?? '—' }}</td>
                            <td>
                                <span class="badge {{ ($client->status ?? '') === 'Active' ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $client->status ?? '—' }}
                                </span>
                            </td>
                            <td style="white-space: nowrap;">
                                {{ $client->created_at ? \Carbon\Carbon::parse($client->created_at)->format('d-m-Y') : '—' }}
                            </td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light" type="button" id="dropdownMenuButton{{ $client->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>

                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton{{ $client->id }}">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('clients.show', $client->id) }}">
                                                <i class="bi bi-eye me-2"></i> View
                                            </a>
                                        </li>

                                        <li>
                                            <a class="dropdown-item" href="{{ route('clients.edit', $client->id) }}">
                                                <i class="bi bi-pencil-square me-2"></i> Edit
                                            </a>
                                        </li>

                                        <li>
                                            <form action="{{ route('clients.destroy', $client->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this client?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item text-danger">
                                                    <i class="bi bi-trash me-2"></i> Delete
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    {{-- no @empty row here; DataTables will show "No clients found." --}}
                </tbody>
            </table>

            {{-- Bulk delete button under table --}}
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    <button id="bulkDeleteBtn" class="btn btn-danger btn-sm" disabled>
                        <i class="bi bi-trash me-1"></i> Bulk Delete
                    </button>
                </div>
            </div>

            {{-- Laravel pagination control --}}
            <div class="mt-2">
                {{ $clients->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
    {{-- CSS/JS dependencies --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.1/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta2/dist/css/bootstrap-select.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta2/dist/js/bootstrap-select.min.js"></script>

    <script>
    $(document).ready(function () {
        // DataTable
        $('#clientsTable').DataTable({
            dom: 'Bfrtip',
            buttons: [
                { extend: 'copy',  exportOptions: { columns: [1,2,3,4,5] } },
                { extend: 'csv',   exportOptions: { columns: [1,2,3,4,5] } },
                { extend: 'excel', exportOptions: { columns: [1,2,3,4,5] } },
                { extend: 'pdf',   exportOptions: { columns: [1,2,3,4,5] } },
                { extend: 'print', exportOptions: { columns: [1,2,3,4,5] } }
            ],
            responsive: true,
            pageLength: 10,
            lengthMenu: [10,25,50,100],
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Start typing to search...",
                emptyTable: "No clients found."
            },
            drawCallback: function() {
                // keep bulk button state consistent after redraw
                updateBulkButtonsState();
            }
        });

        // daterangepicker for Duration filter
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

        // select2 init
        $('.select2').select2({ width: '100%' });

        // Select all checkbox
        $('#selectAll').on('change', function () {
            $('.client-checkbox').prop('checked', this.checked).trigger('change');
            updateBulkButtonsState();
        });

        // Individual checkbox change
        $(document).on('change', '.client-checkbox', function () {
            $('#selectAll').prop(
                'checked',
                $('.client-checkbox').length === $('.client-checkbox:checked').length
            );
            updateBulkButtonsState();
        });

        // Quick action controls toggle
        $('#quick-action-type').on('change', function () {
            if ($(this).val() === 'change-status') {
                $('#quick-action-status').removeClass('d-none');
            } else {
                $('#quick-action-status').addClass('d-none');
            }
            toggleQuickApply();
        });

        $('#quick-action-status').on('change', toggleQuickApply);
        $(document).on('change', '.client-checkbox', toggleQuickApply);

        function toggleQuickApply() {
            const anyChecked = $('.client-checkbox:checked').length > 0;
            const actionSelected = $('#quick-action-type').val() !== '';
            $('#quick-action-apply').prop('disabled', !(anyChecked && actionSelected));
        }

        // Apply quick action (status or delete)
        $('#quick-action-apply').on('click', function () {
            const ids = $('.client-checkbox:checked').map(function () { return $(this).val(); }).get();
            const action = $('#quick-action-type').val();
            const status = $('#quick-action-status').val();

            if (!action || ids.length === 0) {
                alert('Please select at least one client and an action.');
                return;
            }

            if (action === 'delete' && !confirm('Are you sure you want to delete selected clients?')) {
                return;
            }

            $.ajax({
                url: '{{ route("clients.bulkAction") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    client_ids: ids,
                    action: action,
                    status: status
                },
                success: function (res) {
                    if (res.success) {
                        if (action === 'delete') {
                            ids.forEach(function(id){
                                $("tr[data-id='" + id + "']").remove();
                            });
                        }
                        location.reload();
                    } else {
                        alert(res.message || 'Action failed');
                    }
                },
                error: function () {
                    alert('Something went wrong');
                }
            });
        });

        // Separate Bulk Delete button under table
        $('#bulkDeleteBtn').on('click', function () {
            const ids = $('.client-checkbox:checked').map(function () { return $(this).val(); }).get();

            if (ids.length === 0) {
                alert('Please select at least one client.');
                return;
            }

            if (!confirm('Are you sure you want to permanently delete the selected clients? This will also remove associated files and users.')) {
                return;
            }

            $.ajax({
                url: '{{ route("clients.bulk-delete") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    client_ids: ids
                },
                success: function (res) {
                    if (res.success) {
                        ids.forEach(function(id){
                            $("tr[data-id='" + id + "']").remove();
                        });

                        $('#selectAll').prop('checked', false);
                        updateBulkButtonsState();

                        alert(res.message || 'Clients deleted successfully.');
                    } else {
                        alert(res.message || 'Failed to delete clients.');
                    }
                },
                error: function (xhr) {
                    let msg = 'Something went wrong while deleting clients.';
                    if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                    alert(msg);
                }
            });
        });

        // Enable/disable bulk buttons
        function updateBulkButtonsState() {
            const anyChecked = $('.client-checkbox:checked').length > 0;
            $('#bulkDeleteBtn').prop('disabled', !anyChecked);
            toggleQuickApply();
        }

        // initial state
        updateBulkButtonsState();
    });
    </script>
@endpush
