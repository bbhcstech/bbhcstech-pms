<?php

namespace App\Http\Controllers;

use App\Models\Designation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Database\QueryException;

class DesignationController extends Controller
{
    public function index()
    {
        $designations = Designation::get();
        return view('admin.designations.index', compact('designations'));
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
            'parent_id' => ['nullable','exists:designations,id']
        ]);

        try {
            $designation = Designation::create([
                'name'            => $request->name,
                'parent_id'       => $request->parent_id ?: null,
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
            'parent_id' => ['nullable','exists:designations,id']
        ]);

        try {
            $designation->update([
                'name'            => $request->name,
                'parent_id'       => $request->parent_id ?: null,
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
