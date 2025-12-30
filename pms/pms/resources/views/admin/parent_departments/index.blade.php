@extends('admin.layout.app')

@section('title', 'Parent Departments')

@section('content')
<main class="main py-4">
    <div class="container-fluid px-4">
        <!-- Header Section -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1 text-dark">Departments</h4>
                <p class="text-muted mb-0">Manage and organize your departments</p>
            </div>
            <div class="header-actions">
                <a href="{{ route('parent-departments.create') }}" class="btn btn-primary d-flex align-items-center">
                    <i class="bi bi-plus-circle me-2"></i>
                    Add Department
                </a>
            </div>
        </div>

        <!-- Flash Messages -->
        @if($errors->any() || session('error') || session('success'))
        <div class="row mb-4">
            <div class="col-12">
                @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                        <div>
                            <strong>Please fix the following errors:</strong>
                            <ul class="mb-0 ps-3">
                                @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif

                @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-x-circle-fill me-2 fs-5"></i>
                        <div>{{ session('error') }}</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif

                @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                        <div>{{ session('success') }}</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- DataTable Card -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 d-flex align-items-center">
                        <i class="bi bi-building me-2 text-primary"></i>
                        Department List
                    </h5>
                    <div class="d-flex align-items-center">
                        <div class="form-check me-3">
                            <input class="form-check-input" type="checkbox" id="select-all">
                            <label class="form-check-label small text-muted" for="select-all">
                                Select All
                            </label>
                        </div>
                        <button id="bulk-delete-btn" class="btn btn-danger btn-sm d-flex align-items-center">
                            <i class="bi bi-trash me-1"></i>
                            Delete Selected
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="parentTable" class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="50" class="text-center">
                                    <input type="checkbox" id="select-all-header" class="form-check-input">
                                </th>
                                <th width="60">#</th>
                                <th>Code</th>
                                <th>Department Name</th>
                                <th width="200" class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($departments as $index => $dpt)
                            <tr>
                                <td class="text-center">
                                    <input type="checkbox" name="bulk_ids[]" class="row-checkbox form-check-input" value="{{ $dpt->id }}">
                                </td>
                                <td class="text-muted">{{ $index + 1 }}</td>
                                <td>
                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-3 py-2 rounded-pill">
                                        {{ $dpt->dpt_code }}
                                    </span>
                                </td>
                                <td class="fw-medium">{{ $dpt->dpt_name }}</td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('parent-departments.edit', $dpt) }}"
                                           class="btn btn-outline-warning d-flex align-items-center"
                                           data-bs-toggle="tooltip"
                                           title="Edit Department">
                                            <i class="bi bi-pencil-square me-1"></i>
                                            Edit
                                        </a>
                                        <form action="{{ route('parent-departments.destroy', $dpt) }}"
                                              method="POST"
                                              class="d-inline"
                                              onsubmit="return confirmDelete(this);">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="btn btn-outline-danger d-flex align-items-center"
                                                    data-bs-toggle="tooltip"
                                                    title="Delete Department">
                                                <i class="bi bi-trash me-1"></i>
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <div class="empty-state">
                                        <i class="bi bi-building text-muted display-6 mb-3"></i>
                                        <h5 class="text-muted">No Departments Found</h5>
                                        <p class="text-muted small mb-4">Get started by creating your first department</p>
                                        <a href="{{ route('parent-departments.create') }}" class="btn btn-primary">
                                            <i class="bi bi-plus-circle me-1"></i>
                                            Add Department
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($departments->count())
            <div class="card-footer bg-white border-top py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="small text-muted">
                        Showing {{ $departments->count() }} department(s)
                    </div>
                    <div id="bulk-delete-wrapper">
                        <!-- Bulk delete button is already in header -->
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</main>
@endsection

@section('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

<style>
    /* DataTable Custom Styling */
    .dataTables_wrapper {
        padding: 20px !important;
    }

    #parentTable {
        border-collapse: separate !important;
        border-spacing: 0 !important;
    }

    #parentTable thead th {
        background-color: #f8f9fa;
        border-bottom: 2px solid #dee2e6;
        padding: 12px 15px;
        font-weight: 600;
        color: #495057;
    }

    #parentTable tbody td {
        padding: 15px;
        vertical-align: middle;
        border-top: 1px solid #f0f0f0;
    }

    #parentTable tbody tr:hover {
        background-color: rgba(13, 110, 253, 0.02);
    }

    /* DataTable Buttons Styling */
    .dt-buttons {
        margin-bottom: 15px !important;
    }

    .dt-buttons .dt-button {
        margin-right: 8px;
        border: 1px solid #dee2e6 !important;
        background: white !important;
        padding: 8px 16px !important;
        border-radius: 6px !important;
        font-size: 14px !important;
        color: #495057 !important;
        transition: all 0.2s !important;
    }

    .dt-buttons .dt-button:hover {
        background-color: #f8f9fa !important;
        border-color: #0d6efd !important;
        color: #0d6efd !important;
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    /* Header Actions */
    .header-actions {
        display: flex;
        gap: 0.75rem;
        align-items: center;
        position: relative;
        z-index: 5;
    }

    /* Custom Badge */
    .badge {
        font-weight: 500;
        letter-spacing: 0.3px;
    }

    /* Empty State */
    .empty-state {
        padding: 3rem 1rem;
    }

    .empty-state i {
        opacity: 0.5;
    }

    /* Bulk Delete Button */
    #bulk-delete-btn {
        transition: all 0.2s;
    }

    #bulk-delete-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(220, 53, 69, 0.2);
    }

    /* Card Styling */
    .card {
        border-radius: 10px;
        overflow: hidden;
    }

    .card-header {
        border-bottom: 1px solid rgba(0,0,0,0.08);
    }

    /* Table Checkbox */
    .form-check-input:checked {
        background-color: #0d6efd;
        border-color: #0d6efd;
    }

    /* Responsive Adjustments */
    @media (max-width: 768px) {
        .header-actions {
            flex-direction: column;
            align-items: flex-end;
        }

        .card-header .d-flex {
            flex-direction: column;
            gap: 10px;
            align-items: flex-start !important;
        }

        .dt-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
        }

        .dt-buttons .dt-button {
            margin-right: 0;
            margin-bottom: 5px;
        }

        .btn-group {
            flex-wrap: wrap;
            justify-content: center;
        }

        .btn-group .btn {
            margin-bottom: 5px;
        }
    }
</style>
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
$(document).ready(function () {
    // Initialize Bootstrap tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initialize DataTable
    const table = $('#parentTable').DataTable({
        dom: '<"row mb-3"<"col-md-6"B><"col-md-6"f>>rt<"row mt-3"<"col-md-6"i><"col-md-6"p>>',
        paging: true,
        pageLength: 10,
        lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]],
        ordering: true,
        searching: true,
        info: true,
        autoWidth: false,
        buttons: [
            {
                extend: 'copyHtml5',
                text: '<i class="bi bi-files"></i> Copy',
                className: 'btn btn-sm btn-outline-secondary',
                exportOptions: { columns: [1,2,3] }
            },
            {
                extend: 'csvHtml5',
                text: '<i class="bi bi-file-earmark-spreadsheet"></i> CSV',
                className: 'btn btn-sm btn-outline-secondary',
                exportOptions: { columns: [1,2,3] }
            },
            {
                extend: 'excelHtml5',
                text: '<i class="bi bi-file-excel"></i> Excel',
                className: 'btn btn-sm btn-outline-secondary',
                exportOptions: { columns: [1,2,3] }
            },
            {
                extend: 'pdfHtml5',
                text: '<i class="bi bi-file-pdf"></i> PDF',
                className: 'btn btn-sm btn-outline-secondary',
                exportOptions: { columns: [1,2,3] }
            },
            {
                extend: 'print',
                text: '<i class="bi bi-printer"></i> Print',
                className: 'btn btn-sm btn-outline-secondary',
                exportOptions: { columns: [1,2,3] }
            }
        ],
        columnDefs: [
            { orderable: false, targets: 0 },
            { orderable: false, targets: 4 },
            { searchable: false, targets: 0 },
            { searchable: false, targets: 4 }
        ],
        language: {
            search: "Search departments:",
            lengthMenu: "Show _MENU_ departments",
            info: "Showing _START_ to _END_ of _TOTAL_ departments",
            infoEmpty: "No departments found",
            infoFiltered: "(filtered from _MAX_ total departments)",
            paginate: {
                first: "First",
                last: "Last",
                next: "Next",
                previous: "Previous"
            }
        },
        drawCallback: function(settings) {
            // Reinitialize tooltips after table redraw
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        }
    });

    // Select all functionality
    $('#select-all, #select-all-header').on('change', function() {
        const isChecked = $(this).is(':checked');
        $('.row-checkbox').prop('checked', isChecked);
        $('#select-all').prop('checked', isChecked);
        $('#select-all-header').prop('checked', isChecked);

        // Visual feedback
        if (isChecked) {
            $('.row-checkbox').closest('tr').addClass('table-primary');
        } else {
            $('.row-checkbox').closest('tr').removeClass('table-primary');
        }
    });

    // Individual checkbox change
    $(document).on('change', '.row-checkbox', function() {
        const allChecked = $('.row-checkbox').length === $('.row-checkbox:checked').length;
        $('#select-all').prop('checked', allChecked);
        $('#select-all-header').prop('checked', allChecked);

        // Visual feedback
        if ($(this).is(':checked')) {
            $(this).closest('tr').addClass('table-primary');
        } else {
            $(this).closest('tr').removeClass('table-primary');
        }
    });

    // Delete confirmation function
    window.confirmDelete = function(form) {
        return confirm('Are you sure you want to delete this department? This action cannot be undone.');
    };

    // Bulk delete functionality
    $('#bulk-delete-btn').on('click', function() {
        const ids = $('.row-checkbox:checked').map(function() {
            return $(this).val();
        }).get();

        if (!ids.length) {
            showAlert('warning', 'Please select at least one department to delete.');
            return;
        }

        if (!confirm('Are you sure you want to delete the selected department(s)? This action cannot be undone.')) {
            return;
        }

        const deleteBtn = $(this);
        const originalText = deleteBtn.html();

        // Show loading state
        deleteBtn.html('<i class="bi bi-hourglass-split me-1"></i> Deleting...');
        deleteBtn.prop('disabled', true);

        $.ajax({
            url: "{{ route('parent-departments.bulk-delete') }}",
            method: 'POST',
            data: {
                bulk_ids: ids,
                _token: '{{ csrf_token() }}'
            },
            success: function(res) {
                if (res.status === 'success') {
                    // Remove deleted rows
                    if (res.deleted_ids && res.deleted_ids.length) {
                        res.deleted_ids.forEach(function(id) {
                            table.row($('.row-checkbox[value="' + id + '"]').closest('tr')).remove().draw();
                        });
                    }

                    // Reset checkboxes
                    $('#select-all, #select-all-header').prop('checked', false);

                    // Show success message
                    showAlert('success', res.message);

                    // If all rows were deleted, show empty state
                    if (table.rows().count() === 0) {
                        table.draw();
                    }
                } else {
                    showAlert('danger', res.message || 'Something went wrong.');
                }
            },
            error: function(xhr) {
                let msg = 'Something went wrong. Please try again.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                showAlert('danger', msg);
            },
            complete: function() {
                // Reset button state
                deleteBtn.html(originalText);
                deleteBtn.prop('disabled', false);
            }
        });
    });

    // Alert notification function
    function showAlert(type, message) {
        // Remove any existing alerts
        $('.alert-dismissible.position-fixed').remove();

        // Create new alert
        const alert = $(`
            <div class="alert alert-${type} alert-dismissible position-fixed top-3 end-3 shadow-lg fade show" role="alert" style="z-index: 1060; max-width: 400px;">
                <div class="d-flex align-items-center">
                    <i class="bi ${type === 'success' ? 'bi-check-circle-fill' : type === 'warning' ? 'bi-exclamation-triangle-fill' : 'bi-x-circle-fill'} me-2 fs-5"></i>
                    <div>${message}</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `);

        $('body').append(alert);

        // Auto remove after 5 seconds
        setTimeout(() => {
            alert.alert('close');
        }, 5000);
    }

    // Add some visual feedback for table interactions
    $(document).on('mouseenter', '#parentTable tbody tr', function() {
        $(this).css('transform', 'translateY(-1px)');
        $(this).css('box-shadow', '0 2px 4px rgba(0,0,0,0.05)');
    }).on('mouseleave', '#parentTable tbody tr', function() {
        $(this).css('transform', 'translateY(0)');
        $(this).css('box-shadow', 'none');
    });
});
</script>
@endsection
