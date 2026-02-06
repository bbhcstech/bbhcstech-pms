@extends('admin.layout.app')

@section('title', 'Designation Hierarchy')

@section('content')
<main class="main py-4">
    <div class="container-fluid px-4">
        <!-- Page Header -->
        <div class="page-header card border-0 shadow-sm mb-5">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div class="d-flex align-items-center">
                        <div class="symbol symbol-45px bg-primary bg-opacity-10 rounded-3 me-3 p-2">
                            <i class="bi bi-diagram-3 fs-4 text-primary"></i>
                        </div>
                        <div>
                            <h1 class="h3 mb-1 text-gray-800 fw-bold">Organization Hierarchy</h1>
                            <p class="text-muted mb-0">Visualize and manage your organizational structure</p>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary px-4 dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-gear me-2"></i>Options
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                <li><a class="dropdown-item" href="#" id="exportChartBtn"><i class="bi bi-download me-2"></i>Export Chart</a></li>
                                <li><a class="dropdown-item" href="#" id="printChartBtn"><i class="bi bi-printer me-2"></i>Print Chart</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="{{ route('designations.index') }}"><i class="bi bi-table me-2"></i>Switch to Table View</a></li>
                            </ul>
                        </div>
                        <a href="{{ route('designations.create') }}" class="btn btn-primary px-4">
                            <i class="bi bi-plus-circle me-2"></i>Add Designation
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row mb-5">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-muted small mb-2">Total Designations</div>
                                <div class="h3 fw-bold text-primary mb-0">{{ $designations->count() }}</div>
                                <div class="text-success small mt-1">
                                    <i class="bi bi-arrow-up me-1"></i>
                                    Organizational levels
                                </div>
                            </div>
                            <div class="symbol symbol-55px bg-primary bg-opacity-10 rounded-3">
                                <i class="bi bi-people fs-3 text-primary"></i>
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
                                <div class="text-muted small mb-2">Top Level Designations</div>
                                <div class="h3 fw-bold text-success mb-0">{{ $designations->whereNull('parent_id')->count() }}</div>
                                <div class="text-muted small mt-1">
                                    <i class="bi bi-diagram-3 me-1"></i>
                                    Executive level
                                </div>
                            </div>
                            <div class="symbol symbol-55px bg-success bg-opacity-10 rounded-3">
                                <i class="bi bi-arrow-up-circle fs-3 text-success"></i>
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
                                <div class="text-muted small mb-2">Hierarchy Levels</div>
                                <div class="h3 fw-bold text-info mb-0">{{ $designations->pluck('level')->unique()->count() }}</div>
                                <div class="text-muted small mt-1">
                                    Max depth: {{ $maxDepth ?? 'N/A' }}
                                </div>
                            </div>
                            <div class="symbol symbol-55px bg-info bg-opacity-10 rounded-3">
                                <i class="bi bi-layers fs-3 text-info"></i>
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

        <!-- Main Content -->
        <div class="row g-4">
            <!-- Left Panel: Drag & Drop Hierarchy -->
            <div class="col-xl-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-bottom p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-1 fw-bold text-gray-800">
                                    <i class="bi bi-arrows-move text-primary me-2"></i>
                                    Hierarchy Management
                                </h5>
                                <p class="text-muted small mb-0">Drag and drop to reorganize your organizational structure</p>
                            </div>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-funnel me-1"></i>Filter
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="#" data-level="all">Show All Levels</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    @for($i = 0; $i <= 6; $i++)
                                        <li><a class="dropdown-item" href="#" data-level="{{ $i }}">Level {{ $i }} Only</a></li>
                                    @endfor
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <!-- Instructions -->
                        <div class="alert alert-light border-0 bg-light-primary mb-4" role="alert">
                            <div class="d-flex align-items-center">
                                <div class="symbol symbol-35px bg-primary bg-opacity-10 rounded-3 me-3 p-2">
                                    <i class="bi bi-info-circle text-primary"></i>
                                </div>
                                <div>
                                    <div class="fw-medium mb-1">How to reorganize:</div>
                                    <div class="text-muted small">
                                        • Drag items using the handle (<i class="bi bi-grip-vertical text-muted"></i>) to reorder<br>
                                        • Drop items onto other items to create parent-child relationships<br>
                                        • Changes are automatically saved when you click "Save Hierarchy"
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Hierarchy Container -->
                        <div class="border rounded bg-white p-4" style="min-height: 500px; max-height: 600px; overflow-y: auto;">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-light text-dark border me-2">Level</span>
                                    <div class="d-flex gap-1">
                                        @for($i = 0; $i <= 6; $i++)
                                            <span class="badge level-badge" data-level="{{ $i }}">L{{ $i }}</span>
                                        @endfor
                                    </div>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="expandAll">
                                    <i class="bi bi-arrows-expand me-1"></i>Expand All
                                </button>
                            </div>

                            <!-- Hierarchy Tree -->
                            <div class="hierarchy-tree-container">
                                @if($designations->whereNull('parent_id')->isEmpty())
                                    <div class="text-center py-5">
                                        <div class="symbol symbol-80px bg-light rounded-3 mx-auto mb-4 p-4">
                                            <i class="bi bi-diagram-3 fs-2 text-gray-400"></i>
                                        </div>
                                        <h5 class="text-gray-600 fw-medium mb-3">No Designations Found</h5>
                                        <p class="text-muted mb-4">Start building your organizational structure</p>
                                        <a href="{{ route('designations.create') }}" class="btn btn-primary px-4">
                                            <i class="bi bi-plus-circle me-2"></i>Create First Designation
                                        </a>
                                    </div>
                                @else
                                    <ul id="hierarchyList" class="hierarchy-list list-group list-group-flush">
                                        @foreach($designations->whereNull('parent_id') as $designation)
                                            @include('admin.designations.partials.designation-item', ['designation' => $designation])
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="mt-4 d-flex justify-content-between align-items-center">
                            <div class="text-muted small">
                                <i class="bi bi-lightbulb text-warning me-1"></i>
                                Changes are saved when you click the button
                            </div>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-outline-secondary" id="resetHierarchy">
                                    <i class="bi bi-arrow-clockwise me-1"></i>Reset Changes
                                </button>
                                <button id="saveHierarchy" class="btn btn-primary px-4">
                                    <i class="bi bi-check-circle me-1"></i>Save Hierarchy
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Panel: Organizational Chart -->
            <div class="col-xl-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-bottom p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-1 fw-bold text-gray-800">
                                    <i class="bi bi-diagram-3 text-primary me-2"></i>
                                    Organizational Chart
                                </h5>
                                <p class="text-muted small mb-0">Live visualization of your organization structure</p>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-light text-dark border">Real-time</span>
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="fullscreenChart">
                                    <i class="bi bi-fullscreen"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0 position-relative">
                        <!-- Chart Controls -->
                        <div class="chart-controls position-absolute top-0 end-0 mt-3 me-3 z-index-10">
                            <div class="btn-group btn-group-sm shadow-sm" role="group">
                                <button type="button" class="btn btn-white border" id="zoomIn" title="Zoom In">
                                    <i class="bi bi-zoom-in"></i>
                                </button>
                                <button type="button" class="btn btn-white border" id="zoomOut" title="Zoom Out">
                                    <i class="bi bi-zoom-out"></i>
                                </button>
                                <button type="button" class="btn btn-white border" id="resetZoom" title="Reset Zoom">
                                    <i class="bi bi-fullscreen-exit"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Chart Legend -->
                        <div class="chart-legend position-absolute bottom-0 start-0 mb-3 ms-3 bg-white p-3 rounded shadow-sm border">
                            <div class="text-muted small mb-2 fw-medium">Level Legend</div>
                            <div class="d-flex flex-column gap-1">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-primary" style="width: 12px; height: 12px;"></span>
                                    <span class="small">L0: Executive</span>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-success" style="width: 12px; height: 12px;"></span>
                                    <span class="small">L1-2: Management</span>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-info" style="width: 12px; height: 12px;"></span>
                                    <span class="small">L3-4: Senior</span>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-warning" style="width: 12px; height: 12px;"></span>
                                    <span class="small">L5-6: Entry Level</span>
                                </div>
                            </div>
                        </div>

                        <!-- Chart Container -->
                        <div id="chartDiv" class="rounded-bottom bg-light" style="height: 600px;"></div>
                    </div>
                    <div class="card-footer bg-white border-top p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="small text-muted">
                                <i class="bi bi-info-circle me-1"></i>
                                Chart updates automatically when changes are saved
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <div class="spinner-border spinner-border-sm text-primary d-none" id="chartLoading" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <span class="badge bg-success bg-opacity-10 text-success border border-success">
                                    <i class="bi bi-check-circle me-1"></i>Live
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Legend Section -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                            <div class="d-flex align-items-center gap-3">
                                <span class="text-muted fw-medium me-2">Hierarchy Status:</span>
                                <span class="badge bg-success rounded-pill px-3 py-2 d-flex align-items-center">
                                    <i class="bi bi-check-circle me-1"></i>Active
                                </span>
                                <span class="badge bg-light text-dark border rounded-pill px-3 py-2 d-flex align-items-center">
                                    <i class="bi bi-diagram-3 me-1"></i>{{ $designations->count() }} Positions
                                </span>
                                <span class="badge bg-light text-dark border rounded-pill px-3 py-2 d-flex align-items-center">
                                    <i class="bi bi-layers me-1"></i>{{ $designations->pluck('level')->unique()->count() }} Levels
                                </span>
                            </div>
                            <div class="text-muted small">
                                Last saved: <span class="fw-medium">{{ now()->format('M d, Y h:i A') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

@push('css')
<style>
    /* Main Styles */
    .page-header.card {
        background: linear-gradient(135deg, #f8f9fa 0%, #f1f3f4 100%);
        border-left: 4px solid #0d6efd;
    }

    .symbol {
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .symbol-35px { width: 35px; height: 35px; }
    .symbol-45px { width: 45px; height: 45px; }
    .symbol-55px { width: 55px; height: 55px; }
    .symbol-80px { width: 80px; height: 80px; }

    /* Card Styling */
    .card {
        border-radius: 12px;
        transition: transform 0.2s ease;
    }

    .card:hover {
        transform: translateY(-2px);
    }

    .card-header {
        border-radius: 12px 12px 0 0 !important;
    }

    /* Hierarchy Tree */
    .hierarchy-tree-container {
        position: relative;
    }

    .hierarchy-list {
        margin: 0;
        padding: 0;
        list-style: none;
    }

    .hierarchy-list > li {
        margin-bottom: 8px;
    }

    .hierarchy-list ul {
        margin-left: 40px;
        margin-top: 8px;
        padding-left: 15px;
        border-left: 2px solid #e9ecef;
        position: relative;
    }

    .hierarchy-list ul::before {
        content: '';
        position: absolute;
        left: -2px;
        top: 0;
        height: 20px;
        width: 2px;
        background: #e9ecef;
    }

    /* List Item Styling */
    .designation-item {
        background: white;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        padding: 12px 15px;
        margin-bottom: 6px;
        transition: all 0.2s ease;
        position: relative;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }

    .designation-item:hover {
        border-color: #0d6efd;
        box-shadow: 0 4px 12px rgba(13, 110, 253, 0.1);
        transform: translateX(4px);
    }

    .designation-item.dragging {
        opacity: 0.8;
        transform: rotate(2deg);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }

    .designation-item .drag-handle {
        cursor: grab;
        color: #adb5bd;
        padding: 8px;
        margin: -8px 8px -8px -8px;
        border-radius: 6px 0 0 6px;
        background: #f8f9fa;
        transition: all 0.2s ease;
    }

    .designation-item .drag-handle:hover {
        color: #0d6efd;
        background: #e7f1ff;
    }

    /* Level Badges */
    .level-badge {
        cursor: pointer;
        transition: all 0.2s ease;
        padding: 4px 8px;
        font-size: 0.7rem;
    }

    .level-badge:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .level-badge.active {
        background-color: #0d6efd !important;
        color: white !important;
    }

    /* Chart Container */
    #chartDiv {
        position: relative;
    }

    .chart-controls {
        z-index: 100;
    }

    .chart-legend {
        z-index: 100;
        max-width: 200px;
    }

    /* Status Indicators */
    .status-indicator {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 6px;
    }

    .status-active { background-color: #198754; }
    .status-inactive { background-color: #dc3545; }

    /* Animation */
    @keyframes pulse {
        0% { box-shadow: 0 0 0 0 rgba(13, 110, 253, 0.4); }
        70% { box-shadow: 0 0 0 10px rgba(13, 110, 253, 0); }
        100% { box-shadow: 0 0 0 0 rgba(13, 110, 253, 0); }
    }

    .pulse {
        animation: pulse 2s infinite;
    }

    /* Loading Spinner */
    .spinner-border {
        vertical-align: middle;
    }

    /* Gradient Backgrounds */
    .bg-light-primary { background-color: rgba(13, 110, 253, 0.08) !important; }
    .bg-light-success { background-color: rgba(25, 135, 84, 0.08) !important; }
    .bg-light-info { background-color: rgba(13, 202, 240, 0.08) !important; }
    .bg-light-warning { background-color: rgba(255, 193, 7, 0.08) !important; }

    /* Scrollbar Styling */
    .hierarchy-tree-container::-webkit-scrollbar {
        width: 6px;
    }

    .hierarchy-tree-container::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }

    .hierarchy-tree-container::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 10px;
    }

    .hierarchy-tree-container::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }

    /* Fullscreen Mode */
    .fullscreen {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        width: 100vw !important;
        height: 100vh !important;
        z-index: 9999 !important;
        margin: 0 !important;
        padding: 20px !important;
        background: white;
    }

    .fullscreen .card {
        height: calc(100vh - 40px) !important;
        margin: 0 !important;
    }
</style>
@endpush

@push('js')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script src="https://code.jscharting.com/latest/jscharting.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));

    // Level filter functionality
    document.querySelectorAll('.dropdown-item[data-level]').forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const level = this.dataset.level;

            if (level === 'all') {
                document.querySelectorAll('.designation-item').forEach(el => el.style.display = '');
            } else {
                document.querySelectorAll('.designation-item').forEach(el => {
                    el.style.display = el.dataset.level === level ? '' : 'none';
                });
            }

            // Update active state
            document.querySelectorAll('.level-badge').forEach(badge => badge.classList.remove('active'));
            if (level !== 'all') {
                document.querySelector(`.level-badge[data-level="${level}"]`)?.classList.add('active');
            }
        });
    });

    // Initialize SortableJS for hierarchy
    const hierarchyList = document.getElementById('hierarchyList');
    let originalHierarchy = null;

    if (hierarchyList) {
        // Store original hierarchy state
        originalHierarchy = hierarchyList.innerHTML;

        // Initialize Sortable
        new Sortable(hierarchyList, {
            group: 'nested',
            animation: 200,
            fallbackOnBody: true,
            swapThreshold: 0.65,
            handle: '.drag-handle',
            ghostClass: 'dragging',
            chosenClass: 'bg-primary-light',
            dragClass: 'designation-item',
            onStart: function(evt) {
                evt.item.classList.add('dragging');
                document.body.style.cursor = 'grabbing';
            },
            onEnd: function(evt) {
                evt.item.classList.remove('dragging');
                document.body.style.cursor = '';

                // Visual feedback
                evt.item.classList.add('pulse');
                setTimeout(() => evt.item.classList.remove('pulse'), 1000);

                // Update hierarchy status
                updateHierarchyStatus();
            }
        });
    }

    // Expand/Collapse All
    document.getElementById('expandAll').addEventListener('click', function() {
        const items = document.querySelectorAll('.designation-item');
        items.forEach(item => {
            const children = item.querySelector('ul');
            if (children) {
                if (children.style.display === 'none') {
                    children.style.display = '';
                    item.querySelector('.toggle-children i').className = 'bi bi-chevron-down';
                } else {
                    children.style.display = 'none';
                    item.querySelector('.toggle-children i').className = 'bi bi-chevron-right';
                }
            }
        });
    });

    // Save hierarchy functionality
    const saveBtn = document.getElementById('saveHierarchy');
    saveBtn.addEventListener('click', function() {
        const originalHtml = saveBtn.innerHTML;

        // Show loading state
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';

        // Show loading on chart
        document.getElementById('chartLoading').classList.remove('d-none');

        // Build hierarchy data
        const hierarchy = [];

        function traverseList(list, parentId = null) {
            const items = list.children;
            Array.from(items).forEach((item, index) => {
                const id = item.dataset.id;
                hierarchy.push({
                    id: id,
                    parent_id: parentId,
                    order: index
                });

                const children = item.querySelector('ul');
                if (children) {
                    traverseList(children, id);
                }
            });
        }

        traverseList(hierarchyList);

        // Send AJAX request
        fetch('{{ route("designations.save-hierarchy") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ hierarchy: hierarchy })
        })
        .then(async response => {
            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Failed to save hierarchy');
            }

            return data;
        })
        .then(data => {
            // Show success message
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: data.message || 'Hierarchy saved successfully',
                timer: 2000,
                showConfirmButton: false,
                background: '#f8f9fa',
                position: 'top-end'
            });

            // Update chart with new data
            updateOrganizationalChart();

            // Update last saved time
            document.querySelector('.card-footer .small .fw-medium').textContent =
                new Date().toLocaleDateString('en-US', {
                    month: 'short',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: error.message || 'Failed to save hierarchy. Please try again.',
                confirmButtonColor: '#0d6efd'
            });
        })
        .finally(() => {
            // Reset button state
            setTimeout(() => {
                saveBtn.disabled = false;
                saveBtn.innerHTML = originalHtml;
                document.getElementById('chartLoading').classList.add('d-none');
            }, 1000);
        });
    });

    // Reset hierarchy
    document.getElementById('resetHierarchy').addEventListener('click', function() {
        Swal.fire({
            title: 'Reset Changes?',
            text: 'This will discard all unsaved changes and restore the last saved state.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#0d6efd',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, reset',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                hierarchyList.innerHTML = originalHierarchy;
                updateHierarchyStatus();

                Swal.fire({
                    icon: 'success',
                    title: 'Reset!',
                    text: 'Hierarchy has been reset to last saved state',
                    timer: 1500,
                    showConfirmButton: false
                });
            }
        });
    });

    // Fullscreen chart
    document.getElementById('fullscreenChart').addEventListener('click', function() {
        const chartContainer = document.querySelector('.col-xl-6 .card');
        chartContainer.classList.toggle('fullscreen');

        if (chartContainer.classList.contains('fullscreen')) {
            this.innerHTML = '<i class="bi bi-fullscreen-exit"></i>';
            chart.redraw();
        } else {
            this.innerHTML = '<i class="bi bi-fullscreen"></i>';
            chart.redraw();
        }
    });

    // Initialize organizational chart
    let chart;
    function initOrganizationalChart() {
        chart = JSC.chart('chartDiv', {
            debug: false,
            type: 'organizational',
            palette: ['#0d6efd', '#198754', '#6f42c1', '#fd7e14', '#20c997', '#6610f2'],
            defaultSeries: {
                shape: {
                    outline: { width: 1, color: 'white' },
                    fill: '#0d6efd'
                },
                label: {
                    style: { fontSize: 12, color: 'white', fontWeight: '600' },
                    verticalAlign: 'middle',
                    autoWrap: true,
                    maxWidth: 120,
                    lineSpacing: 1.2
                }
            },
            title: {
                label: {
                    text: 'Organizational Structure',
                    style: { fontSize: 16, color: '#495057' }
                },
                margin: [0, 0, 10, 0]
            },
            legend: {
                visible: false
            },
            xAxis: {
                visible: false
            },
            yAxis: {
                visible: false
            },
            defaultPoint: {
                tooltip: '<b>%name</b><br>Level: %level<br>Employees: %employees',
                label_text: '%name',
                hover_state: {
                    fill: '#0056b3',
                    outline: { width: 2, color: 'white' }
                }
            },
            series: [{
                name: 'Designations',
                points: buildChartPoints()
            }]
        });
    }

    // Build chart points from hierarchy
    function buildChartPoints() {
        // This should be populated with actual designation data
        // For now, using sample data
        return [
            { id: '1', parent: null, name: 'CEO', level: 'L0', employees: 5 },
            { id: '2', parent: '1', name: 'CTO', level: 'L1', employees: 3 },
            { id: '3', parent: '1', name: 'CFO', level: 'L1', employees: 2 },
            { id: '4', parent: '2', name: 'Senior Developer', level: 'L2', employees: 8 },
            { id: '5', parent: '2', name: 'UX Designer', level: 'L2', employees: 4 },
            { id: '6', parent: '3', name: 'Account Manager', level: 'L2', employees: 6 }
        ];
    }

    // Update chart with current hierarchy
    function updateOrganizationalChart() {
        // In a real implementation, you would fetch updated data from the server
        // For now, we'll just simulate an update
        chart.series[0].points = buildChartPoints();
        chart.update();
    }

    // Chart zoom controls
    document.getElementById('zoomIn').addEventListener('click', () => chart.zoom(1.2));
    document.getElementById('zoomOut').addEventListener('click', () => chart.zoom(0.8));
    document.getElementById('resetZoom').addEventListener('click', () => chart.zoom('reset'));

    // Export chart functionality
    document.getElementById('exportChartBtn').addEventListener('click', function(e) {
        e.preventDefault();
        chart.export({
            format: 'png',
            width: 1920,
            height: 1080,
            download: true,
            filename: 'organizational-chart-' + new Date().toISOString().split('T')[0]
        });
    });

    // Print chart
    document.getElementById('printChartBtn').addEventListener('click', function(e) {
        e.preventDefault();
        const printWindow = window.open('', '_blank');
        printWindow.document.write(`
            <html>
                <head>
                    <title>Organizational Chart</title>
                    <style>
                        body { margin: 0; padding: 20px; font-family: Arial, sans-serif; }
                        .print-header { text-align: center; margin-bottom: 30px; }
                        .print-header h1 { margin: 0; color: #2c3e50; }
                        .print-header p { color: #7f8c8d; margin-top: 5px; }
                        .print-date { text-align: right; color: #95a5a6; margin-bottom: 20px; }
                    </style>
                </head>
                <body>
                    <div class="print-header">
                        <h1>Organizational Chart</h1>
                        <p>Generated on ${new Date().toLocaleDateString()}</p>
                    </div>
                    <div class="print-date">
                        ${new Date().toLocaleString()}
                    </div>
                    <img src="${chart.export({ format: 'png', width: 1000, height: 700 }).dataUrl}"
                         style="max-width: 100%; height: auto;">
                </body>
            </html>
        `);
        printWindow.document.close();
        printWindow.focus();
        setTimeout(() => printWindow.print(), 500);
    });

    // Update hierarchy status
    function updateHierarchyStatus() {
        const items = document.querySelectorAll('.designation-item').length;
        const levels = new Set(Array.from(document.querySelectorAll('.designation-item'))
            .map(el => el.dataset.level)).size;

        // Update status badges
        document.querySelectorAll('.badge')[1].innerHTML =
            `<i class="bi bi-diagram-3 me-1"></i>${items} Positions`;
        document.querySelectorAll('.badge')[2].innerHTML =
            `<i class="bi bi-layers me-1"></i>${levels} Levels`;
    }

    // Initialize everything
    initOrganizationalChart();
    updateHierarchyStatus();
});
</script>
@endpush
@endsection
