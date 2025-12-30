@extends('admin.layout.app')

@section('title', 'Designation Hierarchy')

@section('content')
<main class="main py-4">
    <div class="container-fluid">
        <!-- Header Section -->
        <div class="row mb-4 align-items-center">
            <div class="col">
                <h4 class="mb-0 fw-bold text-dark">Designation Hierarchy</h4>
                <p class="text-muted mb-0">Manage and visualize your organizational structure</p>
            </div>
            <div class="col-auto">
                <div class="d-flex align-items-center gap-3">
                    <!-- View Toggle -->
                    <div class="btn-group btn-group-sm" role="group" aria-label="View Options">
                        <a href="{{ route('designations.index') }}" class="btn btn-outline-secondary d-flex align-items-center" data-toggle="tooltip" title="Table View">
                            <i class="bi bi-list-ul me-1"></i> List
                        </a>
                        <a href="{{ route('designations.hierarchy') }}" class="btn btn-secondary d-flex align-items-center" data-toggle="tooltip" title="Hierarchy View">
                            <i class="bi bi-diagram-3 me-1"></i> Hierarchy
                        </a>
                    </div>

                    <!-- Add Designation Button -->
                    <a href="{{ route('designations.create') }}" class="btn btn-primary btn-sm d-flex align-items-center">
                        <i class="bi bi-plus-lg me-1"></i> Add Designation
                    </a>
                </div>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="row g-4">
            <!-- Left Panel: Drag & Drop Hierarchy -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="card-title mb-0 d-flex align-items-center">
                            <i class="bi bi-arrows-move text-primary me-2"></i>
                            Drag & Drop Hierarchy
                        </h5>
                        <p class="text-muted small mb-0">Drag designations to restructure the organizational hierarchy</p>
                    </div>
                    <div class="card-body p-3">
                        <!-- Info Alert -->
                        <div class="alert alert-info border-0 bg-light-info d-flex align-items-center mb-3 py-2" role="alert">
                            <i class="bi bi-info-circle-fill me-2"></i>
                            <div class="small">Drag items using the handle (<i class="bi bi-grip-vertical"></i>) to reorder</div>
                        </div>

                        <!-- Hierarchy List -->
                        <div class="border rounded bg-light p-3" style="min-height: 500px; max-height: 700px; overflow-y: auto;">
                            <ul id="hierarchyList" class="list-group list-group-flush border-0">
                                @foreach($designations->whereNull('parent_id') as $designation)
                                    @include('admin.designations.partials.designation-item', ['designation' => $designation])
                                @endforeach
                            </ul>

                            @if($designations->whereNull('parent_id')->isEmpty())
                                <div class="text-center py-5">
                                    <i class="bi bi-diagram-3 display-4 text-muted mb-3"></i>
                                    <h5 class="text-muted">No designations found</h5>
                                    <p class="text-muted small">Start by adding your first designation</p>
                                    <a href="{{ route('designations.create') }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-plus-lg me-1"></i> Add Designation
                                    </a>
                                </div>
                            @endif
                        </div>

                        <!-- Save Button -->
                        <div class="mt-4 text-end">
                            <button id="saveHierarchy" class="btn btn-primary px-4">
                                <i class="bi bi-check-circle me-1"></i> Save Hierarchy
                            </button>
                            <button type="button" class="btn btn-outline-secondary ms-2" id="resetHierarchy">
                                <i class="bi bi-arrow-clockwise me-1"></i> Reset
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Panel: Organizational Chart -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="card-title mb-0 d-flex align-items-center">
                            <i class="bi bi-diagram-3 text-primary me-2"></i>
                            Organizational Chart
                        </h5>
                        <p class="text-muted small mb-0">Visual representation of your designation hierarchy</p>
                    </div>
                    <div class="card-body p-0">
                        <div id="chartDiv" class="rounded-bottom" style="height: 650px; min-height: 500px;"></div>
                    </div>
                    <div class="card-footer bg-white border-top py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="small text-muted">
                                <i class="bi bi-lightbulb me-1"></i>
                                Chart updates automatically when hierarchy is saved
                            </div>
                            <div>
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="zoomIn">
                                    <i class="bi bi-zoom-in"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary ms-1" id="zoomOut">
                                    <i class="bi bi-zoom-out"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary ms-1" id="resetZoom">
                                    <i class="bi bi-fullscreen"></i>
                                </button>
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
    /* Custom styles for hierarchy list */
    #hierarchyList {
        cursor: move;
    }

    .drag-handle {
        cursor: grab;
        color: #6c757d;
        padding: 0 8px;
    }

    .drag-handle:hover {
        color: #0d6efd;
    }

    .list-group-item {
        border-left: 3px solid #0d6efd;
        margin-bottom: 5px;
        border-radius: 4px !important;
        transition: all 0.2s;
    }

    .list-group-item:hover {
        background-color: #f8f9fa;
        transform: translateX(2px);
    }

    /* Nested list styling */
    ul ul {
        margin-left: 25px;
        margin-top: 5px;
        border-left: 1px dashed #dee2e6;
        padding-left: 15px;
    }

    /* Card styling */
    .card {
        border-radius: 10px;
    }

    .card-header {
        border-radius: 10px 10px 0 0 !important;
        padding: 1rem 1.25rem;
    }

    /* Chart container */
    #chartDiv {
        background-color: #f8f9fa;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        #chartDiv {
            height: 400px !important;
        }
    }
</style>
@endpush

@push('js')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script src="https://code.jscharting.com/latest/jscharting.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Make hierarchy sortable
    var hierarchyList = document.getElementById('hierarchyList');

    if (hierarchyList) {
        new Sortable(hierarchyList, {
            group: 'nested',
            animation: 200,
            fallbackOnBody: true,
            swapThreshold: 0.65,
            handle: '.drag-handle',
            ghostClass: 'bg-light',
            chosenClass: 'bg-primary-light',
            dragClass: 'drag-class',
            onStart: function(evt) {
                evt.item.classList.add('dragging');
            },
            onEnd: function(evt) {
                evt.item.classList.remove('dragging');
                // Visual feedback
                evt.item.classList.add('bg-success-light');
                setTimeout(() => {
                    evt.item.classList.remove('bg-success-light');
                }, 500);
            }
        });
    }

    // Save hierarchy
    document.getElementById('saveHierarchy').addEventListener('click', function() {
        const saveBtn = this;
        const originalText = saveBtn.innerHTML;

        // Show loading state
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<i class="bi bi-arrow-repeat me-1 spin"></i> Saving...';

        let hierarchy = [];

        function traverse(list, parentId = null) {
            list.querySelectorAll('li').forEach((li, index) => {
                const id = li.dataset.id;
                hierarchy.push({ id: id, parent_id: parentId, order: index });
                const children = li.querySelector('ul');
                if(children) traverse(children, id);
            });
        }

        traverse(document.getElementById('hierarchyList'));

        fetch('{{ route("designations.save-hierarchy") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ hierarchy })
        })
        .then(res => {
            if (!res.ok) throw new Error('Network response was not ok');
            return res.json();
        })
        .then(data => {
            // Show success message
            showAlert('success', data.message || 'Hierarchy saved successfully!');

            // Refresh chart with new data (you might want to implement actual chart update)
            // For now, we'll just reload the chart with a small animation
            chart.series[0].points = [
                { id: 'ceo', parent: null, label_text: 'CEO' },
                { id: 'cto', parent: 'ceo', label_text: 'CTO' },
                { id: 'cfo', parent: 'ceo', label_text: 'CFO' },
                { id: 'dev', parent: 'cto', label_text: 'Senior Developer' },
                { id: 'designer', parent: 'cto', label_text: 'Graphics Designer' },
                { id: 'sales', parent: 'cfo', label_text: 'Sales Executive' }
            ];
            chart.update();

            // Visual feedback on the hierarchy
            document.querySelectorAll('#hierarchyList li').forEach(li => {
                li.classList.add('bg-success-light');
            });
            setTimeout(() => {
                document.querySelectorAll('#hierarchyList li').forEach(li => {
                    li.classList.remove('bg-success-light');
                });
            }, 1000);
        })
        .catch(err => {
            console.error('Error:', err);
            showAlert('danger', 'Failed to save hierarchy. Please try again.');
        })
        .finally(() => {
            // Reset button state
            setTimeout(() => {
                saveBtn.disabled = false;
                saveBtn.innerHTML = originalText;
            }, 1000);
        });
    });

    // Reset button functionality
    document.getElementById('resetHierarchy')?.addEventListener('click', function() {
        if (confirm('Are you sure you want to reset the hierarchy to its last saved state?')) {
            location.reload();
        }
    });

    // Chart zoom controls
    document.getElementById('zoomIn')?.addEventListener('click', function() {
        chart.zoom(1.2);
    });

    document.getElementById('zoomOut')?.addEventListener('click', function() {
        chart.zoom(0.8);
    });

    document.getElementById('resetZoom')?.addEventListener('click', function() {
        chart.zoom('reset');
    });

    // JSCharting organizational chart
    const chart = JSC.chart('chartDiv', {
        debug: false,
        type: 'organization horizontal',
        palette: ['#0d6efd', '#6f42c1', '#198754', '#fd7e14', '#20c997', '#6610f2'],
        defaultSeries: {
            shape: {
                outline: { width: 0 },
                fill: '#0d6efd'
            },
            label: {
                style: { fontSize: 14, color: 'white', fontWeight: 'bold' },
                verticalAlign: 'middle',
                autoWrap: false,
                maxWidth: 150
            }
        },
        legend: { template: '%icon %name' },
        title: { label: { text: 'Company Structure', style: { fontSize: 18 } } },
        toolbar: { visible: false },
        xAxis: { visible: false },
        yAxis: { visible: false },
        series: [{
            name: 'Designations',
            points: [
                { id: 'ceo', parent: null, name: 'CEO', label_text: 'CEO' },
                { id: 'cto', parent: 'ceo', name: 'CTO', label_text: 'CTO' },
                { id: 'cfo', parent: 'ceo', name: 'CFO', label_text: 'CFO' },
                { id: 'dev', parent: 'cto', name: 'Development', label_text: 'Senior Developer' },
                { id: 'designer', parent: 'cto', name: 'Design', label_text: 'Graphics Designer' },
                { id: 'sales', parent: 'cfo', name: 'Sales', label_text: 'Sales Executive' }
            ]
        }]
    });

    // Helper function to show alerts
    function showAlert(type, message) {
        // Remove any existing alerts
        const existingAlert = document.querySelector('.alert-dismissible.position-fixed');
        if (existingAlert) existingAlert.remove();

        // Create new alert
        const alert = document.createElement('div');
        alert.className = `alert alert-${type} alert-dismissible position-fixed top-3 end-3 shadow-lg`;
        alert.style.zIndex = '1050';
        alert.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        document.body.appendChild(alert);

        // Auto remove after 5 seconds
        setTimeout(() => {
            if (alert.parentNode) alert.parentNode.removeChild(alert);
        }, 5000);
    }

    // Add CSS for loading spinner
    const style = document.createElement('style');
    style.textContent = `
        .spin {
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        .bg-success-light {
            background-color: rgba(25, 135, 84, 0.1) !important;
        }
        .bg-primary-light {
            background-color: rgba(13, 110, 253, 0.1) !important;
        }
        .dragging {
            opacity: 0.7;
            transform: rotate(2deg);
        }
    `;
    document.head.appendChild(style);
});
</script>
@endpush
@endsection
