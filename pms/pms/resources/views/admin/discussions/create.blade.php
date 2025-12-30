@extends('admin.layout.app')

@section('content')
<main id="main" class="main">
    <div class="container-fluid">
    
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
            {{-- Add more optional tabs here if needed --}}
        </ul>
  
        <h4>Create Discussion for {{ $project->name }}</h4>

        <form method="POST" action="{{ route('projects.discussions.store', $project->id) }}" enctype="multipart/form-data">
            @csrf

            <div class="mb-3">
            <label class="form-label">Category <span class="text-danger">*</span></label>
            <select name="discussion_category_id" class="form-control selectpicker" data-live-search="true" required>
                <option value="">Select</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}"
                        data-content="<span class='badge px-3 py-2' style='background-color: {{ $category->color }}'>{{ $category->name }}</span>">
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </div>





            <div class="mb-3">
                <label class="form-label">Title <span class="text-danger">*</span></label>
                <input type="text" name="title" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Reply <span class="text-danger">*</span></label>
                <textarea name="reply" class="form-control" rows="4" required></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Add File</label>
                <input type="file" name="file" class="form-control">
            </div>

            <button class="btn btn-success">Save</button>
            <a href="{{ route('projects.discussions.index', $project->id) }}" class="btn btn-secondary">Cancel</a>
        </form>
        
    
        

    </div>
</main>
@endsection

@push('js')


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
