@extends('admin.layout.app') {{-- or your main layout --}}

@section('content')

<div class="container mt-4">
<main id="main" class="main">

    <!-- Breadcrumb -->
    <div class="pagetitle">
        <h1>Leave Details</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('leaves.index') }}">Leaves</a></li>
                <li class="breadcrumb-item active">{{ $leave->user->name ?? 'N/A' }}</li>
            </ol>
        </nav>
    </div><!-- End Page Title -->

    <!-- Leave Info Card -->
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">{{ $leave->user->name ?? 'N/A' }}</h5>
            <p class="text-muted">{{ $leave->user->designation ?? '' }}</p>
            <p><strong>Employee ID:</strong> {{ $leave->user->employee_code ?? 'N/A' }}</p>
            <p><strong>Department:</strong> {{ $leave->user->department->dpt_name ?? 'N/A' }}</p>
            <hr>

            <div class="row mb-3">
                <div class="col-md-6">
                    <strong>Apply Date:</strong> {{ $leave->created_at->format('d-m-Y h:i a') }}
                </div>
                <div class="col-md-6">
                    <strong>Leave Date:</strong> {{ $leave->date ? \Carbon\Carbon::parse($leave->date)->format('d-m-Y') : 'N/A' }}
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <strong>Leave Type:</strong> {{ ucfirst($leave->type) }}
                </div>
                <div class="col-md-6">
                    <strong>Duration:</strong> {{ ucfirst($leave->duration) }}
                </div>
            </div>

            <div class="mb-3">
                <strong>Reason for absence:</strong><br>
                {{ $leave->reason ?? 'N/A' }}
            </div>

            <div class="mb-3">
                <strong>Status:</strong>
                @if($leave->status == 'approved')
                    <span class="badge bg-success">Approved</span>
                @elseif($leave->status == 'rejected')
                    <span class="badge bg-danger">Rejected</span>
                @else
                    <span class="badge bg-warning">Pending</span>
                @endif
            </div>

            <div class="mb-3">
                <strong>Files:</strong><br>
                @if($leave->files)
                    <a href="{{ asset('storage/'.$leave->files) }}" target="_blank">View File</a>
                @else
                    <span class="text-muted">No file uploaded.</span>
                @endif
            </div>
        </div>
    </div>
</main>
</div>
@endsection
