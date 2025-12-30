<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class TimeLog extends Model
{
    protected $fillable = [
        'user_id',
        'project_id',
        'task_id',
        'start_date',
        'start_time',
        'end_date',
        'end_time',
        'memo',
        'invoice_id',
        'total_hours'
    ];

    protected static function boot()
    {
        parent::boot();

        // Auto calculate total hours on create
        static::creating(function ($log) {
            $log->total_hours = $log->calculateHours();
        });

        // Auto calculate total hours on update
        static::updating(function ($log) {
            $log->total_hours = $log->calculateHours();
        });
    }

    /** Relationship: Employee/User */
    public function employee()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** Relationship: Project */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /** Relationship: Task */
    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * Calculate the total working hours accurately
     */
    public function calculateHours()
    {
        // Ensure all fields exist
        if (!$this->start_date || !$this->start_time || !$this->end_date || !$this->end_time) {
            return 0;
        }

        // Build Carbon timestamps
        $start = Carbon::parse($this->start_date . ' ' . $this->start_time);
        $end   = Carbon::parse($this->end_date . ' ' . $this->end_time);

        // If end time is earlier than start, DO NOT return negative hours
        if ($end->lessThan($start)) {
            return 0;
        }

        // Calculate and round to 2 decimals
        return round($start->floatDiffInHours($end), 2);
    }
}
