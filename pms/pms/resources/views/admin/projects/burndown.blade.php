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
               <a class="nav-link" href="{{ route('tickets.index') }}">Ticket</a>


            </li>
            {{-- Add more optional tabs here if needed --}}
        </ul>
        
        
    <h4>Burndown Chart</h4>
    <form method="GET">
        <div class="row mb-3">
            <div class="col-md-3">
                <label>Start Date</label>
                <input type="date" name="start_date" class="form-control" value="{{ request('start_date') ?? $start->format('Y-m-d') }}">
            </div>
            <div class="col-md-3">
                <label>End Date</label>
                <input type="date" name="end_date" class="form-control" value="{{ request('end_date') ?? $end->format('Y-m-d') }}">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary">Filter</button>
            </div>
        </div>
    </form>

   <div id="burndownChart" style="height: 400px;"></div>
</div>
@endsection

@section('scripts')

<pre>
Labels: @json($labels)
Actual: @json($actual)
Ideal: @json($ideal)
</pre>

<script>
    var options = {
        chart: {
            type: 'line',
            height: 400,
            toolbar: { show: false }
        },
        series: [
            {
                name: 'Actual',
                data: @json($actual)
            },
            {
                name: 'Ideal',
                data: @json($ideal)
            }
        ],
        colors: ['#007bff', '#ccc'],
        stroke: {
            dashArray: [8, 0] // Actual dashed, Ideal solid
        },
        xaxis: {
            categories: @json($labels),
            title: { text: 'Date' }
        },
        yaxis: {
            title: { text: 'Remaining Tasks' },
            min: 0
        },
        legend: {
            position: 'top',
            horizontalAlign: 'center'
        }
    };

    var chart = new ApexCharts(document.querySelector("#burndownChart"), options);
    chart.render();
</script>

@endsection
