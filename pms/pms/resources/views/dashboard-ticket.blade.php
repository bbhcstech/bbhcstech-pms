@extends('admin.layout.app')

@section('title', 'Ticket Dashboard')

@section('content')

@php
    $startDateFormatted = \Carbon\Carbon::parse($startDate)->format('Y-m-d');
    $endDateFormatted = \Carbon\Carbon::parse($endDate)->format('Y-m-d');
@endphp

<style>
    .small-chart {
        height: 200px !important;
        max-height: 200px !important;
    }
</style>
<div class="container-fluid py-4">
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
            <h3 class="fw-bold mb-0">Ticket Dashboard</h3>
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

    <!-- Stats -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm text-center">
                <div class="card-body">
                    <h6>Tickets</h6>
                    <span class="text-danger">{{ $unresolved }}</span> Unresolved /
                    <span class="text-success">{{ $resolved }}</span> Resolved
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm text-center">
                <div class="card-body">
                    <h6>Total Unassigned Ticket</h6>
                    <span>{{ $unassigned }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-header"><h6 class="mb-0">Type Wise Ticket</h6></div>
                <div class="card-body p-3">
                    <canvas id="typeWiseChart" class="small-chart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-header"><h6 class="mb-0">Status Wise Ticket</h6></div>
                <div class="card-body p-3 text-center">
                    @if(empty($statusWiseData))
                        <p class="text-muted">- Not enough data -</p>
                    @else
                        <canvas id="statusWiseChart" class="small-chart"></canvas>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Channel and Open Tickets -->
    <div class="row">
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-header"><h6 class="mb-0">Channel Wise Ticket</h6></div>
                <div class="card-body p-3 text-center">
                    @if(empty($channelWiseData))
                        <p class="text-muted">- Not enough data -</p>
                    @else
                        <canvas id="channelWiseChart"></canvas>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-header"><h6 class="mb-0">Open Tickets</h6></div>
                <div class="card-body p-3">
                    @forelse($openTickets as $ticket)
                        <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                            <div>
                                <strong>{{ $ticket->subject }}</strong><br>
                                <small>{{ $ticket->employee->name ?? 'N/A' }}</small>
                            </div>
                            <div class="text-end">
                                <small>{{ \Carbon\Carbon::parse($ticket->created_at)->format('d-m-Y') }}</small><br>
                                <span class="badge bg-warning text-dark">{{ ucfirst($ticket->priority) }}</span>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted text-center">No open tickets.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const typeCtx = document.getElementById('typeWiseChart');
    new Chart(typeCtx, {
        type: 'pie',
        data: {
            labels: {!! json_encode(array_keys($typeWiseData)) !!},
            datasets: [{
                data: {!! json_encode(array_values($typeWiseData)) !!},
                backgroundColor: ['#007bff', '#28a745', '#dc3545', '#ffc107']
            }]
        }
    });

    const statusCtx = document.getElementById('statusWiseChart');
    @if(!empty($statusWiseData))
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode(array_keys($statusWiseData)) !!},
            datasets: [{
                data: {!! json_encode(array_values($statusWiseData)) !!},
                backgroundColor: ['#17a2b8', '#6f42c1', '#fd7e14', '#20c997']
            }]
        }
    });
    @endif

    const channelCtx = document.getElementById('channelWiseChart');
    @if(!empty($channelWiseData))
    new Chart(channelCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode(array_keys($channelWiseData)) !!},
            datasets: [{
                label: 'Tickets',
                data: {!! json_encode(array_values($channelWiseData)) !!},
                backgroundColor: '#6c757d'
            }]
        }
    });
    @endif
</script>
@endpush
