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
            
            <li class="nav-item">
               <a class="nav-link" href="{{ route('projects.burndown', $project->id) }}">Burndown Chart</a>

            </li>
            
            <li class="nav-item">
               <a href="{{ route('admin.activities.project', $project->id) }}" >Activity</a>


            </li>
            
             <li class="nav-item">
               <a class="nav-link" href="{{ route('tickets.index', ['project_id' => $project->id]) }}">Ticket</a>


            </li>
            {{-- Add more optional tabs here if needed --}}
        </ul>

     

        {{-- Overview Widgets --}}
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <canvas id="pieChart" style="max-height: 250px;"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="mb-3 bg-light p-3 rounded">
                    <h6>Project Budget</h6>
                    <p><strong>₹{{ $project->budget ?? 0 }}</strong></p>

                    <h6>Hours Logged</h6>
                    <p><strong>{{ $project->hours_logged ?? 0 }}</strong></p>
                </div>

                <div class="d-flex justify-content-between bg-white p-3 shadow-sm rounded">
                    <div>
                        <p>Earnings</p>
                        <strong>₹{{ $project->earnings ?? '0.00' }}</strong>
                    </div>
                    <div>
                        <p>Expenses</p>
                        <strong>₹{{ $project->expenses ?? '0.00' }}</strong>
                    </div>
                    <div>
                        <p>Profit</p>
                        <strong>₹{{ $project->profit ?? '0.00' }}</strong>
                    </div>
                </div>
            </div>
        </div>

        {{-- Budget & Hours Chart --}}
        <div class="row mt-4">
            <div class="col-md-6">
                <canvas id="hoursChart" height="100"></canvas>
            </div>
            <div class="col-md-6">
                <canvas id="budgetChart" height="100"></canvas>
            </div>
        </div>

        {{-- Project Details --}}
        <div class="card mt-4">
            <div class="card-header"><strong>Project Details</strong></div>
            <div class="card-body">
                <p><strong>Client:</strong> {{ $project->client->name ?? 'N/A' }}</p>
                <p><strong>Project Code:</strong> {{ $project->project_code ?? 'N/A' }}</p>
                <p><strong>Start Date:</strong> {{ $project->start_date ?? 'N/A' }}</p>
                <p><strong>Deadline:</strong> {{ $project->deadline ?? 'N/A' }}</p>
                <p><strong>Status:</strong> {{ $project->status ?? 'N/A' }}</p>
                <p><strong>Description:</strong> {!! nl2br(e($project->description ?? '')) !!}</p>
            </div>
        </div>

        {{-- Members --}}
        <div class="card mt-4">
            <div class="card-header"><strong>Assigned Members</strong></div>
            <div class="card-body">
                @if($project->users && count($project->users))
                    <ul>
                        @foreach($project->users as $user)
                            <li>{{ $user->name }} ({{ $user->email }})</li>
                        @endforeach
                    </ul>
                @else
                    <p>No members assigned.</p>
                @endif
            </div>
        </div>
    </div>
</main>


 

    
@endsection


@push('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>


<script>
    const pieCtx = document.getElementById('pieChart').getContext('2d');
    new Chart(pieCtx, {
        type: 'pie',
        data: {
            labels: ['Used', 'Remaining'],
            datasets: [{
                data: [100, 0], // Example static data
                backgroundColor: ['#dc3545', '#f0f0f0'],
            }]
        },
        options: { responsive: true }
    });

    const hoursCtx = document.getElementById('hoursChart').getContext('2d');
    new Chart(hoursCtx, {
        type: 'bar',
        data: {
            labels: ['Planned', 'Actual'],
            datasets: [{
                label: 'Hours',
                data: [0, 0],
                backgroundColor: ['#28a745', '#dc3545']
            }]
        }
    });

    const budgetCtx = document.getElementById('budgetChart').getContext('2d');
    new Chart(budgetCtx, {
        type: 'bar',
        data: {
            labels: ['Planned', 'Actual'],
            datasets: [{
                label: 'Budget',
                data: [0, 0],
                backgroundColor: ['#28a745', '#dc3545']
            }]
        }
    });

    document.getElementById('toggle-more').addEventListener('click', function (e) {
        e.preventDefault();
        const moreTabs = document.getElementById('more-tabs');
        moreTabs.classList.toggle('d-none');
        this.innerHTML = moreTabs.classList.contains('d-none') ? 'More ▾' : 'Less ▴';
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

