<?php

namespace App\Http\Controllers;
use App\Models\Award;
use App\Models\Appreciations;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;


class AwardController extends Controller
{
     // Show all awards (admin)
     public function index(Request $request)
{
    // Start query with relationships
    $query = Award::with('user','appreciation')->latest();

    // Non-admin users see only their awards
    if (auth()->user()->role !== 'admin') {
        $query->where('user_id', auth()->id());
    }

    // Optional date filter
    if ($request->filled('start_date') && $request->filled('end_date')) {
        $query->whereBetween('award_date', [$request->start_date, $request->end_date]);
    }

    // Fetch results
    $awards = $query->get();

    // Pass to view
    return view('admin.awards.index', compact('awards'));
}



    // Show form to create
    public function create()
    {
        $appreciations = Appreciations::get();
        $employees = User::where('role', 'employee')->get();
        return view('admin.awards.create', compact('employees','appreciations'));
    }

    // Store new award
//  public function store(Request $request)
// {
//       $request->validate([
//         'user_id' => 'required|exists:users,id',
//         'title'   => [
//             'required',
//             // prevent duplicate award for same employee & title on same date
//             Rule::unique('awards')->where(function ($query) use ($request) {
//                 return $query->where('user_id', $request->user_id)
//                              ->where('award_date', $request->award_date);
//             }),
//         ],
//         'award_date' => 'required|date',
//         'description' => 'nullable|string'
//     ], [
//         'title.unique' => 'This employee already has this award on the selected date.',
//     ]);

//     // Check for duplicate (case-insensitive + date only)
//     $existingAward = Award::where('user_id', $request->user_id)
//                           ->whereRaw('LOWER(title) = ?', [strtolower($request->title)])
//                           ->whereDate('award_date', $request->award_date)
//                           ->first();

//     if ($existingAward) {
//         return redirect()->back()->withInput()->withErrors([
//             'duplicate' => 'An award with the same title and date already exists for this user.',
//         ]);
//     }

//     Award::create($request->only(['user_id', 'title', 'description', 'award_date']));

//     return redirect()->route('awards.index')->with('success', 'Award added successfully.');
// }


public function store(Request $request)
{
    $request->validate([
        'user_id'     => 'required|exists:users,id',
        'award_date'  => 'required|date',
        'award_id'    => 'required',
        'description' => 'nullable|string',
        'image'       => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
    ], [
        'title.unique' => 'This employee already has this award on the selected date.',
    ]);

    // Check for duplicate (case-insensitive + date only)
    $existingAward = Award::where('user_id', $request->user_id)
                          ->whereRaw('LOWER(title) = ?', [strtolower($request->title)])
                          ->whereDate('award_date', $request->award_date)
                          ->first();

    if ($existingAward) {
        return redirect()->back()->withInput()->withErrors([
            'duplicate' => 'An award with the same title and date already exists for this user.',
        ]);
    }

    // Handle photo upload
    $photoPath = null;
    if ($request->hasFile('image')) {
        $image = $request->file('image');
        $imageName = time() . '-' . $image->getClientOriginalName();
        $image->move(public_path('admin/uploads/awards'), $imageName);
        $photoPath = 'admin/uploads/awards/' . $imageName;
    }

    // Save award
    Award::create([
        'user_id'    => $request->user_id,
        'award_id'   => $request->award_id,
        'title'      => $request->title,
        'description'=> $request->description,
        'award_date' => $request->award_date,
        'photo'      => $photoPath,   // <-- Save uploaded photo
    ]);

    return redirect()->route('awards.index')->with('success', 'Award added successfully.');
}


    // Show awards to employee (self)
    public function myAwards()
    {
        //  dd(auth()->id());
        $awards = Award::where('user_id', auth()->id())->latest()->get();
        
        return view('admin.awards.employee-index', compact('awards'));
    }
    
    // Show edit form
    public function edit($id)
    {
        $appreciations = Appreciations::get();
        $award = Award::findOrFail($id);
        $employees = User::all(); // assuming 'users' table contains employees
        return view('admin.awards.edit', compact('award', 'employees','appreciations'));
    }
    
    // Handle update
    // public function update(Request $request, $id)
    // {
    //     $request->validate([
    //         'title' => 'required|string|max:255',
    //         'description' => 'nullable|string',
    //     ]);
    
    //     $award = Award::findOrFail($id);
    
    //     // Prevent duplicate: same user, same date, and same new title (excluding self)
    //     $duplicate = Award::where('user_id', $award->user_id)
    //                       ->where('award_date', $award->award_date)
    //                       ->where('title', $request->title)
    //                       ->where('id', '!=', $id)
    //                       ->first();
    
    //     if ($duplicate) {
    //         return redirect()->back()->withInput()->withErrors([
    //             'duplicate' => 'An award with the same title and date already exists for this user.',
    //         ]);
    //     }
    
    //     $award->update([
    //         'title' => $request->title,
    //         'description' => $request->description,
    //     ]);
    
    //     return redirect()->route('awards.index')->with('success', 'Award updated successfully.');
    // }
    
    public function update(Request $request, $id)
{
    $request->validate([
        'award_id'    => 'required|exists:appreciations,id', // foreign key from appreciations
        'title'       => 'required|string|max:255',
        'description' => 'nullable|string',
        'award_date'  => 'required|date',
        'image'       => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
    ]);

    $award = Award::findOrFail($id);

    // Prevent duplicate: same user, same award_date, same award_id/title (excluding current record)
    $duplicate = Award::where('user_id', $award->user_id)
                      ->whereDate('award_date', $request->award_date)
                      ->where('award_id', $request->award_id)
                      ->where('title', $request->title)
                      ->where('id', '!=', $id)
                      ->first();

    if ($duplicate) {
        return redirect()->back()->withInput()->withErrors([
            'duplicate' => 'This employee already has this award on the selected date.',
        ]);
    }

    // Handle photo upload
    $photoPath = $award->photo; // keep old photo if not replaced
    if ($request->hasFile('image')) {
        $image = $request->file('image');
        $imageName = time() . '-' . $image->getClientOriginalName();
        $image->move(public_path('admin/uploads/awards'), $imageName);
        $photoPath = 'admin/uploads/awards/' . $imageName;

        // Optionally delete old image
        if ($award->photo && file_exists(public_path($award->photo))) {
            unlink(public_path($award->photo));
        }
    }

    // Update award
    $award->update([
        'award_id'    => $request->award_id,
        'title'       => $request->title,
        'description' => $request->description,
        'award_date'  => $request->award_date,
        'photo'       => $photoPath,
    ]);

    return redirect()->route('awards.index')->with('success', 'Award updated successfully.');
}

    
    // Handle delete
    public function destroy($id)
    {
        $award = Award::findOrFail($id);
        $award->delete();
    
        return redirect()->route('awards.index')->with('success', 'Award deleted successfully.');
    }
    
    // Appreciation
    public function appreciationstore(Request $request)
{
    $request->validate([
        'title'      => 'required|string|max:255',
        'icon'       => 'required|string|max:255',
        'color_code' => 'required|string|max:7',
        'summary'    => 'nullable|string',
    ]);

    Appreciations::create([
        'title'      => $request->title,
        'icon'       => $request->icon,
        'color_code' => $request->color_code,
        'summary'    => $request->summary,
    ]);

    return redirect()->route('awards.apreciation-index')
                     ->with('success', 'Award created successfully.');
}

       public function appreciationindex(Request $request)
    {
        $query = Appreciations::query();

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
         // Use the built query, not a fresh get()
        $appreciations = $query->get();
        return view('admin.awards.appreciationindex', compact('appreciations'));
    }

    // Show form to create
    public function appreciationcreate()
    {
     
        $appreciations = Appreciations::get();
        $employees = User::where('role', 'employee')->get();
        return view('admin.awards.appreciationcreate', compact('employees','appreciations'));
    }
    
     public function appreciationedit($id)
    {
        $appreciations = Appreciations::get();
        $award = Appreciations::findOrFail($id);
        $employees = User::all(); // assuming 'users' table contains employees
        return view('admin.awards.appreciationedit', compact('award', 'employees','appreciations'));
    }
    
    public function appreciationupdate(Request $request, $id)
    {
        $request->validate([
            'title'      => 'required|string|max:255',
            'icon'       => 'required|string|max:255',
            'color_code' => 'required|string|max:7', // hex color like #FF0000
            'summary'    => 'nullable|string',
        ]);
    
        $appreciation = Appreciations::findOrFail($id); // get the specific record
        $appreciation->update([
            'title'      => $request->title,
            'icon'       => $request->icon,
            'color_code' => $request->color_code,
            'summary'    => $request->summary,
        ]);
    
        return redirect()->route('awards.apreciation-index')
                         ->with('success', 'Award updated successfully.');
    }

    
    public function appreciationdestroy($id)
    {
        $award = Appreciations::findOrFail($id);
        $award->delete();
    
        return redirect()->route('awards.apreciation-index')
                         ->with('success', 'Appreciation deleted successfully.');
    }
    
public function updateStatus(Request $request, $id)
{
    $request->validate([
        'status' => 'required|in:active,inactive',
    ]);

    $appreciation = Appreciations::findOrFail($id);
    $appreciation->status = $request->status;
    $appreciation->save();

    return response()->json([
        'success' => true,
        'message' => 'Status updated successfully',
        'status'  => $appreciation->status
    ]);
}

public function bulkAction(Request $request)
{
    $request->validate([
        'action' => 'required|string',
        'ids' => 'required|array',
    ]);

    if ($request->action === 'delete') {
        Award::whereIn('id', $request->ids)->delete();
    } elseif ($request->action === 'status') {
        $request->validate(['status' => 'required|string|in:active,inactive']);
        Award::whereIn('id', $request->ids)->update(['status' => ucfirst($request->status)]);
    }

    return response()->json(['success' => true]);
}

public function apreciationbulkAction(Request $request)
{
    $request->validate([
        'action' => 'required|string',
        'ids' => 'required|array',
    ]);

    if ($request->action === 'delete') {
        Appreciations::whereIn('id', $request->ids)->delete();
    } elseif ($request->action === 'status') {
        $request->validate(['status' => 'required|string|in:active,inactive']);
        Appreciations::whereIn('id', $request->ids)->update(['status' => $request->status]);
    }

    return response()->json(['success' => true]);
}

public function bulkDeleteAwards(Request $request)
{
    $request->validate([
        'ids' => 'required|array',
        'ids.*' => 'integer|exists:awards,id',
    ]);

    $ids = $request->ids;

    DB::beginTransaction();
    try {

        $awards = Award::whereIn('id', $ids)->get(['id', 'image']);

        foreach ($awards as $award) {
            if ($award->image) {
                $photoPath = public_path(ltrim($award->image, '/'));
                if (file_exists($photoPath)) {
                    @unlink($photoPath);
                }
            }
        }

        Award::whereIn('id', $ids)->delete();

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Selected awards deleted successfully.',
        ]);

    } catch (\Throwable $e) {
        DB::rollBack();

        return response()->json([
            'success' => false,
            'message' => 'Something went wrong while deleting awards.',
            'error' => $e->getMessage(),
        ], 500);
    }
}





}
