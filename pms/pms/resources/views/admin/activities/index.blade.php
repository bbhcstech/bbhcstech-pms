@extends('admin.layout.app')

@section('content')
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
    <h4>User Activities</h4>
    <ul class="list-group mb-4">
        @foreach($userActivities as $activity)
            <li class="list-group-item">
                [{{ $activity->created_at->format('d M Y H:i') }}] 
                User ID {{ $activity->user_id }} — {{ $activity->activity }}
            </li>
        @endforeach
    </ul>

    <h4>Project Activities</h4>
    <ul class="list-group mb-4">
        @foreach($projectActivities as $activity)
            <li class="list-group-item">
                [{{ $activity->created_at->format('d M Y H:i') }}] 
                Project ID {{ $activity->project_id }} — {{ $activity->activity }}
            </li>
        @endforeach
    </ul>

    <h4>Ticket Activities</h4>
    <ul class="list-group">
        @foreach($ticketActivities as $activity)
            <li class="list-group-item">
                [{{ $activity->created_at->format('d M Y H:i') }}]
                Ticket ID {{ $activity->ticket_id }} — 
                {{ $activity->type }}: {{ $activity->content }}
            </li>
        @endforeach
    </ul>
</div>
@endsection
@push('js')
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