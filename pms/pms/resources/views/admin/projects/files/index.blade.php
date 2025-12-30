@extends('admin.layout.app')

@section('content')
<main id="main" class="main">
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
          <br>

        <h4>Upload File</h4>
        <form action="{{ route('project-files.store', $project->id) }}" method="POST" enctype="multipart/form-data" class="mb-4">
            @csrf
            <div class="row g-3">
                <div class="col-md-8">
                    <input type="file" name="file" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary">Upload</button>
                </div>
            </div>
        </form>

        <h5 class="mt-4">Uploaded Files</h5>
        <ul class="list-group">
            @forelse($files as $file)
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <a href="{{ asset($file->file_path) }}" target="_blank">{{ $file->filename }}</a>
                    
                    <form action="{{ route('project-files.destroy', [$project->id, $file->id]) }}" method="POST" onsubmit="return confirm('Delete this file?')">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-danger">Delete</button>
                    </form>
                </li>
            @empty
                <li class="list-group-item">No files uploaded yet.</li>
            @endforelse
        </ul>
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

