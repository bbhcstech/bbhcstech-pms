{{-- resources/views/admin/attendance/table.blade.php --}}
@php $authUser = Auth::user(); @endphp

{{-- make container relative so we can position export buttons at top-right --}}
<div class="table-wrapper position-relative" style="margin-top:12px;">
  {{-- export area: positioned at top-right (where original buttons were) --}}
  <div class="export-top" style="position:absolute; top:-64px; right:16px; z-index:30;">
    <!-- Added export buttons (Excel + PDF) -->
    <button id="exportExcelBtnTable" class="btn btn-outline-success btn-sm moved-export" type="button" style="margin-right:6px;">
      <i class="bi bi-file-earmark-excel"></i>&nbsp;Excel
    </button>

    <button id="exportPdfBtnTable" class="btn btn-outline-danger btn-sm moved-export" type="button">
      <i class="bi bi-file-earmark-pdf"></i>&nbsp;PDF
    </button>
  </div>

  <div class="table-responsive">
    <table id="attendanceTable" class="table table-bordered align-middle text-center">
      <thead class="table-light">
        <tr>
          <th style="background-color:#f0f0f0; min-width:180px;">Employee</th>
          @for($i=1;$i<=$daysInMonth;$i++)
            @php $date = \Carbon\Carbon::createFromDate($year,$month,$i); @endphp
            <th style="font-size:12px; width:48px;">{{ $i }}<br><small>{{ $date->format('D') }}</small></th>
          @endfor
          <th style="background-color:#f0f0f0; min-width:140px;">Total Hours</th>
        </tr>
      </thead>

      <tbody>
        @foreach($users as $user)
          <tr>
            <td class="text-start" style="background-color:#f9f9f9; white-space:nowrap;">
              <strong>{{ $user->name }}</strong><br>
              <small>{{ $user->employeeDetail->designation->name ?? '-' }}</small>
            </td>

            @php
              $totalSeconds = 0;
              $presentCount = 0;
            @endphp

            @for($d=1;$d<=$daysInMonth;$d++)
              @php
                $dateKey = \Carbon\Carbon::createFromDate($year,$month,$d)->format('Y-m-d');
                $attendance = $attendanceMap[$user->id][$dateKey] ?? null;
                $status = strtolower($attendance->status ?? '');
                $durationFlag = strtolower($attendance->duration ?? '');
                $iconClass = 'fs-5 fw-bold';
                $symbol = '-';
                $popupClass = '';
                $rowSeconds = 0;
                $today = \Carbon\Carbon::now()->format('Y-m-d');

                if ($status === 'holiday') {
                  $occ = $attendance->occassion ?? 'Holiday';
                  $symbol = "<i class='bx bx-star text-warning {$iconClass}' data-bs-toggle='tooltip' title='{$occ}'></i>";
                  $popupClass = '';
                } elseif ($dateKey <= $today) {
                  switch ($status) {
                    case 'present':
                      $symbol = "<i class='bx bx-check text-success {$iconClass}' data-bs-toggle='tooltip' title='Present'></i>";
                      $presentCount++;
                      $popupClass = 'view-attendance';
                      break;
                    case 'absent':
                      $symbol = "<i class='bx bx-x text-danger {$iconClass}' data-bs-toggle='tooltip' title='Absent'></i>";
                      $popupClass = 'edit-attendance';
                      break;
                    case 'late':
                      $symbol = "<i class='bx bx-time-five text-warning {$iconClass}' data-bs-toggle='tooltip' title='Late'></i>";
                      $presentCount++;
                      $popupClass = 'view-attendance';
                      break;
                    case 'half_day':
                      $symbol = "<i class='bx bxs-star-half text-warning {$iconClass}' data-bs-toggle='tooltip' title='Half Day'></i>";
                      $presentCount += 0.5;
                      $popupClass = 'view-attendance';
                      break;
                    case 'leave':
                      $lr = $attendance->reason ?? 'On Leave';
                      if ($durationFlag === 'full-day') {
                        $symbol = "<i class='bx bxs-plane-take-off text-info {$iconClass}' data-bs-toggle='tooltip' title='{$lr}'></i>";
                      } else {
                        $symbol = "<i class='bx bxs-star-half text-warning {$iconClass}' data-bs-toggle='tooltip' title='{$lr} (Half Day)'></i>";
                        $presentCount += 0.5;
                      }
                      $popupClass = '';
                      break;
                    default:
                      $symbol = '-';
                      $popupClass = '';
                  }
                } else {
                  if ($status === 'leave') {
                    if ($durationFlag === 'full-day') {
                      $symbol = "<i class='bx bxs-plane-take-off text-info {$iconClass}' data-bs-toggle='tooltip' title='Planned Leave'></i>";
                    } else {
                      $symbol = "<i class='bx bxs-star-half text-warning {$iconClass}' data-bs-toggle='tooltip' title='Planned Half Day'></i>";
                    }
                  } else {
                    $symbol = '-';
                  }
                  $popupClass = '';
                }

                if (!empty($attendance)) {
                  if (isset($attendance->total_seconds) && is_numeric($attendance->total_seconds)) {
                    $rowSeconds = (int)$attendance->total_seconds;
                  } elseif (isset($attendance->duration_seconds) && is_numeric($attendance->duration_seconds)) {
                    $rowSeconds = (int)$attendance->duration_seconds;
                  } else {
                    $ciRaw = $attendance->clock_in ?? null;
                    $coRaw = $attendance->clock_out ?? null;
                    $ciStr = is_null($ciRaw) ? '' : trim((string)$ciRaw);
                    $coStr = is_null($coRaw) ? '' : trim((string)$coRaw);
                    if ($ciStr !== '' && $coStr !== '') {
                      try {
                        $ci = \Carbon\Carbon::parse($dateKey . ' ' . $ciStr);
                        $co = \Carbon\Carbon::parse($dateKey . ' ' . $coStr);
                        if ($co->lt($ci)) $co = $co->copy()->addDay();
                        $rowSeconds = max(0, $co->diffInSeconds($ci));
                      } catch (\Throwable $e) {
                        $ciParts = preg_split('/\D+/', $ciStr, -1, PREG_SPLIT_NO_EMPTY);
                        $coParts = preg_split('/\D+/', $coStr, -1, PREG_SPLIT_NO_EMPTY);
                        if (count($ciParts) >= 2 && count($coParts) >= 2) {
                          $ciSeconds = ((int)$ciParts[0])*3600 + ((int)$ciParts[1])*60 + ((int)($ciParts[2] ?? 0));
                          $coSeconds = ((int)$coParts[0])*3600 + ((int)$coParts[1])*60 + ((int)($coParts[2] ?? 0));
                          if ($coSeconds < $ciSeconds) $coSeconds += 86400;
                          $rowSeconds = max(0, $coSeconds - $ciSeconds);
                        } else {
                          $rowSeconds = 0;
                        }
                      }
                    }
                  }
                }

                $totalSeconds += (int)$rowSeconds;
              @endphp

              <td class="fw-bold">
                @php $isAdmin = auth()->user()->role === 'admin'; @endphp

                @if($popupClass === 'view-attendance')
                  <a href="javascript:;" class="view-attendance" data-attendance-id="{{ $attendance->id ?? '' }}" data-user-id="{{ $user->id }}" data-date="{{ $dateKey }}">
                    {!! $symbol !!}
                  </a>
                @elseif($popupClass === 'edit-attendance' && $isAdmin)
                  <a href="javascript:;" class="edit-attendance" data-attendance-id="{{ $attendance->id ?? '' }}" data-user-id="{{ $user->id }}" data-date="{{ $dateKey }}">
                    {!! $symbol !!}
                  </a>
                @else
                  {!! $symbol !!}
                @endif
              </td>

            @endfor

            @php
              $h = intdiv($totalSeconds, 3600);
              $m = intdiv($totalSeconds % 3600, 60);
              $s = $totalSeconds % 60;
              $total_human = sprintf('%d:%02d:%02d', $h, $m, $s);
            @endphp

            <td class="fw-bold text-primary">
              {{ $total_human }}
              <div class="small text-muted">({{ $presentCount }} / {{ $daysInMonth }})</div>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>

{{-- Modal: kept here so clicks can open it --}}
<div class="modal fade" id="attendanceDetailsModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">Attendance Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div id="attendanceDetailsBody" class="modal-body">
        <div class="text-center py-4">
          <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

{{-- small styles --}}
<style>
  .half-star { display:inline-block; width:18px; height:18px; }
  .leave-plane { display:inline-block; padding:2px 6px; border-radius:4px; font-size:14px; }

  /* hide DataTables export buttons if any other code still injects them */
  .dt-buttons { display: none !important; }

  /* exported buttons placed in top-right area */
  .export-top .moved-export { margin-left:6px; display:inline-block; }
  .export-top button { min-width:85px; }
</style>

@push('js')
<script>
$(document).ready(function () {

    // initialize datatable if needed (no DataTables export buttons)
    if ($.fn.DataTable) {
        if ($.fn.DataTable.isDataTable('#attendanceTable')) {
            $('#attendanceTable').DataTable().destroy();
        }

        $('#attendanceTable').DataTable({
            dom: 'lrtip',
            responsive: true,
            scrollX: true,
            pageLength: 10,
            lengthMenu: [10, 25, 50, 100],
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search attendance..."
            }
        });

        // ensure any dt-buttons are removed
        setTimeout(function(){
            if ($('.dt-buttons').length) {
                $('.dt-buttons').remove();
            }
        }, 50);
    }

    // Move any existing Excel/PDF controls into the top-right export area.
    function moveTopExportsOnce() {
        var candidates = $('a, button').filter(function(){
            var txt = $(this).text().trim().toLowerCase();
            return txt.indexOf('excel') !== -1 || txt.indexOf('pdf') !== -1;
        });

        candidates.each(function(){
            var el = $(this);
            if (!el.hasClass('moved-export')) {
                // clone then hide original to avoid breaking other handlers
                var clone = el.clone(true).addClass('moved-export');
                $('.export-top').append(clone);
                el.hide().addClass('export-hidden-original');
            }
        });
    }

    moveTopExportsOnce();

    // re-run on ajaxComplete in case top controls are rendered later
    $(document).ajaxComplete(function(){
        moveTopExportsOnce();
    });

    // If user clicks a moved button (cloned), trigger the original hidden element's click if present
    $(document).on('click', '.export-top .moved-export', function(e){
        var txt = $(this).text().trim().toLowerCase();
        var orig = $('.export-hidden-original').filter(function(){
            return $(this).text().trim().toLowerCase() === txt;
        }).first();

        if (orig.length) {
            // trigger original
            orig.trigger('click');
            return;
        }

        // fallback: if no original exists, handle excel/pdf for table buttons
        if ($(this).attr('id') === 'exportExcelBtnTable') {
            // build query from main filter form if present
            var qs = (function() {
                var form = document.getElementById('attendanceFilter');
                if (!form) return '';
                return new URLSearchParams(new FormData(form)).toString();
            })();
            var url = "{{ url('attendance/export/excel') }}";
            window.location.href = qs ? (url + '?' + qs) : url;
        } else if ($(this).attr('id') === 'exportPdfBtnTable') {
            var qs2 = (function() {
                var form = document.getElementById('attendanceFilter');
                if (!form) return '';
                return new URLSearchParams(new FormData(form)).toString();
            })();
            var url2 = "{{ url('attendance/export/pdf') }}";
            var full = qs2 ? (url2 + '?' + qs2) : url2;
            var win = window.open(full, '_blank');
            if (!win) window.location.href = full;
        }

        // nothing else to do here
    });

    // Direct bind for our explicit table buttons (works even if clones don't exist)
    $('#exportExcelBtnTable').off('click').on('click', function (e) {
        e.preventDefault();
        var qs = (function() {
            var form = document.getElementById('attendanceFilter');
            if (!form) return '';
            return new URLSearchParams(new FormData(form)).toString();
        })();
        var url = "{{ url('attendance/export/excel') }}";
        window.location.href = qs ? (url + '?' + qs) : url;
    });

    $('#exportPdfBtnTable').off('click').on('click', function (e) {
        e.preventDefault();
        var qs = (function() {
            var form = document.getElementById('attendanceFilter');
            if (!form) return '';
            return new URLSearchParams(new FormData(form)).toString();
        })();
        var url = "{{ url('attendance/export/pdf') }}";
        var full = qs ? (url + '?' + qs) : url;
        var w = window.open(full, '_blank');
        if (!w) window.location.href = full;
    });

    // Filter AJAX submit (optional; fallback to normal GET if disabled)
    $('#attendanceFilter').on('submit', function(e) {
        // allow default GET submit, but if you want ajax un-comment below
        // e.preventDefault();
        // $.ajax({...})
    });

    // view attendance modal
    $(document).on('click', '.view-attendance', function(e) {
        e.preventDefault();
        var attendanceId = $(this).data('attendance-id');
        var userId = $(this).data('user-id');
        var date = $(this).data('date');

        var url = "{{ url('attendance/details') }}?attendance_id=" + attendanceId + "&user_id=" + userId + "&date=" + date;

        var modal = new bootstrap.Modal(document.getElementById('attendanceDetailsModal'));
        modal.show();

        $('#attendanceDetailsBody').html('<div class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>');

        $.ajax({
            url: url,
            type: 'GET',
            success: function(response) {
                $('#attendanceDetailsBody').html(response);
            },
            error: function(xhr) {
                $('#attendanceDetailsBody').html('<div class="alert alert-danger">Error loading attendance details.</div>');
            }
        });
    });

    // edit attendance modal for admins
    $(document).on('click', '.edit-attendance', function(e) {
        e.preventDefault();
        var attendanceId = $(this).data('attendance-id');
        var userId       = $(this).data('user-id');
        var date         = $(this).data('date');

        var url = "{{ url('attendance/edit') }}?attendance_id=" + attendanceId + "&user_id=" + userId + "&date=" + date;

        var modal = new bootstrap.Modal(document.getElementById('attendanceDetailsModal'));
        modal.show();

        $('#attendanceDetailsBody').html('<div class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>');

        $.ajax({
            url: url,
            type: 'GET',
            success: function(response) {
                $('#attendanceDetailsBody').html(response);
            },
            error: function(xhr) {
                $('#attendanceDetailsBody').html('<div class="alert alert-danger">Error loading attendance form.</div>');
            }
        });
    });

    // tooltips
    $(function () {
        $('[data-bs-toggle="tooltip"]').tooltip({
            trigger: 'hover',
            placement: 'top'
        });
    });

    $(document).ajaxComplete(function () {
        $('[data-bs-toggle="tooltip"]').tooltip({
            trigger: 'hover',
            placement: 'top'
        });
    });

});
</script>
@endpush
