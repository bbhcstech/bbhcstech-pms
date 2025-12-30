
@extends('admin.layout.app')

@section('content')
<div class="container mt-4">

    <a href="{{ route('employees.index') }}" class="btn btn-outline-primary mb-4">
        <i class="fas fa-arrow-left me-1"></i> Back to Employee List
    </a>

    <div class="card shadow border-0">
        <div class="card-body row p-4">

            {{-- Profile Section --}}
            <div class="col-md-3 text-center border-end">
                @if(!empty($employee->profile_image))
                    <img src="{{ asset($employee->profile_image) }}" alt="Profile Image" class="rounded-circle img-thumbnail mb-3" width="150" height="150">
                @else
                    <img src="{{ asset('admin/assets/img/avatars/1.png') }}" alt="Default Image" class="rounded-circle img-thumbnail mb-3" width="150" height="150">
                @endif

                <h5 class="fw-bold mb-1">{{ $employee->name }}</h5>
                <p class="text-muted mb-0">{{ $employee->employeeDetail->designation->name ?? 'N/A' }}</p>
            </div>

            {{-- Details Section --}}
            <div class="col-md-9">
                <h5 class="mb-3 text-primary border-bottom pb-2">Employee Details</h5>
                <div class="row">

                    @php
                        $detail = $employee->employeeDetail;
                    @endphp

                    <div class="col-md-6 mb-3"><strong>Employee ID:</strong> {{ $detail->employee_id }}</div>
                    <div class="col-md-6 mb-3"><strong>Email:</strong> {{ $employee->email }}</div>
                    <div class="col-md-6 mb-3"><strong>Mobile:</strong> {{ $detail->mobile }}</div>
                    <div class="col-md-6 mb-3"><strong>Department:</strong> {{ $detail->department->dpt_name ?? 'N/A' }}</div>
                    <div class="col-md-6 mb-3"><strong>Joining Date:</strong> {{ \Carbon\Carbon::parse($detail->joining_date)->format('d-m-Y') }}</div>
                    <div class="col-md-6 mb-3"><strong>Date of Birth:</strong> {{ $detail->dob }}</div>
                    <div class="col-md-6 mb-3"><strong>Country:</strong> {{ $detail->country }}</div>
                    <div class="col-md-6 mb-3"><strong>Gender:</strong> {{ $detail->gender }}</div>
                    <div class="col-md-6 mb-3"><strong>Reporting To:</strong> {{ $detail->reportingTo->name ?? 'N/A' }}</div>
                    <div class="col-md-6 mb-3"><strong>Language:</strong> {{ $detail->language }}</div>
                    <div class="col-md-6 mb-3"><strong>Skills:</strong> {{ $detail->skills }}</div>
                    <div class="col-md-6 mb-3"><strong>Address:</strong> {{ $detail->address }}</div>
                    <div class="col-md-6 mb-3"><strong>Business Address:</strong> {{ $detail->business_address }}</div>
                    <div class="col-md-6 mb-3"><strong>Login Allowed:</strong> {{ $detail->login_allowed ? 'Yes' : 'No' }}</div>
                    <div class="col-md-6 mb-3"><strong>Email Notifications:</strong> {{ $detail->email_notifications ? 'Yes' : 'No' }}</div>
                    <div class="col-md-6 mb-3"><strong>Hourly Rate:</strong> {{ $detail->hourly_rate }}</div>
                    <div class="col-md-6 mb-3"><strong>Marital Status:</strong> {{ ucfirst($detail->marital_status) }}</div>
                    <div class="col-md-6 mb-3"><strong>Employment Type:</strong> {{ ucfirst(str_replace('_', ' ', $detail->employment_type)) }}</div>

                </div>
            </div>
        </div>
    </div>
</div>

<!-- Optional FontAwesome (for back icon) -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
@endsection
