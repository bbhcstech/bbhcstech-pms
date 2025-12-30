@extends('admin.layout.app')

@section('content')
<div class="container mt-4">
    
    <br>
        <a href="{{ route('projects.index') }}" class="btn btn-secondary mb-3">← Back to Projects</a>

             {{-- Sub-navigation bar --}}
          <ul class="nav nav-tabs mb-4">
            <li class="nav-item">
                <a class="nav-link active" href="{{ route('projects.show', $project->id) }}">Overview</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('project-members.index', $project->id)}}">Members</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('project-files.index', $project->id)}}">Files</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('milestones.index', $project->id)}}">Milestones</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('projects.tasks.index', $project->id) }}">Tasks</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('projects.tasks.board', $project->id) }}">Task Board</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('projects.gantt', $project->id) }}">Gantt Chart</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('projects.timelogs.index', $project->id) }}">Timesheet</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('expenses.index', $project->id) }}">Expenses</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('projects.notes.index', $project->id) }}">Notes</a>
            </li>
        
            {{-- Toggle Button --}}
            <li class="nav-item">
                <a class="nav-link text-primary" href="#" id="toggle-more">More ▾</a>
            </li>
        </ul>
        
        {{-- Collapsible Extra Tabs --}}
        <ul class="nav nav-tabs mb-4 d-none" id="more-tabs">
             <li class="nav-item">
                <a class="nav-link" href="{{ route('projects.discussions.index', $project->id) }}" >Discussion</a>
                </li>
                
                <li class="nav-item">
                   <a class="nav-link" href="{{ route('projects.burndown', $project->id) }}">Burndown Chart</a>
    
                </li>
                
                <li class="nav-item">
                   <a class="nav-link" href="{{ route('admin.activities.project', $project->id) }}">Activity</a>
    
    
                </li>
                
                 <li class="nav-item">
               <a class="nav-link" href="{{ route('tickets.index', ['project_id' => $project->id]) }}">Ticket</a>


            </li>
            {{-- Add more optional tabs here if needed --}}
        </ul>


    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Project Milestones</h4>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createMilestoneModal">
            Create Milestone
        </button>
    </div>

    <!-- Milestone Table -->
    <div class="table-responsive">
          <table id="mileTable" class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>Title</th>
                    <th>Cost</th>
                    <th>Status</th>
                    <th>Budget</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Created</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($milestones as $milestone)
                    <tr>
                        <td>{{ $milestone->title }}</td>
                        <td>{{ $milestone->cost ?? '—' }}</td>
                        <td><span class="badge bg-secondary text-capitalize">{{ str_replace('_', ' ', $milestone->status) }}</span></td>
                        <td>{{ $milestone->add_to_budget ? 'Yes' : 'No' }}</td>
                        <td>{{ $milestone->start_date ?? '—' }}</td>
                        <td>{{ $milestone->end_date ?? '—' }}</td>
                        <td>{{ $milestone->created_at->format('d-m-Y') }}</td>
                        <td><form action="{{ route('milestones.destroy', $milestone->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this milestone?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger">Delete</button>
                        </form>
                        </td>
                    </tr>
                @empty
                    No milestones found.
                    
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Create Milestone Modal -->
<div class="modal fade" id="createMilestoneModal" tabindex="-1" aria-labelledby="milestoneModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('milestones.store') }}" method="POST">
            @csrf
            <input type="hidden" name="project_id" value="{{ $project->id }}">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="milestoneModalLabel">Create Milestone</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Milestone Title *</label>
                        <input type="text" name="title" class="form-control" required placeholder="Enter milestone title">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Milestone Cost</label>
                        <input type="number" name="cost" class="form-control" step="0.01" placeholder="e.g. 10000">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="pending">Pending</option>
                            <option value="in_progress">In Progress</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="add_to_budget" value="1" id="budgetSwitch">
                        <label class="form-check-label" for="budgetSwitch">Add Cost To Project Budget</label>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Milestone Summary *</label>
                        <textarea name="summary" class="form-control" rows="3" required placeholder="Enter milestone summary"></textarea>
                    </div>

                    @php
                    $today = \Carbon\Carbon::today()->format('Y-m-d');
                    @endphp
                    
                    <div class="mb-3">
                        <label class="form-label">Start Date</label>
                        <input type="date" name="start_date" class="form-control" value="{{ $today }}">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">End Date</label>
                        <input type="date" name="end_date" class="form-control" value="{{ $today }}">
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Milestone</button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('js')
<script>
  $(document).ready(function () {
    $('#mileTable').DataTable({
        dom: 'Bfrtip',
        buttons: ['copy', 'csv', 'excel', 'pdf', 'print'],
        responsive: true,
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],
        language: {
                search: "_INPUT_",
                searchPlaceholder: "Search tickets..."
        }
    });
  });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
    const toggleBtn = document.getElementById('toggle-more');
    const moreTabs = document.getElementById('more-tabs');

    if (toggleBtn && moreTabs) {
        toggleBtn.addEventListener('click', function (e) {
            e.preventDefault();
            moreTabs.classList.toggle('d-none');
            this.innerHTML = moreTabs.classList.contains('d-none') ? 'More ▾' : 'Less ▴';
        });
    }
});

</script>
@endpush


@endsection
