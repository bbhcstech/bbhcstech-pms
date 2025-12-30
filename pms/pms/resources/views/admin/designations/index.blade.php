@extends('admin.layout.app')

@section('title', 'Designations')

@section('content')
<main class="main py-4">
    <div class="container-fluid px-4">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0 text-gray-800">
                    <i class="bi bi-person-badge me-2 text-primary"></i>Designations
                </h1>
                <p class="text-muted mb-0">Manage and organize employee designations</p>
            </div>

            <div>
                <a href="{{ route('designations.create') }}" class="btn btn-primary px-4">
                    <i class="bi bi-plus-circle me-2"></i>Add Designation
                </a>
            </div>
        </div>

        <!-- Quick Actions Card -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <!-- View Toggle -->
                    <div class="btn-group" role="group">
                        <a href="{{ route('designations.index') }}"
                           class="btn btn-outline-primary btn-sm px-3 {{ request()->route()->getName() == 'designations.index' ? 'active' : '' }}"
                           data-bs-toggle="tooltip" title="Table View">
                            <i class="bi bi-grid-3x3-gap me-2"></i>Table
                        </a>
                        <a href="{{ route('designations.hierarchy') }}"
                           class="btn btn-outline-primary btn-sm px-3 {{ request()->route()->getName() == 'designations.hierarchy' ? 'active' : '' }}"
                           data-bs-toggle="tooltip" title="Hierarchy View">
                            <i class="bi bi-diagram-3 me-2"></i>Hierarchy
                        </a>
                    </div>

                    <!-- Quick Actions -->
                    <div class="d-flex align-items-center flex-wrap gap-2">
                        <div class="input-group input-group-sm" style="min-width: 250px;">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="bi bi-lightning-charge text-warning"></i>
                            </span>
                            <select class="form-select form-select-sm border-start-0 ps-0" id="quick-action-type">
                                <option value="">Quick Actions</option>
                                <option value="delete" class="text-danger">Delete Selected</option>
                            </select>
                            <button class="btn btn-primary btn-sm px-3" id="quick-action-apply" disabled>
                                <i class="bi bi-check2 me-1"></i>Apply
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alert Messages -->
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            <div class="flex-grow-1">{{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <div class="flex-grow-1">{{ session('error') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        <!-- Main Table Card -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <!-- Bulk Actions Bar -->
                <div class="bg-light border-bottom p-3 d-flex align-items-center justify-content-between" id="bulk-actions-bar" style="display: none;">
                    <div class="d-flex align-items-center gap-3">
                        <div class="form-check mb-0">
                            <input class="form-check-input" type="checkbox" id="select-all">
                            <label class="form-check-label fw-medium" for="select-all">
                                Select All
                            </label>
                        </div>
                        <span class="text-muted" id="selected-count">0 selected</span>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <form id="bulk-delete-form"
                              action="{{ route('designations.bulk-delete') }}"
                              method="POST"
                              class="mb-0">
                            @csrf
                            <button type="submit"
                                    class="btn btn-danger btn-sm px-3"
                                    onclick="return confirmBulkDelete()"
                                    id="delete-selected-btn"
                                    disabled>
                                <i class="bi bi-trash me-1"></i>Delete Selected
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Table Container -->
                <div class="table-responsive">
                    <table id="designationTable" class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 40px;" class="ps-4">
                                    <input type="checkbox" id="table-select-all">
                                </th>
                                <th class="ps-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <span>Designation Code</span>
                                        <i class="bi bi-sort-alpha-down text-muted"></i>
                                    </div>
                                </th>
                                <th>Name</th>
                                <th>Added By</th>
                                <th>Last Updated By</th>
                                <th class="text-end pe-4" style="width: 120px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($designations as $designation)
                            <tr class="position-relative">
                                <td class="ps-4">
                                    <input type="checkbox" class="select-item" value="{{ $designation->id }}">
                                </td>
                                <td class="ps-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill fw-medium">
                                            {{ $designation->unique_code ?? '-' }}
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="symbol symbol-40px me-2">
                                            <div class="symbol-label bg-light-primary">
                                                <i class="bi bi-person-badge text-primary fs-4"></i>
                                            </div>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-medium">{{ $designation->name }}</h6>
                                            <small class="text-muted">{{ $designation->created_at->format('d M Y') }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="symbol symbol-30px">
                                            <div class="symbol-label bg-light-success">
                                                <i class="bi bi-person-check text-success"></i>
                                            </div>
                                        </div>
                                        <span class="fw-medium">{{ $designation->addedBy?->name ?? 'System' }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="symbol symbol-30px">
                                            <div class="symbol-label bg-light-info">
                                                <i class="bi bi-arrow-clockwise text-info"></i>
                                            </div>
                                        </div>
                                        <span class="fw-medium">{{ $designation->updatedBy?->name ?? '-' }}</span>
                                    </div>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="dropdown">
                                        <button class="btn btn-light btn-sm btn-icon"
                                                type="button"
                                                data-bs-toggle="dropdown"
                                                aria-expanded="false">
                                            <i class="bi bi-three-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                            <li>
                                                <a class="dropdown-item py-2"
                                                   href="{{ route('designations.show', $designation->id) }}">
                                                    <i class="bi bi-eye text-primary me-2"></i>View Details
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item py-2"
                                                   href="{{ route('designations.edit', $designation->id) }}">
                                                    <i class="bi bi-pencil-square text-warning me-2"></i>Edit
                                                </a>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form action="{{ route('designations.destroy', $designation->id) }}"
                                                      method="POST"
                                                      onsubmit="return confirm('Are you sure you want to delete this designation?');"
                                                      class="mb-0">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item py-2 text-danger">
                                                        <i class="bi bi-trash me-2"></i>Delete
                                                    </button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="py-5">
                                        <div class="symbol symbol-80px mb-3">
                                            <div class="symbol-label bg-light">
                                                <i class="bi bi-person-badge text-muted fs-1"></i>
                                            </div>
                                        </div>
                                        <h5 class="text-muted mb-2">No Designations Found</h5>
                                        <p class="text-muted mb-4">Get started by adding your first designation</p>
                                        <a href="{{ route('designations.create') }}" class="btn btn-primary">
                                            <i class="bi bi-plus-circle me-2"></i>Add Designation
                                        </a>
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
</main>

<style>
    .symbol {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    overflow: hidden;
}
.symbol-label {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
}
.symbol-30px {
    width: 30px;
    height: 30px;
}
.symbol-40px {
    width: 40px;
    height: 40px;
}
.symbol-80px {
    width: 80px;
    height: 80px;
}
.bg-light-primary { background-color: rgba(13, 110, 253, 0.1) !important; }
.bg-light-success { background-color: rgba(25, 135, 84, 0.1) !important; }
.bg-light-info { background-color: rgba(13, 202, 240, 0.1) !important; }
.bg-light-warning { background-color: rgba(255, 193, 7, 0.1) !important; }
.bg-light-danger { background-color: rgba(220, 53, 69, 0.1) !important; }
.bg-light { background-color: rgba(33, 37, 41, 0.05) !important; }

.table-hover tbody tr:hover {
    background-color: rgba(13, 110, 253, 0.02) !important;
    transform: translateY(-1px);
    transition: all 0.2s ease;
}

.btn-icon {
    width: 32px;
    height: 32px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 6px;
}

.dropdown-menu {
    border: 1px solid rgba(0,0,0,0.08);
    box-shadow: 0 8px 16px rgba(0,0,0,0.08);
    border-radius: 8px;
    padding: 8px;
}

.dropdown-item {
    border-radius: 6px;
    margin: 2px 0;
    font-weight: 500;
}

.dropdown-item:hover {
    background-color: rgba(13, 110, 253, 0.08);
}

.card {
    border-radius: 12px;
    overflow: hidden;
}

.table th {
    font-weight: 600;
    color: #374151;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.05em;
    border-bottom: 2px solid #e5e7eb !important;
}

.table td {
    vertical-align: middle;
    padding: 1rem 0.75rem;
    border-color: #f9fafb;
}

.form-check-input:checked {
    background-color: #0d6efd;
    border-color: #0d6efd;
}

.form-check-input {
    cursor: pointer;
    width: 18px;
    height: 18px;
}

.btn-group .btn.active {
    background-color: #0d6efd;
    color: white;
    border-color: #0d6efd;
}

.input-group .input-group-text {
    background-color: #f8f9fa;
    border-color: #dee2e6;
}

.alert {
    border: none;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.badge {
    font-weight: 500;
    letter-spacing: 0.3px;
}

#bulk-actions-bar {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-top-left-radius: 12px;
    border-top-right-radius: 12px;
}
</style>
@endsection

@push('js')
<script>
$(document).ready(function() {
    // Initialize DataTable
    var table = $('#designationTable').DataTable({
        dom: '<"d-flex justify-content-between align-items-center mb-3"lBf>rt<"d-flex justify-content-between align-items-center mt-3"ip>',
        buttons: [
            {
                extend: 'copy',
                className: 'btn btn-light btn-sm',
                text: '<i class="bi bi-copy me-1"></i>Copy'
            },
            {
                extend: 'csv',
                className: 'btn btn-light btn-sm',
                text: '<i class="bi bi-file-earmark-spreadsheet me-1"></i>CSV'
            },
            {
                extend: 'excel',
                className: 'btn btn-light btn-sm',
                text: '<i class="bi bi-file-excel me-1"></i>Excel'
            },
            {
                extend: 'pdf',
                className: 'btn btn-light btn-sm',
                text: '<i class="bi bi-file-pdf me-1"></i>PDF'
            },
            {
                extend: 'print',
                className: 'btn btn-light btn-sm',
                text: '<i class="bi bi-printer me-1"></i>Print'
            }
        ],
        responsive: true,
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],
        language: {
            search: "",
            searchPlaceholder: "Search designations...",
            lengthMenu: "Show _MENU_ entries",
            info: "Showing _START_ to _END_ of _TOTAL_ entries",
            paginate: {
                first: '<i class="bi bi-chevron-double-left"></i>',
                last: '<i class="bi bi-chevron-double-right"></i>',
                next: '<i class="bi bi-chevron-right"></i>',
                previous: '<i class="bi bi-chevron-left"></i>'
            }
        },
        initComplete: function() {
            $('.dataTables_filter input').addClass('form-control form-control-sm');
            $('.dataTables_length select').addClass('form-select form-select-sm');
        }
    });

    // Get selected IDs
    function getSelectedIds() {
        return $('.select-item:checked').map(function() {
            return $(this).val();
        }).get();
    }

    // Update UI based on selection
    function updateUI() {
        var count = getSelectedIds().length;
        var bulkBar = $('#bulk-actions-bar');

        // Show/hide bulk actions bar
        if (count > 0) {
            bulkBar.slideDown();
        } else {
            bulkBar.slideUp();
        }

        // Update counters
        $('#selected-count').text(count + ' selected');
        $('#bulk-info').text(count + ' selected');

        // Enable/disable buttons
        $('#delete-selected-btn').prop('disabled', count === 0);
        $('#quick-action-apply').prop('disabled', count === 0 || $('#quick-action-type').val() === '');
    }

    // Individual checkbox change
    $(document).on('change', '.select-item', function() {
        updateUI();
    });

    // Table select all
    $('#table-select-all').on('change', function() {
        $('.select-item').prop('checked', $(this).is(':checked'));
        updateUI();
    });

    // Bulk select all
    $('#select-all').on('change', function() {
        $('.select-item').prop('checked', $(this).is(':checked'));
        updateUI();
    });

    // Quick action type change
    $('#quick-action-type').on('change', updateUI);

    // Quick action apply
    $('#quick-action-apply').on('click', function() {
        var ids = getSelectedIds();
        if (ids.length === 0) {
            toastr.warning('Please select at least one designation.');
            return;
        }

        if (!confirm(`Are you sure you want to delete ${ids.length} selected designation(s)?`)) {
            return;
        }

        $.ajax({
            url: '{{ route("designations.bulk-delete") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                ids: ids
            },
            success: function(response) {
                toastr.success('Selected designations deleted successfully.');
                setTimeout(function() {
                    location.reload();
                }, 1500);
            },
            error: function(xhr) {
                toastr.error('Failed to delete designations. Please try again.');
                console.error('Delete error:', xhr);
            }
        });
    });

    // Bulk delete form confirmation
    window.confirmBulkDelete = function() {
        var ids = getSelectedIds();
        if (ids.length === 0) {
            toastr.warning('Please select at least one designation.');
            return false;
        }

        var form = $('#bulk-delete-form');
        form.find('input[name="ids[]"]').remove();

        ids.forEach(function(id) {
            form.append(`<input type="hidden" name="ids[]" value="${id}">`);
        });

        return confirm(`Are you sure you want to delete ${ids.length} selected designation(s)?`);
    };

    // Table redraw event
    table.on('draw', function() {
        updateUI();
    });

    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Toastr notifications (optional)
    if (typeof toastr !== 'undefined') {
        toastr.options = {
            "closeButton": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "timeOut": "3000"
        };
    }
});
</script>

@if(session('success'))
<script>
    $(document).ready(function() {
        toastr.success('{{ session('success') }}');
    });
</script>
@endif

@if(session('error'))
<script>
    $(document).ready(function() {
        toastr.error('{{ session('error') }}');
    });
</script>
@endif
@endpush
