@extends('admin.layout.app')

@section('title', 'Parent Departments')

@section('content')
<main class="main py-3">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 fw-semibold mb-2">Departments</h1>
                <p class="text-muted mb-0">Manage and organize department structure</p>
            </div>
            <div>
                <a href="{{ route('parent-departments.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>
                    Add Department
                </a>
            </div>
        </div>

        <!-- Flash Messages -->
        @if($errors->any() || session('error') || session('success') || session('warning'))
        <div class="row mb-4">
            <div class="col-12">
                @if($errors->any())
                <div class="alert alert-danger border-0 shadow-sm rounded-sm" role="alert">
                    <div class="d-flex align-items-start">
                        <i class="bi bi-exclamation-circle-fill text-danger me-2 fs-5 mt-1"></i>
                        <div class="flex-grow-1">
                            <h6 class="alert-heading mb-2">Validation Errors</h6>
                            <ul class="mb-0 ps-3">
                                @foreach($errors->all() as $error)
                                <li class="small">{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                </div>
                @endif

                @if(session('error'))
                    @php
                        $isEmployeeError = str_contains(session('error'), 'tagged with employees');
                        $isSubDeptError = str_contains(session('error'), 'sub-departments');
                    @endphp

                    <div class="alert {{ $isEmployeeError ? 'alert-warning' : ($isSubDeptError ? 'alert-info' : 'alert-danger') }} border-0 shadow-sm rounded-sm" role="alert">
                        <div class="d-flex align-items-start">
                            @if($isEmployeeError)
                                <i class="bi bi-people-fill text-warning me-2 fs-5 mt-1"></i>
                            @elseif($isSubDeptError)
                                <i class="bi bi-diagram-2-fill text-info me-2 fs-5 mt-1"></i>
                            @else
                                <i class="bi bi-x-circle-fill text-danger me-2 fs-5 mt-1"></i>
                            @endif
                            <div class="flex-grow-1">
                                @if($isEmployeeError)
                                    <h6 class="alert-heading mb-1">Cannot Delete Department</h6>
                                    <p class="mb-2">{{ session('error') }}</p>
                                    <div class="bg-warning bg-opacity-10 p-2 rounded-sm small">
                                        <i class="bi bi-info-circle me-1"></i>
                                        Reassign employees before deletion
                                    </div>
                                @elseif($isSubDeptError)
                                    <h6 class="alert-heading mb-1">Contains Sub-Departments</h6>
                                    <p class="mb-2">{{ session('error') }}</p>
                                    <div class="bg-info bg-opacity-10 p-2 rounded-sm small">
                                        <i class="bi bi-info-circle me-1"></i>
                                        Remove or relocate sub-departments first
                                    </div>
                                @else
                                    <h6 class="alert-heading mb-1">Error</h6>
                                    <p class="mb-0">{{ session('error') }}</p>
                                @endif
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    </div>
                @endif

                @if(session('warning'))
                <div class="alert alert-warning border-0 shadow-sm rounded-sm" role="alert">
                    <div class="d-flex align-items-start">
                        <i class="bi bi-exclamation-triangle-fill text-warning me-2 fs-5 mt-1"></i>
                        <div class="flex-grow-1">
                            <h6 class="alert-heading mb-1">Notice</h6>
                            <p class="mb-0">{{ session('warning') }}</p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                </div>
                @endif

                @if(session('success'))
                <div class="alert alert-success border-0 shadow-sm rounded-sm" role="alert">
                    <div class="d-flex align-items-start">
                        <i class="bi bi-check-circle-fill text-success me-2 fs-5 mt-1"></i>
                        <div class="flex-grow-1">
                            <h6 class="alert-heading mb-1">Success</h6>
                            <p class="mb-0">{{ session('success') }}</p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- DataTable Card -->
        <div class="card border rounded-sm">
            <div class="card-header bg-transparent border-bottom px-4 py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 d-flex align-items-center text-dark">
                        <i class="bi bi-building me-2"></i>
                        Department List
                    </h5>
                    <div class="d-flex align-items-center gap-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="select-all">
                            <label class="form-check-label small text-muted" for="select-all">
                                Select All
                            </label>
                        </div>
                        <button id="bulk-delete-btn" class="btn btn-outline-danger btn-sm d-flex align-items-center">
                            <i class="bi bi-trash me-1"></i>
                            Delete Selected
                        </button>
                    </div>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="parentTable" class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th width="50" class="ps-4">
                                    <input type="checkbox" id="select-all-header" class="form-check-input">
                                </th>
                                <th width="60">#</th>
                                <th>Department Code</th>
                                <th>Department Name</th>
                                <th width="200" class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($departments as $index => $dpt)
                            <tr>
                                <td class="ps-4">
                                    <input type="checkbox" name="bulk_ids[]" class="row-checkbox form-check-input" value="{{ $dpt->id }}">
                                </td>
                                <td class="text-muted">{{ $index + 1 }}</td>
                                <td>
                                    <span class="badge bg-light border text-dark px-3 py-1 rounded-sm">
                                        <i class="bi bi-hash me-1 small"></i>{{ $dpt->dpt_code }}
                                    </span>
                                </td>
                                <td class="fw-medium">{{ $dpt->dpt_name }}</td>
                                <td class="pe-4">
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="{{ route('parent-departments.edit', $dpt) }}"
                                           class="btn btn-outline-secondary btn-sm d-flex align-items-center"
                                           data-bs-toggle="tooltip"
                                           title="Edit Department">
                                            <i class="bi bi-pencil me-1"></i>
                                            Edit
                                        </a>

                                        <button type="button"
                                                class="btn btn-outline-danger btn-sm d-flex align-items-center delete-btn"
                                                data-bs-toggle="tooltip"
                                                title="Delete Department"
                                                data-id="{{ $dpt->id }}"
                                                data-name="{{ $dpt->dpt_name }}"
                                                data-code="{{ $dpt->dpt_code }}">
                                            <i class="bi bi-trash me-1"></i>
                                            Delete
                                        </button>

                                        <form action="{{ route('parent-departments.destroy', $dpt) }}"
                                              method="POST"
                                              class="d-none delete-form-{{ $dpt->id }}">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @if($departments->isEmpty())
                    <div class="text-center py-5">
                        <div class="empty-state">
                            <div class="mb-3">
                                <i class="bi bi-building text-muted" style="font-size: 3rem;"></i>
                            </div>
                            <h6 class="text-muted mb-2">No Departments Found</h6>
                            <p class="text-muted small mb-4">Get started by adding your first department</p>
                            <a href="{{ route('parent-departments.create') }}" class="btn btn-primary btn-sm">
                                <i class="bi bi-plus-circle me-1"></i>
                                Add Department
                            </a>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            @if($departments->count())
            <div class="card-footer bg-transparent border-top px-4 py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="small text-muted">
                        Total: {{ $departments->count() }} department(s)
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
    /* Professional Office Theme */
    :root {
        --border-color: #e0e0e0;
        --hover-bg: #f8f9fa;
        --card-shadow: 0 1px 3px rgba(0,0,0,0.08);
        --transition-speed: 0.15s;
    }

    body {
        background-color: #f9fafb;
    }

    .card {
        border: 1px solid var(--border-color);
        box-shadow: var(--card-shadow);
        border-radius: 4px;
    }

    .card-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid var(--border-color);
    }

    /* Table Styling */
    #parentTable {
        border-collapse: separate;
        border-spacing: 0;
        font-size: 0.875rem;
    }

    #parentTable thead th {
        background-color: #f8f9fa;
        border-bottom: 2px solid var(--border-color);
        padding: 12px 16px;
        font-weight: 600;
        color: #374151;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
    }

    #parentTable tbody td {
        padding: 14px 16px;
        border-top: 1px solid var(--border-color);
        vertical-align: middle;
        color: #4b5563;
    }

    #parentTable tbody tr {
        transition: background-color var(--transition-speed);
    }

    #parentTable tbody tr:hover {
        background-color: var(--hover-bg);
    }

    /* Badge */
    .badge {
        font-weight: 500;
        font-size: 0.75rem;
        border: 1px solid #e5e7eb;
        background-color: #f9fafb;
    }

    /* Buttons */
    .btn {
        font-weight: 500;
        border-radius: 4px;
        transition: all var(--transition-speed);
        font-size: 0.875rem;
    }

    .btn-sm {
        padding: 0.375rem 0.75rem;
    }

    .btn-outline-secondary {
        border-color: #d1d5db;
        color: #6b7280;
    }

    .btn-outline-secondary:hover {
        border-color: #9ca3af;
        background-color: #f9fafb;
        color: #374151;
    }

    .btn-outline-danger:hover {
        background-color: #fee2e2;
    }

    /* Alert Styling */
    .alert {
        border-radius: 4px;
        font-size: 0.875rem;
        border-left: 4px solid transparent;
    }

    .alert-danger {
        border-left-color: #dc2626;
    }

    .alert-success {
        border-left-color: #059669;
    }

    .alert-warning {
        border-left-color: #d97706;
    }

    .alert-info {
        border-left-color: #2563eb;
    }

    .alert-heading {
        font-size: 0.875rem;
        font-weight: 600;
        margin-bottom: 0.25rem;
    }

    /* DataTable Buttons */
    .dt-buttons .dt-button {
        border: 1px solid var(--border-color) !important;
        background: white !important;
        padding: 6px 12px !important;
        border-radius: 4px !important;
        font-size: 0.875rem !important;
        color: #4b5563 !important;
        font-weight: 500;
    }

    .dt-buttons .dt-button:hover {
        background-color: var(--hover-bg) !important;
        border-color: #9ca3af !important;
    }

    /* Checkboxes */
    .form-check-input {
        width: 1.1em;
        height: 1.1em;
        border: 2px solid #d1d5db;
    }

    .form-check-input:checked {
        background-color: #2563eb;
        border-color: #2563eb;
    }

    /* Empty State */
    .empty-state {
        color: #9ca3af;
    }

    /* Delete Button Loading */
    .delete-btn.loading {
        position: relative;
        color: transparent !important;
        pointer-events: none;
    }

    .delete-btn.loading::after {
        content: '';
        position: absolute;
        width: 14px;
        height: 14px;
        top: 50%;
        left: 50%;
        margin: -7px 0 0 -7px;
        border: 2px solid #dc2626;
        border-top-color: transparent;
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    /* Toast Notification */
    .position-fixed.alert {
        border: 1px solid var(--border-color);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        max-width: 400px;
        font-size: 0.875rem;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .card-header .d-flex {
            flex-direction: column;
            align-items: flex-start !important;
            gap: 12px;
        }

        .btn-group {
            flex-wrap: wrap;
        }

        .dt-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
        }

        #parentTable {
            font-size: 0.8125rem;
        }

        #parentTable td, #parentTable th {
            padding: 10px 12px;
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
        paging: true,
        pageLength: 10,
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
        ordering: true,
        searching: true,
        info: true,
        autoWidth: false,
        dom: '<"d-flex justify-content-between align-items-center mb-3"lf>rt<"d-flex justify-content-between align-items-center mt-3"ip>',
        language: {
            search: "Search:",
            lengthMenu: "Show _MENU_ entries",
            info: "Showing _START_ to _END_ of _TOTAL_ entries",
            paginate: {
                first: "First",
                last: "Last",
                next: "Next",
                previous: "Previous"
            }
        },
        buttons: [
            {
                extend: 'copy',
                text: '<i class="bi bi-copy me-1"></i>Copy',
                className: 'btn btn-sm',
                exportOptions: {
                    columns: [1, 2, 3]
                }
            },
            {
                extend: 'csv',
                text: '<i class="bi bi-file-earmark-spreadsheet me-1"></i>CSV',
                className: 'btn btn-sm',
                exportOptions: {
                    columns: [1, 2, 3]
                }
            },
            {
                extend: 'excel',
                text: '<i class="bi bi-file-excel me-1"></i>Excel',
                className: 'btn btn-sm',
                exportOptions: {
                    columns: [1, 2, 3]
                }
            },
            {
                extend: 'pdf',
                text: '<i class="bi bi-file-pdf me-1"></i>PDF',
                className: 'btn btn-sm',
                exportOptions: {
                    columns: [1, 2, 3]
                }
            },
            {
                extend: 'print',
                text: '<i class="bi bi-printer me-1"></i>Print',
                className: 'btn btn-sm',
                exportOptions: {
                    columns: [1, 2, 3]
                }
            }
        ],
        columnDefs: [
            {
                orderable: false,
                searchable: false,
                targets: [0, 4]
            }
        ]
    });

    // Table buttons wrapper
    table.buttons().container().appendTo('.dt-buttons');

    // Select all functionality
    $('#select-all, #select-all-header').on('change', function() {
        const isChecked = $(this).is(':checked');
        $('.row-checkbox').prop('checked', isChecked).trigger('change');
    });

    // Individual checkbox change
    $(document).on('change', '.row-checkbox', function() {
        const allChecked = $('.row-checkbox').length === $('.row-checkbox:checked').length;
        $('#select-all, #select-all-header').prop('checked', allChecked);

        if ($(this).is(':checked')) {
            $(this).closest('tr').addClass('table-active');
        } else {
            $(this).closest('tr').removeClass('table-active');
        }
    });

    // Individual Delete
    $(document).on('click', '.delete-btn', function() {
        const deleteBtn = $(this);
        const departmentId = deleteBtn.data('id');
        const departmentName = deleteBtn.data('name');
        const departmentCode = deleteBtn.data('code');

        if (!confirm(`Delete department "${departmentName}" (${departmentCode})?\n\nThis action cannot be undone.`)) {
            return;
        }

        const originalHtml = deleteBtn.html();
        deleteBtn.html('<i class="bi bi-hourglass-split me-1"></i>');
        deleteBtn.prop('disabled', true);
        deleteBtn.addClass('loading');

        $.ajax({
            url: `{{ url('admin/parent-departments') }}/${departmentId}`,
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                _method: 'DELETE'
            },
            success: function(response) {
                if (response.status === 'success') {
                    const row = deleteBtn.closest('tr');
                    table.row(row).remove().draw();
                    showToast('success', 'Department deleted successfully');

                    if (table.rows().count() === 0) {
                        table.draw();
                    }
                } else {
                    let message = response.message || 'Error deleting department';
                    let type = 'danger';

                    if (message.includes('tagged with employees')) {
                        message = `<div class="mb-2">${message}</div>
                                  <small class="text-muted">Reassign employees before deletion</small>`;
                        type = 'warning';
                    } else if (message.includes('sub-departments')) {
                        message = `<div class="mb-2">${message}</div>
                                  <small class="text-muted">Remove sub-departments first</small>`;
                        type = 'info';
                    }

                    showToast(type, message);
                }
            },
            error: function(xhr) {
                let message = 'Error deleting department';
                let type = 'danger';

                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;

                    if (message.includes('tagged with employees')) {
                        message = `<div class="mb-2">${message}</div>
                                  <small class="text-muted">Reassign employees before deletion</small>`;
                        type = 'warning';
                    } else if (message.includes('sub-departments')) {
                        message = `<div class="mb-2">${message}</div>
                                  <small class="text-muted">Remove sub-departments first</small>`;
                        type = 'info';
                    }
                }

                showToast(type, message);
            },
            complete: function() {
                deleteBtn.html(originalHtml);
                deleteBtn.prop('disabled', false);
                deleteBtn.removeClass('loading');
            }
        });
    });

    // Bulk Delete
    $('#bulk-delete-btn').on('click', function() {
        const ids = $('.row-checkbox:checked').map(function() {
            return $(this).val();
        }).get();

        if (!ids.length) {
            showToast('warning', 'Please select at least one department');
            return;
        }

        if (!confirm(`Delete ${ids.length} selected department(s)?\n\nThis action cannot be undone.`)) {
            return;
        }

        const deleteBtn = $(this);
        const originalText = deleteBtn.html();

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
                if (res.status === 'success' || res.status === 'warning') {
                    if (res.deleted_ids && res.deleted_ids.length) {
                        res.deleted_ids.forEach(function(id) {
                            table.row($('.row-checkbox[value="' + id + '"]').closest('tr')).remove().draw();
                        });
                    }

                    $('#select-all, #select-all-header').prop('checked', false);
                    $('.row-checkbox').closest('tr').removeClass('table-active');

                    showToast(res.status === 'warning' ? 'warning' : 'success', res.message);

                    if (table.rows().count() === 0) {
                        table.draw();
                    }
                } else {
                    showToast('danger', res.message || 'Error deleting departments');
                }
            },
            error: function(xhr) {
                let message = 'Error deleting departments';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                showToast('danger', message);
            },
            complete: function() {
                deleteBtn.html(originalText);
                deleteBtn.prop('disabled', false);
            }
        });
    });

    // Toast notification function
    function showToast(type, message) {
        // Remove existing toasts
        $('.toast-alert').remove();

        // Create toast
        const toast = $(`
            <div class="toast-alert position-fixed top-3 end-3" style="z-index: 1060;">
                <div class="alert alert-${type} border-0 shadow-sm rounded-sm fade show" role="alert">
                    <div class="d-flex align-items-start">
                        <i class="bi ${getToastIcon(type)} me-2 mt-1"></i>
                        <div style="flex: 1;">${message}</div>
                        <button type="button" class="btn-close ms-2" data-bs-dismiss="alert"></button>
                    </div>
                </div>
            </div>
        `);

        $('body').append(toast);

        // Auto remove
        setTimeout(() => {
            toast.find('.alert').alert('close');
            setTimeout(() => toast.remove(), 300);
        }, 5000);
    }

    function getToastIcon(type) {
        const icons = {
            'success': 'bi-check-circle-fill',
            'danger': 'bi-x-circle-fill',
            'warning': 'bi-exclamation-triangle-fill',
            'info': 'bi-info-circle-fill'
        };
        return icons[type] || 'bi-info-circle-fill';
    }

    // Table row hover effect
    $(document).on('mouseenter', '#parentTable tbody tr', function() {
        $(this).addClass('hover-effect');
    }).on('mouseleave', '#parentTable tbody tr', function() {
        $(this).removeClass('hover-effect');
    });
});
</script>
@endsection
