<?php

namespace App\Http\Controllers;
use App\Models\Task;
use App\Models\TaskTimer;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TaskTimerController extends Controller
{
   // Start Timer
public function start(Task $task)
{
    TaskTimer::where('user_id', auth()->id())->whereNull('end_time')->update(['end_time' => now()]);

    TaskTimer::create([
        'task_id' => $task->id,
        'user_id' => auth()->id(),
        'start_time' => now()
    ]);

    return back()->with('success', 'Timer started.');
}

// Pause Timer
public function pause(Task $task)
{
    TaskTimer::where('task_id', $task->id)
        ->where('user_id', auth()->id())
        ->whereNull('end_time')
        ->update(['pause_time' => now()]);

    return back()->with('success', 'Timer paused.');
}

// Resume Timer
public function resume(Task $task)
{
    $timer = TaskTimer::where('task_id', $task->id)
        ->where('user_id', auth()->id())
        ->whereNull('end_time')
        ->whereNotNull('pause_time')
        ->first();

    if ($timer) {
        $timer->update(['pause_time' => null]);
        return back()->with('success', 'Timer resumed.');
    }

    return back()->with('error', 'No paused timer found.');
}


// Stop Timer
public function stop(Request $request, Task $task)
{
    
    $request->validate([
        'memo' => 'required|string|max:500',
    ]);
    $timer = TaskTimer::where('id', $request->timer_id)
        ->where('user_id', auth()->id())
        ->whereNull('end_time')
        ->first();

    if ($timer) {
        $start = Carbon::parse($timer->start_time);
        $end = Carbon::now();

        if ($start->lt($end)) {
            $seconds = $end->diffInSeconds($start);
            $total_hours_decimal = round($seconds / 3600, 2);
        } else {
            $seconds = 0;
            $total_hours_decimal = 0;
        }

        $timer->end_time = $end;
        $timer->memo = $request->memo;
        $timer->project_id = $request->project_id;
        $timer->start_date = $request->start_date;
        $timer->end_date = $request->end_date;
        $timer->total_hours = $total_hours_decimal;
        $timer->save();
    }

    return back()->with('success', 'Timer stopped and logged.');
}


public function globalstop(Request $request, Task $task)
{
    $request->validate([
        'timer_id'   => 'required|exists:task_timers,id',
        'memo'       => 'required|string|max:500',
        'project_id' => 'nullable|exists:projects,id',
        'start_date' => 'nullable|date',
        'end_date'   => 'nullable|date',
    ]);

    $timer = TaskTimer::where('id', $request->timer_id)
        ->where('user_id', auth()->id())
        ->whereNull('end_time')
        ->first();

    if ($timer) {
        $start = Carbon::parse($timer->start_time);
        $end   = Carbon::now();

        $seconds = $start->lt($end) ? $end->diffInSeconds($start) : 0;
        $total_hours_decimal = round($seconds / 3600, 2);

        $timer->update([
            'end_time'    => $end,
            'memo'        => $request->memo,
            'project_id'  => $request->project_id ?? $timer->project_id,
            'start_date'  => $request->start_date ?? $start->toDateString(),
            'end_date'    => $request->end_date ?? $end->toDateString(),
            'total_hours' => $total_hours_decimal,
        ]);
    }

    return back()->with('success', 'Timer stopped and logged.');
}


}
