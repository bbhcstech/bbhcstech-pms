@extends('admin.layout.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Deals Management</h4>
                        <div class="d-flex gap-2 align-items-center">
                            <!-- Filter Button -->
                            <button type="button" class="btn btn-outline-secondary" id="filterToggleBtn">
                                <i class="fas fa-filter"></i> Filter
                            </button>

                            <!-- Show Entries -->
                            <div class="d-flex align-items-center ms-2">
                                <label class="me-2 mb-0">Show</label>
                                <select class="form-select form-select-sm" id="showEntries" style="width: auto;">
                                    <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                                    <option value="25" {{ request('per_page', 10) == 25 ? 'selected' : '' }}>25</option>
                                    <option value="50" {{ request('per_page', 10) == 50 ? 'selected' : '' }}>50</option>
                                    <option value="100" {{ request('per_page', 10) == 100 ? 'selected' : '' }}>100</option>
                                </select>
                                <label class="ms-2 mb-0">entries</label>
                            </div>

                            <!-- Other Buttons -->
                            <button type="button" class="btn btn-outline-primary" onclick="toggleView()">
                                <i class="fas fa-exchange-alt"></i> Toggle View
                            </button>
                            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#importModal">
                                <i class="fas fa-file-import"></i> Import
                            </button>
                            <a href="{{ route('admin.deals.export') }}" class="btn btn-info">
                                <i class="fas fa-file-export"></i> Export
                            </a>
                            <a href="{{ route('admin.deals.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Add Deal
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Success/Error Messages -->
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Filters Section -->
                    <form method="GET" action="{{ route('admin.deals.index') }}" id="filterForm">
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label class="form-label">Duration</label>
                                <div class="input-group">
                                    <input type="date" class="form-control" name="start_date"
                                           value="{{ request('start_date') }}">
                                    <span class="input-group-text">To</span>
                                    <input type="date" class="form-control" name="end_date"
                                           value="{{ request('end_date') }}">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label for="pipeline" class="form-label">Pipeline</label>
                                <select class="form-select" name="pipeline" id="pipeline">
                                    <option value="">All</option>
                                    <option value="Sales Pipeline" {{ request('pipeline') == 'Sales Pipeline' ? 'selected' : '' }}>
                                        Sales Pipeline
                                    </option>
                                    <option value="Marketing Pipeline" {{ request('pipeline') == 'Marketing Pipeline' ? 'selected' : '' }}>
                                        Marketing Pipeline
                                    </option>
                                    <option value="Other Pipeline" {{ request('pipeline') == 'Other Pipeline' ? 'selected' : '' }}>
                                        Other Pipeline
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="category" class="form-label">Category</label>
                                <select class="form-select" name="category" id="category">
                                    <option value="All">All</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->slug }}" {{ request('category') == $category->slug ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="product" class="form-label">Product</label>
                                <select class="form-select" name="product" id="product">
                                    <option value="All">All</option>
                                    <option value="Project Management Software" {{ request('product') == 'Project Management Software' ? 'selected' : '' }}>
                                        Project Management Software
                                    </option>
                                    <option value="Custom Website Development" {{ request('product') == 'Custom Website Development' ? 'selected' : '' }}>
                                        Custom Website Development
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="search" class="form-label">Search</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" name="search"
                                           placeholder="Start typing to search" value="{{ request('search') }}">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="view" id="viewType" value="{{ request('view', 'table') }}">
                        <input type="hidden" name="per_page" id="perPage" value="{{ request('per_page', 10) }}">
                    </form>

                    <!-- Bulk Actions Section -->
                    <div class="row mb-3" id="bulkActions" style="display: none;">
                        <div class="col-md-12">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <div class="d-flex align-items-center gap-3">
                                        <span id="selectedCount">0 items selected</span>
                                        <select class="form-select w-auto" id="bulkActionSelect">
                                            <option value="">No Action</option>
                                            <option value="change_stage">Change Stage</option>
                                            <option value="assign_agent">Add Deals Agents</option>
                                            <option value="delete">Delete</option>
                                        </select>
                                        <div id="actionFields" style="display: none;">
                                            <select class="form-select w-auto" id="stageSelect">
                                                @foreach($stages as $stage)
                                                    <option value="{{ $stage->id }}">{{ $stage->name }}</option>
                                                @endforeach
                                            </select>
                                            <select class="form-select w-auto" id="agentSelect">
                                                @foreach($agents as $agent)
                                                    <option value="{{ $agent->id }}">{{ $agent->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <button type="button" class="btn btn-primary" id="applyBulkAction">Apply</button>
                                        <button type="button" class="btn btn-secondary" id="clearSelection">Clear</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Kanban View -->
                    @if(request('view') == 'kanban' && isset($dealsByStage))
                    <div id="kanbanView">
                        <div class="kanban-board">
                            <div class="row">
                                @foreach($stages as $stage)
                                <div class="col-md">
                                    <div class="card">
                                        <div class="card-header" style="background-color: {{ $stage->color }}; color: white;">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h6 class="mb-0">{{ $stage->name }}</h6>
                                                    <small>{{ isset($dealsByStage[$stage->id]) ? $dealsByStage[$stage->id]->count() : 0 }} deals</small>
                                                </div>
                                                <button class="btn btn-sm btn-light" onclick="addDealToStage({{ $stage->id }})">
                                                    <i class="fas fa-plus"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="card-body kanban-column" data-stage-id="{{ $stage->id }}" style="min-height: 500px;">
                                            @if(isset($dealsByStage[$stage->id]))
                                            @foreach($dealsByStage[$stage->id] as $deal)
                                            <div class="card mb-2 deal-card" data-deal-id="{{ $deal->id }}" draggable="true">
                                                <div class="card-body p-2">
                                                    <h6 class="card-title mb-1">{{ $deal->deal_name }}</h6>
                                                    <p class="card-text mb-1 small">
                                                        <strong>Lead:</strong> {{ $deal->lead_name }}
                                                    </p>
                                                    <p class="card-text mb-1 small">
                                                        <strong>Value:</strong> ₹{{ number_format($deal->value, 2) }}
                                                    </p>
                                                    <p class="card-text mb-1 small">
                                                        <strong>Close:</strong> {{ $deal->close_date->format('d M') }}
                                                    </p>
                                                    <div class="d-flex justify-content-between align-items-center mt-2">
                                                        <span class="badge bg-secondary">{{ $deal->product ?? 'No Product' }}</span>
                                                        <div class="dropdown">
                                                            <button class="btn btn-sm btn-outline-primary dropdown-toggle"
                                                                    type="button" data-bs-toggle="dropdown">
                                                                Actions
                                                            </button>
                                                            <ul class="dropdown-menu">
                                                                <li>
                                                                    <a class="dropdown-item" href="{{ route('admin.deals.edit', $deal) }}">
                                                                        <i class="fas fa-edit"></i> Edit
                                                                    </a>
                                                                </li>
                                                                <li>
                                                                    <a class="dropdown-item" href="{{ route('admin.deals.show', $deal) }}">
                                                                        <i class="fas fa-eye"></i> View
                                                                    </a>
                                                                </li>
                                                                <li>
                                                                    <a class="dropdown-item" href="#" onclick="addFollowUp({{ $deal->id }})">
                                                                        <i class="fas fa-plus-circle"></i> Add Follow Up
                                                                    </a>
                                                                </li>
                                                                <li><hr class="dropdown-divider"></li>
                                                                <li>
                                                                    <form action="{{ route('admin.deals.destroy', $deal) }}" method="POST" class="d-inline">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                        <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Are you sure?')">
                                                                            <i class="fas fa-trash"></i> Delete
                                                                        </button>
                                                                    </form>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            @endforeach
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Table View -->
                    @if(request('view') != 'kanban' || !isset($dealsByStage))
                    <div id="tableView">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th width="50">
                                            <input type="checkbox" id="selectAll">
                                        </th>
                                        <th>Deal Name</th>
                                        <th>Lead Name</th>
                                        <th>Contact Details</th>
                                        <th>Value</th>
                                        <th>Close Date</th>
                                        <th>Next Follow Up</th>
                                        <th>Deal Agent</th>
                                        <th>Stage</th>
                                        <th>Deal Watcher</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($deals as $deal)
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="deal-checkbox" value="{{ $deal->id }}">
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.deals.show', $deal) }}" class="text-primary">
                                                {{ $deal->deal_name }}
                                            </a>
                                        </td>
                                        <td>{{ $deal->lead_name }}</td>
                                        <td>{{ $deal->contact_details }}</td>
                                        <td>₹{{ number_format($deal->value, 2) }}</td>
                                        <td>{{ $deal->close_date->format('d M Y') }}</td>
                                        <td>{{ $deal->next_follow_up ? $deal->next_follow_up->format('d M Y') : '-' }}</td>
                                        <td>{{ $deal->agent->name ?? '-' }}</td>
                                        <td>
                                            <span class="badge" style="background-color: {{ $deal->stage->color }}">
                                                {{ $deal->stage->name }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($deal->watchers && $deal->watchers->count() > 0)
                                                <div class="d-flex">
                                                    @foreach($deal->watchers->take(2) as $watcher)
                                                        <span class="badge bg-info me-1" title="{{ $watcher->name }}">
                                                            {{ substr($watcher->name, 0, 1) }}
                                                        </span>
                                                    @endforeach
                                                    @if($deal->watchers->count() > 2)
                                                        <span class="badge bg-secondary" title="+{{ $deal->watchers->count() - 2 }} more">
                                                            +{{ $deal->watchers->count() - 2 }}
                                                        </span>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-muted">No watchers</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="dropdown">
                                                <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle"
                                                        data-bs-toggle="dropdown" aria-expanded="false">
                                                    Actions
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('admin.deals.show', $deal) }}">
                                                            <i class="fas fa-eye"></i> View
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('admin.deals.edit', $deal) }}">
                                                            <i class="fas fa-edit"></i> Edit
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="#" onclick="addFollowUp({{ $deal->id }})">
                                                            <i class="fas fa-plus-circle"></i> Add Follow Up
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item change-stage-btn" href="#" data-deal-id="{{ $deal->id }}">
                                                            <i class="fas fa-sync"></i> Change Stage
                                                        </a>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <form action="{{ route('admin.deals.destroy', $deal) }}"
                                                              method="POST" class="d-inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="dropdown-item text-danger"
                                                                    onclick="return confirm('Are you sure you want to delete this deal?')">
                                                                <i class="fas fa-trash"></i> Delete
                                                            </button>
                                                        </form>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="11" class="text-center">No deals found.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div>
                                Showing {{ $deals->firstItem() }} to {{ $deals->lastItem() }}
                                of {{ $deals->total() }} entries
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <!-- Previous Button -->
                                @if($deals->onFirstPage())
                                    <button class="btn btn-outline-secondary btn-sm" disabled>
                                        <i class="fas fa-chevron-left"></i> Prev
                                    </button>
                                @else
                                    <a href="{{ $deals->previousPageUrl() }}" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-chevron-left"></i> Prev
                                    </a>
                                @endif

                                <!-- Page Numbers -->
                                <div class="btn-group">
                                    @foreach(range(1, min(5, $deals->lastPage())) as $page)
                                        @if($page == $deals->currentPage())
                                            <button class="btn btn-primary btn-sm">{{ $page }}</button>
                                        @else
                                            <a href="{{ $deals->url($page) }}" class="btn btn-outline-secondary btn-sm">{{ $page }}</a>
                                        @endif
                                    @endforeach
                                    @if($deals->lastPage() > 5)
                                        <button class="btn btn-outline-secondary btn-sm disabled">...</button>
                                        <a href="{{ $deals->url($deals->lastPage()) }}" class="btn btn-outline-secondary btn-sm">{{ $deals->lastPage() }}</a>
                                    @endif
                                </div>

                                <!-- Next Button -->
                                @if($deals->hasMorePages())
                                    <a href="{{ $deals->nextPageUrl() }}" class="btn btn-outline-primary btn-sm">
                                        Next <i class="fas fa-chevron-right"></i>
                                    </a>
                                @else
                                    <button class="btn btn-outline-secondary btn-sm" disabled>
                                        Next <i class="fas fa-chevron-right"></i>
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filter Sidebar -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="filterSidebar" aria-labelledby="filterSidebarLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="filterSidebarLabel">Filter Deals</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body">
        <form id="sidebarFilterForm" method="GET" action="{{ route('admin.deals.index') }}">
            <!-- Date Filters -->
            <div class="mb-4">
                <h6 class="mb-3 border-bottom pb-2">Date Filters</h6>
                <div class="mb-3">
                    <label class="form-label">Created Date</label>
                    <div class="input-group">
                        <input type="date" class="form-control" name="created_from"
                               value="{{ request('created_from') }}">
                        <span class="input-group-text">To</span>
                        <input type="date" class="form-control" name="created_to"
                               value="{{ request('created_to') }}">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Updated Date</label>
                    <div class="input-group">
                        <input type="date" class="form-control" name="updated_from"
                               value="{{ request('updated_from') }}">
                        <span class="input-group-text">To</span>
                        <input type="date" class="form-control" name="updated_to"
                               value="{{ request('updated_to') }}">
                    </div>
                </div>
            </div>

            <!-- Deal Value Range -->
            <div class="mb-4">
                <h6 class="mb-3 border-bottom pb-2">Deal Value</h6>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Min Value (₹)</label>
                        <input type="number" class="form-control" name="min_value"
                               value="{{ request('min_value') }}" placeholder="0">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Max Value (₹)</label>
                        <input type="number" class="form-control" name="max_value"
                               value="{{ request('max_value') }}" placeholder="1000000">
                    </div>
                </div>
            </div>

            <!-- Deal Stage -->
            <div class="mb-4">
                <h6 class="mb-3 border-bottom pb-2">Deal Stage</h6>
                <div class="form-group">
                    @foreach([
                        'generated' => 'Generated',
                        'qualified' => 'Qualified',
                        'initial_contact' => 'Initial Contact',
                        'schedule_appointment' => 'Schedule Appointment',
                        'proposal_sent' => 'Proposal Sent',
                        'win' => 'Win',
                        'lost' => 'Lost'
                    ] as $value => $label)
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="stages[]"
                               value="{{ $value }}" id="stage_{{ $value }}"
                               {{ in_array($value, (array)request('stages', [])) ? 'checked' : '' }}>
                        <label class="form-check-label" for="stage_{{ $value }}">
                            {{ $label }}
                        </label>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Agent -->
            <div class="mb-4">
                <h6 class="mb-3 border-bottom pb-2">Agent</h6>
                <select class="form-select" name="agent_id">
                    <option value="">All Agents</option>
                    @foreach($agents as $agent)
                        <option value="{{ $agent->id }}" {{ request('agent_id') == $agent->id ? 'selected' : '' }}>
                            {{ $agent->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Deal Watcher -->
            <div class="mb-4">
                <h6 class="mb-3 border-bottom pb-2">Deal Watcher</h6>
                <select class="form-select" name="watcher_id">
                    <option value="">All Watchers</option>
                    @foreach($agents as $watcher)
                        <option value="{{ $watcher->id }}" {{ request('watcher_id') == $watcher->id ? 'selected' : '' }}>
                            {{ $watcher->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Hidden Fields -->
            <input type="hidden" name="view" value="{{ request('view', 'table') }}">
            <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">

            <!-- Action Buttons -->
            <div class="d-grid gap-2 mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-filter"></i> Apply Filters
                </button>
                <button type="button" class="btn btn-outline-secondary" id="clearFiltersBtn">
                    <i class="fas fa-times"></i> Clear Filters
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.deals.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Import Deals</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="importFile" class="form-label">Choose CSV File</label>
                        <input type="file" class="form-control" name="file" id="importFile" accept=".csv" required>
                        <div class="form-text">
                            Download <a href="{{ route('admin.deals.export') }}?template=true">template</a> for correct format
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Import</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Change Stage Modal -->
<div class="modal fade" id="changeStageModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="changeStageForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Change Stage</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="deal_id" id="modalDealId">
                    <div class="mb-3">
                        <label for="stage_id" class="form-label">Select Stage</label>
                        <select class="form-select" name="stage_id" id="stage_id" required>
                            @foreach($stages as $stage)
                                <option value="{{ $stage->id }}">{{ $stage->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Follow Up Modal -->
<div class="modal fade" id="addFollowUpModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="addFollowUpForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add Follow Up</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="deal_id" id="followUpDealId">
                    <div class="mb-3">
                        <label for="follow_up_date" class="form-label">Follow Up Date *</label>
                        <input type="datetime-local" class="form-control" name="follow_up_date" id="follow_up_date" required>
                    </div>
                    <div class="mb-3">
                        <label for="follow_up_notes" class="form-label">Notes</label>
                        <textarea class="form-control" name="follow_up_notes" id="follow_up_notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Follow Up</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Deal Modal -->
<div class="modal fade" id="addDealModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="addDealForm" action="{{ route('admin.deals.store') }}" method="POST">
                @csrf
                <input type="hidden" name="deal_stage_id" id="modalStageId">
                <div class="modal-header">
                    <h5 class="modal-title">Add Deal to Stage</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="modalDealName" class="form-label">Deal Name *</label>
                        <input type="text" class="form-control" id="modalDealName" name="deal_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="modalLeadName" class="form-label">Lead Name *</label>
                        <input type="text" class="form-control" id="modalLeadName" name="lead_name" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Deal</button>
                </div>
            </form>
        </div>
</div>
@endsection

@push('styles')
<style>
    /* Custom styles for better UI */
    .offcanvas {
        width: 400px !important;
    }

    .deal-card {
        cursor: move;
        transition: all 0.3s ease;
    }

    .deal-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }

    .kanban-column {
        transition: background-color 0.3s ease;
    }

    .dropdown-menu {
        min-width: 180px;
    }

    .badge {
        font-size: 0.75em;
    }

    .table th {
        white-space: nowrap;
    }
</style>
@endpush

@push('scripts')
<script>
    // CSRF token for AJAX requests
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // Toggle Filter Sidebar
    document.getElementById('filterToggleBtn')?.addEventListener('click', function() {
        const filterSidebar = new bootstrap.Offcanvas(document.getElementById('filterSidebar'));
        filterSidebar.show();
    });

    // Show Entries functionality
    document.getElementById('showEntries')?.addEventListener('change', function() {
        document.getElementById('perPage').value = this.value;
        document.getElementById('filterForm').submit();
    });

    // Clear Filters
    document.getElementById('clearFiltersBtn')?.addEventListener('click', function() {
        // Clear all form inputs in sidebar
        const form = document.getElementById('sidebarFilterForm');
        form.reset();

        // Remove query parameters and submit
        const url = new URL(window.location.href);
        url.search = '';
        window.location.href = url.toString();
    });

    // Toggle between Table and Kanban view
    function toggleView() {
        const currentView = document.getElementById('viewType').value;
        const newView = currentView === 'table' ? 'kanban' : 'table';
        document.getElementById('viewType').value = newView;
        document.getElementById('filterForm').submit();
    }

    // Filter form auto-submit on change
    document.querySelectorAll('#filterForm select, #filterForm input[type="date"]').forEach(element => {
        element.addEventListener('change', function() {
            document.getElementById('filterForm').submit();
        });
    });

    // Bulk selection functionality
    document.getElementById('selectAll')?.addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.deal-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateBulkActions();
    });

    document.querySelectorAll('.deal-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', updateBulkActions);
    });

    function updateBulkActions() {
        const selected = document.querySelectorAll('.deal-checkbox:checked');
        const count = selected.length;
        const bulkActionsDiv = document.getElementById('bulkActions');

        if (count > 0) {
            bulkActionsDiv.style.display = 'block';
            document.getElementById('selectedCount').textContent = count + ' items selected';
        } else {
            bulkActionsDiv.style.display = 'none';
        }
    }

    document.getElementById('clearSelection')?.addEventListener('click', function() {
        document.querySelectorAll('.deal-checkbox').forEach(checkbox => {
            checkbox.checked = false;
        });
        document.getElementById('selectAll').checked = false;
        updateBulkActions();
    });

    // Bulk action selection
    document.getElementById('bulkActionSelect')?.addEventListener('change', function() {
        const actionFields = document.getElementById('actionFields');
        if (actionFields) {
            actionFields.style.display = this.value ? 'block' : 'none';

            // Show relevant field
            const stageSelect = document.getElementById('stageSelect');
            const agentSelect = document.getElementById('agentSelect');

            if (this.value === 'change_stage') {
                stageSelect.style.display = 'block';
                agentSelect.style.display = 'none';
            } else if (this.value === 'assign_agent') {
                stageSelect.style.display = 'none';
                agentSelect.style.display = 'block';
            } else {
                stageSelect.style.display = 'none';
                agentSelect.style.display = 'none';
            }
        }
    });

    // Apply bulk action
    document.getElementById('applyBulkAction')?.addEventListener('click', function() {
        const action = document.getElementById('bulkActionSelect').value;
        const selectedIds = Array.from(document.querySelectorAll('.deal-checkbox:checked'))
            .map(cb => cb.value);

        if (!action || selectedIds.length === 0) {
            alert('Please select an action and at least one deal.');
            return;
        }

        const formData = new FormData();
        formData.append('_token', csrfToken);
        formData.append('action', action);
        formData.append('ids', JSON.stringify(selectedIds));

        if (action === 'change_stage') {
            const stageId = document.getElementById('stageSelect').value;
            if (!stageId) {
                alert('Please select a stage.');
                return;
            }
            formData.append('stage_id', stageId);
        } else if (action === 'assign_agent') {
            const agentId = document.getElementById('agentSelect').value;
            if (!agentId) {
                alert('Please select an agent.');
                return;
            }
            formData.append('agent_id', agentId);
        }

        // Show loading
        const applyBtn = document.getElementById('applyBulkAction');
        const originalText = applyBtn.innerHTML;
        applyBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
        applyBtn.disabled = true;

        fetch('{{ route("admin.deals.bulk.action") }}', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Show success message
                showToast('Success', data.message || 'Action completed successfully', 'success');
                // Reload after 1 second
                setTimeout(() => {
                    location.reload();
                }, 1000);
            } else {
                throw new Error(data.message || 'Action failed');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Error', error.message || 'Failed to perform action', 'error');
            // Reset button
            applyBtn.innerHTML = originalText;
            applyBtn.disabled = false;
        });
    });

    // Individual deal stage change
    document.querySelectorAll('.change-stage-btn').forEach(button => {
        button.addEventListener('click', function() {
            const dealId = this.getAttribute('data-deal-id');
            document.getElementById('modalDealId').value = dealId;

            const modal = new bootstrap.Modal(document.getElementById('changeStageModal'));
            modal.show();
        });
    });

    // Change stage form submission
    document.getElementById('changeStageForm')?.addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const dealId = formData.get('deal_id');

        // Show loading
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
        submitBtn.disabled = true;

        fetch(`/deals/${dealId}/update-stage`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Show success message
                showToast('Success', data.message || 'Stage updated successfully', 'success');
                // Close modal
                bootstrap.Modal.getInstance(document.getElementById('changeStageModal')).hide();
                // Reload after 1 second
                setTimeout(() => {
                    location.reload();
                }, 1000);
            } else {
                throw new Error(data.message || 'Failed to update stage');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Error', error.message || 'Failed to update stage', 'error');
            // Reset button
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
    });

    // Add Follow Up functionality
    function addFollowUp(dealId) {
        document.getElementById('followUpDealId').value = dealId;

        // Set default date to tomorrow
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        document.getElementById('follow_up_date').value = tomorrow.toISOString().slice(0, 16);

        const modal = new bootstrap.Modal(document.getElementById('addFollowUpModal'));
        modal.show();
    }

    // Add Follow Up form submission
    document.getElementById('addFollowUpForm')?.addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const dealId = formData.get('deal_id');

        // Show loading
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
        submitBtn.disabled = true;

        fetch(`/deals/${dealId}/add-follow-up`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Success', 'Follow up added successfully', 'success');
                bootstrap.Modal.getInstance(document.getElementById('addFollowUpModal')).hide();
                setTimeout(() => location.reload(), 1000);
            } else {
                throw new Error(data.message || 'Failed to add follow up');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Error', error.message || 'Failed to add follow up', 'error');
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
    });

    // Set today's date as default for date filters
    document.addEventListener('DOMContentLoaded', function() {
        const startDateInput = document.querySelector('input[name="start_date"]');
        const endDateInput = document.querySelector('input[name="end_date"]');

        if (startDateInput && !startDateInput.value) {
            const today = new Date().toISOString().split('T')[0];
            const oneMonthAgo = new Date();
            oneMonthAgo.setMonth(oneMonthAgo.getMonth() - 1);
            const oneMonthAgoFormatted = oneMonthAgo.toISOString().split('T')[0];

            startDateInput.value = oneMonthAgoFormatted;
        }

        if (endDateInput && !endDateInput.value) {
            const today = new Date().toISOString().split('T')[0];
            endDateInput.value = today;
        }
    });

    // Kanban drag and drop functionality
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize drag and drop for kanban view
        initKanbanDragDrop();
    });

    function initKanbanDragDrop() {
        const columns = document.querySelectorAll('.kanban-column');
        const dealCards = document.querySelectorAll('.deal-card');

        if (columns.length === 0) return;

        columns.forEach(column => {
            column.addEventListener('dragover', function(e) {
                e.preventDefault();
                this.style.backgroundColor = '#f8f9fa';
            });

            column.addEventListener('dragleave', function(e) {
                this.style.backgroundColor = '';
            });

            column.addEventListener('drop', function(e) {
                e.preventDefault();
                this.style.backgroundColor = '';

                const dealId = e.dataTransfer.getData('text/plain');
                const stageId = this.getAttribute('data-stage-id');

                if (dealId && stageId) {
                    updateDealStage(dealId, stageId);
                }
            });
        });

        dealCards.forEach(card => {
            card.addEventListener('dragstart', function(e) {
                e.dataTransfer.setData('text/plain', this.getAttribute('data-deal-id'));
                this.style.opacity = '0.5';
            });

            card.addEventListener('dragend', function(e) {
                this.style.opacity = '1';
            });
        });
    }

    function updateDealStage(dealId, stageId) {
        const formData = new FormData();
        formData.append('_token', csrfToken);
        formData.append('stage_id', stageId);

        fetch(`/deals/${dealId}/update-stage`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Success', 'Deal moved successfully', 'success');
                setTimeout(() => location.reload(), 500);
            } else {
                showToast('Error', 'Failed to move deal', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Error', 'Failed to move deal', 'error');
        });
    }

    function addDealToStage(stageId) {
        document.getElementById('modalStageId').value = stageId;
        const modal = new bootstrap.Modal(document.getElementById('addDealModal'));
        modal.show();
    }

    // Toast notification function
    function showToast(title, message, type = 'info') {
        // Create toast element
        const toastId = 'toast-' + Date.now();
        const toastHtml = `
            <div id="${toastId}" class="toast align-items-center text-bg-${type} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        <strong>${title}:</strong> ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        `;

        // Add to toast container
        let toastContainer = document.getElementById('toastContainer');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'toastContainer';
            toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';
            document.body.appendChild(toastContainer);
        }

        toastContainer.insertAdjacentHTML('beforeend', toastHtml);

        // Show toast
        const toastElement = document.getElementById(toastId);
        const toast = new bootstrap.Toast(toastElement);
        toast.show();

        // Remove toast after it hides
        toastElement.addEventListener('hidden.bs.toast', function () {
            this.remove();
        });
    }
</script>
@endpush
