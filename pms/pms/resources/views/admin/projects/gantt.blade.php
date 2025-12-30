@extends('admin.layout.app')

@section('content')
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

        
    <h4 class="my-4 text-primary fw-bold">Project:{{ $project->name }} — Gantt Chart</h4>
    &nbsp;
    &nbsp;
    <div class="d-flex justify-content-between align-items-center mb-3">
            
                <a href="{{ route('tasks.create') }}" class="btn btn-primary">Add Task</a>
            </div>
    <!-- Top Timeline Header -->
    <div class="bg-light p-3 border rounded mb-3 shadow-sm">
        @php
            $min = $project->tasks->min('start_date');
            $max = $project->tasks->max('due_date');
            $startDate = \Carbon\Carbon::parse($min ?? now())->startOfWeek();
            $endDate = \Carbon\Carbon::parse($max ?? now())->endOfWeek();
        @endphp

        <div class="fw-bold mb-2">
            Timeline: {{ $startDate->format('d M, y') }} - {{ $endDate->format('d M, y') }}
        </div>

        <div class="d-flex flex-wrap gap-2">
            @for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay())
                <div class="text-center px-2" style="min-width: 70px;">
                    <div class="fw-semibold">{{ $date->format('d, D') }}</div>
                </div>
            @endfor
        </div>
    </div>

    <!-- Task & Gantt Grid -->
    <div class="row">
        <div class="col-md-3">
            <table class="table table-sm table-bordered shadow-sm">
                <thead class="table-light text-center">
                    <tr>
                        <th>Task name</th>
                        <th>Start time</th>
                        <th>Duration</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($project->tasks as $task)
                        @php
                            $start = \Carbon\Carbon::parse($task->start_date);
                            $end = \Carbon\Carbon::parse($task->due_date);
                            $duration = $start->diffInDays($end) + 1;
                        @endphp
                        <tr>
                            <td>{{ Str::limit($task->title, 20) }}</td>
                            <td>{{ $start->format('d M, y') }}</td>
                            <td class="text-center">{{ $duration }} day{{ $duration > 1 ? 's' : '' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="col-md-9">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div id="gantt-chart" style="height: 500px;"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/frappe-gantt@0.6.1/dist/frappe-gantt.css">
<style>
#gantt-chart {
    height: 500px;
    overflow-x: auto;
}
.gantt .bar-incomplete {
    fill: #ffcccb !important;
}
.gantt .bar-complete {
    fill: #90ee90 !important;
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/frappe-gantt@0.6.1/dist/frappe-gantt.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    fetch("{{ route('projects.gantt-tasks', $project->id) }}")
        .then(response => response.json())
        .then(tasks => {
            if (!tasks.length) {
                document.getElementById("gantt-chart").innerHTML = "<p class='text-danger'>No tasks to display.</p>";
                return;
            }

            new Gantt("#gantt-chart", tasks, {
                view_mode: 'Week',
                custom_popup_html: task => `
                    <div class="details-container">
                        <strong>${task.name}</strong><br>
                        <span><b>Start:</b> ${task.start}</span><br>
                        <span><b>End:</b> ${task.end}</span><br>
                        <span><b>Status:</b> ${task.progress === 100 ? '✅ Completed' : '🔄 In Progress'}</span>
                    </div>`
            });
        })
        .catch(error => {
            console.error("Gantt Chart Load Failed:", error);
            document.getElementById("gantt-chart").innerHTML = "<p class='text-danger'>Failed to load chart data.</p>";
        });
});
</script>

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
