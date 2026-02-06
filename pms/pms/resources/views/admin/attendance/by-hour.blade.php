@extends('admin.layout.app')
@section('title', 'Attendance by Hour')

@section('content')
@php
use Carbon\Carbon;
$authUser = Auth::user();

/** Helpers */
function secsToHms($s) {
    $s = max(0, (int)$s);
    $h = intdiv($s, 3600);
    $m = intdiv($s % 3600, 60);
    $sec = $s % 60;
    return sprintf('%02d:%02d:%02d', $h, $m, $sec);
}

function normalizeRecords($cell) {
    if (is_null($cell)) return [];
    if ($cell instanceof \Illuminate\Support\Collection) return $cell->all();
    if (is_array($cell)) return $cell;
    if ($cell instanceof \App\Models\Attendance) return [$cell];
    return [];
}
@endphp

<main class="main">
  <div class="content-wrapper py-4 px-3" style="background: linear-gradient(135deg, #f5f7fa 0%, #f0f4f8 100%); min-height: 100vh;">
    <div class="container-fluid">

      {{-- Page Header --}}
      <div class="page-header mb-4">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h4 class="fw-bold mb-2" style="color: #2c3e50;">
              <i class="fas fa-clock me-2" style="color: #3498db;"></i>
              Attendance by Hour
            </h4>
            <nav style="--bs-breadcrumb-divider: '›';" aria-label="breadcrumb">
              <ol class="breadcrumb small mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-muted">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('attendance.index') }}" class="text-muted">Attendances</a></li>
                <li class="breadcrumb-item active" style="color: #3498db;" aria-current="page">By Hour</li>
              </ol>
            </nav>
          </div>

          {{-- Navigation Tabs --}}
          <div class="btn-group shadow-sm" role="group">
            <a href="{{ route('attendance.index') }}" class="btn btn-outline-primary btn-sm">
              <i class="fas fa-list-ul me-1"></i> Summary
            </a>
            <a href="{{ route('attendance.byMember') }}" class="btn btn-outline-primary btn-sm">
              <i class="fas fa-user me-1"></i> By Member
            </a>
            <a href="{{ route('attendance.byHour') }}" class="btn btn-primary btn-sm">
              <i class="fas fa-clock me-1"></i> By Hour
            </a>
            <a href="{{ route('attendance.today.map', ['year' => $year, 'month' => $month]) }}" class="btn btn-outline-primary btn-sm">
              <i class="fas fa-map-marker-alt me-1"></i> Location
            </a>
          </div>
        </div>
      </div>

      {{-- Filter Card --}}
      <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3">
          <h6 class="m-0 font-weight-bold" style="color: #2c3e50;">
            <i class="fas fa-filter me-2"></i>Filter Attendance Records
          </h6>
        </div>
        <div class="card-body">
          <form id="attendanceFilter" method="GET" action="{{ route('attendance.byHour') }}" class="row g-3 align-items-end">
            <div class="col-md-4">
              <label for="employeeSelect" class="form-label small fw-semibold text-muted">Employee</label>
              <select id="employeeSelect" name="user_id" class="form-select form-select-sm shadow-sm">
                @if($authUser->role === 'admin')
                  <option value="">All Employees</option>
                  @foreach(\App\Models\User::where('role','employee')->orderBy('name')->get() as $opt)
                    <option value="{{ $opt->id }}" {{ (string)request('user_id') === (string)$opt->id ? 'selected' : '' }}>
                      {{ $opt->name }}
                    </option>
                  @endforeach
                @else
                  <option value="{{ $authUser->id }}" selected>{{ $authUser->name }}</option>
                @endif
              </select>
            </div>

            <div class="col-md-3">
              <label for="month" class="form-label small fw-semibold text-muted">Month</label>
              <select name="month" class="form-select form-select-sm shadow-sm">
                @foreach(range(1,12) as $m)
                  <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>
                    {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                  </option>
                @endforeach
              </select>
            </div>

            <div class="col-md-3">
              <label for="year" class="form-label small fw-semibold text-muted">Year</label>
              <select name="year" class="form-select form-select-sm shadow-sm">
                @foreach(range(date('Y')-2, date('Y')+1) as $y)
                  <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
              </select>
            </div>

            <div class="col-md-2">
              <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary btn-sm shadow-sm">
                  <i class="fas fa-search me-1"></i> Apply
                </button>
                <a href="{{ route('attendance.byHour') }}" class="btn btn-outline-secondary btn-sm shadow-sm">
                  <i class="fas fa-redo me-1"></i> Reset
                </a>
              </div>
            </div>
          </form>
        </div>
      </div>

      {{-- Export Buttons --}}
      <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-3">
          <div class="d-flex flex-wrap gap-2">
            <button id="excelBtn" class="btn btn-outline-success btn-sm shadow-sm">
              <i class="fas fa-file-excel me-2"></i> Export Excel
            </button>
            <button id="pdfBtn" class="btn btn-outline-danger btn-sm shadow-sm">
              <i class="fas fa-file-pdf me-2"></i> Export PDF
            </button>
            <button id="printBtn" class="btn btn-outline-secondary btn-sm shadow-sm">
              <i class="fas fa-print me-2"></i> Print Report
            </button>
          </div>
        </div>
      </div>

      {{-- Legend --}}
      <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-3">
          <h6 class="mb-3 fw-semibold" style="color: #2c3e50;">
            <i class="fas fa-key me-2"></i>Status Legend
          </h6>
          <div class="d-flex flex-wrap gap-3">
            <div class="legend-item">
              <span class="legend-icon present"><i class="fas fa-clock"></i></span>
              <span class="legend-text">Work Hours (HH:MM:SS)</span>
            </div>
            <div class="legend-item">
              <span class="legend-icon holiday"><i class="fas fa-star"></i></span>
              <span class="legend-text">Holiday / Weekend</span>
            </div>
            <div class="legend-item">
              <span class="legend-icon leave"><i class="fas fa-plane"></i></span>
              <span class="legend-text">On Leave</span>
            </div>
            <div class="legend-item">
              <span class="legend-icon absent"><i class="fas fa-times"></i></span>
              <span class="legend-text">Absent / No Punch</span>
            </div>
          </div>
          <div class="mt-2">
            <small class="text-muted">
              <i class="fas fa-info-circle me-1"></i>
              Click on any time duration to view detailed session information
            </small>
          </div>
        </div>
      </div>

      {{-- Main Table Card --}}
      <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
          <div class="d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold" style="color: #2c3e50;">
              <i class="fas fa-table me-2"></i>Hourly Attendance Summary
              <span class="badge bg-primary ms-2">{{ \Carbon\Carbon::create()->month($month)->format('F') }} {{ $year }}</span>
            </h6>
            <div class="text-muted small">
              <i class="fas fa-calendar-alt me-1"></i>
              {{ $daysInMonth }} days in month
            </div>
          </div>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table id="attendanceGrid" class="table table-hover align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th class="py-3 px-4 text-start" style="min-width: 250px; background-color: #f8f9fa;">
                    <i class="fas fa-user me-2"></i>Employee Details
                  </th>

                  @for($d=1;$d<=$daysInMonth;$d++)
                    @php
                      $dateObj = \Carbon\Carbon::create($year,$month,$d);
                      $isWeekend = $dateObj->isWeekend();
                      $isToday = $dateObj->isToday();
                    @endphp
                    <th class="text-center py-3 {{ $isWeekend ? 'bg-light' : '' }} {{ $isToday ? 'border border-primary' : '' }}"
                        style="min-width: 60px; font-size: 12px;">
                      <div class="fw-medium">{{ $d }}</div>
                      <div class="small text-muted">{{ $dateObj->format('D') }}</div>
                    </th>
                  @endfor

                  <th class="text-center py-3" style="background-color: #f8f9fa; min-width: 100px;">
                    <i class="fas fa-clock-rotate-left me-1"></i>Total
                  </th>
                </tr>
              </thead>

              <tbody>
                @foreach($users as $user)
                  @php
                    $uid = $user->id;
                    $userMap = $attendanceMap[$uid] ?? [];
                    $period = $periodTotals[$uid] ?? ['seconds'=>0];
                    $controllerDayMap = $dayTotals[$uid] ?? null;
                    $computedRowSeconds = 0;
                  @endphp

                  <tr class="attendance-row" data-user-id="{{ $uid }}">
                    <td class="py-3 px-4 text-start" style="border-right: 2px solid #f8f9fa;">
                      <div class="d-flex align-items-center">
                        <div class="avatar-placeholder me-3">
                          <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center"
                               style="width: 36px; height: 36px; font-size: 14px; font-weight: 600;">
                            {{ substr($user->name, 0, 1) }}
                          </div>
                        </div>
                        <div>
                          <strong class="d-block">{{ $user->name }}</strong>
                          <small class="text-muted">{{ $user->employeeDetail->designation->name ?? '-' }}</small>
                          <br>
                          <small class="text-muted">ID: {{ $user->employee_id ?? 'N/A' }}</small>
                        </div>
                      </div>
                    </td>

                    @for($d=1;$d<=$daysInMonth;$d++)
                      @php
                        $dateKey = \Carbon\Carbon::create($year,$month,$d)->format('Y-m-d');
                        $cell = $userMap[$dateKey] ?? null;
                        $recs = normalizeRecords($cell);
                        $status = '';

                        if (is_object($cell) && property_exists($cell, 'status')) {
                            $status = strtolower($cell->status ?? '');
                        }

                        $controllerDaySeconds = $controllerDayMap[$dateKey] ?? null;
                        $dateObj = \Carbon\Carbon::create($year,$month,$d);
                        $isWeekend = $dateObj->isWeekend();
                        $isToday = $dateObj->isToday();
                      @endphp

                      @if(!empty($recs))
                        @if($controllerDaySeconds !== null)
                          @php
                            $cellSeconds = (int)$controllerDaySeconds;
                            $display = secsToHms($cellSeconds);
                            $computedRowSeconds += $cellSeconds;
                            $firstId = $recs[0]->id ?? '';
                          @endphp

                          <td class="text-center align-middle py-2 {{ $isWeekend ? 'bg-light' : '' }} {{ $isToday ? 'border border-primary' : '' }}">
                            <a href="javascript:;" class="view-attendance text-decoration-none"
                               data-attendance-id="{{ $firstId }}"
                               data-user-id="{{ $user->id }}"
                               data-date="{{ $dateKey }}"
                               title="Click to view session details">
                              <div class="time-badge bg-success text-white rounded-pill d-inline-block px-3 py-1">
                                <i class="fas fa-clock me-1"></i>{{ $display }}
                              </div>
                            </a>
                          </td>
                        @else
                          @php
                            $cellSeconds = 0;
                            $hasOpenSession = false;
                            $hasBadSession = false;
                            foreach ($recs as $r) {
                                try {
                                    $inRaw  = $r->clock_in_datetime ?? ($r->clock_in ?? null);
                                    $outRaw = $r->clock_out_datetime ?? ($r->clock_out ?? null);
                                    $inDt  = $inRaw  ? Carbon::parse($inRaw)  : null;
                                    $outDt = $outRaw ? Carbon::parse($outRaw) : null;
                                } catch (\Exception $ex) {
                                    $inDt = $outDt = null;
                                    $hasBadSession = true;
                                }

                                if ($inDt && $outDt) {
                                    if ($outDt->lessThanOrEqualTo($inDt)) {
                                        $hasBadSession = true;
                                        continue;
                                    }
                                    $cellSeconds += $outDt->diffInSeconds($inDt);
                                } elseif ($inDt && !$outDt) {
                                    $hasOpenSession = true;
                                } else {
                                    $hasBadSession = true;
                                }
                            }

                            $computedRowSeconds += $cellSeconds;
                            $display = secsToHms($cellSeconds);
                            if ($hasOpenSession) $display .= ' <span class="text-warning" title="Open session">*</span>';
                            if ($hasBadSession)  $display .= ' <span class="text-danger" title="Bad data">!</span>';
                            $firstId = $recs[0]->id ?? '';
                          @endphp

                          <td class="text-center align-middle py-2 {{ $isWeekend ? 'bg-light' : '' }} {{ $isToday ? 'border border-primary' : '' }}">
                            <a href="javascript:;" class="view-attendance text-decoration-none"
                               data-attendance-id="{{ $firstId }}"
                               data-user-id="{{ $user->id }}"
                               data-date="{{ $dateKey }}"
                               title="Click to view session details">
                              <div class="time-badge bg-success text-white rounded-pill d-inline-block px-3 py-1">
                                <i class="fas fa-clock me-1"></i>{!! $display !!}
                              </div>
                            </a>
                          </td>
                        @endif

                      @else
                        @php
                          $today = \Carbon\Carbon::now()->format('Y-m-d');
                          $dObj = \Carbon\Carbon::parse($dateKey);

                          $cellHtml = '';
                          $cellTitle = '';
                          $cellIcon = '';
                          $cellClass = '';

                          if ($status === 'holiday') {
                              $occ = is_object($cell) ? ($cell->occassion ?? 'Holiday') : 'Holiday';
                              $cellIcon = 'fas fa-star';
                              $cellClass = 'text-warning';
                              $cellTitle = $occ;
                          } elseif ($status === 'leave') {
                              $ln = is_object($cell) ? ($cell->reason ?? 'Leave') : 'Leave';
                              $cellIcon = 'fas fa-plane';
                              $cellClass = 'text-info';
                              $cellTitle = $ln;
                          } elseif ($status === 'absent') {
                              $cellIcon = 'fas fa-times';
                              $cellClass = 'text-danger';
                              $cellTitle = 'Absent';
                          } else {
                              if ($dObj->isWeekend()) {
                                  $cellIcon = 'fas fa-star';
                                  $cellClass = 'text-warning';
                                  $cellTitle = 'Weekend / Holiday';
                              } elseif ($dateKey <= $today) {
                                  $cellIcon = 'fas fa-times';
                                  $cellClass = 'text-danger';
                                  $cellTitle = 'Absent (no punch)';
                              } else {
                                  $cellIcon = '';
                                  $cellClass = 'text-muted';
                                  $cellTitle = 'Future date';
                              }
                          }

                          if (!empty($cellIcon)) {
                              $cellHtml = "<i class='{$cellIcon} fs-5' title='{$cellTitle}'></i>";
                          } else {
                              $cellHtml = '<span class="text-muted">-</span>';
                          }
                        @endphp

                        <td class="text-center align-middle py-3 {{ $isWeekend ? 'bg-light' : '' }} {{ $isToday ? 'border border-primary' : '' }}">
                          <div class="d-flex justify-content-center" title="{{ $cellTitle }}">
                            {!! $cellHtml !!}
                          </div>
                        </td>
                      @endif
                    @endfor

                    {{-- total H:M:S --}}
                    @php
                      $totalSeconds = (int) ($period['seconds'] ?? 0);
                      if (empty($totalSeconds)) {
                          $totalSeconds = $computedRowSeconds;
                      }
                      $totalHms = secsToHms($totalSeconds);
                    @endphp
                    <td class="text-center py-3 fw-bold" style="background-color: #f8f9fa;">
                      <div class="total-badge bg-primary text-white rounded-pill d-inline-block px-3 py-2">
                        {{ $totalHms }}
                      </div>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
        <div class="card-footer bg-white py-3">
          <div class="row">
            <div class="col-md-6">
              <small class="text-muted">
                <i class="fas fa-users me-1"></i>
                Showing {{ $users->count() }} employees
              </small>
            </div>
            <div class="col-md-6 text-end">
              <small class="text-muted">
                <i class="fas fa-sync-alt me-1"></i>
                Last updated: {{ now()->format('d M Y, h:i A') }}
              </small>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</main>

{{-- Attendance Details Modal --}}
<div class="modal fade" id="attendanceDetailsModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">
          <i class="fas fa-clock me-2"></i>Attendance Session Details
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div id="attendanceDetailsBody" class="modal-body">
        <div class="text-center py-5">
          <div class="spinner-border text-primary" role="status"></div>
          <p class="mt-2 text-muted">Loading attendance details...</p>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-outline-secondary" data-bs-dismiss="modal">
          <i class="fas fa-times me-1"></i> Close
        </button>
      </div>
    </div>
  </div>
</div>

@push('styles')
<style>
  .page-header {
    padding: 20px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    margin-bottom: 24px;
  }

  .card {
    border-radius: 12px;
    border: none;
    transition: all 0.3s ease;
  }

  .card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1) !important;
  }

  .legend-item {
    display: flex;
    align-items: center;
    padding: 6px 12px;
    background: #f8f9fa;
    border-radius: 6px;
    transition: all 0.3s ease;
  }

  .legend-item:hover {
    background: #e9ecef;
    transform: translateY(-1px);
  }

  .legend-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    margin-right: 8px;
    font-size: 12px;
    color: white;
  }

  .legend-icon.present { background: #2ecc71; }
  .legend-icon.absent { background: #e74c3c; }
  .legend-icon.leave { background: #3498db; }
  .legend-icon.holiday { background: #f39c12; }

  .avatar-placeholder .rounded-circle {
    font-weight: 600;
  }

  .time-badge {
    font-size: 12px;
    font-weight: 500;
    transition: all 0.2s ease;
  }

  .time-badge:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
  }

  .total-badge {
    font-size: 13px;
    font-weight: 600;
  }

  .table th {
    background-color: #f8f9fa;
    font-weight: 600;
    color: #2c3e50;
    border-top: none;
    border-bottom: 2px solid #e9ecef;
  }

  .table td {
    vertical-align: middle;
    border-color: #f1f3f4;
  }

  .table tr:hover td {
    background-color: #f8f9fa;
  }

  .form-select {
    border-radius: 8px;
    border: 1px solid #ddd;
    transition: all 0.3s ease;
  }

  .form-select:focus {
    border-color: #3498db;
    box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
  }

  .btn-group .btn {
    border-radius: 8px !important;
    margin: 0 2px;
  }

  @media (max-width: 768px) {
    .table-responsive {
      font-size: 11px;
    }

    .time-badge {
      padding: 4px 8px !important;
      font-size: 10px;
    }

    .avatar-placeholder .rounded-circle {
      width: 28px !important;
      height: 28px !important;
      font-size: 12px !important;
    }
  }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
  var employeeSelect = document.getElementById('employeeSelect');

  function applyRowFilter() {
    var val = employeeSelect.value;
    var rows = document.querySelectorAll('.attendance-row');
    if (!val) {
      rows.forEach(function(r){ r.style.display = ''; });
      return;
    }
    rows.forEach(function(r){
      if (r.getAttribute('data-user-id') === val) r.style.display = '';
      else r.style.display = 'none';
    });
  }

  applyRowFilter();
  employeeSelect.addEventListener('change', applyRowFilter);

  // export actions
  document.getElementById('excelBtn').addEventListener('click', function(){
    window.location.href = "{{ url('attendance/export/excel') }}?" + new URLSearchParams(new FormData(document.getElementById('attendanceFilter'))).toString();
  });

  document.getElementById('pdfBtn').addEventListener('click', function(){
    window.open("{{ url('attendance/export/pdf') }}?" + new URLSearchParams(new FormData(document.getElementById('attendanceFilter'))).toString(), '_blank');
  });

  document.getElementById('printBtn').addEventListener('click', function(){
    window.print();
  });

  // attendance details modal
  document.addEventListener('click', function(e){
    var el = e.target.closest('.view-attendance');
    if (!el) return;
    e.preventDefault();

    var attendanceId = el.getAttribute('data-attendance-id');
    var userId = el.getAttribute('data-user-id');
    var date = el.getAttribute('data-date');
    var url = "{{ url('attendance/details') }}?attendance_id=" + attendanceId + "&user_id=" + userId + "&date=" + date;

    var modalEl = document.getElementById('attendanceDetailsModal');
    var modal = new bootstrap.Modal(modalEl);
    modal.show();

    document.getElementById('attendanceDetailsBody').innerHTML = `
      <div class="text-center py-5">
        <div class="spinner-border text-primary" role="status"></div>
        <p class="mt-2 text-muted">Loading attendance details...</p>
      </div>
    `;

    fetch(url)
      .then(function(resp){ return resp.text(); })
      .then(function(html){
        document.getElementById('attendanceDetailsBody').innerHTML = html;
      })
      .catch(function(){
        document.getElementById('attendanceDetailsBody').innerHTML = `
          <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle me-2"></i>
            Error loading attendance details. Please try again.
          </div>`;
      });
  });
});
</script>
@endpush

@endsection
