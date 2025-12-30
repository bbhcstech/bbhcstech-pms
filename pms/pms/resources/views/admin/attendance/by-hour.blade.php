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
    return []; // status objects (holiday/leave/absent) are not records
}
@endphp

<main class="main">
  <div class="content-wrapper py-4 px-3" style="background-color:#f5f7fa;">
    <div class="container-fluid">

      {{-- FILTERS --}}
      <div class="d-flex justify-content-start align-items-center mb-3 gap-2 border-bottom pb-2">
        <form id="attendanceFilter" class="d-flex gap-2 w-100" method="GET" action="{{ route('attendance.byHour') }}">
          <select id="employeeSelect" name="user_id" class="form-select form-select-sm" style="min-width:260px;">
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

          <select name="month" class="form-select form-select-sm">
            @foreach(range(1,12) as $m)
              <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>
                {{ \Carbon\Carbon::create()->month($m)->format('F') }}
              </option>
            @endforeach
          </select>

          <select name="year" class="form-select form-select-sm">
            @foreach(range(date('Y')-2, date('Y')+1) as $y)
              <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
            @endforeach
          </select>

          <div class="d-flex gap-2">
            <button type="submit" class="btn btn-success btn-sm">Filter</button>
            <a href="{{ route('attendance.byHour') }}" class="btn btn-secondary btn-sm">Reset</a>
          </div>
        </form>
      </div>

      {{-- Header --}}
      <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb small text-muted">
              <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
              <li class="breadcrumb-item"><a href="{{ route('attendance.index') }}">Attendances</a></li>
              <li class="breadcrumb-item active">By Hour</li>
            </ol>
          </nav>
        </div>

        <div class="btn-group">
          <a href="{{ route('attendance.index') }}" class="btn btn-secondary btn-sm"><i class="bi bi-list-ul"></i></a>
          <a href="{{ route('attendance.byMember') }}" class="btn btn-secondary btn-sm"><i class="bi bi-person"></i></a>
          <a href="{{ route('attendance.byHour') }}" class="btn btn-secondary btn-sm active"><i class="bi bi-clock"></i></a>
          <a href="{{ route('attendance.today.map', ['year' => $year, 'month' => $month]) }}" class="btn btn-secondary btn-sm"><i class="bi bi-geo-alt"></i></a>
        </div>
      </div>

      {{-- Exports --}}
      <div class="mb-2">
        <button id="excelBtn" class="btn btn-outline-secondary btn-sm">Excel</button>
        <button id="pdfBtn" class="btn btn-outline-secondary btn-sm">PDF</button>
        <button id="printBtn" class="btn btn-outline-secondary btn-sm">Print</button>
      </div>

      {{-- GRID TABLE --}}
      <div class="card shadow-sm p-3">
        <div class="table-responsive">
          <table id="attendanceGrid" class="table table-bordered align-middle table-sm">
            <thead class="table-light">
              <tr>
                <th style="min-width:220px">Employee</th>

                @for($d=1;$d<=$daysInMonth;$d++)
                  @php $dateObj = \Carbon\Carbon::create($year,$month,$d); @endphp
                  <th class="text-center small">
                    {{ $d }}<br><small>{{ $dateObj->format('D') }}</small>
                  </th>
                @endfor

                <th class="text-center">Total H:M:S</th>
              </tr>
            </thead>

            <tbody>
              @foreach($users as $user)
                @php
                  $uid = $user->id;
                  $userMap = $attendanceMap[$uid] ?? [];
                  // prefer controller-provided period total if present
                  $period = $periodTotals[$uid] ?? ['seconds'=>0];
                  // controller-provided dayTotals (optional)
                  $controllerDayMap = $dayTotals[$uid] ?? null;
                  $computedRowSeconds = 0; // fallback accumulation when dayTotals isn't provided
                @endphp

                <tr class="attendance-row" data-user-id="{{ $uid }}">
                  <td class="text-start">
                    <strong>{{ $user->name }}</strong><br>
                    <small class="text-muted">{{ $user->employeeDetail->designation->name ?? '-' }}</small>
                  </td>

                  @for($d=1;$d<=$daysInMonth;$d++)
                    @php
                      $dateKey = \Carbon\Carbon::create($year,$month,$d)->format('Y-m-d');
                      $cell = $userMap[$dateKey] ?? null;
                      $recs = normalizeRecords($cell);

                      // detect controller-provided status for holiday/leave/absent
                      $status = '';
                      if (is_object($cell) && property_exists($cell, 'status')) {
                          $status = strtolower($cell->status ?? '');
                      }

                      // prefer server-computed seconds if controller provided dayTotals
                      $controllerDaySeconds = $controllerDayMap[$dateKey] ?? null;
                    @endphp

                    @if(!empty($recs))
                      @if($controllerDaySeconds !== null)
                        @php
                          // use controller value directly
                          $cellSeconds = (int)$controllerDaySeconds;
                          $display = secsToHms($cellSeconds);
                          $computedRowSeconds += $cellSeconds; // keep fallback consistent
                          $firstId = $recs[0]->id ?? '';
                        @endphp

                        <td class="text-center align-middle">
                          <a href="javascript:;" class="view-attendance" data-attendance-id="{{ $firstId }}" data-user-id="{{ $user->id }}" data-date="{{ $dateKey }}" title="Click to view session details">
                            <div class="small text-primary fw-bold">{{ $display }}</div>
                          </a>
                        </td>
                      @else
                        {{-- controller did not provide day total — compute in-view --}}
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
                                  // by default do not add ongoing session to completed-day total
                                  // to count ongoing session against now, uncomment next line:
                                  // $cellSeconds += Carbon::now()->diffInSeconds($inDt);
                              } else {
                                  $hasBadSession = true;
                              }
                          }

                          $computedRowSeconds += $cellSeconds;
                          $display = secsToHms($cellSeconds);
                          if ($hasOpenSession) $display .= ' <small class="text-warning" title="Open session (missing clock-out)">*</small>';
                          if ($hasBadSession)  $display .= ' <small class="text-danger" title="Bad data (clock-out ≤ clock-in)">!</small>';
                          $firstId = $recs[0]->id ?? '';
                        @endphp

                        <td class="text-center align-middle">
                          <a href="javascript:;" class="view-attendance" data-attendance-id="{{ $firstId }}" data-user-id="{{ $user->id }}" data-date="{{ $dateKey }}" title="Click to view session details">
                            <div class="small text-primary fw-bold">{!! $display !!}</div>
                          </a>
                        </td>
                      @endif

                    {{-- no attendance records: decide holiday/leave/absent/future --}}
                    @else
                      @php
                        $today = \Carbon\Carbon::now()->format('Y-m-d');
                        $dObj = \Carbon\Carbon::parse($dateKey);

                        $cellHtml = '<span class="text-muted">-</span>';

                        if ($status === 'holiday') {
                            $occ = is_object($cell) ? ($cell->occassion ?? 'Holiday') : 'Holiday';
                            $cellHtml = "<i class='bx bx-star text-warning fs-5' title='".e($occ)."'></i>";
                        } elseif ($status === 'leave') {
                            $ln = is_object($cell) ? ($cell->reason ?? 'Leave') : 'Leave';
                            $cellHtml = "<i class='bx bxs-plane-take-off text-info fs-5' title='".e($ln)."'></i>";
                        } elseif ($status === 'absent') {
                            $cellHtml = "<i class='bx bx-x text-danger fs-5' title='Absent'></i>";
                        } else {
                            if ($dObj->isWeekend()) {
                                $cellHtml = "<i class='bx bx-star text-warning fs-5' title='Weekend / Holiday'></i>";
                            } elseif ($dateKey <= $today) {
                                $cellHtml = "<i class='bx bx-x text-danger fs-5' title='Absent (no punch)'></i>";
                            } else {
                                $cellHtml = '<span class="text-muted">-</span>';
                            }
                        }
                      @endphp

                      <td class="text-center align-middle">{!! $cellHtml !!}</td>
                    @endif

                  @endfor

                  {{-- total H:M:S --}}
                  @php
                    // prefer controller-provided period total if available
                    $totalSeconds = (int) ($period['seconds'] ?? 0);
                    if (empty($totalSeconds)) {
                        $totalSeconds = $computedRowSeconds;
                    }
                    $totalHms = secsToHms($totalSeconds);
                  @endphp
                  <td class="text-center fw-bold">{{ $totalHms }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
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
        <h5 class="modal-title">Attendance Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div id="attendanceDetailsBody" class="modal-body">
        <div class="text-center py-4"><div class="spinner-border text-primary"></div></div>
      </div>
      <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Close</button></div>
    </div>
  </div>
</div>

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

  // run on load (useful when server returned user_id in querystring)
  applyRowFilter();

  // immediate client-side filtering when dropdown changes
  employeeSelect.addEventListener('change', function() {
    applyRowFilter();
  });

  // export actions
  document.getElementById('excelBtn').addEventListener('click', function(){
    window.location.href = "{{ url('attendance/export/excel') }}?" + new URLSearchParams(new FormData(document.getElementById('attendanceFilter'))).toString();
  });
  document.getElementById('pdfBtn').addEventListener('click', function(){
    window.open("{{ url('attendance/export/pdf') }}?" + new URLSearchParams(new FormData(document.getElementById('attendanceFilter'))).toString(), '_blank');
  });
  document.getElementById('printBtn').addEventListener('click', function(){ window.print(); });

  // attendance details modal (AJAX)
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
    document.getElementById('attendanceDetailsBody').innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary"></div></div>';

    fetch(url).then(function(resp){ return resp.text(); }).then(function(html){
      document.getElementById('attendanceDetailsBody').innerHTML = html;
    }).catch(function(){
      document.getElementById('attendanceDetailsBody').innerHTML = '<div class="alert alert-danger">Error loading attendance details.</div>';
    });
  });
});
</script>
@endpush

@endsection
