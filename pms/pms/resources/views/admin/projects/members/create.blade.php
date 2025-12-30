@extends('admin.layout.app')

@section('content')
<main class="main">
    <div class="container">
        
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
               <a class="nav-link active" href="{{ route('projects.tasks.index', $project->id)}}">Tasks</a>
            </li>
             <li class="nav-item">
               <a class="nav-link" href="{{ route('projects.tasks.index', $project->id) }}">Task Board</a>
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
          <br>
        <h4>Add Member to Project: {{ $project->name }}</h4>

        <form action="{{ route('project-members.store', $project->id) }}" method="POST">
            @csrf
            <div class="mb-3">
                <label>User</label>
                <select name="user_id" class="form-select" required>
                    <option value="">Select user</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label>Hourly Rate</label>
                <input type="number" name="hourly_rate" class="form-control" step="0.01">
            </div>

            <div class="mb-3">
                <label for="role">Role</label>
                <select name="role" id="role" class="form-select" required>
                    <option value="">Select Role</option>
                    <option value="Project Member" {{ old('role') == 'Project Member' ? 'selected' : '' }}>Project Member</option>
                    <option value="Project Admin" {{ old('role') == 'Project Admin' ? 'selected' : '' }}>Project Admin</option>
                </select>
            </div>


            <button class="btn btn-success">Add Member</button>
            <a href="{{ route('project-members.index', $project->id) }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</main>
@endsection
@push('scripts')
<script>
    document.getElementById('toggle-more').addEventListener('click', function(e) {
        e.preventDefault();
        const moreTabs = document.getElementById('more-tabs');
        if (moreTabs.classList.contains('d-none')) {
            moreTabs.classList.remove('d-none');
            this.innerHTML = 'Less ▴';
        } else {
            moreTabs.classList.add('d-none');
            this.innerHTML = 'More ▾';
        }
    });
</script>
@endpush


