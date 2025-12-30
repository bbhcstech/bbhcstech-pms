<?php

namespace App\Http\Controllers;
use App\Models\Holiday;
use App\Models\Department;
use App\Models\Designation;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;


use Carbon\Carbon;

use Illuminate\Http\Request;

class HolidayController extends Controller
{
    public function index(Request $request)
    {
        $query = Holiday::query();
    
        // Filter by Year
        if ($request->filled('year')) {
            $query->whereYear('date', $request->year);
        }
    
        // Filter by Month
        if ($request->filled('month')) {
            $query->whereMonth('date', $request->month);
        }
    
        $holidays = $query->orderBy('date', 'asc')->get();
    
        // Pass current filters to view
        return view('admin.holidays.index', compact('holidays'))
            ->with('selectedYear', $request->year)
            ->with('selectedMonth', $request->month);
    }

    public function create()
    {
        $department= Department::get();
        $designations= Designation::get();
        return view('admin.holidays.create',compact('department','designations'));
    }

   


//   public function store(Request $request)
// {
//     // $request->validate([
//     //     'title' => 'required|string|max:30',
//     //     'recurring_type' => 'nullable|in:weekly,monthly',
//     //     'date' => 'nullable|required_unless:recurring_type,weekly,monthly|date',
//     //     'weekday' => 'required_if:recurring_type,weekly|array',
//     //     'weekday.*' => 'in:Sunday,Monday,Tuesday,Wednesday,Thursday,Friday,Saturday',
//     //     'month_dates' => 'required_if:recurring_type,monthly|array',
//     //     'month_dates.*' => 'integer|between:1,31',
//     //     'start_date' => 'nullable|required_if:recurring_type,weekly,monthly|date',
//     //     'end_date'   => 'nullable|required_if:recurring_type,weekly,monthly|date|after_or_equal:start_date',
//     // ]);
//   $request->validate([
//     'title'         => 'required|string|max:30',
//     'recurring_type'=> 'required|in:single,weekly,monthly',
    
//     // For single date
//     'date'          => 'required_if:recurring_type,single|date|nullable',

//     // For weekly recurring
//     'weekday'       => 'required_if:recurring_type,weekly|array|nullable',
//     'weekday.*'     => 'in:Sunday,Monday,Tuesday,Wednesday,Thursday,Friday,Saturday',

//     // For monthly recurring
//     'month_dates'   => 'required_if:recurring_type,monthly|array|nullable',
//     'month_dates.*' => 'integer|between:1,31',

//     // Range only for weekly & monthly
//     'start_date'    => 'nullable|required_if:recurring_type,weekly,monthly|date',
//     'end_date'      => 'nullable|required_if:recurring_type,weekly,monthly|date|after_or_equal:start_date',
// ]);



//     // Generate one group_id for all holidays created in this request
//     $groupId = Holiday::max('group_id') + 1;

//     if ($request->recurring_type === null) {
//         // Single holiday (no recurring)
//         $exists = Holiday::where('date', $request->date)->exists();
//         if ($exists) {
//             throw ValidationException::withMessages([
//                 'date' => ['Holiday already exists on this date.'],
//             ]);
//         }

//         Holiday::create([
//             'title' => trim($request->title),
//             'date' => $request->date,
//             'recurring_day' => null,
//             'type' => 'holiday',
//             'group_id' => $groupId,
//         ]);

//     } elseif ($request->recurring_type === 'weekly') {
//         $start = Carbon::parse($request->start_date);
//         $end   = Carbon::parse($request->end_date);

//         foreach ($request->weekday as $dayName) {
//             $dayNum = Carbon::parse($dayName)->dayOfWeek;

//             for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
//                 if ($date->dayOfWeek === $dayNum) {
//                     $exists = Holiday::where('date', $date->format('Y-m-d'))
//                         ->where('recurring_day', strtolower($dayName))
//                         ->exists();

//                     if (!$exists) {
//                         Holiday::create([
//                             'title' => trim($request->title),
//                             'date' => $date->format('Y-m-d'),
//                             'recurring_day' => strtolower($dayName),
//                             'type' => 'holiday',
//                             'group_id' => $groupId,
//                         ]);
//                     }
//                 }
//             }
//         }

//     } elseif ($request->recurring_type === 'monthly') {
//         $start = Carbon::parse($request->start_date);
//         $end   = Carbon::parse($request->end_date);

//         foreach ($request->month_dates as $day) {
//             $current = $start->copy();

//             while ($current->lte($end)) {
//                 try {
//                     $date = Carbon::create($current->year, $current->month, $day);

//                     if ($date->between($start, $end)) {
//                         $exists = Holiday::where('date', $date->format('Y-m-d'))->exists();

//                         if (!$exists) {
//                             Holiday::create([
//                                 'title' => trim($request->title),
//                                 'date' => $date->format('Y-m-d'),
//                                 'recurring_day' => null,
//                                 'type' => 'holiday',
//                                 'group_id' => $groupId,
//                             ]);
//                         }
//                     }
//                 } catch (\Exception $e) {
//                     // skip invalid dates like Feb 30
//                 }

//                 $current->addMonth();
//             }
//         }
//     }

//     return redirect()->route('holidays.index')->with('success', 'Holiday(s) added successfully');
// }

public function store(Request $request)
{
    $request->validate([
        'date.*'                => 'required|date',
        'occassion.*'           => 'required|string|max:255',
        'department_id_json'    => 'nullable|array',
        'designation_id_json'   => 'nullable|array',
        'employment_type_json'  => 'nullable|array',
    ]);

    // Generate a group_id for this batch
  $groupId = (string) \Illuminate\Support\Str::uuid();

    foreach ($request->date as $index => $holidayDate) {
        Holiday::create([
            'date'                 => $holidayDate,
            'title'                => $request->occassion[$index], // save occasion as title
            'occassion'            => $request->occassion[$index], // also save in occassion column if needed
            'department_id_json'   => $request->department_id_json ? json_encode($request->department_id_json) : null,
            'designation_id_json'  => $request->designation_id_json ? json_encode($request->designation_id_json) : null,
            'employment_type_json' => $request->employment_type_json ? json_encode($request->employment_type_json) : null,
            'group_id'             => $groupId,
            'is_active'            => 1, // default active
            'type'                 => 'holiday',
        ]);
    }

    return redirect()->route('holidays.index')->with('success', 'Holiday(s) added successfully');
}


    public function destroy(Holiday $holiday)
    {
        $holiday->delete();
        return back()->with('success', 'Holiday deleted.');
    }

    // For employee view
    public function employeeView(Request $request)
{
    $month = $request->month ?? now()->month;
    $year = $request->year ?? now()->year;

    $holidays = Holiday::whereMonth('date', $month)
        ->whereYear('date', $year)
        ->orderBy('date')
        ->get()
        ->groupBy(function ($holiday) {
            return \Carbon\Carbon::parse($holiday->date)->startOfWeek()->format('W'); // Group by week number
        });

    return view('admin.holidays.employee-index', compact('holidays', 'month', 'year'));
}
public function calendarView()
{
    $holidays = Holiday::all()->map(function ($holiday) {
        return [
            'title' => $holiday->title,
            'start' => $holiday->date,
            'allDay' => true,
        ];
    });

    return view('admin.holidays.calendar', ['holidays' => $holidays]);
}

// public function edit(Holiday $holiday)
// {
//     // Load related holidays if this holiday is part of a group
//     $relatedHolidays = $holiday->group_id
//         ? Holiday::where('group_id', $holiday->group_id)->get()
//         : collect([$holiday]);

//     $recurringType = null;
//     $weekdays = [];
//     $monthDates = [];
//     $startDate = null;
//     $endDate = null;

//     if ($relatedHolidays->count() > 1) {
//         // Detect weekly recurring
//         if ($relatedHolidays->pluck('recurring_day')->filter()->isNotEmpty()) {
//             $recurringType = 'weekly';
//             $weekdays = $relatedHolidays->pluck('recurring_day')
//                 ->unique()
//                 ->map(function ($day) {
//                     return ucfirst($day);
//                 })
//                 ->toArray();
//         }
//         // Detect monthly recurring
//         elseif (
//             $relatedHolidays
//                 ->pluck('date')
//                 ->map(function ($d) {
//                     return date('j', strtotime($d));
//                 })
//                 ->unique()
//                 ->count() > 1
//         ) {
//             $recurringType = 'monthly';
//             $monthDates = $relatedHolidays->pluck('date')
//                 ->map(function ($d) {
//                     return (int) date('j', strtotime($d));
//                 })
//                 ->unique()
//                 ->sort()
//                 ->values()
//                 ->toArray();
//         }

//         // Set start and end dates for recurring
//         $startDate = $relatedHolidays->min('date');
//         $endDate = $relatedHolidays->max('date');
//     }

//     // Pass all variables to the view
//     return view('admin.holidays.edit', [
//         'holiday'       => $holiday,
//         'recurringType' => $recurringType,
//         'weekdays'      => $weekdays,
//         'monthDates'    => $monthDates,
//         'startDate'     => $startDate,
//         'endDate'       => $endDate,
//     ]);
// }
// public function edit(Holiday $holiday)
// {
//     // All holidays in the same group (or just this one)
//     $relatedHolidays = $holiday->group_id
//         ? Holiday::where('group_id', $holiday->group_id)->get()
//         : collect([$holiday]);

//     $recurringType = null;
//     $weekdays = [];
//     $monthDates = [];
//     $startDate = null;
//     $endDate = null;

//     if ($relatedHolidays->count() > 1) {
//         // Weekly recurring
//         if ($relatedHolidays->pluck('recurring_day')->filter()->count() > 0) {
//             $recurringType = 'weekly';
//             $weekdays = $relatedHolidays->pluck('recurring_day')
//                 ->filter()
//                 ->map(fn($d) => ucfirst(strtolower($d)))
//                 ->unique()
//                 ->values()
//                 ->toArray();
//         } else {
//             // Monthly recurring
//             $recurringType = 'monthly';
//             $monthDates = $relatedHolidays->pluck('date')
//                 ->filter()
//                 ->map(fn($d) => (int) \Carbon\Carbon::parse($d)->day)
//                 ->unique()
//                 ->sort()
//                 ->values()
//                 ->toArray();
//         }

//         $startDate = $relatedHolidays->min('date');
//         $endDate   = $relatedHolidays->max('date');
//     } else {
//         // ✅ Single holiday → treat as single date
//         $recurringType = null;
//         $startDate = $holiday->date;
//         $endDate   = $holiday->date;
//     }

//     return view('admin.holidays.edit', [
//         'holiday'        => $holiday,
//         'relatedHolidays'=> $relatedHolidays,
//         'recurringType'  => $recurringType,
//         'weekdays'       => $weekdays,
//         'monthDates'     => $monthDates,
//         'startDate'      => $startDate,
//         'endDate'        => $endDate,
//     ]);
// }

public function edit(Holiday $holiday)
{
    $department   = Department::get();
    $designations = Designation::get();

    return view('admin.holidays.edit', compact('holiday','department','designations'));
}





// public function update(Request $request, Holiday $holiday)
// {
//     $request->validate([
//         'title' => 'required|string|max:30',
//         'recurring_type' => 'nullable|in:weekly,monthly',
//         'date' => 'nullable|required_unless:recurring_type,weekly,monthly|date',
//         'weekday' => 'required_if:recurring_type,weekly|array',
//         'weekday.*' => 'in:Sunday,Monday,Tuesday,Wednesday,Thursday,Friday,Saturday',
//         'month_dates' => 'required_if:recurring_type,monthly|array',
//         'month_dates.*' => 'integer|between:1,31',
//         'start_date' => 'nullable|required_if:recurring_type,weekly,monthly|date',
//         'end_date'   => 'nullable|required_if:recurring_type,weekly,monthly|date|after_or_equal:start_date',
//     ]);

//     // Use same group_id (or generate if null)
//     $groupId = $holiday->group_id ?? \Illuminate\Support\Str::uuid();

//     // Delete old holidays from this group
//     Holiday::where('group_id', $groupId)->delete();

//     // Recreate holidays with new rules
//     if ($request->recurring_type === null) {
//         Holiday::create([
//             'title' => trim($request->title),
//             'date' => $request->date,
//             'recurring_day' => null,
//             'type' => 'holiday',
//             'group_id' => $groupId,
//         ]);
//     } elseif ($request->recurring_type === 'weekly') {
//         $start = Carbon::parse($request->start_date);
//         $end   = Carbon::parse($request->end_date);

//         foreach ($request->weekday as $dayName) {
//             $dayNum = Carbon::parse($dayName)->dayOfWeek;

//             for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
//                 if ($date->dayOfWeek === $dayNum) {
//                     Holiday::create([
//                         'title' => trim($request->title),
//                         'date' => $date->format('Y-m-d'),
//                         'recurring_day' => strtolower($dayName),
//                         'type' => 'holiday',
//                         'group_id' => $groupId,
//                     ]);
//                 }
//             }
//         }
//     } elseif ($request->recurring_type === 'monthly') {
//         $start = Carbon::parse($request->start_date);
//         $end   = Carbon::parse($request->end_date);

//         foreach ($request->month_dates as $day) {
//             $current = $start->copy();

//             while ($current->lte($end)) {
//                 try {
//                     $date = Carbon::create($current->year, $current->month, $day);

//                     if ($date->between($start, $end)) {
//                         Holiday::create([
//                             'title' => trim($request->title),
//                             'date' => $date->format('Y-m-d'),
//                             'recurring_day' => null,
//                             'type' => 'holiday',
//                             'group_id' => $groupId,
//                         ]);
//                     }
//                 } catch (\Exception $e) {
//                     // Skip invalid dates like Feb 30
//                 }

//                 $current->addMonth();
//             }
//         }
//     }

//     return redirect()->route('holidays.index')->with('success', 'Holiday(s) updated successfully');
// }

public function update(Request $request, Holiday $holiday)
{
    $request->validate([
        'date.*'                => 'required|date',
        'occassion.*'           => 'required|string|max:255',
        'department_id_json'    => 'nullable|array',
        'designation_id_json'   => 'nullable|array',
        'employment_type_json'  => 'nullable|array',
    ]);

    // Use existing group_id or create new one
    $groupId = $holiday->group_id ?? (Holiday::max('group_id') + 1);

    // Fetch all existing holidays in this group
    $existingHolidays = Holiday::where('group_id', $groupId)->get();

    // Track updated IDs
    $updatedIds = [];

    foreach ($request->date as $index => $holidayDate) {
        $holidayData = [
            'date'                 => $holidayDate,
            'title'                => $request->occassion[$index],
            'occassion'            => $request->occassion[$index],
            'department_id_json'   => $request->department_id_json ? json_encode($request->department_id_json) : null,
            'designation_id_json'  => $request->designation_id_json ? json_encode($request->designation_id_json) : null,
            'employment_type_json' => $request->employment_type_json ? json_encode($request->employment_type_json) : null,
            'group_id'             => $groupId,
            'is_active'            => 1,
            'type'                 => 'holiday',
        ];

        // If an existing holiday at this index, update it
        if (isset($existingHolidays[$index])) {
            $existingHolidays[$index]->update($holidayData);
            $updatedIds[] = $existingHolidays[$index]->id;
        } else {
            // Otherwise, create a new one
            $new = Holiday::create($holidayData);
            $updatedIds[] = $new->id;
        }
    }

    // Delete holidays that were not in the submitted form
    Holiday::where('group_id', $groupId)
           ->whereNotIn('id', $updatedIds)
           ->delete();

    return redirect()->route('holidays.index')->with('success', 'Holiday(s) updated successfully');
}

// public function markHoliday(Request $request)
// {
//     try {
//         // validate modal fields
//         $validated = $request->validate([
//             'office_holiday_days' => 'nullable|array',
//             'occassion'           => 'nullable|string|max:255',
//         ]);

//         $holidays = [];

//         // if weekly days are selected → create holiday entries for each day
//         if (!empty($validated['office_holiday_days'])) {
//             foreach ($validated['office_holiday_days'] as $day) {
//                 $holidays[] = Holiday::create([
//                     'group_id'            => uniqid(),
//                     'title'               => $validated['occassion'] ?? date('l', strtotime("Sunday +{$day} days")),
//                     'date'                => null, // weekly pattern, not single date
//                     'occassion'           => $validated['occassion'] ?? date('l', strtotime("Sunday +{$day} days")),
//                     'department_id_json'  => null,
//                     'designation_id_json' => null,
//                     'employment_type_json'=> null,
//                     'is_active'           => 1,
//                     'type'                => 'weekly_holiday',
//                 ]);
//             }
//         } 
//         // else if user only enters an occasion (single holiday without day/date)
//         elseif (!empty($validated['occassion'])) {
//             $holidays[] = Holiday::create([
//                 'group_id'            => uniqid(),
//                 'title'               => $validated['occassion'],
//                 'date'                => null,
//                 'occassion'           => $validated['occassion'],
//                 'department_id_json'  => null,
//                 'designation_id_json' => null,
//                 'employment_type_json'=> null,
//                 'is_active'           => 1,
//                 'type'                => 'holiday',
//             ]);
//         }

//         return response()->json([
//             'status'   => 'success',
//             'message'  => 'Holiday(s) marked successfully',
//             'holidays' => $holidays
//         ], 201);

//     } catch (\Illuminate\Validation\ValidationException $e) {
//         return response()->json([
//             'status'  => 'error',
//             'message' => $e->errors()
//         ], 422);
//     } catch (\Exception $e) {
//         return response()->json([
//             'status'  => 'error',
//             'message' => 'Something went wrong: ' . $e->getMessage()
//         ], 500);
//     }
// }

public function markHoliday(Request $request)
{
    $request->validate([
        'office_holiday_days' => 'nullable|array',
        'occassion'           => 'nullable|string|max:255',
        'date'                => 'nullable|date',
    ]);

    // 1️⃣ Handle recurring weekly holidays (e.g., Sat & Sun for entire year)
    if ($request->filled('office_holiday_days')) {
        $days = ['sunday','monday','tuesday','wednesday','thursday','friday','saturday'];

        // Define current financial year (you can adjust if needed)
        $startOfYear = Carbon::now()->startOfYear();
        $endOfYear   = Carbon::now()->endOfYear();

        foreach ($request->office_holiday_days as $dayIndex) {
            $dayName = $days[$dayIndex];

            // find first occurrence of that weekday in the year
            $current = $startOfYear->copy()->next($dayName);

            while ($current->lte($endOfYear)) {
                Holiday::create([
                    'group_id'            => uniqid(),
                    'title'               => ucfirst($dayName),
                    'date'                => $current->format('Y-m-d'), // 👈 store actual date
                    'occassion'           => ucfirst($dayName),
                    'recurring_day'       => $dayName,
                    'department_id_json'  => null,
                    'designation_id_json' => null,
                    'employment_type_json'=> null,
                    'is_active'           => 1,
                    'type'                => 'weekly_holiday',
                ]);

                // jump to next week
                $current->addWeek();
            }
        }
    }

    // 2️⃣ Handle a one-time custom holiday
    if ($request->filled('occassion') && $request->filled('date')) {
        Holiday::create([
            'group_id'            => uniqid(),
            'title'               => $request->occassion,
            'date'                => $request->date,
            'occassion'           => $request->occassion,
            'recurring_day'       => null,
            'department_id_json'  => null,
            'designation_id_json' => null,
            'employment_type_json'=> null,
            'is_active'           => 1,
            'type'                => 'holiday',
        ]);
    }

    return redirect()->route('holidays.index')
        ->with('success', 'Holiday(s) added successfully');
}

public function bulkAction(Request $request)
    {
        $request->validate([
            'holiday_ids' => 'required|array',
            'action' => 'required|string|in:delete,mark_active,mark_inactive',
        ]);

        $holidays = Holiday::whereIn('id', $request->holiday_ids);

        switch ($request->action) {
            case 'delete':
                $holidays->delete();
                return response()->json(['message' => 'Selected holidays deleted successfully.']);
            
            case 'mark_active':
                $holidays->update(['notification_sent' => 1]); // Example field
                return response()->json(['message' => 'Selected holidays marked as active.']);
            
            case 'mark_inactive':
                $holidays->update(['notification_sent' => 0]); // Example field
                return response()->json(['message' => 'Selected holidays marked as inactive.']);
            
            default:
                return response()->json(['message' => 'Invalid action.'], 400);
        }
    }



}
