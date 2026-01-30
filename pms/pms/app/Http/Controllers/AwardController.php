<?php

namespace App\Http\Controllers;

use App\Models\Award;
use App\Models\Appreciations;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AwardController extends Controller
{
    public function index(Request $request)
    {
        $awards = Award::with(['user', 'appreciation']);

        if (Auth::user()->role !== 'admin') {
            $awards->where('user_id', Auth::id());
        }

        if ($request->filled('duration')) {
            $duration = $request->duration;

            if (str_contains($duration, 'to')) {
                [$start, $end] = preg_split('/\s*to\s*/', $duration);
                try {
                    $startDate = Carbon::parse(trim($start))->startOfDay();
                    $endDate   = Carbon::parse(trim($end))->endOfDay();

                    $awards->where(function ($q) use ($startDate, $endDate) {
                        $q->whereBetween('award_date', [$startDate, $endDate]);
                    });
                } catch (\Exception $e) {
                    // Invalid format, ignore
                }
            } else {
                switch ($duration) {
                    case 'Today':
                        $startDate = Carbon::today();
                        $endDate   = Carbon::today()->endOfDay();
                        break;
                    case 'Last 30 Days':
                        $startDate = Carbon::now()->subDays(29)->startOfDay();
                        $endDate   = Carbon::now()->endOfDay();
                        break;
                    case 'This Month':
                        $startDate = Carbon::now()->startOfMonth();
                        $endDate   = Carbon::now()->endOfMonth();
                        break;
                    case 'Last Month':
                        $startDate = Carbon::now()->subMonth()->startOfMonth();
                        $endDate   = Carbon::now()->subMonth()->endOfMonth();
                        break;
                    case 'Last 90 Days':
                        $startDate = Carbon::now()->subDays(89)->startOfDay();
                        $endDate   = Carbon::now()->endOfDay();
                        break;
                    case 'Last 6 Months':
                        $startDate = Carbon::now()->subMonths(6)->startOfMonth();
                        $endDate   = Carbon::now()->endOfDay();
                        break;
                    case 'Last 1 Year':
                        $startDate = Carbon::now()->subYear()->startOfMonth();
                        $endDate   = Carbon::now()->endOfDay();
                        break;
                    default:
                        $startDate = null;
                        $endDate   = null;
                }

                if ($startDate && $endDate) {
                    $awards->where(function ($q) use ($startDate, $endDate) {
                        $q->whereBetween('award_date', [$startDate, $endDate]);
                    });
                }
            }
        }

        $awards = $awards->latest()->get();
        return view('admin.awards.index', compact('awards'));
    }

    public function appreciationIndex(Request $request)
    {
        $appreciations = Appreciations::query();

        if ($request->has('status') && $request->status != '') {
            $appreciations->where('status', $request->status);
        }

        $appreciations = $appreciations->latest()->get();
        return view('admin.awards.appreciation-index', compact('appreciations'));
    }

    public function myAwards()
    {
        $awards = Award::with('appreciation')
            ->where('user_id', Auth::id())
            ->latest()
            ->get();

        return redirect()->route('awards.index');
    }

    public function create()
    {
        $employees = \App\Models\User::where('role', 'employee')->get();
        $appreciations = Appreciations::where('status', 'active')->get();
        return view('admin.awards.create', compact('employees', 'appreciations'));
    }

    public function appreciationCreate()
    {
        return view('admin.awards.appreciation-create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'appreciation_id' => 'required|exists:appreciations,id',
            'award_date' => 'required|date',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photo = $request->file('photo');
            $photoName = time() . '-' . $photo->getClientOriginalName();
            $photo->move(public_path('admin/uploads/awards'), $photoName);
            $photoPath = 'admin/uploads/awards/' . $photoName;
        }

        Award::create([
            'user_id' => $request->user_id,
            'appreciation_id' => $request->appreciation_id,
            'award_date' => $request->award_date,
            'photo' => $photoPath,
        ]);

        return redirect()->route('awards.index')->with('success', 'Award assigned successfully.');
    }

    public function appreciationStore(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'color_code' => 'required|string|max:7',
            'status' => 'required|in:active,inactive',
        ]);

        Appreciations::create([
            'title' => $request->title,
            'color_code' => $request->color_code,
            'status' => $request->status,
        ]);

        return redirect()->route('awards.appreciation-index')->with('success', 'Appreciation created successfully.');
    }

    public function edit($id)
    {
        $award = Award::findOrFail($id);
        $users = \App\Models\User::where('role', 'employee')->get();
        $appreciations = Appreciations::where('status', 'active')->get();
        return view('admin.awards.edit', compact('award', 'users', 'appreciations'));
    }

    public function appreciationEdit($id)
    {
        $appreciation = Appreciations::findOrFail($id);
        return view('admin.awards.appreciation-edit', compact('appreciation'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'appreciation_id' => 'required|exists:appreciations,id',
            'award_date' => 'required|date',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $award = Award::findOrFail($id);

        $photoPath = $award->photo;
        if ($request->hasFile('photo')) {
            if ($award->photo && file_exists(public_path($award->photo))) {
                unlink(public_path($award->photo));
            }

            $photo = $request->file('photo');
            $photoName = time() . '-' . $photo->getClientOriginalName();
            $photo->move(public_path('admin/uploads/awards'), $photoName);
            $photoPath = 'admin/uploads/awards/' . $photoName;
        }

        $award->update([
            'user_id' => $request->user_id,
            'appreciation_id' => $request->appreciation_id,
            'award_date' => $request->award_date,
            'photo' => $photoPath,
        ]);

        return redirect()->route('awards.index')->with('success', 'Award updated successfully.');
    }

    public function appreciationUpdate(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'given_to' => 'nullable|string|max:255',
            'given_on' => 'nullable|date',
            'status' => 'required|in:active,inactive',
        ]);

        $appreciation = Appreciations::findOrFail($id);
        $appreciation->update($request->all());

        return redirect()->route('awards.appreciation-index')->with('success', 'Appreciation updated successfully.');
    }

    public function destroy($id)
    {
        $award = Award::findOrFail($id);

        if ($award->photo && file_exists(public_path($award->photo))) {
            unlink(public_path($award->photo));
        }

        $award->delete();

        return redirect()->back()->with('success', 'Award deleted successfully.');
    }

    public function appreciationDestroy($id)
    {
        $appreciation = Appreciations::findOrFail($id);
        $appreciation->delete();

        return redirect()->back()->with('success', 'Appreciation deleted successfully.');
    }

    // Bulk Actions for Awards
    public function bulkAction(Request $request)
    {
        $ids = $request->ids;
        $action = $request->action;

        if (!$ids || count($ids) === 0) {
            return response()->json(['message' => 'No items selected'], 400);
        }

        if ($action === 'delete') {
            Award::whereIn('id', $ids)->delete();
            return response()->json(['message' => 'Selected items deleted successfully!']);
        }

        if ($action === 'change_status') {
            $status = $request->status;
            if (!$status) {
                return response()->json(['message' => 'Please select a status'], 400);
            }
            Award::whereIn('id', $ids)->update(['status' => $status]);
            return response()->json(['message' => "Selected items updated to {$status}"]);
        }

        return response()->json(['message' => 'No action performed']);
    }

    // NEW METHOD: Bulk Actions for Appreciations
    public function appreciationBulkAction(Request $request)
    {
        $ids = $request->ids;
        $action = $request->action;

        if (!$ids || count($ids) === 0) {
            return response()->json(['message' => 'No items selected'], 400);
        }

        if ($action === 'delete') {
            Appreciations::whereIn('id', $ids)->delete();
            return response()->json(['message' => 'Selected appreciation templates deleted successfully!']);
        }

        if ($action === 'status') {
            $status = $request->status;
            if (!$status) {
                return response()->json(['message' => 'Please select a status'], 400);
            }
            Appreciations::whereIn('id', $ids)->update(['status' => $status]);
            return response()->json(['message' => "Selected items updated to {$status}"]);
        }

        return response()->json(['message' => 'No action performed']);
    }

    // NEW METHOD: Update individual appreciation status
    public function updateStatus(Request $request, $id)
    {
        $appreciation = Appreciations::findOrFail($id);
        $appreciation->status = $request->status;
        $appreciation->save();

        return response()->json(['success' => true]);
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
        ]);

        $ids = $request->ids;
        $awards = Award::whereIn('id', $ids)->get();

        foreach ($awards as $award) {
            if ($award->photo && file_exists(public_path($award->photo))) {
                unlink(public_path($award->photo));
            }
        }

        Award::whereIn('id', $ids)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Selected awards deleted successfully!'
        ]);
    }
}
