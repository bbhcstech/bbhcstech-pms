<?php

namespace App\Http\Controllers;

use App\Models\Designation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Database\QueryException;

class DesignationController extends Controller
{
    // public function index()
    // {

    //     // Use 'updatedBy' instead of 'lastUpdatedBy' to match your model method
    //     $designations = Designation::with(['addedBy', 'updatedBy'])->get();

    //     return view('admin.designations.index', compact('designations'));
    // }


    public function index(Request $request)
        {
            // Use 'updatedBy' instead of 'lastUpdatedBy' to match your model method
            $perPage = $request->get('per_page', 10);

            $designations = Designation::with(['addedBy', 'updatedBy'])
                ->paginate($perPage);

            // Get unique levels count
            $levelsCount = Designation::distinct('level')->count('level');

            return view('admin.designations.index', compact('designations', 'levelsCount'));
        }


    // Add this method after the 'index' method or before the 'create' method
            public function show(Designation $designation)
            {
                // Load relationships if needed
                $designation->load(['addedBy', 'updatedBy', 'parent', 'employeeDetails']);

                // If you want to show hierarchy information
                $hierarchy = $this->getHierarchyTree($designation);

                return view('admin.designations.show', compact('designation', 'hierarchy'));
            }

            // Optional: Helper method to get hierarchy tree
            private function getHierarchyTree(Designation $designation)
            {
                // Get all ancestors (parents, grandparents, etc.)
                $ancestors = collect();
                $current = $designation;

                while ($current->parent) {
                    $ancestors->prepend($current->parent);
                    $current = $current->parent;
                }

                // Get immediate children
                $children = $designation->children;

                // Get all descendants (for tree view)
                $descendants = $designation->descendants;

                return [
                    'ancestors' => $ancestors,
                    'children' => $children,
                    'descendants' => $descendants,
                ];
            }

    public function create()
    {
        $designations = Designation::get();
        return view('admin.designations.create', compact('designations'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'      => ['required','string','max:255', Rule::unique('designations','name')],
            'parent_id' => ['nullable','exists:designations,id'],
            'level'     => ['required','integer','min:0','max:6'] // Added level validation
        ]);

        try {
            $designation = Designation::create([
                'name'            => $request->name,
                'parent_id'       => $request->parent_id ?: null,
                'level'           => $request->level, // Added level
                'added_by'        => Auth::id(),
                'last_updated_by' => Auth::id(),
            ]);

            $designation->unique_code = 'DGN-' . str_pad($designation->id, 4, '0', STR_PAD_LEFT);
            $designation->saveQuietly();

        } catch (QueryException $e) {
            if (strpos($e->getMessage(), 'Duplicate') !== false) {
                return back()->withErrors(['name' => 'This designation name already exists.'])->withInput();
            }
            throw $e;
        }

        return redirect()->route('designations.index')->with('success', 'Designation added successfully.');
    }

    public function edit(Designation $designation)
    {
        $designations = Designation::get();
        return view('admin.designations.create', compact('designation', 'designations'));
    }

    public function update(Request $request, Designation $designation)
    {
        $request->validate([
            'name' => [
                'required','string','max:255',
                Rule::unique('designations', 'name')->ignore($designation->id)
            ],
            'parent_id' => ['nullable','exists:designations,id'],
            'level'     => ['required','integer','min:1','max:10'] // Added level validation
        ]);

        try {
            $designation->update([
                'name'            => $request->name,
                'parent_id'       => $request->parent_id ?: null,
                'level'           => $request->level, // Added level
                'last_updated_by' => Auth::id(),
            ]);

        } catch (QueryException $e) {
            if (strpos($e->getMessage(), 'Duplicate') !== false) {
                return back()->withErrors(['name' => 'This designation name already exists.'])
                    ->withInput();
            }
            throw $e;
        }

        return redirect()->route('designations.index')->with('success', 'Designation updated successfully.');
    }

    public function destroy(Designation $designation)
    {
        // CHECK: Employees linked or not
        if ($designation->employeeDetails()->count() > 0) {
            return back()->with('error', 'Designation cannot be deleted because Employees are tagged under it.');
        }

        $designation->delete();

        return redirect()->route('designations.index')->with('success', 'Designation deleted successfully.');
    }

    // ------------- BULK DELETE ----------------
    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids', []);

        if (empty($ids) || !is_array($ids)) {
            return $request->ajax()
                ? response()->json(['status' => false, 'message' => 'No designations selected.'], 422)
                : back()->with('error', 'No designations selected.');
        }

        $deleted = 0;
        $blocked = 0;

        foreach ($ids as $id) {
            $designation = Designation::find($id);

            if ($designation && $designation->employeeDetails()->count() == 0) {
                $designation->delete();
                $deleted++;
            } else {
                $blocked++;
            }
        }

        $message = "{$deleted} item(s) deleted. {$blocked} item(s) were not deleted because Employees are tagged under them.";

        return $request->ajax()
            ? response()->json(['status' => true, 'message' => $message])
            : back()->with('success', $message);
    }

    // ---------------- HIERARCHY VIEW ----------------
    public function hierarchy()
    {
        $designations = Designation::orderBy('order', 'asc')->get();

        return view('admin.designations.hierarchy', compact('designations'));
    }

    // ---------------- HIERARCHY UPDATE (RECURSIVE SAVE) ----------------
    public function saveHierarchy(Request $request)
    {
        if (!is_array($request->hierarchy)) {
            return response()->json(['message' => 'Invalid hierarchy format'], 422);
        }

        $this->updateHierarchy($request->hierarchy, null);

        return response()->json(['message' => 'Hierarchy saved successfully!']);
    }

    private function updateHierarchy(array $items, $parentId = null)
    {
        foreach ($items as $index => $item) {

            Designation::where('id', $item['id'])
                ->update([
                    'parent_id'       => $parentId,
                    'sort_order'      => $index,
                    'last_updated_by' => Auth::id(),
                ]);

            if (!empty($item['children'])) {
                $this->updateHierarchy($item['children'], $item['id']);
            }
        }
    }
}
