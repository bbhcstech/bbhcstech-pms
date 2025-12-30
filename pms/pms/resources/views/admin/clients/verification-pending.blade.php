@extends('admin.layout.app')

@section('content')
<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="btn-sm ms-5">Clients</h4>
        <br>
        @if(session('success'))
            <div class="alert alert-success" style="background-color: #28a745; color: white; border-color: #28a745;">
                {{ session('success') }}
            </div>
        @endif  
        <br>
    </div>

    <!-- Filters -->
    <div class="container-fluid mt-4">
        <div class="card shadow-sm border-0 mb-4">
            <form method="GET" action="{{ route('clients.index') }}">
                <div class="card-body">
                    <div class="row g-3">
                        <!-- Date Range -->
                        <div class="col-md-3">
                            <label for="datatableRange" class="form-label mb-1 text-muted">Duration</label>
                            <input type="text"
                                   class="form-control border rounded-2"
                                   id="duration"
                                   name="duration"
                                   value="{{ request('duration') }}"
                                   placeholder="Select Range"
                                   autocomplete="off">
                        </div>

                        <!-- Clients -->
                        <div class="col-md-3">
                            <label for="name" class="form-label mb-1 text-muted">Clients</label>
                            <select class="form-select select2" id="name" name="name" style="min-height: 45px; font-size: 15px;">
                                <option value="">All</option>
                                @foreach($clients as $c)
                                    <option value="{{ $c->id }}" {{ request('name') == $c->id ? 'selected' : '' }}>
                                        {{ $c->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Buttons -->
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">Filter</button>
                            <a href="{{ route('employees.index') }}" class="btn btn-secondary">Reset</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Client & Bulk Actions -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <!-- Add Client -->
        <a href="{{ route('clients.create') }}" class="btn btn-primary btn-sm ms-5">
            <i class="bi bi-plus-circle"></i> &nbsp; Add Client
        </a>

        <!-- Bulk Action -->
        <div class="btn-group" role="group" aria-label="Basic example">
            <div class="d-flex align-items-center gap-2">
                <!-- Action Dropdown -->
                <select class="form-select" id="quick-action-type" style="min-width: 180px;">
                    <option value="">No Action</option>
                    <option value="change-status">Change Status</option>
                    <option value="delete">Delete</option>
                </select>

                <!-- Status Dropdown (Hidden by default) -->
                <select class="form-select d-none" id="quick-action-status" style="min-width: 150px;">
                    <option value="Active">Active</option>
                    <option value="Inactive">Inactive</option>
                </select>

                <!-- Apply Button -->
                <button type="button" class="btn btn-primary" id="quick-action-apply" >Apply</button>
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

    <!-- Client Table -->
    <div class="container-fluid mt-4">
        <div class="card-body table-responsive">
            <table id="clientsTable" class="table table-bordered table-hover table-striped align-middle">
                <thead class="table-dark">
                    <tr>
                        <th><input type="checkbox" id="selectAll"></th>
                        <th>ID</th>
                        <th style="white-space: nowrap;">Name</th>
                        <th>Email</th>
                        
                        <th>Status</th>
                        <th style="white-space: nowrap;">Created</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($clients as $client)
                        <tr>
                            <td><input type="checkbox" class="client-checkbox" value="{{ $client->id }}"></td>
                            <td>{{ $client->id }}</td>
                            <td style="white-space: nowrap;">
                                <strong>{{ $client->name }}</strong><br>
                                @if($client->salutation)
                                    <small>{{ $client->salutation }} {{ $client->name }}</small>
                                @endif
                            </td>
                            <td>{{ $client->email }}</td>
                           
                            <td><span class="badge bg-success">{{ $client->status ?? '—' }}</span></td>
                            <td style="white-space: nowrap;">{{ \Carbon\Carbon::parse($client->created_at)->format('d-m-Y') }}</td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light" type="button" id="dropdownMenuButton{{ $client->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton{{ $client->id }}">
                                        <li><a class="dropdown-item" href="{{ route('clients.show', $client->id) }}"><i class="bi bi-eye me-2"></i> View</a></li>
                                        <li><a class="dropdown-item" href="{{ route('clients.edit', $client->id) }}"><i class="bi bi-pencil-square me-2"></i> Edit</a></li>
                                        <li>
                                            <form action="{{ route('clients.destroy', $client->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this client?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item text-danger"><i class="bi bi-trash me-2"></i> Delete</button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                     
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

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

<meta name="csrf-token" content="{{ csrf_token() }}">

<script>
$(function() {
    // Initialize Select2
    $('.select2').select2({ width: '100%' });

    // Initialize DataTable
    $('#clientsTable').DataTable({
        dom: 'Bfrtip',
        buttons: ['copy','csv','excel','pdf','print'],
        responsive: true,
        pageLength: 10,
        lengthMenu: [10,25,50,100],
        language: { search: "_INPUT_", searchPlaceholder: "Start typing to search..." }
    });

    // CSRF setup for AJAX
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

    // Toggle Apply button
    function toggleApplyButton() {
        let anyChecked = $('.client-checkbox:checked').length > 0;
        let actionSelected = $('#quick-action-type').val() !== '';
        $('#quick-action-apply').prop('disabled', !(anyChecked && actionSelected));
    }

    // Select all checkbox
    $('#selectAll').on('change', function() {
        $('.client-checkbox').prop('checked', this.checked);
        toggleApplyButton();
    });

    // Individual checkboxes & dropdown
    $('.client-checkbox, #quick-action-type, #quick-action-status').on('change', toggleApplyButton);

    // Show/hide status dropdown
    $('#quick-action-type').on('change', function() {
        if ($(this).val() === 'change-status') {
            $('#quick-action-status').removeClass('d-none');
        } else {
            $('#quick-action-status').addClass('d-none');
        }
        toggleApplyButton();
    });

    // Bulk action AJAX
    $('#quick-action-apply').on('click', function(e) {
        e.preventDefault();
        let ids = $('.client-checkbox:checked').map(function() { return $(this).val(); }).get();
        let action = $('#quick-action-type').val();
        let status = $('#quick-action-status').val();

        if (!ids.length) return alert('Please select at least one client.');
        if (action === 'delete' && !confirm('Are you sure you want to delete selected clients?')) return;

        $.post('{{ route("clients.bulkAction") }}', { client_ids: ids, action: action, status: status }, function() {
            location.reload();
        }).fail(function() { alert('Something went wrong'); });
    });

    // Date range picker
    const predefinedRanges = {
        'Today': [moment(), moment()],
        'Last 30 Days': [moment().subtract(29, 'days'), moment()],
        'This Month': [moment().startOf('month'), moment().endOf('month')],
        'Last Month': [moment().subtract(1,'month').startOf('month'), moment().subtract(1,'month').endOf('month')],
        'Last 90 Days': [moment().subtract(89,'days'), moment()],
        'Last 6 Months': [moment().subtract(6,'months').startOf('month'), moment()],
        'Last 1 Year': [moment().subtract(1,'year').startOf('month'), moment()],
        'Custom Range': []
    };

    $('#duration').daterangepicker({
        autoUpdateInput: false,
        showDropdowns: true,
        opens: 'left',
        locale: { format: 'YYYY-MM-DD', cancelLabel: 'Clear' },
        ranges: predefinedRanges
    });

    $('#duration').on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.chosenLabel === 'Custom Range' ? picker.startDate.format('YYYY-MM-DD') + ' to ' + picker.endDate.format('YYYY-MM-DD') : picker.chosenLabel);
    });

    $('#duration').on('cancel.daterangepicker', function() { $(this).val(''); });
});
</script>
@endpush

@endsection
