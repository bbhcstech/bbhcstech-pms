@extends('admin.layout.app')


@section('content')
@php
    $startDateFormatted = \Carbon\Carbon::parse($startDate)->format('Y-m-d');
    $endDateFormatted = \Carbon\Carbon::parse($endDate)->format('Y-m-d');
@endphp
<div class="container-fluid">
    
    <!-- Sub-navigation pills -->
     <ul class="nav nav-pills nav-fill mb-4 shadow-sm rounded border" id="dashboardTabs" role="tablist" style="background-color: #f8f9fa;">
    <li class="nav-item" role="presentation">
        <a class="nav-link fw-bold text-dark py-3 {{ request('tab') === 'project' ? 'active' : '' }}"
           href="{{ route('dashboard', ['tab' => 'project']) }}">
           <i class="bx bx-folder me-1"></i> Overview
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link fw-bold text-dark py-3 {{ Route::currentRouteName() === 'dashproject' ? 'active' : '' }}"
           href="{{ route('dashproject') }}">
           <i class="bx bx-folder me-1"></i> Project
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link fw-bold text-dark py-3 {{ Route::currentRouteName() === 'dashboard.client' ? 'active' : '' }}"
           href="{{ route('dashboard.client') }}">
           <i class="bx bx-folder me-1"></i> Clients
        </a>
    </li>

    <li class="nav-item" role="presentation">
        <a class="nav-link fw-bold text-dark py-3 {{ Route::currentRouteName() === 'hr.dashboard' ? 'active' : '' }}"
           href="{{ route('hr.dashboard') }}">
           <i class="bx bx-folder me-1"></i> HR
        </a>
    </li>

    <li class="nav-item" role="presentation">
        <a class="nav-link fw-bold text-dark py-3 {{ Route::currentRouteName() === 'dashboard.ticket' ? 'active' : '' }}"
           href="{{ route('dashboard.ticket') }}">
           <i class="bx bx-folder me-1"></i> Ticket
        </a>
    </li>
</ul>

  
      <!-- Heading and Date Filter on Same Line -->
            <div class="row align-items-center mb-4">
                <!-- Left: Page Title -->
                <div class="col-md-6 col-12 mb-2 mb-md-0">
                    <h3 class="fw-bold mb-0">Client Dashboard</h3>
                </div>
            
                <!-- Right: Date Filter -->
                <div class="col-md-6 col-12 text-md-end">
                    <form method="GET" class="d-flex align-items-center gap-2">
                        <label class="mb-0 fw-semibold">Date Range:</label>
                        <input type="date" name="start_date" class="form-control form-control-sm" value="{{ $startDateFormatted }}">
                        <span class="fw-bold">to</span>
                        <input type="date" name="end_date" class="form-control form-control-sm" value="{{ $endDateFormatted }}">
                        <button type="submit" class="btn btn-sm btn-primary">Filter</button>
                    </form>
                </div>
            </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h6 class="mb-1">Total Clients</h6>
                    <h4 class="fw-bold">{{ $totalClients }}</h4>
                </div>
            </div>
        </div>
        

       
    </div>
</div>
@endsection
