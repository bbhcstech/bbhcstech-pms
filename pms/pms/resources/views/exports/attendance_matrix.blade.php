<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"/>
    <title>{{ $title }}</title>

    <style>
        /* Use DejaVu Sans for PDF compatibility (unicode) */
        @font-face {
            font-family: 'DejaVu Sans';
            font-style: normal;
            font-weight: normal;
            src: local('DejaVu Sans'), local('Arial');
        }

        html, body {
            font-family: "DejaVu Sans", Arial, sans-serif;
            color: #2b3d4f;
            margin: 0;
            padding: 18px 22px;
            font-size: 10px;
            line-height: 1.25;
        }

        /* Header */
        .doc-header {
            text-align: center;
            margin-bottom: 10px;
        }
        .doc-title {
            font-size: 16px;
            color: #123241;
            font-weight: 700;
            margin: 0;
            letter-spacing: 0.2px;
        }
        .doc-meta {
            font-size: 10px;
            color: #6b7b86;
            margin-top: 6px;
        }
        .hr {
            height: 1px;
            background: #e1e8ec;
            margin: 12px 0 16px;
            border: none;
        }

        /* Legend */
        .legend {
            display: flex;
            gap: 12px;
            align-items: center;
            margin-bottom: 12px;
            font-size: 9px;
            color: #435d6b;
        }
        .legend .item {
            display: flex;
            gap: 6px;
            align-items: center;
        }
        .swatch {
            width: 12px;
            height: 12px;
            border-radius: 2px;
            border: 1px solid rgba(0,0,0,0.06);
            display: inline-block;
        }

        /* Table */
        .attendance-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            font-size: 9px;
        }

        .attendance-table thead th {
            background: #163240;
            color: #ffffff;
            font-weight: 700;
            padding: 6px 6px;
            text-align: center;
            border: 1px solid #cfdde6;
            font-size: 9px;
        }

        .attendance-table tbody td,
        .attendance-table thead th {
            border: 1px solid #e3eef6;
        }

        .employee-info {
            background: #f7fbfd;
            text-align: left;
            padding: 6px;
            font-weight: 600;
            color: #0f2b3a;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .attendance-table td {
            padding: 6px 4px;
            text-align: center;
            vertical-align: middle;
            word-break: break-word;
        }

        /* Day cells: compact */
        .col-day {
            width: 22px;
            font-weight: 700;
            color: #284a5a;
        }

        /* Summary column */
        .col-summary {
            width: 58px;
            font-weight: 700;
            color: #0f2b3a;
            background: #fbfcfd;
        }

        /* row striping */
        .attendance-table tbody tr:nth-child(odd) td { background: #ffffff; }
        .attendance-table tbody tr:nth-child(even) td { background: #fbfdfe; }

        /* status classes */
        .status-P { background: #e9f6ee; color: #11633a; font-weight: 700; }
        .status-A { background: #fff0f1; color: #7a2328; font-weight: 700; }
        .status-L { background: #fff9ec; color: #7a5b00; font-weight: 700; }
        .status-H { background: #eef9fb; color: #0f5966; font-weight: 700; }
        .status-Late { background: #fff6e6; color: #7a5b00; font-weight: 700; }
        .status-HD { background: #eef5ff; color: #063a7a; font-weight: 700; }

        /* compact employee columns */
        .col-sno { width: 24px; text-align: center; }
        .col-name { width: 120px; text-align: left; padding-left: 8px; }
        .col-design { width: 110px; text-align: left; padding-left: 8px; }
        .col-dept { width: 100px; text-align: left; padding-left: 8px; }

        /* Summary block */
        .summary-box {
            margin-top: 14px;
            padding: 10px;
            background: #f7fbfd;
            border: 1px solid #e6f0f5;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 10px;
            color: #23424f;
        }
        .summary-left { font-weight: 600; color: #23424f; }
        .summary-right { text-align: right; color: #435d6b; }

        /* Footer */
        .doc-footer {
            margin-top: 18px;
            text-align: center;
            font-size: 9px;
            color: #9aa9b3;
            border-top: 1px solid #e6eef3;
            padding-top: 8px;
        }

        /* Page break rules for long reports */
        .page-break { page-break-after: always; }

        /* Make sure cell text doesn't overflow PDF page */
        td, th { overflow-wrap: break-word; word-wrap: break-word; }

    </style>
</head>
<body>

    <div class="doc-header">
        <div class="doc-title">{{ $title }}</div>
        <div class="doc-meta">Generated on: {{ now()->format('M d, Y \\a\\t h:i A') }}</div>
    </div>

    <hr class="hr" />

    <div class="legend" aria-hidden="true">
        <strong style="margin-right:8px;color:#334f5f;">Legend:</strong>

        <div class="item"><span class="swatch" style="background:#e9f6ee"></span><span>P = Present</span></div>
        <div class="item"><span class="swatch" style="background:#fff0f1"></span><span>A = Absent</span></div>
        <div class="item"><span class="swatch" style="background:#fff9ec"></span><span>L = Leave</span></div>
        <div class="item"><span class="swatch" style="background:#eef9fb"></span><span>H = Holiday</span></div>
        <div class="item"><span class="swatch" style="background:#fff6e6"></span><span>Late</span></div>
        <div class="item"><span class="swatch" style="background:#eef5ff"></span><span>HD</span></div>
    </div>

    <table class="attendance-table">
        <thead>
            <tr>
                @foreach($headings as $index => $heading)
                    @if($index === 0)
                        <th class="col-sno">{{ $heading }}</th>
                    @elseif($index === 1)
                        <th class="col-name">{{ $heading }}</th>
                    @elseif($index === 2)
                        <th class="col-design">{{ $heading }}</th>
                    @elseif($index === 3)
                        <th class="col-dept">{{ $heading }}</th>
                    @elseif($index >= count($headings) - 8)
                        <th class="col-summary">{{ $heading }}</th>
                    @else
                        <th class="col-day">{{ $heading }}</th>
                    @endif
                @endforeach
            </tr>
        </thead>

        <tbody>
            @foreach($rows as $rowIndex => $row)
                @if($rowIndex === 0) @continue @endif
                <tr>
                    @foreach($row as $colIndex => $cell)
                        @php
                            // compute whether this is a summary column (last 8)
                            $isSummary = $colIndex >= (count($row) - 8);
                        @endphp

                        @if($colIndex === 0)
                            <td class="col-sno employee-info">{{ $cell }}</td>
                        @elseif($colIndex === 1)
                            <td class="col-name employee-info">{{ $cell }}</td>
                        @elseif($colIndex === 2)
                            <td class="col-design employee-info">{{ $cell }}</td>
                        @elseif($colIndex === 3)
                            <td class="col-dept employee-info">{{ $cell }}</td>
                        @elseif($isSummary)
                            <td class="col-summary">{{ $cell }}</td>
                        @else
                            @php
                                $map = [
                                    'P' => 'P', 'A' => 'A', 'L' => 'L', 'H' => 'H',
                                    'LATE' => 'Late', 'HD' => 'HD'
                                ];
                                $uc = strtoupper((string)$cell);
                                $statusClass = isset($map[$uc]) ? 'status-' . $map[$uc] : '';
                            @endphp
                            <td class="{{ $statusClass }}">{{ $cell }}</td>
                        @endif
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary-box">
        <div class="summary-left">
            Monthly Summary
        </div>

        <div class="summary-right">
            @if(isset($summary))
                Present: <strong>{{ $summary['total_present'] ?? 0 }}</strong> &nbsp;|&nbsp;
                Absent: <strong>{{ $summary['total_absent'] ?? 0 }}</strong> &nbsp;|&nbsp;
                Leave: <strong>{{ $summary['total_leave'] ?? 0 }}</strong> &nbsp;|&nbsp;
                Holiday: <strong>{{ $summary['total_holiday'] ?? 0 }}</strong> &nbsp;|&nbsp;
                Late: <strong>{{ $summary['total_late'] ?? 0 }}</strong> &nbsp;|&nbsp;
                Half Days: <strong>{{ $summary['total_half_days'] ?? 0 }}</strong> &nbsp;|&nbsp;
                Total Hours: <strong>{{ number_format($summary['total_hours'] ?? 0, 2) }}</strong> &nbsp;|&nbsp;
                Working Days: <strong>{{ $summary['total_working_days'] ?? 0 }}</strong>
            @else
                No summary available
            @endif
        </div>
    </div>

    <div class="doc-footer">
        Page generated by {{ config('app.name', 'Laravel') }} • {{ now()->format('M d, Y') }}
    </div>

</body>
</html>
