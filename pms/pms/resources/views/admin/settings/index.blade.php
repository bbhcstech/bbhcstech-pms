@extends('layouts.admin')

@section('title', 'Settings Dashboard')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="fw-bold mb-0">Settings Dashboard</h4>
                    <p class="text-muted mb-0">Configure all application settings from here</p>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-secondary" onclick="location.reload()">
                        <i class="bx bx-refresh me-2"></i> Refresh
                    </button>
                    <a href="{{ route('dashboard') }}" class="btn btn-primary">
                        <i class="bx bx-home me-2"></i> Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Settings Cards Grid -->
    <div class="row g-4">
        @foreach($settingsGroups as $slug => $group)
        <div class="col-xl-3 col-lg-4 col-md-6">
            <a href="{{ route($group['route']) }}" class="card settings-group-card h-100 border-{{ $group['color'] }} shadow-sm">
                <div class="card-body text-center p-4">
                    <div class="avatar avatar-lg mb-3 mx-auto">
                        <div class="avatar-initial bg-{{ $group['color'] }}-subtle text-{{ $group['color'] }} rounded-circle p-3">
                            <i class="{{ $group['icon'] }} fs-4"></i>
                        </div>
                    </div>
                    <h5 class="card-title mb-2">{{ $group['name'] }}</h5>
                    <p class="card-text text-muted small mb-0">{{ $group['description'] }}</p>
                    <div class="mt-3">
                        <span class="badge bg-{{ $group['color'] }}">Configure</span>
                    </div>
                </div>
            </a>
        </div>
        @endforeach
    </div>

    <!-- Quick Stats -->
    <div class="row mt-5">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Settings Summary</h5>
                    <span class="badge bg-primary">Total: {{ count($settingsGroups) }} Categories</span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-md bg-primary-subtle text-primary me-3">
                                    <i class="bx bx-cog fs-4"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">System Settings</h6>
                                    <small class="text-muted">App, Security, Theme, etc.</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-md bg-success-subtle text-success me-3">
                                    <i class="bx bx-building fs-4"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">Business Settings</h6>
                                    <small class="text-muted">Company, Finance, Tax, etc.</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-md bg-info-subtle text-info me-3">
                                    <i class="bx bx-user fs-4"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">User Settings</h6>
                                    <small class="text-muted">Roles, Permissions, Profile, etc.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.settings-group-card {
    transition: all 0.3s ease;
    border-width: 2px !important;
}

.settings-group-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important;
    text-decoration: none;
}

.settings-group-card .card-body {
    padding: 2rem 1.5rem;
}

.avatar-initial {
    width: 70px;
    height: 70px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.bg-primary-subtle { background-color: #e3f2fd; }
.bg-success-subtle { background-color: #e8f5e9; }
.bg-info-subtle { background-color: #e1f5fe; }
.bg-warning-subtle { background-color: #fff3e0; }
.bg-danger-subtle { background-color: #ffebee; }
</style>
@endsection
