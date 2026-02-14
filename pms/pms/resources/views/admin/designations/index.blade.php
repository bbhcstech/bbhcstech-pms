@extends('admin.layout.app')

@section('title', 'Designations')

@section('content')

<main class="main py-4">
    <div class="container-fluid px-4">
        <!-- Page Header - Purple White Combo -->
        <div class="page-header card border-0 shadow-lg mb-4" style="background: linear-gradient(135deg, #ffffff 0%, #faf5ff 100%);">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="d-flex align-items-center mb-2">
                            <div class="symbol symbol-50px bg-purple bg-opacity-10 rounded-4 me-3 p-2" style="background: rgba(139, 92, 246, 0.08) !important;">
                                <i class="bi bi-person-badge fs-3" style="color: #8b5cf6;"></i>
                            </div>
                            <div>
                                <h1 class="h3 mb-1 fw-bold" style="color: #6d28d9;">Designation Management</h1>
                                <p class="mb-0" style="color: #a78bfa;">Manage and organize employee designations with hierarchical structure</p>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <div class="dropdown">
                            <button class="btn btn-outline-purple btn-lg px-4 dropdown-toggle" type="button" data-bs-toggle="dropdown" style="border-color: #c4b5fd; color: #7c3aed; background: white;">
                                <i class="bi bi-download me-2"></i>Export
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 p-2" style="border-radius: 16px;">
                                <li><button type="button" class="dropdown-item rounded-3 py-2" onclick="exportTo('csv')"><i class="bi bi-file-earmark-spreadsheet me-2" style="color: #8b5cf6;"></i>Export as CSV</button></li>
                                <li><button type="button" class="dropdown-item rounded-3 py-2" onclick="exportTo('excel')"><i class="bi bi-file-excel me-2" style="color: #8b5cf6;"></i>Export as Excel</button></li>
                                <li><button type="button" class="dropdown-item rounded-3 py-2" onclick="exportTo('pdf')"><i class="bi bi-file-pdf me-2" style="color: #8b5cf6;"></i>Export as PDF</button></li>
                                <li><div class="dropdown-divider"></div></li>
                                <li><button type="button" class="dropdown-item rounded-3 py-2" onclick="printTable()"><i class="bi bi-printer me-2" style="color: #8b5cf6;"></i>Print</button></li>
                            </ul>
                        </div>
                        <a href="{{ route('designations.create') }}" class="btn btn-purple btn-lg px-4" style="background: linear-gradient(145deg, #8b5cf6, #7c3aed); border: none; color: white; box-shadow: 0 8px 16px -4px rgba(124, 58, 237, 0.2);">
                            <i class="bi bi-plus-circle me-2"></i>Add New
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards - Purple White Theme -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-0 shadow-sm h-100 stats-card" style="background: white; border-radius: 20px; transition: all 0.3s ease;">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="small fw-medium mb-2" style="color: #9ca3af;">Total Designations</div>
                                <div class="h2 fw-bold mb-0" style="color: #6d28d9;">{{ $designations->total() }}</div>
                                <div class="small mt-1 d-flex align-items-center" style="color: #34d399;">
                                    <i class="bi bi-arrow-up me-1"></i>
                                    Active designations
                                </div>
                            </div>
                            <div class="symbol symbol-60px rounded-4 d-flex align-items-center justify-content-center" style="background: rgba(139, 92, 246, 0.08);">
                                <i class="bi bi-person-badge fs-2" style="color: #8b5cf6;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-0 shadow-sm h-100 stats-card" style="background: white; border-radius: 20px;">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="small fw-medium mb-2" style="color: #9ca3af;">Active Levels</div>
                                <div class="h2 fw-bold mb-0" style="color: #6d28d9;">{{ $levelsCount }}</div>
                                <div class="small mt-1" style="color: #9ca3af;">
                                    @php
                                        $avgLevel = $designations->avg('level') ?? 0;
                                    @endphp
                                    Present. Level
                                    <!-- : {{ number_format($avgLevel, 1) }} -->
                                </div>
                            </div>
                            <div class="symbol symbol-60px rounded-4 d-flex align-items-center justify-content-center" style="background: rgba(52, 211, 153, 0.08);">
                                <i class="bi bi-layers fs-2" style="color: #34d399;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-0 shadow-sm h-100 stats-card" style="background: white; border-radius: 20px;">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="small fw-medium mb-2" style="color: #9ca3af;">Top Level (1-2)</div>
                                <div class="h2 fw-bold mb-0" style="color: #6d28d9;">
                                    {{ $designations->where('level', '<=', 2)->count() }}
                                </div>
                                <div class="small mt-1" style="color: #9ca3af;">
                                    {{ $designations->where('level', 1)->count() }} Executive
                                </div>
                            </div>
                            <div class="symbol symbol-60px rounded-4 d-flex align-items-center justify-content-center" style="background: rgba(96, 165, 250, 0.08);">
                                <i class="bi bi-arrow-up-circle fs-2" style="color: #60a5fa;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-0 shadow-sm h-100 stats-card" style="background: white; border-radius: 20px;">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="small fw-medium mb-2" style="color: #9ca3af;">Recently Updated</div>
                                <div class="h2 fw-bold mb-0" style="color: #6d28d9;">
                                    {{ $designations->where('updated_at', '>=', now()->subDays(7))->count() }}
                                </div>
                                <div class="small mt-1" style="color: #9ca3af;">
                                    Last: {{ optional($designations->max('updated_at'))->format('M d') ?? 'Never' }}
                                </div>
                            </div>
                            <div class="symbol symbol-60px rounded-4 d-flex align-items-center justify-content-center" style="background: rgba(251, 191, 36, 0.08);">
                                <i class="bi bi-clock-history fs-2" style="color: #fbbf24;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alert Messages - Purple Theme -->
        @if(session('success'))
        <div class="alert border-0 shadow-sm mb-4" role="alert" style="background: linear-gradient(145deg, #ffffff, #faf5ff); border-left: 4px solid #8b5cf6 !important; border-radius: 16px;">
            <div class="d-flex align-items-center">
                <div class="symbol symbol-40px rounded-3 me-3 d-flex align-items-center justify-content-center" style="background: rgba(139, 92, 246, 0.08);">
                    <i class="bi bi-check-circle-fill" style="color: #8b5cf6;"></i>
                </div>
                <div class="flex-grow-1" style="color: #4b5563;">
                    <strong style="color: #6d28d9;">Success!</strong> {{ session('success') }}
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
        @endif

        @if(session('error'))
        <div class="alert border-0 shadow-sm mb-4" role="alert" style="background: linear-gradient(145deg, #ffffff, #fef2f2); border-left: 4px solid #ef4444 !important; border-radius: 16px;">
            <div class="d-flex align-items-center">
                <div class="symbol symbol-40px rounded-3 me-3 d-flex align-items-center justify-content-center" style="background: rgba(239, 68, 68, 0.08);">
                    <i class="bi bi-exclamation-triangle-fill" style="color: #ef4444;"></i>
                </div>
                <div class="flex-grow-1" style="color: #4b5563;">
                    <strong style="color: #dc2626;">Error!</strong> {{ session('error') }}
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
        @endif

        <!-- Main Card - Pure White with Purple Accents -->
        <div class="card border-0 shadow-lg" style="border-radius: 24px; background: white;">
            <!-- Card Header -->
            <div class="card-header bg-transparent border-0 p-4 pb-0">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="d-flex align-items-center">
                            <span class="fw-medium me-2" style="color: #9ca3af;">View:</span>
                            <div class="btn-group" style="background: #f9fafb; padding: 4px; border-radius: 12px;">
                                <a href="{{ route('designations.index') }}"
                                   class="btn px-4 py-2 rounded-3 {{ request()->route()->getName() == 'designations.index' ? 'btn-purple' : '' }}"
                                   style="{{ request()->route()->getName() == 'designations.index' ? 'background: #8b5cf6; color: white;' : 'color: #6b7280; background: transparent; border: none;' }}">
                                    <i class="bi bi-table me-1"></i>Table
                                </a>
                                <a href="{{ route('designations.hierarchy') }}"
                                   class="btn px-4 py-2 rounded-3 {{ request()->route()->getName() == 'designations.hierarchy' ? 'btn-purple' : '' }}"
                                   style="{{ request()->route()->getName() == 'designations.hierarchy' ? 'background: #8b5cf6; color: white;' : 'color: #6b7280; background: transparent; border: none;' }}">
                                    <i class="bi bi-diagram-3 me-1"></i>Hierarchy
                                </a>
                            </div>
                        </div>

                        <div class="vr" style="background-color: #e5e7eb; width: 1px; height: 30px;"></div>

                        <div class="d-flex align-items-center gap-2">
                            <span class="fw-medium" style="color: #9ca3af;">Levels:</span>
                            <div class="level-filters d-flex gap-1">
                                @for($i = 0; $i <= 6; $i++)
                                    <button class="btn btn-sm level-filter-btn px-3" data-level="{{ $i }}" style="border-radius: 20px; background: white; border: 1px solid #e5e7eb; color: #6b7280;">
                                        L{{ $i }}
                                    </button>
                                @endfor
                                <button class="btn btn-sm px-3" onclick="resetFilters()" style="border-radius: 20px; background: #f3f4f6; border: 1px solid #e5e7eb; color: #6b7280;">
                                    All
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-3">
                        <!-- Search -->
                        <div class="position-relative" style="min-width: 260px;">
                            <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3" style="color: #a78bfa;"></i>
                            <input type="search" class="form-control ps-5" id="designationSearch" placeholder="Search designations..." style="border-radius: 40px; border: 1px solid #e5e7eb; padding: 0.75rem 1rem; background: #f9fafb;">
                        </div>

                        <!-- Bulk Actions -->
                        <div class="dropdown">
                            <button class="btn px-4 py-2" type="button" data-bs-toggle="dropdown" style="border-radius: 40px; border: 1px solid #e5e7eb; background: white; color: #6b7280;">
                                <i class="bi bi-sliders me-1"></i>Bulk Actions
                            </button>
                            <div class="dropdown-menu dropdown-menu-end shadow-lg border-0 p-2" style="border-radius: 16px; min-width: 200px;">
                                <h6 class="dropdown-header text-muted mb-2 px-3">Bulk Operations</h6>
                                <button type="button"
                                        class="dropdown-item rounded-3 py-2 text-danger"
                                        onclick="confirmBulkDelete()"
                                        id="bulk-delete-btn">
                                    <i class="bi bi-trash me-2"></i>Delete Selected
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bulk Actions Bar - Purple Theme -->
            <div class="p-3 mx-4 mt-3 d-flex align-items-center justify-content-between" id="bulk-actions-bar" style="display: none; background: #faf5ff; border-radius: 40px; border: 1px solid #e9d5ff;">
                <div class="d-flex align-items-center gap-3">
                    <div class="form-check mb-0">
                        <input class="form-check-input" type="checkbox" id="select-all" style="border-color: #8b5cf6; border-radius: 6px;">
                        <label class="form-check-label fw-medium ms-2" for="select-all" style="color: #6d28d9;">
                            Select All
                        </label>
                    </div>
                    <span class="badge rounded-pill px-4 py-2" style="background: #8b5cf6; color: white;" id="selected-count">0 selected</span>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-sm px-3" id="clear-selection" style="border-radius: 30px; border: 1px solid #e5e7eb; background: white; color: #6b7280;">
                        <i class="bi bi-x-circle me-1"></i>Clear
                    </button>
                </div>
            </div>

            <!-- Table - Clean Design -->
            <div class="table-responsive px-2">
                <table id="designationTable" class="table" style="border-collapse: separate; border-spacing: 0 8px;">
                    <thead>
                        <tr style="background: transparent;">
                            <th style="width: 50px; border: none; padding: 0.75rem 1rem; color: #9ca3af; font-weight: 600; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="table-select-all" style="border-color: #8b5cf6;">
                                </div>
                            </th>
                            <th style="border: none; padding: 0.75rem 1rem; color: #9ca3af; font-weight: 600; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">Designation Code</th>
                            <th style="border: none; padding: 0.75rem 1rem; color: #9ca3af; font-weight: 600; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">Designation Name</th>
                            <th style="border: none; padding: 0.75rem 1rem; color: #9ca3af; font-weight: 600; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">Level</th>
                            <th style="border: none; padding: 0.75rem 1rem; color: #9ca3af; font-weight: 600; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">Added By</th>
                            <th style="border: none; padding: 0.75rem 1rem; color: #9ca3af; font-weight: 600; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">Last Updated</th>
                            <th class="text-end" style="border: none; padding: 0.75rem 1rem; color: #9ca3af; font-weight: 600; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($designations as $designation)
                        <tr class="designation-row" data-level="{{ $designation->level }}" style="background: white; box-shadow: 0 2px 6px rgba(0,0,0,0.02); border-radius: 16px; transition: all 0.2s ease;">
                            <td style="border: none; border-radius: 16px 0 0 16px; padding: 1rem;">
                                <div class="form-check">
                                    <input class="form-check-input select-item" type="checkbox" value="{{ $designation->id }}" style="border-color: #8b5cf6;">
                                </div>
                            </td>
                            <td style="border: none; padding: 1rem;">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="symbol symbol-40px rounded-3 d-flex align-items-center justify-content-center" style="background: rgba(139, 92, 246, 0.08);">
                                        <span class="fw-bold" style="color: #8b5cf6; font-size: 0.85rem;">DGN</span>
                                    </div>
                                    <div>
                                        <div class="fw-semibold" style="color: #1f2937;">{{ $designation->unique_code ?? 'N/A' }}</div>
                                        <small style="color: #9ca3af;">ID: {{ str_pad($designation->id, 3, '0', STR_PAD_LEFT) }}</small>
                                    </div>
                                </div>
                            </td>
                            <td style="border: none; padding: 1rem;">
                                <div class="fw-medium" style="color: #374151;">{{ $designation->name ?? '-' }}</div>
                                @if($designation->parent)
                                <small style="color: #9ca3af;">
                                    <i class="bi bi-arrow-up-right me-1" style="color: #a78bfa;"></i>{{ $designation->parent->name }}
                                </small>
                                @endif
                            </td>
                            <td style="border: none; padding: 1rem;">
                                @if($designation->level !== null)
                                    <span class="badge rounded-4 px-3 py-2 fw-medium"
                                          style="@if($designation->level == 0) background: #111827; color: white;
                                          @elseif($designation->level == 1) background: #8b5cf6; color: white;
                                          @elseif($designation->level == 2) background: #34d399; color: white;
                                          @elseif($designation->level == 3) background: #60a5fa; color: white;
                                          @elseif($designation->level == 4) background: #fbbf24; color: white;
                                          @elseif($designation->level == 5) background: #f97316; color: white;
                                          @elseif($designation->level == 6) background: #ef4444; color: white;
                                          @else background: #9ca3af; color: white; @endif">
                                        <i class="bi bi-chevron-double-up me-1"></i>L{{ $designation->level }}
                                    </span>
                                @else
                                    <span class="badge bg-light text-muted border px-3 py-2 rounded-4">Not Set</span>
                                @endif
                            </td>
                            <td style="border: none; padding: 1rem;">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="symbol symbol-40px rounded-circle d-flex align-items-center justify-content-center" style="background: rgba(139, 92, 246, 0.08);">
                                        <span class="fw-medium" style="color: #8b5cf6;">
                                            {{ substr($designation->addedBy?->name ?? 'S', 0, 1) }}
                                        </span>
                                    </div>
                                    <div>
                                        <div style="color: #374151;">{{ $designation->addedBy?->name ?? 'System' }}</div>
                                        <small style="color: #9ca3af;">{{ $designation->created_at->format('d M Y') }}</small>
                                    </div>
                                </div>
                            </td>
                            <td style="border: none; padding: 1rem;">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="symbol symbol-40px rounded-circle d-flex align-items-center justify-content-center" style="background: rgba(52, 211, 153, 0.08);">
                                        <span class="fw-medium" style="color: #34d399;">
                                            {{ substr($designation->updatedBy?->name ?? '-', 0, 1) }}
                                        </span>
                                    </div>
                                    <div>
                                        <div style="color: #374151;">{{ $designation->updatedBy?->name ?? '-' }}</div>
                                        <small style="color: #9ca3af;">{{ $designation->updated_at->format('d M Y') }}</small>
                                    </div>
                                </div>
                            </td>
                            <td class="text-end" style="border: none; border-radius: 0 16px 16px 0; padding: 1rem;">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('designations.show', $designation->id) }}"
                                       class="btn btn-sm btn-icon rounded-3"
                                       style="background: white; border: 1px solid #e5e7eb; color: #6b7280; padding: 0.5rem;"
                                       data-bs-toggle="tooltip" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('designations.edit', $designation->id) }}"
                                       class="btn btn-sm btn-icon rounded-3"
                                       style="background: white; border: 1px solid #e5e7eb; color: #8b5cf6; padding: 0.5rem;"
                                       data-bs-toggle="tooltip" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('designations.destroy', $designation->id) }}"
                                        method="POST"
                                        class="d-inline"
                                        onsubmit="return confirm('Are you sure you want to delete this designation?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="btn btn-sm btn-icon rounded-3"
                                                style="background: white; border: 1px solid #e5e7eb; color: #ef4444; padding: 0.5rem;"
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
                            <td colspan="7" class="text-center py-5" style="border: none; border-radius: 16px; background: white;">
                                <div class="py-5">
                                    <div class="symbol symbol-100px rounded-4 mx-auto mb-4 d-flex align-items-center justify-content-center" style="background: rgba(139, 92, 246, 0.08);">
                                        <i class="bi bi-person-badge fs-1" style="color: #8b5cf6;"></i>
                                    </div>
                                    <h5 style="color: #374151; font-weight: 600;">No Designations Found</h5>
                                    <p style="color: #9ca3af; margin-bottom: 1.5rem;">Get started by creating your first designation</p>
                                    <a href="{{ route('designations.create') }}" class="btn btn-purple px-5 py-2" style="background: #8b5cf6; border: none; color: white; border-radius: 40px;">
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
            <div class="card-footer bg-transparent border-0 p-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <!-- Show Entries -->
                    <div class="mb-0">
                        <div class="d-flex align-items-center">
                            <span style="color: #9ca3af;" class="me-2">Show:</span>
                            <select class="form-select form-select-sm" id="showEntries" style="width: auto; border-radius: 40px; border: 1px solid #e5e7eb; background: white; padding: 0.5rem 2rem 0.5rem 1rem;">
                                <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                                <option value="20" {{ request('per_page') == 20 ? 'selected' : '' }}>20</option>
                                <option value="30" {{ request('per_page') == 30 ? 'selected' : '' }}>30</option>
                                <option value="40" {{ request('per_page') == 40 ? 'selected' : '' }}>40</option>
                                <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                                <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                            </select>
                            <span style="color: #9ca3af;" class="ms-2">entries</span>
                        </div>
                    </div>

                    <!-- Show Info -->
                    <div style="color: #6b7280;">
                        Showing <span class="fw-semibold" style="color: #6d28d9;">{{ $designations->firstItem() ?? 0 }}</span> to
                        <span class="fw-semibold" style="color: #6d28d9;">{{ $designations->lastItem() ?? 0 }}</span> of
                        <span class="fw-semibold" style="color: #6d28d9;">{{ $designations->total() }}</span> designations
                    </div>

                    <!-- Pagination - Purple Theme -->
                    <nav aria-label="Designation pagination">
                        <ul class="pagination pagination-sm mb-0">
                            @if ($designations->onFirstPage())
                                <li class="page-item disabled">
                                    <span class="page-link" style="border: none; background: #f3f4f6; color: #9ca3af; border-radius: 12px; margin: 0 2px;">
                                        <i class="bi bi-chevron-left"></i>
                                    </span>
                                </li>
                            @else
                                <li class="page-item">
                                    <a class="page-link" href="{{ $designations->previousPageUrl() . (request('per_page') ? '&per_page=' . request('per_page') : '') }}" style="border: none; background: white; color: #6b7280; border-radius: 12px; margin: 0 2px;">
                                        <i class="bi bi-chevron-left"></i>
                                    </a>
                                </li>
                            @endif

                            @php
                                $current = $designations->currentPage();
                                $last = $designations->lastPage();
                                $start = max(1, $current - 2);
                                $end = min($last, $current + 2);
                            @endphp

                            @if($start > 1)
                                <li class="page-item"><a class="page-link" href="{{ $designations->url(1) . (request('per_page') ? '&per_page=' . request('per_page') : '') }}" style="border: none; background: white; color: #6b7280; border-radius: 12px; margin: 0 2px;">1</a></li>
                                @if($start > 2)
                                    <li class="page-item disabled"><span class="page-link" style="border: none; background: #f3f4f6; color: #9ca3af; border-radius: 12px;">...</span></li>
                                @endif
                            @endif

                            @for ($i = $start; $i <= $end; $i++)
                                <li class="page-item {{ $i == $current ? 'active' : '' }}">
                                    <a class="page-link" href="{{ $designations->url($i) . (request('per_page') ? '&per_page=' . request('per_page') : '') }}"
                                       style="{{ $i == $current ? 'background: #8b5cf6; color: white; border: none; border-radius: 12px; margin: 0 2px;' : 'border: none; background: white; color: #6b7280; border-radius: 12px; margin: 0 2px;' }}">
                                        {{ $i }}
                                    </a>
                                </li>
                            @endfor

                            @if($end < $last)
                                @if($end < $last - 1)
                                    <li class="page-item disabled"><span class="page-link" style="border: none; background: #f3f4f6; color: #9ca3af; border-radius: 12px;">...</span></li>
                                @endif
                                <li class="page-item"><a class="page-link" href="{{ $designations->url($last) . (request('per_page') ? '&per_page=' . request('per_page') : '') }}" style="border: none; background: white; color: #6b7280; border-radius: 12px; margin: 0 2px;">{{ $last }}</a></li>
                            @endif

                            @if ($designations->hasMorePages())
                                <li class="page-item">
                                    <a class="page-link" href="{{ $designations->nextPageUrl() . (request('per_page') ? '&per_page=' . request('per_page') : '') }}" style="border: none; background: white; color: #6b7280; border-radius: 12px; margin: 0 2px;">
                                        <i class="bi bi-chevron-right"></i>
                                    </a>
                                </li>
                            @else
                                <li class="page-item disabled">
                                    <span class="page-link" style="border: none; background: #f3f4f6; color: #9ca3af; border-radius: 12px; margin: 0 2px;">
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

        <!-- Legend - Purple White Theme -->
        <div class="mt-4">
            <div class="card border-0 shadow-sm" style="border-radius: 20px; background: white;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-3 flex-wrap">
                        <span class="fw-medium me-2" style="color: #9ca3af;">Level Legend:</span>
                        <span class="badge rounded-4 px-3 py-2" style="background: #111827; color: white;"><i class="bi bi-chevron-double-up me-1"></i>L0 (Intern)</span>
                        <span class="badge rounded-4 px-3 py-2" style="background: #8b5cf6; color: white;"><i class="bi bi-chevron-double-up me-1"></i>L1 (Associate)</span>
                        <span class="badge rounded-4 px-3 py-2" style="background: #34d399; color: white;"><i class="bi bi-chevron-double-up me-1"></i>L2 (Sr. Associate)</span>
                        <span class="badge rounded-4 px-3 py-2" style="background: #60a5fa; color: white;"><i class="bi bi-chevron-double-up me-1"></i>L3 (Manager)</span>
                        <span class="badge rounded-4 px-3 py-2" style="background: #fbbf24; color: white;"><i class="bi bi-chevron-double-up me-1"></i>L4 (Sr. Manager)</span>
                        <span class="badge rounded-4 px-3 py-2" style="background: #f97316; color: white;"><i class="bi bi-chevron-double-up me-1"></i>L5 (Associate Director)</span>
                        <span class="badge rounded-4 px-3 py-2" style="background: #ef4444; color: white;"><i class="bi bi-chevron-double-up me-1"></i>L6 (Director)</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<style>
/* Purple + White Theme */
.btn-purple {
    background: linear-gradient(145deg, #8b5cf6, #7c3aed);
    border: none;
    color: white;
    transition: all 0.3s ease;
}

.btn-purple:hover {
    background: linear-gradient(145deg, #7c3aed, #6d28d9);
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 12px 20px -8px rgba(124, 58, 237, 0.3);
}

.btn-outline-purple {
    border: 1px solid #c4b5fd;
    color: #7c3aed;
    background: white;
    transition: all 0.3s ease;
}

.btn-outline-purple:hover {
    background: #faf5ff;
    border-color: #8b5cf6;
    color: #6d28d9;
}

/* Stats Cards */
.stats-card {
    transition: all 0.3s ease;
    border: 1px solid #f3f4f6;
}

.stats-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 20px 25px -5px rgba(139, 92, 246, 0.1), 0 8px 10px -6px rgba(139, 92, 246, 0.05) !important;
    border-color: #e9d5ff;
}

/* Table Rows */
.designation-row {
    transition: all 0.2s ease;
    border: 1px solid #f9fafb;
}

.designation-row:hover {
    background: #faf5ff !important;
    transform: scale(1.01);
    border-color: #e9d5ff;
    box-shadow: 0 4px 12px rgba(139, 92, 246, 0.08);
}

/* Form Controls */
.form-control:focus {
    border-color: #8b5cf6;
    box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);
    background: white;
}

.form-check-input:checked {
    background-color: #8b5cf6;
    border-color: #8b5cf6;
}

/* Level Filter Buttons */
.level-filter-btn.active {
    background: #8b5cf6 !important;
    color: white !important;
    border-color: #8b5cf6 !important;
}

/* Dropdown Items */
.dropdown-item:hover {
    background: #faf5ff !important;
    color: #6d28d9 !important;
}

/* Symbols */
.symbol {
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.symbol-35px { width: 35px; height: 35px; }
.symbol-40px { width: 40px; height: 40px; }
.symbol-45px { width: 45px; height: 45px; }
.symbol-50px { width: 50px; height: 50px; }
.symbol-55px { width: 55px; height: 55px; }
.symbol-60px { width: 60px; height: 60px; }
.symbol-80px { width: 80px; height: 80px; }
.symbol-100px { width: 100px; height: 100px; }

/* Pagination */
.page-item.active .page-link {
    background: #8b5cf6 !important;
    color: white !important;
}

.page-link:focus {
    box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);
}

/* Table */
.table {
    border-collapse: separate;
    border-spacing: 0 8px;
}

.table td:first-child,
.table th:first-child {
    padding-left: 1.5rem;
}

.table td:last-child,
.table th:last-child {
    padding-right: 1.5rem;
}

/* Animations */
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

#bulk-actions-bar {
    animation: slideDown 0.2s ease;
}

/* Card */
.card {
    transition: all 0.3s ease;
}

/* Alert */
.alert {
    border-left-width: 4px !important;
}

/* Button Icons */
.btn-icon {
    width: 36px;
    height: 36px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
}

.btn-icon:hover {
    background: #faf5ff !important;
    border-color: #8b5cf6 !important;
}
</style>

@endsection

@push('js')
<script>
$(document).ready(function() {
    // Show Entries dropdown
    $('#showEntries').on('change', function() {
        var perPage = $(this).val();
        var currentUrl = window.location.href;
        var url = new URL(currentUrl);
        url.searchParams.set('per_page', perPage);
        window.location.href = url.toString();
    });

    // DataTable
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
            $('.dataTables_filter').hide();
        }
    });

    // Custom search
    $('#designationSearch').on('keyup', function() {
        table.search(this.value).draw();
    });

    // Level filter buttons
    $('.level-filter-btn').on('click', function() {
        $('.level-filter-btn').removeClass('active');
        $(this).addClass('active');
        var level = $(this).data('level');
        table.search('').draw();
        $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
            var rowLevel = table.row(dataIndex).data()[3];
            var rowLevelNum = rowLevel.match(/L(\d+)/);
            if (rowLevelNum && rowLevelNum[1] == level) {
                return true;
            }
            return false;
        });
        table.draw();
        $.fn.dataTable.ext.search.pop();
    });

    // Reset filters
    window.resetFilters = function() {
        $('.level-filter-btn').removeClass('active');
        table.search('').columns().search('').draw();
        $('#designationSearch').val('');
        $.fn.dataTable.ext.search = [];
        table.draw();
    };

    // Get selected IDs
    function getSelectedIds() {
        return $('.select-item:checked').map(function() {
            return $(this).val();
        }).get();
    }

    // Update UI
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

    // Checkbox change
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
                confirmButtonColor: '#8b5cf6',
                cancelButtonColor: '#9ca3af',
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

        ids.forEach(function(id) {
            var idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = 'ids[]';
            idInput.value = id;
            form.appendChild(idInput);
        });

        document.body.appendChild(form);
        form.submit();
    }

    // Show toast
    function showToast(message, type = 'info') {
        if (typeof toastr !== 'undefined') {
            toastr[type](message);
        } else {
            alert(message);
        }
    }

    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Toastr options
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

    // Row click
    $('.designation-row').on('click', function(e) {
        if (!$(e.target).closest('input, a, button, .dropdown, .form-check').length) {
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
