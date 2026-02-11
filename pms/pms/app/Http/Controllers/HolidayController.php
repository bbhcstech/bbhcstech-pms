<?php

namespace App\Http\Controllers;

use App\Models\Holiday;
use App\Models\Department;
use App\Models\Designation;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Http\Request;

class HolidayController extends Controller
{
    public function index(Request $request)
    {
        // Check user role
        $isAdmin = auth()->user()->role === 'admin';

        $query = Holiday::query();

        if ($request->filled('year')) {
            $query->whereYear('date', $request->year);
        }

        if ($request->filled('month')) {
            $query->whereMonth('date', $request->month);
        }

        $holidays = $query->orderBy('date', 'asc')->get();

        return view('admin.holidays.index', compact('holidays', 'isAdmin'))
            ->with('selectedYear', $request->year)
            ->with('selectedMonth', $request->month);
    }

    public function create()
    {
        // Only admin can create
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('holidays.index')
                ->with('error', 'You are not authorized to add holidays.');
        }

        $department = Department::get();
        $designations = Designation::get();
        return view('admin.holidays.create', compact('department', 'designations'));
    }

    public function store(Request $request)
    {
        // Only admin can store
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('holidays.index')
                ->with('error', 'You are not authorized to add holidays.');
        }

        $request->validate([
            'date.*'                => 'required|date',
            'occassion.*'           => 'required|string|max:255',
            'department_id_json'    => 'nullable|array',
            'designation_id_json'   => 'nullable|array',
            'employment_type_json'  => 'nullable|array',
        ]);

        $groupId = (string) Str::uuid();

        foreach ($request->date as $index => $holidayDate) {
            Holiday::create([
                'date'                 => $holidayDate,
                'title'                => $request->occassion[$index],
                'occassion'            => $request->occassion[$index],
                'department_id_json'   => $request->department_id_json ? json_encode($request->department_id_json) : null,
                'designation_id_json'  => $request->designation_id_json ? json_encode($request->designation_id_json) : null,
                'employment_type_json' => $request->employment_type_json ? json_encode($request->employment_type_json) : null,
                'group_id'             => $groupId,
                'type'                 => 'holiday',
                'recurring_day'        => null
            ]);
        }

        return redirect()->route('holidays.index')->with('success', 'Holiday(s) added successfully');
    }

    public function destroy(Holiday $holiday)
    {
        // Only admin can delete
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('holidays.index')
                ->with('error', 'You are not authorized to delete holidays.');
        }

        $holiday->delete();
        return back()->with('success', 'Holiday deleted.');
    }

    public function calendar(Request $request)
    {
        $isAdmin = auth()->user()->role === 'admin';

        // Get all holidays
        $holidays = Holiday::all();

        // Format for FullCalendar
        $events = [];

        foreach ($holidays as $holiday) {
            $event = [
                'id' => $holiday->id,
                'title' => $holiday->title,
                'start' => $holiday->date,
                'end' => $holiday->date,
                'color' => $this->getHolidayColor($holiday),
                'textColor' => '#fff',
                'allDay' => true,
                'extendedProps' => [
                    'description' => $holiday->occassion,
                    'type' => $holiday->type
                ]
            ];

            // Only admin gets edit URL
            if ($isAdmin) {
                $event['url'] = route('holidays.edit', $holiday->id);
            }

            $events[] = $event;
        }

        return view('admin.holidays.calendar', [
            'holidays' => json_encode($events),
            'isAdmin' => $isAdmin
        ]);
    }

    public function edit(Holiday $holiday)
    {
        // Only admin can edit
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('holidays.index')
                ->with('error', 'You are not authorized to edit holidays.');
        }

        $department = Department::get();
        $designations = Designation::get();
        return view('admin.holidays.edit', compact('holiday', 'department', 'designations'));
    }

    public function update(Request $request, Holiday $holiday)
    {
        // Only admin can update
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('holidays.index')
                ->with('error', 'You are not authorized to update holidays.');
        }

        $request->validate([
            'date.*'                => 'required|date',
            'occassion.*'           => 'required|string|max:255',
            'department_id_json'    => 'nullable|array',
            'designation_id_json'   => 'nullable|array',
            'employment_type_json'  => 'nullable|array',
        ]);

        $groupId = $holiday->group_id ?? (string) Str::uuid();
        $existingHolidays = Holiday::where('group_id', $groupId)->get();
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
                'type'                 => 'holiday',
                'recurring_day'        => null
            ];

            if (isset($existingHolidays[$index])) {
                $existingHolidays[$index]->update($holidayData);
                $updatedIds[] = $existingHolidays[$index]->id;
            } else {
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

    public function markHoliday(Request $request)
    {
        // Only admin can mark holidays
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('holidays.index')
                ->with('error', 'You are not authorized to mark holidays.');
        }

        $request->validate([
            'office_holiday_days' => 'nullable|array',
            'occassion'           => 'nullable|string|max:255',
            'date'                => 'nullable|date',
        ]);

        // Handle recurring weekly holidays
        if ($request->filled('office_holiday_days')) {
            $days = ['sunday','monday','tuesday','wednesday','thursday','friday','saturday'];
            $startOfYear = Carbon::now()->startOfYear();
            $endOfYear   = Carbon::now()->endOfYear();

            foreach ($request->office_holiday_days as $dayIndex) {
                $dayName = $days[$dayIndex];
                $current = $startOfYear->copy()->next($dayName);

                while ($current->lte($endOfYear)) {
                    Holiday::create([
                        'group_id'            => uniqid(),
                        'title'               => $request->occassion ?: ucfirst($dayName),
                        'date'                => $current->format('Y-m-d'),
                        'occassion'           => $request->occassion ?: ucfirst($dayName),
                        'recurring_day'       => $dayName,
                        'department_id_json'  => null,
                        'designation_id_json' => null,
                        'employment_type_json'=> null,
                        'type'                => 'weekly_holiday'
                    ]);

                    $current->addWeek();
                }
            }
        }

        // Handle a one-time custom holiday
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
                'type'                => 'holiday'
            ]);
        }

        return redirect()->route('holidays.index')->with('success', 'Holiday(s) added successfully');
    }

    public function bulkAction(Request $request)
    {
        // Only admin can perform bulk actions
        if (auth()->user()->role !== 'admin') {
            return response()->json([
                'error' => 'You are not authorized to perform this action.'
            ], 403);
        }

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
                $holidays->update(['notification_sent' => 1]);
                return response()->json(['message' => 'Selected holidays marked as active.']);

            case 'mark_inactive':
                $holidays->update(['notification_sent' => 0]);
                return response()->json(['message' => 'Selected holidays marked as inactive.']);

            default:
                return response()->json(['message' => 'Invalid action.'], 400);
        }
    }

    // Helper method for holiday colors
    private function getHolidayColor($holiday)
    {
        if ($holiday->type === 'weekly_holiday') {
            return '#28a745'; // Green for weekly holidays
        }

        return '#0d6efd'; // Blue for regular holidays
    }
}
