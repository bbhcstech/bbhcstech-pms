@extends('admin.layout.app')

@section('title', 'Designations')

@section('content')

<main class="main py-4">
    <div class="container-fluid px-4">
        <!-- Page Header -->
        <div class="page-header card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="d-flex align-items-center mb-2">
                            <div class="symbol symbol-45px bg-primary bg-opacity-10 rounded-3 me-3 p-2">
                                <i class="bi bi-person-badge fs-4 text-primary"></i>
                            </div>
                            <div>
                                <h1 class="h3 mb-1 text-gray-800 fw-bold">Designation Management</h1>
                                <p class="text-muted mb-0">Manage and organize employee designations with hierarchical structure</p>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary btn-lg px-4 dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-download me-2"></i>Export
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                <li><button type="button" class="dropdown-item" onclick="exportTo('csv')"><i class="bi bi-file-earmark-spreadsheet me-2"></i>Export as CSV</button></li>
                                <li><button type="button" class="dropdown-item" onclick="exportTo('excel')"><i class="bi bi-file-excel me-2"></i>Export as Excel</button></li>
                                <li><button type="button" class="dropdown-item" onclick="exportTo('pdf')"><i class="bi bi-file-pdf me-2"></i>Export as PDF</button></li>
                                <li><button type="button" class="dropdown-item" onclick="printTable()"><i class="bi bi-printer me-2"></i>Print</button></li>
                            </ul>
                        </div>
                        <a href="{{ route('designations.create') }}" class="btn btn-primary btn-lg px-4">
                            <i class="bi bi-plus-circle me-2"></i>Add New
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-muted small mb-2">Total Designations</div>
                                <div class="h3 fw-bold text-primary mb-0">{{ $designations->total() }}</div>
                                <div class="text-success small mt-1">
                                    <i class="bi bi-arrow-up me-1"></i>
                                    Active designations
                                </div>
                            </div>
                            <div class="symbol symbol-55px bg-primary bg-opacity-10 rounded-3">
                                <i class="bi bi-person-badge fs-3 text-primary"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-muted small mb-2">Active Levels</div>
                                <div class="h3 fw-bold text-success mb-0">{{ $levelsCount }}</div>
                                <div class="text-muted small mt-1">
                                    @php
                                        $avgLevel = $designations->avg('level') ?? 0;
                                    @endphp
                                    Avg. Level: {{ number_format($avgLevel, 1) }}
                                </div>
                            </div>
                            <div class="symbol symbol-55px bg-success bg-opacity-10 rounded-3">
                                <i class="bi bi-layers fs-3 text-success"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-muted small mb-2">Top Level (1-2)</div>
                                <div class="h3 fw-bold text-info mb-0">
                                    {{ $designations->where('level', '<=', 2)->count() }}
                                </div>
                                <div class="text-muted small mt-1">
                                    {{ $designations->where('level', 1)->count() }} Executive
                                </div>
                            </div>
                            <div class="symbol symbol-55px bg-info bg-opacity-10 rounded-3">
                                <i class="bi bi-arrow-up-circle fs-3 text-info"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-muted small mb-2">Recently Updated</div>
                                <div class="h3 fw-bold text-warning mb-0">
                                    {{ $designations->where('updated_at', '>=', now()->subDays(7))->count() }}
                                </div>
                                <div class="text-muted small mt-1">
                                    Last: {{ optional($designations->max('updated_at'))->format('M d') ?? 'Never' }}
                                </div>
                            </div>
                            <div class="symbol symbol-55px bg-warning bg-opacity-10 rounded-3">
                                <i class="bi bi-clock-history fs-3 text-warning"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alert Messages -->
        @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm mb-4" role="alert">
            <div class="d-flex align-items-center">
                <div class="symbol symbol-35px bg-success bg-opacity-10 rounded-3 me-3">
                    <i class="bi bi-check-circle-fill text-success"></i>
                </div>
                <div class="flex-grow-1">
                    <strong>Success!</strong> {{ session('success') }}
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm mb-4" role="alert">
            <div class="d-flex align-items-center">
                <div class="symbol symbol-35px bg-danger bg-opacity-10 rounded-3 me-3">
                    <i class="bi bi-exclamation-triangle-fill text-danger"></i>
                </div>
                <div class="flex-grow-1">
                    <strong>Error!</strong> {{ session('error') }}
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
        @endif

        <!-- Main Card -->
        <div class="card border-0 shadow-sm">
            <!-- Card Header with Actions -->
            <div class="card-header bg-transparent border-0 p-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="d-flex align-items-center">
                            <span class="text-muted fw-medium me-2">View:</span>
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="{{ route('designations.index') }}"
                                   class="btn {{ request()->route()->getName() == 'designations.index' ? 'btn-primary' : 'btn-outline-primary' }} px-3">
                                    <i class="bi bi-table me-1"></i>Table
                                </a>
                                <a href="{{ route('designations.hierarchy') }}"
                                   class="btn {{ request()->route()->getName() == 'designations.hierarchy' ? 'btn-primary' : 'btn-outline-primary' }} px-3">
                                    <i class="bi bi-diagram-3 me-1"></i>Hierarchy
                                </a>
                            </div>
                        </div>

                        <div class="vr"></div>

                        <div class="d-flex align-items-center gap-2">
                            <span class="text-muted fw-medium">Levels:</span>
                            <div class="level-filters d-flex gap-1">
                                @for($i = 0; $i <= 6; $i++)
                                    <button class="btn btn-sm btn-outline-secondary level-filter-btn" data-level="{{ $i }}">
                                        L{{ $i }}
                                    </button>
                                @endfor
                                <button class="btn btn-sm btn-outline-secondary" onclick="resetFilters()">
                                    All
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-3">
                        <!-- Search -->
                        <div class="position-relative" style="min-width: 250px;">
                            <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                            <input type="search" class="form-control ps-5" id="designationSearch" placeholder="Search designations...">
                        </div>

                        <!-- Bulk Actions -->
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary px-3" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-sliders me-1"></i>Bulk Actions
                            </button>
                            <div class="dropdown-menu dropdown-menu-end shadow-sm border-0 p-3" style="min-width: 200px;">
                                <h6 class="dropdown-header text-muted mb-2">Bulk Operations</h6>
                                <button type="button"
                                        class="dropdown-item text-danger py-2"
                                        onclick="confirmBulkDelete()"
                                        id="bulk-delete-btn">
                                    <i class="bi bi-trash me-2"></i>Delete Selected
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bulk Actions Bar -->
            <div class="bg-light border-y p-3 d-flex align-items-center justify-content-between" id="bulk-actions-bar" style="display: none;">
                <div class="d-flex align-items-center gap-3">
                    <div class="form-check mb-0">
                        <input class="form-check-input" type="checkbox" id="select-all">
                        <label class="form-check-label fw-medium" for="select-all">
                            Select All
                        </label>
                    </div>
                    <span class="badge bg-primary rounded-pill px-3 py-2" id="selected-count">0 selected</span>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-outline-secondary btn-sm" id="clear-selection">
                        <i class="bi bi-x-circle me-1"></i>Clear
                    </button>
                </div>
            </div>

            <!-- Table -->
            <div class="table-responsive">
                <table id="designationTable" class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th style="width: 50px;" class="ps-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="table-select-all">
                                </div>
                            </th>
                            <th class="ps-3">
                                <div class="d-flex align-items-center gap-2">
                                    <span>Code</span>
                                    <i class="bi bi-arrow-down-up text-muted opacity-50"></i>
                                </div>
                            </th>
                            <th>Designation Name</th>
                            <th style="width: 100px;">Level</th>
                            <th style="width: 180px;">Added By</th>
                            <th style="width: 180px;">Last Updated</th>
                            <th class="text-end pe-4" style="width: 120px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($designations as $designation)
                        <tr class="designation-row" data-level="{{ $designation->level }}">
                            <td class="ps-4">
                                <div class="form-check">
                                    <input class="form-check-input select-item" type="checkbox" value="{{ $designation->id }}">
                                </div>
                            </td>
                            <td class="ps-3">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="symbol symbol-35px bg-light-primary rounded-2 d-flex align-items-center justify-content-center">
                                        <span class="text-primary fw-medium">DGN</span>
                                    </div>
                                    <div>
                                        <div class="fw-semibold text-gray-800">{{ $designation->unique_code ?? 'N/A' }}</div>
                                        <small class="text-muted">ID: {{ str_pad($designation->id, 3, '0', STR_PAD_LEFT) }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="fw-medium text-gray-800">{{ $designation->name ?? '-' }}</div>
                                @if($designation->parent)
                                <small class="text-muted d-block mt-1">
                                    <i class="bi bi-arrow-up-right me-1"></i>Reports to: {{ $designation->parent->name }}
                                </small>
                                @endif
                            </td>
                            <td>
                                @if($designation->level)
                                <span class="badge rounded-pill px-3 py-2 fw-medium
                                    @if($designation->level == 1) bg-primary
                                    @elseif($designation->level <= 3) bg-success
                                    @elseif($designation->level <= 6) bg-info
                                    @else bg-secondary @endif">
                                    <i class="bi bi-chevron-double-up me-1"></i>L{{ $designation->level }}
                                </span>
                                @else
                                <span class="badge bg-light text-muted border">Not Set</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="symbol symbol-35px bg-light rounded-circle d-flex align-items-center justify-content-center">
                                        <span class="text-gray-600 fw-medium">
                                            {{ substr($designation->addedBy?->name ?? 'S', 0, 1) }}
                                        </span>
                                    </div>
                                    <div>
                                        <div class="text-gray-800">{{ $designation->addedBy?->name ?? 'System' }}</div>
                                        <small class="text-muted">{{ $designation->created_at->format('d M Y') }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="symbol symbol-35px bg-light-success rounded-circle d-flex align-items-center justify-content-center">
                                        <span class="text-success fw-medium">
                                            {{ substr($designation->updatedBy?->name ?? '-', 0, 1) }}
                                        </span>
                                    </div>
                                    <div>
                                        <div class="text-gray-800">{{ $designation->updatedBy?->name ?? '-' }}</div>
                                        <small class="text-muted">{{ $designation->updated_at->format('d M Y') }}</small>
                                    </div>
                                </div>
                            </td>
                            <td class="text-end pe-4">
                                <div class="d-flex justify-content-end gap-1">
                                    <a href="{{ route('designations.show', $designation->id) }}"
                                       class="btn btn-sm btn-outline-gray-400 btn-icon rounded-2"
                                       data-bs-toggle="tooltip" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>

                                    <a href="{{ route('designations.edit', $designation->id) }}"
                                       class="btn btn-sm btn-outline-gray-400 btn-icon rounded-2"
                                       data-bs-toggle="tooltip" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>

                                     <!-- Delete -->
                                    <form action="{{ route('designations.destroy', $designation->id) }}"
                                        method="POST"
                                        class="d-inline"
                                        onsubmit="return confirm('Are you sure you want to delete this designation?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="btn btn-sm btn-outline-gray-400 btn-icon rounded-2 hover-text-danger"
                                                data-bs-toggle="tooltip"
                                                title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="py-5">
                                    <div class="symbol symbol-80px bg-light rounded-3 mx-auto mb-4 p-4">
                                        <i class="bi bi-person-badge fs-2 text-gray-400"></i>
                                    </div>
                                    <h5 class="text-gray-600 fw-medium mb-3">No Designations Found</h5>
                                    <p class="text-muted mb-4">Get started by creating your first designation</p>
                                    <a href="{{ route('designations.create') }}" class="btn btn-primary px-4">
                                        <i class="bi bi-plus-circle me-2"></i>Create Designation
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Card Footer with Pagination -->
            @if($designations->count() > 0)
            <div class="card-footer bg-transparent border-top p-3">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <!-- Show Entries -->
                    <div class="mb-0">
                        <div class="d-flex align-items-center">
                            <span class="text-muted me-2">Show:</span>
                            <select class="form-select form-select-sm" id="showEntries" style="width: auto;">
                                <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                                <option value="20" {{ request('per_page') == 20 ? 'selected' : '' }}>20</option>
                                <option value="30" {{ request('per_page') == 30 ? 'selected' : '' }}>30</option>
                                <option value="40" {{ request('per_page') == 40 ? 'selected' : '' }}>40</option>
                                <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                                <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                            </select>
                            <span class="text-muted ms-2">entries</span>
                        </div>
                    </div>

                    <!-- Show Info -->
                    <div class="text-muted">
                        Showing <span class="fw-semibold">{{ $designations->firstItem() ?? 0 }}</span> to
                        <span class="fw-semibold">{{ $designations->lastItem() ?? 0 }}</span> of
                        <span class="fw-semibold">{{ $designations->total() }}</span> designations
                    </div>

                    <!-- Pagination -->
                    <nav aria-label="Designation pagination">
                        <ul class="pagination pagination-sm mb-0">
                            <!-- Previous Page Link -->
                            @if ($designations->onFirstPage())
                                <li class="page-item disabled">
                                    <span class="page-link">
                                        <i class="bi bi-chevron-left"></i>
                                    </span>
                                </li>
                            @else
                                <li class="page-item">
                                    <a class="page-link" href="{{ $designations->previousPageUrl() . (request('per_page') ? '&per_page=' . request('per_page') : '') }}" aria-label="Previous">
                                        <i class="bi bi-chevron-left"></i>
                                    </a>
                                </li>
                            @endif

                            <!-- Pagination Elements -->
                            @php
                                $current = $designations->currentPage();
                                $last = $designations->lastPage();
                                $start = max(1, $current - 2);
                                $end = min($last, $current + 2);
                            @endphp

                            @if($start > 1)
                                <li class="page-item"><a class="page-link" href="{{ $designations->url(1) . (request('per_page') ? '&per_page=' . request('per_page') : '') }}">1</a></li>
                                @if($start > 2)
                                    <li class="page-item disabled"><span class="page-link">...</span></li>
                                @endif
                            @endif

                            @for ($i = $start; $i <= $end; $i++)
                                <li class="page-item {{ $i == $current ? 'active' : '' }}">
                                    <a class="page-link" href="{{ $designations->url($i) . (request('per_page') ? '&per_page=' . request('per_page') : '') }}">{{ $i }}</a>
                                </li>
                            @endfor

                            @if($end < $last)
                                @if($end < $last - 1)
                                    <li class="page-item disabled"><span class="page-link">...</span></li>
                                @endif
                                <li class="page-item"><a class="page-link" href="{{ $designations->url($last) . (request('per_page') ? '&per_page=' . request('per_page') : '') }}">{{ $last }}</a></li>
                            @endif

                            <!-- Next Page Link -->
                            @if ($designations->hasMorePages())
                                <li class="page-item">
                                    <a class="page-link" href="{{ $designations->nextPageUrl() . (request('per_page') ? '&per_page=' . request('per_page') : '') }}" aria-label="Next">
                                        <i class="bi bi-chevron-right"></i>
                                    </a>
                                </li>
                            @else
                                <li class="page-item disabled">
                                    <span class="page-link">
                                        <i class="bi bi-chevron-right"></i>
                                    </span>
                                </li>
                            @endif
                        </ul>
                    </nav>
                </div>
            </div>
            @endif
        </div>

        <!-- Legend -->
        <div class="mt-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center gap-3 flex-wrap">
                        <span class="text-muted fw-medium me-2">Level Legend:</span>
                        <span class="badge bg-primary rounded-pill px-3 py-2"><i class="bi bi-chevron-double-up me-1"></i>LL 0 (Intern)</span>
                        <span class="badge bg-success rounded-pill px-3 py-2"><i class="bi bi-chevron-double-up me-1"></i>LL1 (Associate)</span>
                        <span class="badge bg-info rounded-pill px-3 py-2"><i class="bi bi-chevron-double-up me-1"></i>LL2 (Sr.Associate)</span>
                        <span class="badge bg-secondary rounded-pill px-3 py-2"><i class="bi bi-chevron-double-up me-1"></i>LL3 (Manager)</span>
                        <span class="badge bg-secondary rounded-pill px-3 py-2"><i class="bi bi-chevron-double-up me-1"></i>LL4 (Sr.Manager)</span>
                        <span class="badge bg-secondary rounded-pill px-3 py-2"><i class="bi bi-chevron-double-up me-1"></i>LL5(Associate Director)</span>
                        <span class="badge bg-secondary rounded-pill px-3 py-2"><i class="bi bi-chevron-double-up me-1"></i>LL6(Director)</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<style>
    .page-header.card {
        background: linear-gradient(135deg, #f8f9fa 0%, #f1f3f4 100%);
        border-left: 4px solid #0d6efd;
    }

    .symbol {
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .symbol-35px {
        width: 35px;
        height: 35px;
    }

    .symbol-45px {
        width: 45px;
        height: 45px;
    }

    .symbol-55px {
        width: 55px;
        height: 55px;
    }

    .symbol-80px {
        width: 80px;
        height: 80px;
    }

    .bg-light-primary { background-color: rgba(13, 110, 253, 0.1) !important; }
    .bg-light-success { background-color: rgba(25, 135, 84, 0.1) !important; }
    .bg-light-info { background-color: rgba(13, 202, 240, 0.1) !important; }

    .designation-row:hover {
        background-color: #f8fafd;
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

    .btn-outline-gray-400 {
        border-color: #dee2e6;
        color: #6c757d;
    }

    .btn-outline-gray-400:hover {
        background-color: #f8f9fa;
        border-color: #adb5bd;
        color: #495057;
    }

    .card {
        border-radius: 12px;
    }

    .table {
        margin-bottom: 0;
    }

    .table th {
        font-weight: 600;
        color: #374151;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        padding: 1rem 0.75rem;
        border-bottom: 2px solid #e5e7eb;
        background-color: #f9fafb;
    }

    .table td {
        padding: 1.25rem 0.75rem;
        vertical-align: middle;
        border-bottom: 1px solid #f3f4f6;
        color: #4b5563;
    }

    .table tbody tr:last-child td {
        border-bottom: none;
    }

    .form-check-input {
        width: 18px;
        height: 18px;
        border-radius: 4px;
        cursor: pointer;
    }

    .form-check-input:checked {
        background-color: #0d6efd;
        border-color: #0d6efd;
    }

    .badge {
        font-weight: 500;
        font-size: 0.75rem;
    }

    .dropdown-menu {
        border: 1px solid rgba(0,0,0,0.08);
        box-shadow: 0 8px 24px rgba(0,0,0,0.12);
        border-radius: 10px;
        padding: 8px;
    }

    .dropdown-item {
        border-radius: 6px;
        padding: 0.5rem 1rem;
        margin: 2px 0;
        font-weight: 500;
        transition: all 0.2s ease;
    }

    .dropdown-item:hover {
        background-color: rgba(13, 110, 253, 0.1);
    }

    .alert {
        border-radius: 10px;
        border: none;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }

    #bulk-actions-bar {
        animation: slideDown 0.2s ease;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .level-filter-btn.active {
        background-color: #0d6efd;
        color: white;
        border-color: #0d6efd;
    }

    .btn-group .btn {
        border-radius: 6px !important;
    }

    .vr {
        width: 1px;
        height: 24px;
        background-color: #dee2e6;
    }

    .text-gray-600 { color: #6c757d !important; }
    .text-gray-800 { color: #343a40 !important; }
    .bg-light { background-color: #f8f9fa !important; }

    .hover-text-danger:hover {
        color: #dc3545 !important;
        border-color: #dc3545 !important;
    }

    .pagination {
        margin-bottom: 0;
    }

    .page-link {
        border-radius: 6px !important;
        margin: 0 3px;
        min-width: 32px;
        text-align: center;
    }

    .page-item.active .page-link {
        background-color: #0d6efd;
        border-color: #0d6efd;
    }
</style>
@endsection

@push('js')
<script>
$(document).ready(function() {
    // Show Entries dropdown functionality
    $('#showEntries').on('change', function() {
        var perPage = $(this).val();
        var currentUrl = window.location.href;
        var url = new URL(currentUrl);
        url.searchParams.set('per_page', perPage);
        window.location.href = url.toString();
    });

    // Initialize DataTable without pagination (since we're using Laravel pagination)
    var table = $('#designationTable').DataTable({
        dom: '<"d-none"lBf>rt',
        paging: false,
        searching: true,
        info: false,
        ordering: true,
        language: {
            search: "",
            searchPlaceholder: "Search designations...",
            zeroRecords: "No matching records found",
        },
        initComplete: function() {
            // Hide default search
            $('.dataTables_filter').hide();
        },
        buttons: [
            {
                extend: 'csv',
                text: '<i class="bi bi-file-earmark-spreadsheet me-1"></i> CSV',
                className: 'btn btn-outline-secondary btn-sm'
            },
            {
                extend: 'excel',
                text: '<i class="bi bi-file-excel me-1"></i> Excel',
                className: 'btn btn-outline-secondary btn-sm'
            },
            {
                extend: 'pdf',
                text: '<i class="bi bi-file-pdf me-1"></i> PDF',
                className: 'btn btn-outline-secondary btn-sm'
            }
        ]
    });

    // Custom search functionality
    $('#designationSearch').on('keyup', function() {
        table.search(this.value).draw();
    });

    // Level filter buttons
    $('.level-filter-btn').on('click', function() {
        $('.level-filter-btn').removeClass('active');
        $(this).addClass('active');
        var level = $(this).data('level');
        table.column(3).search('^' + level + '$', true, false).draw();
    });

    // Reset filters
    window.resetFilters = function() {
        $('.level-filter-btn').removeClass('active');
        table.search('').columns().search('').draw();
        $('#designationSearch').val('');
    };

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

        if (count > 0) {
            bulkBar.slideDown();
        } else {
            bulkBar.slideUp();
        }

        $('#selected-count').text(count + ' selected');
        $('#bulk-delete-btn').prop('disabled', count === 0);
    }

    // Individual checkbox change
    $(document).on('change', '.select-item', function() {
        updateUI();
    });

    // Clear selection
    $('#clear-selection').on('click', function() {
        $('.select-item').prop('checked', false);
        $('#table-select-all').prop('checked', false);
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
        $('#table-select-all').prop('checked', $(this).is(':checked'));
        updateUI();
    });

    // Export functions
    window.exportTo = function(format) {
        switch(format) {
            case 'csv':
                $('.buttons-csv').click();
                break;
            case 'excel':
                $('.buttons-excel').click();
                break;
            case 'pdf':
                $('.buttons-pdf').click();
                break;
        }
        showToast('Exporting data...', 'info');
    };

    window.printTable = function() {
        window.print();
    };

    // Bulk delete confirmation
    window.confirmBulkDelete = function() {
        var ids = getSelectedIds();
        if (ids.length === 0) {
            showToast('Please select at least one designation.', 'warning');
            return false;
        }

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Delete Selected Designations?',
                html: `You are about to delete <b>${ids.length}</b> designation(s).<br><small class="text-muted">This action cannot be undone.</small>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete them!',
                cancelButtonText: 'Cancel',
                reverseButtons: true,
                backdrop: true,
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    submitBulkDelete(ids);
                }
            });
        } else {
            if (confirm(`Are you sure you want to delete ${ids.length} designation(s)?`)) {
                submitBulkDelete(ids);
            }
        }
    };

    function submitBulkDelete(ids) {
        // Create a hidden form to submit
        var form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("designations.bulk-delete") }}';
        form.style.display = 'none';

        var csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        form.appendChild(csrfToken);

        var methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE';
        form.appendChild(methodInput);

        var idsInput = document.createElement('input');
        idsInput.type = 'hidden';
        idsInput.name = 'ids';
        idsInput.value = JSON.stringify(ids);
        form.appendChild(idsInput);

        document.body.appendChild(form);
        form.submit();
    }

    // Show toast notification
    function showToast(message, type = 'info') {
        if (typeof toastr !== 'undefined') {
            toastr[type](message);
        } else {
            // Fallback alert
            alert(message);
        }
    }

    // Initialize tooltips
    function initializeTooltips() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }

    initializeTooltips();

    // Toastr notifications
    if (typeof toastr !== 'undefined') {
        toastr.options = {
            "closeButton": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "timeOut": "4000",
            "showMethod": "fadeIn",
            "hideMethod": "fadeOut"
        };
    }

    // Row click for viewing
    $('.designation-row').on('click', function(e) {
        if (!$(e.target).closest('input, a, button, .dropdown').length) {
            var designationId = $(this).find('.select-item').val();
            window.location.href = "{{ route('designations.show', '') }}/" + designationId;
        }
    });
});

@if(session('success'))
    $(document).ready(function() {
        showToast('{{ session('success') }}', 'success');
    });
@endif

@if(session('error'))
    $(document).ready(function() {
        showToast('{{ session('error') }}', 'error');
    });
@endif
</script>
@endpush
