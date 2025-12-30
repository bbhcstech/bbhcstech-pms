<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Attendance extends Model
{
    // explicit mapping to your table
    protected $table = 'attendances';

    protected $fillable = [
        'user_id',
        'department_id',
        'location_id',
        'location',
        'latitude',
        'longitude',
        'date',
        'status',
        'clock_in',
        'clock_out',
        'working_from',
        'late',
        'half_day',
        'half_day_type',
        'work_from_type',
        'overwrite_attendance'
    ];

    protected $casts = [
        'date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        // keep clock_in/clock_out as strings (DB stores TIME)
        'clock_in' => 'string',
        'clock_out' => 'string',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    protected $appends = [
        'total_duration',      // H:i:s
        'total_seconds',       // integer seconds
        'clock_in_datetime',
        'clock_out_datetime'
    ];

    // fully-qualified relation to avoid namespace issues
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    /**
     * Parse an input value (time-only, full-datetime, Carbon/DateTime) into Carbon or null.
     */
    protected function parseDatetimeValue($value, string $attendanceDate): ?Carbon
    {
        if (empty($value) && $value !== '0') {
            return null;
        }

        if ($value instanceof Carbon) {
            return $value->copy();
        }

        if ($value instanceof \DateTime) {
            return Carbon::instance($value);
        }

        $val = trim((string) $value);
        $attendanceDate = (string) $attendanceDate; // ensure Y-m-d

        if (preg_match('/^\d{4}-\d{2}-\d{2}[ T]\d{2}:\d{2}(:\d{2})?$/', $val)) {
            try { return Carbon::parse($val); } catch (\Throwable $e) {}
        }

        if (preg_match('/^[0-2]?\d:[0-5]\d(:[0-5]\d)?(\s?[AP]M)?$/i', $val)) {
            $tmp = $val;
            if (preg_match('/^\d{1,2}:\d{2}$/', $tmp)) {
                $tmp .= ':00';
            }
            try {
                return Carbon::createFromFormat('Y-m-d H:i:s', $attendanceDate . ' ' . $tmp);
            } catch (\Throwable $e) {
                try { return Carbon::parse($attendanceDate . ' ' . $tmp); } catch (\Throwable $_) {}
            }
        }

        try { return Carbon::parse($attendanceDate . ' ' . $val); } catch (\Throwable $e) {}
        try { return Carbon::parse($val); } catch (\Throwable $e) {}

        return null;
    }

    // combine date + clock_in into a Carbon (or null)
    public function getClockInDatetimeAttribute()
    {
        if (empty($this->clock_in) || empty($this->date)) {
            return null;
        }

        return $this->parseDatetimeValue($this->clock_in, $this->date);
    }

    // combine date + clock_out into a Carbon (or null)
    public function getClockOutDatetimeAttribute()
    {
        if (empty($this->clock_out) || empty($this->date)) {
            return null;
        }

        return $this->parseDatetimeValue($this->clock_out, $this->date);
    }

    // seconds between in/out. If out < in => treat out as next day.
    public function getTotalSecondsAttribute()
    {
        $in = $this->clock_in_datetime;
        $out = $this->clock_out_datetime;

        if (! $in || ! $out) {
            return 0;
        }

        if ($out->lt($in)) {
            $out = $out->copy()->addDay();
        }

        $seconds = $out->getTimestamp() - $in->getTimestamp();
        return max(0, (int) $seconds);
    }

    // human-readable H:i:s (hours may be >24)
    public function getTotalDurationAttribute()
    {
        $seconds = (int) $this->total_seconds;

        if ($seconds <= 0) {
            return '00:00:00';
        }

        $h = intdiv($seconds, 3600);
        $m = intdiv($seconds % 3600, 60);
        $s = $seconds % 60;

        return sprintf('%02d:%02d:%02d', $h, $m, $s);
    }
}
