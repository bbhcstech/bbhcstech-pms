@extends('admin.layout.app')

@section('title', 'Holiday Calendar')

@section('content')
<main class="main">
    <div class="container py-4">
        @php
            // Check if user is admin
            $isAdmin = auth()->user()->role === 'admin' || auth()->user()->is_admin == 1;
        @endphp

        {{-- Check if user is admin --}}
        @if(!$isAdmin)
            {{-- Employee হলে শুধু দেখাবে না --}}
            <div class="alert alert-danger text-center">
                <i class="bi bi-shield-lock fs-4"></i>
                <h5 class="mt-2">Access Denied</h5>
                <p class="mb-0">This page is only accessible by administrators.</p>
            </div>
        @else
            {{-- Admin হলে পুরো পেজ দেখাবে --}}
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold mb-0">Holiday Calendar (Admin View)</h4>

                <!-- Switch between List and Calendar View -->
                <div class="btn-group" role="group">
                    <a href="{{ route('holidays.index') }}"
                       class="btn btn-sm btn-outline-primary {{ request()->routeIs('holidays.index') ? 'active' : '' }}">
                        <i class="bi bi-list-ul"></i> Admin List View
                    </a>
                    <a href="{{ route('holidays.calendar') }}"
                       class="btn btn-sm btn-outline-primary {{ request()->routeIs('holidays.calendar') ? 'active' : '' }}">
                        <i class="bi bi-calendar"></i> Admin Calendar View
                    </a>
                </div>
            </div>

            {{-- Admin Action Buttons --}}
            <div class="d-flex gap-2 mb-4">
                <a href="{{ route('holidays.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Add Holiday
                </a>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#holidayModal">
                    <i class="bi bi-calendar-check"></i> Mark Default Holidays
                </button>
            </div>

            {{-- Filter Form --}}
            <form method="GET" action="{{ route('holidays.calendar') }}" class="row g-2 mb-4">
                <div class="col-md-3">
                    <select name="month" class="form-select">
                        <option value="">All Months</option>
                        @foreach(range(1, 12) as $m)
                            <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="year" class="form-select">
                        <option value="">All Years</option>
                        @foreach(range(date('Y') - 5, date('Y') + 2) as $y)
                            <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary">Filter</button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('holidays.calendar') }}" class="btn btn-secondary">Reset</a>
                </div>
            </form>

            {{-- Holiday Display --}}
            @if($holidays->count() > 0)
                <div class="card">
                    <div class="card-body">
                        @php
                            $monthYear = '';
                            if(request('month') && request('year')) {
                                $monthYear = \Carbon\Carbon::create(request('year'), request('month'))->format('F Y');
                            } elseif(request('year')) {
                                $monthYear = request('year');
                            } elseif(request('month')) {
                                $monthYear = \Carbon\Carbon::create()->month(request('month'))->format('F');
                            }
                        @endphp

                        @if($monthYear)
                            <h5 class="fw-bold mb-3">{{ $monthYear }}</h5>
                        @endif

                        {{-- Display all holidays with admin actions --}}
                        <div class="row">
                            @foreach($holidays as $holiday)
                                <div class="col-md-6 mb-3">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h6 class="mb-1">
                                                        <i class="bi bi-calendar-check text-primary"></i>
                                                        {{ $holiday->title }}
                                                    </h6>
                                                    <small class="text-muted">
                                                        <i class="bi bi-clock"></i>
                                                        {{ \Carbon\Carbon::parse($holiday->date)->format('l, d F Y') }}
                                                    </small>
                                                    @if($holiday->type === 'weekly_holiday')
                                                        <br><small class="badge bg-success">Weekly Holiday</small>
                                                    @endif
                                                </div>
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="badge bg-info">
                                                        {{ \Carbon\Carbon::parse($holiday->date)->format('D') }}
                                                    </span>
                                                    <div class="dropdown">
                                                        <button class="btn btn-sm btn-light" type="button"
                                                                data-bs-toggle="dropdown">
                                                            <i class="bi bi-three-dots-vertical"></i>
                                                        </button>
                                                        <ul class="dropdown-menu">
                                                            <li>
                                                                <a class="dropdown-item"
                                                                   href="{{ route('holidays.edit', $holiday->id) }}">
                                                                    <i class="bi bi-pencil-square me-2"></i> Edit
                                                                </a>
                                                            </li>
                                                            <li>
                                                                <form action="{{ route('holidays.destroy', $holiday->id) }}"
                                                                      method="POST"
                                                                      onsubmit="return confirm('Are you sure?');">
                                                                    @csrf @method('DELETE')
                                                                    <button class="dropdown-item text-danger" type="submit">
                                                                        <i class="bi bi-trash me-2"></i> Delete
                                                                    </button>
                                                                </form>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @else
                <div class="alert alert-info text-center">
                    <i class="bi bi-calendar-x fs-4"></i>
                    <h5 class="mt-2">No Holidays Found</h5>
                    <p class="mb-0">No holidays available for the selected filter.</p>
                </div>
            @endif

            {{-- Admin Modal --}}
            <!-- Unified Modal -->
            <div class="modal fade" id="holidayModal" tabindex="-1" aria-labelledby="holidayModalLabel" aria-hidden="true">
              <div class="modal-dialog modal-lg">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="holidayModalLabel">Mark Holiday</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <form id="save-mark-holiday-form" method="POST" action="{{ route('holidays.mark') }}">
                      @csrf
                      <!-- Default Weekly Holidays -->
                      <div class="mb-3">
                        <label class="form-label">Mark days for default Holidays for the current year</label>
                        <div class="d-flex flex-wrap">
                          @php
                            $days = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
                          @endphp
                          @foreach($days as $i => $day)
                            <div class="form-check me-3">
                              <input class="form-check-input" type="checkbox" name="office_holiday_days[]" id="day_{{ $i }}" value="{{ $i }}">
                              <label class="form-check-label" for="day_{{ $i }}">{{ $day }}</label>
                            </div>
                          @endforeach
                        </div>
                      </div>
                      <!-- Occasion -->
                      <div class="mb-3">
                        <label for="occassion" class="form-label">Occasion </label>
                        <input type="text" class="form-control" name="occassion" id="occassion" placeholder="Occasion name">
                      </div>
                    </form>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" form="save-mark-holiday-form" class="btn btn-primary">Save</button>
                  </div>
                </div>
              </div>
            </div>
        @endif
    </div>
</main>
@endsection
