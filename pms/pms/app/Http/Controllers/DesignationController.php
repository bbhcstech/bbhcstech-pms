<?php

namespace App\Http\Controllers;

use App\Models\Designation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Database\QueryException;

class DesignationController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);

        // FIX 1: Order by level to ensure proper display
        $designations = Designation::with(['addedBy', 'updatedBy'])
            ->orderBy('level', 'asc')  // ADD THIS LINE
            ->orderBy('name', 'asc')   // ADD THIS LINE
            ->paginate($perPage);

        // Get unique levels count
        $levelsCount = Designation::distinct('level')->count('level');

        return view('admin.designations.index', compact('designations', 'levelsCount'));
    }

    public function show(Designation $designation)
    {
        $designation->load(['addedBy', 'updatedBy', 'parent', 'employeeDetails']);
        $hierarchy = $this->getHierarchyTree($designation);
        return view('admin.designations.show', compact('designation', 'hierarchy'));
    }

    private function getHierarchyTree(Designation $designation)
    {
        $ancestors = collect();
        $current = $designation;

        while ($current->parent) {
            $ancestors->prepend($current->parent);
            $current = $current->parent;
        }

        $children = $designation->children;
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
            'level'     => ['required','integer','min:0','max:6']
        ]);

        try {
            $designation = Designation::create([
                'name'        => $request->name,
                'parent_id'   => $request->parent_id ?: null,
                'level'       => $request->level,
                'added_by'    => Auth::id(),
                'updated_by'  => Auth::id(),  // FIX 2: Changed from 'last_updated_by' to 'updated_by'
            ]);

            $designation->unique_code = 'DGN-' . str_pad($designation->id, 4, '0', STR_PAD_LEFT);
            $designation->saveQuietly();

            if ($request->ajax()) {
                return response()->json([
                    'status' => 'success',
                    'designation' => [
                        'id'          => $designation->id,
                        'name'        => $designation->name,
                        'level'       => $designation->level,
                        'unique_code' => $designation->unique_code
                    ]
                ]);
            }

        } catch (QueryException $e) {
            if (strpos($e->getMessage(), 'Duplicate') !== false) {
                if ($request->ajax()) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'This designation name already exists.'
                    ], 422);
                }
                return back()->withErrors(['name' => 'This designation name already exists.'])->withInput();
            }
            throw $e;
        }

        // FIX 3: Add timestamp to prevent caching
        return redirect()->route('designations.index', ['t' => time()])
            ->with('success', 'Designation added successfully.');
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
            'level'     => ['required','integer','min:0','max:6']
        ]);

        try {
            $designation->update([
                'name'        => $request->name,
                'parent_id'   => $request->parent_id ?: null,
                'level'       => $request->level,
                'updated_by'  => Auth::id(),  // FIX 4: Changed from 'last_updated_by' to 'updated_by'
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'status' => 'success',
                    'designation' => [
                        'id'          => $designation->id,
                        'name'        => $designation->name,
                        'level'       => $designation->level,
                        'unique_code' => $designation->unique_code
                    ]
                ]);
            }

        } catch (QueryException $e) {
            if (strpos($e->getMessage(), 'Duplicate') !== false) {
                if ($request->ajax()) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'This designation name already exists.'
                    ], 422);
                }
                return back()->withErrors(['name' => 'This designation name already exists.'])
                    ->withInput();
            }
            throw $e;
        }

        // FIX 5: Add timestamp and updated_id to prevent caching
        return redirect()->route('designations.index', ['t' => time()])
            ->with('success', 'Designation updated successfully.')
            ->with('updated_id', $designation->id);  // Send back ID for potential JS update
    }

    public function ajaxStore(Request $request)
    {
        $request->validate([
            'name'  => ['required','string','max:255', Rule::unique('designations','name')],
            'level' => ['required','integer','min:0','max:6']
        ]);

        try {
            $designation = Designation::create([
                'name'       => $request->name,
                'level'      => $request->level,
                'added_by'   => Auth::id(),
                'updated_by' => Auth::id(),  // FIX 6: Consistency
            ]);

            $designation->unique_code = 'DGN-' . str_pad($designation->id, 4, '0', STR_PAD_LEFT);
            $designation->saveQuietly();

            return response()->json([
                'status' => 'success',
                'designation' => [
                    'id'          => $designation->id,
                    'name'        => $designation->name,
                    'level'       => $designation->level,
                    'unique_code' => $designation->unique_code
                ]
            ]);

        } catch (QueryException $e) {
            if (strpos($e->getMessage(), 'Duplicate') !== false) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'This designation name already exists.'
                ], 422);
            }
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while saving the designation.'
            ], 500);
        }
    }

    public function destroy(Designation $designation)
    {
        if ($designation->employeeDetails()->count() > 0) {
            return back()->with('error', 'Designation cannot be deleted because Employees are tagged under it.');
        }

        $designation->delete();

        return redirect()->route('designations.index')
            ->with('success', 'Designation deleted successfully.');
    }

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

    public function hierarchy()
    {
        $designations = Designation::orderBy('order', 'asc')->get();
        return view('admin.designations.hierarchy', compact('designations'));
    }

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
                    'parent_id'  => $parentId,
                    'sort_order' => $index,
                    'updated_by' => Auth::id(),  // FIX 7: Consistency
                ]);

            if (!empty($item['children'])) {
                $this->updateHierarchy($item['children'], $item['id']);
            }
        }
    }
}
