@extends('admin.layout.app')

@section('content')
<main id="main" class="main">
<div class="container">
    <br>
        <a href="{{ route('projects.index') }}" class="btn btn-secondary mb-3">← Back to Projects</a>
        <br>
    <h4 class="mb-4">TimeLog Details</h4>

    <div class="card p-4 shadow-sm">
        <div class="row mb-3">
            <div class="col-md-4">
                <strong>Start Time</strong><br>
                {{ \Carbon\Carbon::parse($log->start_time)->format('d-m-Y h:i A') }}
            </div>
            
            <div class="col-md-4">
                <strong>End Time</strong><br>
                {{ \Carbon\Carbon::parse($log->end_time)->format('d-m-Y h:i A') }}
            </div>
           <div class="col-md-4">
                    <strong>Total Hours</strong><br>
                    {{ gmdate('H:i:s', $log->total_hours * 3600) }}
                </div>

        </div>

        <div class="row mb-3">
            <div class="col-md-4"><strong>Earnings</strong><br>₹0.00</div>
            <div class="col-md-8"><strong>Memo</strong><br>{{ $log->memo }}</div>
        </div>

        <hr>

        <div class="row mb-3">
            <div class="col-md-6"><strong>Project</strong><br>{{ $log->project->name ?? '-' }}</div>
            <div class="col-md-6"><strong>Task</strong><br>{{ $log->task->title ?? '-' }}</div>
        </div>

        <hr>

        <div class="mb-3">
            <strong>Employee</strong><br>
            <div class="border p-3 bg-light rounded">
                {{ $log->user->name ?? '-' }} <br>
                <small>It's you</small><br>
                <span class="text-muted">{{ $log->user->designation ?? 'Employee' }}</span>
            </div>
        </div>

        <hr>

        <div class="mb-3">
            <strong>History</strong>
            <ul class="list-group">
                @php
                    $startTime = \Carbon\Carbon::parse($log->start_time);
                @endphp
                <li class="list-group-item">
                    <strong>Start Date Time:</strong> {{ $startTime->format('d-m-Y h:i A') }}
                </li>

                <li class="list-group-item">
                    <strong>Task Name:</strong> {{ $log->task->title ?? '-' }}
                </li>
                
                @php
                    $endTime = \Carbon\Carbon::parse($log->end_time);
                @endphp
                <li class="list-group-item">
                    <strong>End Date Time:</strong> {{ $endTime->format('d-m-Y h:i A') }}
                </li>
            </ul>
        </div>
    </div>
</div>
</main>
@endsection
