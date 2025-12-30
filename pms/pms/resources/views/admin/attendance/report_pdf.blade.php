<!doctype html>
<html>
<head>
    <meta charset="utf-8"/>
    <title>Attendance Report</title>
    <style>
        body { font-family: DejaVu Sans, Arial, Helvetica, sans-serif; font-size:12px; color:#222; }
        .header { text-align: center; margin-bottom: 10px; }
        .company { font-weight:700; font-size:14px; }
        .meta { font-size:11px; color:#555; margin-bottom:8px; }
        table { width:100%; border-collapse: collapse; }
        th, td { padding:8px 6px; border: 1px solid #bfbfbf; text-align: left; vertical-align: middle; }
        th { background:#f2f2f2; font-weight:700; }
        .small { font-size:11px; color:#444; }
        .right { text-align: right; }
        .totals-row th, .totals-row td { background:#f8f8f8; font-weight:700; }
        .muted { color:#666; font-size:11px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="company">Attendance Report</div>
        <div class="meta">
            <strong>{{ $user->name ?? ($selectedUser->name ?? 'Unknown User') }}</strong>
            &nbsp;&middot;&nbsp;
            {{ \Carbon\Carbon::createFromDate(null, $month ?? now()->month)->format('F') }} {{ $year ?? now()->year }}
            &nbsp;&middot;&nbsp; Generated: {{ $generated_at ?? now()->format('d-M-Y H:i') }}
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:14%;">Date</th>
                <th style="width:22%;">Status</th>
                <th style="width:20%;">Clock In</th>
                <th style="width:20%;">Clock Out</th>
                <th style="width:24%;">Duration (HH:MM:SS)</th>
            </tr>
        </thead>
        <tbody>
            @php
                // controller provides $matrix (day-indexed). support fallback names.
                $rows = $matrix ?? ($attendances ?? []);
                $totalSeconds = 0;
                $curYear = $year ?? now()->year;
                $curMonth = $month ?? now()->month;
            @endphp

            @forelse($rows as $dayKey => $attRaw)
                @php
                    $att = $attRaw;

                    // build date string if needed
                    if (is_numeric($dayKey) && empty($att->date)) {
                        $dateStr = sprintf('%04d-%02d-%02d', $curYear, $curMonth, (int)$dayKey);
                    } elseif (!empty($att->date)) {
                        $dateStr = $att->date;
                    } else {
                        $dateStr = is_string($dayKey) ? $dayKey : null;
                    }

                    try {
                        $readable = $dateStr ? \Carbon\Carbon::parse($dateStr)->format('d-m-Y') : '-';
                    } catch (\Exception $e) {
                        $readable = $dateStr ?? '-';
                    }

                    $status = $att->status ?? 'N/A';
                    $extra = $att->title ?? $att->leave_type ?? $att->note ?? null;

                    $clockInRaw = trim((string) ($att->clock_in ?? $att->clockIn ?? $att->in_time ?? ''));
                    $clockOutRaw = trim((string) ($att->clock_out ?? $att->clockOut ?? $att->out_time ?? ''));

                    $durationHuman = $att->duration_human ?? null;
                    $rowSeconds = 0;

                    // robust parsing: try Carbon with combined date+time first,
                    // then fallback to strtotime on combined date+time, then on raw time.
                    if ($clockInRaw !== '' && $clockOutRaw !== '') {
                        $ciTimestamp = null;
                        $coTimestamp = null;

                        // Attempt Carbon parse with date + time (preferred)
                        try {
                            $ci = \Carbon\Carbon::parse("{$dateStr} {$clockInRaw}");
                            $co = \Carbon\Carbon::parse("{$dateStr} {$clockOutRaw}");
                            if ($co->lt($ci)) {
                                $co->addDay();
                            }
                            $rowSeconds = $co->diffInSeconds($ci);
                        } catch (\Throwable $e) {
                            // Carbon failed — try strtotime fallbacks
                            // 1) strtotime on "Y-m-d TIME"
                            $ciTs = @strtotime("{$dateStr} {$clockInRaw}");
                            $coTs = @strtotime("{$dateStr} {$clockOutRaw}");

                            // 2) if still false, try parsing raw time (may rely on server date)
                            if ($ciTs === false) {
                                $ciTs = @strtotime($clockInRaw);
                            }
                            if ($coTs === false) {
                                $coTs = @strtotime($clockOutRaw);
                            }

                            if ($ciTs !== false && $coTs !== false) {
                                // if co earlier than ci assume overnight and add 86400
                                if ($coTs < $ciTs) $coTs += 86400;
                                $rowSeconds = max(0, (int)($coTs - $ciTs));
                            } else {
                                // last resort: try parsing as HH:MM:SS difference (04:20 vs 04:22)
                                // split by colon and compute if both look numeric
                                $ciParts = preg_split('/\D+/', $clockInRaw, -1, PREG_SPLIT_NO_EMPTY);
                                $coParts = preg_split('/\D+/', $clockOutRaw, -1, PREG_SPLIT_NO_EMPTY);
                                if (count($ciParts) >= 2 && count($coParts) >= 2) {
                                    $ciSeconds = ((int)$ciParts[0]) * 3600 + ((int)$ciParts[1]) * 60 + ((int)($ciParts[2] ?? 0));
                                    $coSeconds = ((int)$coParts[0]) * 3600 + ((int)$coParts[1]) * 60 + ((int)($coParts[2] ?? 0));
                                    if ($coSeconds < $ciSeconds) $coSeconds += 86400;
                                    $rowSeconds = max(0, $coSeconds - $ciSeconds);
                                } else {
                                    $rowSeconds = 0;
                                }
                            }
                        }

                        // ensure non-negative and integer
                        $rowSeconds = max(0, (int) $rowSeconds);

                        // build human readable string
                        $h = intdiv($rowSeconds, 3600);
                        $m = intdiv($rowSeconds % 3600, 60);
                        $s = $rowSeconds % 60;
                        $durationHuman = sprintf('%d:%02d:%02d', $h, $m, $s);

                        // accumulate only if positive
                        if ($rowSeconds > 0) $totalSeconds += $rowSeconds;
                    } else {
                        // no times -> keep controller-provided duration or default
                        $durationHuman = $durationHuman ?? '--';
                    }
                @endphp

                <tr>
                    <td>{{ $readable }}</td>
                    <td>
                        {{ ucfirst($status) }}
                        @if($extra) — <span class="muted">{{ $extra }}</span>@endif
                    </td>
                    <td class="small">{{ $clockInRaw !== '' ? $clockInRaw : '--' }}</td>
                    <td class="small">{{ $clockOutRaw !== '' ? $clockOutRaw : '--' }}</td>
                    <td class="right">{{ $durationHuman }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center">No attendance records found for the selected period.</td>
                </tr>
            @endforelse

            @php
                // format total seconds into H:MM:SS where hours may exceed 24
                $totalH = intdiv($totalSeconds, 3600);
                $totalM = intdiv($totalSeconds % 3600, 60);
                $totalS = $totalSeconds % 60;
                $total_human = sprintf('%d:%02d:%02d', $totalH, $totalM, $totalS);
            @endphp

            <tr class="totals-row">
                <td colspan="4" class="right">Total Hours</td>
                <td class="right">{{ $total_human ?? '--' }}</td>
            </tr>
        </tbody>
    </table>

    <!--<div style="margin-top:12px; font-size:11px; color:#666;">-->
    <!--    Note: Durations are calculated from Clock In/Out for each day. Overnight sessions (clock-out earlier than clock-in) are treated as next-day clock-out.-->
    <!--</div>-->
</body>
</html>
