<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskTimer extends Model
{
   protected $fillable = ['task_id', 'user_id','project_id','start_date', 'start_time', 'end_time','end_date','pause_time','memo','total_hours','status'];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }
    
    public function user()
    {
        return $this->belongsTo(User::class);
   }
   
    public function employee()
        {
            return $this->belongsTo(User::class, 'user_id'); 
        }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    
     public function calculateHours() {
        if ($this->start_date && $this->start_time && $this->end_date && $this->end_time) {
            $start = Carbon::parse($this->start_date . ' ' . $this->start_time);
            $end = Carbon::parse($this->end_date . ' ' . $this->end_time);
            return round($start->floatDiffInHours($end), 2);
        }
        return 0;
    }
}
