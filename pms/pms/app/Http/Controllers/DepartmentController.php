<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\ParentDepartment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DepartmentController extends Controller
{
    /**
     * Show all departments
     */
    public function index()
    {
       $departments = Department::with('parent')->latest()->get();

        return view('admin.departments.index', compact('departments'));
    }

    /**
     * Show create form with next preview code
     */
    public function create()
    {
        $parentDepartments = ParentDepartment::all();
        $nextCode = $this->generateNextCodePreview();

        return view('admin.departments.create', compact('parentDepartments', 'nextCode'));
    }

    /**
     * Generate next automatic preview code (no DB lock)
     */
    private function generateNextCodePreview()
    {
        $prefix = 'SUB-';

        $last = Department::where('dpt_code', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->first();

        if ($last && preg_match('/(\d+)$/', $last->dpt_code, $m)) {
            $num = (int)$m[1] + 1;
        } else {
            $num = 1;
        }

        $pad = $num > 99 ? 3 : 2;

        return $prefix . str_pad($num, $pad, '0', STR_PAD_LEFT);
    }

    /**
     * Store new Department
     */
    public function store(Request $request)
    {
        $request->validate([
            'dpt_name' => 'required|string|max:255',
            'parent_dpt_id' => 'nullable|exists:parent_departments,id',
        ]);

        $prefix = 'SUB-';  // SAME prefix for preview + save

        DB::beginTransaction();

        try {

            // Lock row for concurrency safety
            $last = Department::where('dpt_code', 'like', $prefix . '%')
                ->orderBy('id', 'desc')
                ->lockForUpdate()
                ->first();

            if ($last && preg_match('/(\d+)$/', $last->dpt_code, $m)) {
                $nextNumber = (int)$m[1] + 1;
            } else {
                $nextNumber = 1;
            }

            $pad = $nextNumber > 99 ? 3 : 2;

            $generatedCode = $prefix . str_pad($nextNumber, $pad, '0', STR_PAD_LEFT);

            // EXTRA CHECK If duplicate somehow exists
            while (Department::where('dpt_code', $generatedCode)->exists()) {
                $nextNumber++;
                $pad = $nextNumber > 99 ? 3 : $pad;
                $generatedCode = $prefix . str_pad($nextNumber, $pad, '0', STR_PAD_LEFT);
            }

            Department::create([
                'dpt_name'       => $request->dpt_name,
                'dpt_code'       => $generatedCode,
                'parent_dpt_id'  => $request->parent_dpt_id,
                'added_by'       => Auth::id(),
            ]);

            DB::commit();

            return redirect()
                ->route('departments.index')
                ->with('success', 'Department created successfully.');

        } catch (\Throwable $e) {

            DB::rollBack();
            return redirect()
                ->back()
                ->with('error', 'Failed to create department. Please try again.');
        }
    }

    /**
     * Edit Department
     */
    public function edit(Department $department)
    {
        $parentDepartments = ParentDepartment::all();
        return view('admin.departments.edit', compact('department', 'parentDepartments'));
    }

    /**
     * Update Department
     */
    public function update(Request $request, Department $department)
    {
        $request->validate([
            'dpt_name' => 'required|string|max:255',
            'parent_dpt_id' => 'required|exists:parent_departments,id',
        ]);

        $department->update([
            'dpt_name'        => $request->dpt_name,
            'parent_dpt_id'   => $request->parent_dpt_id,
            'last_updated_by' => Auth::id(),
        ]);

        return redirect()
            ->route('departments.index')
            ->with('success', 'Department updated.');
    }

    /**
     * Delete single department (must respect employee rule)
     */
    public function destroy(Request $request, Department $department)
    {
        try {
            // RULE: cannot delete if any employees exist under this department
            if ($department->employeeDetails()->exists()) {
                $message = 'Sub Department cannot be deleted because Employees are tagged under it.';

                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'status'  => 'error',
                        'message' => $message,
                    ], 422);
                }

                return back()->withErrors($message);
            }

            $department->delete();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status'  => 'success',
                    'message' => 'Department deleted successfully',
                ]);
            }

            return redirect()
                ->route('departments.index')
                ->with('success', 'Department deleted successfully');

        } catch (\Throwable $e) {

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Delete failed.',
                    'error'   => $e->getMessage(),
                ], 500);
            }

            return back()->withErrors('Delete failed: ' . $e->getMessage());
        }
    }


    /**
     * Bulk Delete Departments
     * Must respect: cannot delete departments that have employees.
     */
    public function bulkDestroy(Request $request)
    {
        \Log::debug('bulkDestroy payload', $request->all());

        $ids = $request->input('bulk_ids', []);

        if (!is_array($ids)) {
            $ids = is_string($ids)
                ? array_filter(array_map('trim', explode(',', $ids)))
                : [$ids];
        }

        // Normalize integers
        $ids = array_values(array_filter(array_map(fn($v) => is_numeric($v) ? (int)$v : null, $ids)));

        if (empty($ids)) {
            $msg = 'No IDs provided.';

            return $request->wantsJson()
                ? response()->json(['status' => 'error', 'message' => $msg], 422)
                : back()->withErrors('No departments selected for deletion.');
        }

        $foundIds = Department::whereIn('id', $ids)->pluck('id')->map(fn($v) => (int)$v)->toArray();

        $missing = array_values(array_diff($ids, $foundIds));
        if (!empty($missing)) {
            $msg = 'Some selected items do not exist: ' . implode(', ', $missing);

            return $request->wantsJson()
                ? response()->json(['status' => 'error', 'message' => $msg], 422)
                : back()->withErrors($msg);
        }

        DB::beginTransaction();

        try {
            // Load with count of employees
            $departments = Department::withCount('employeeDetails')
                ->whereIn('id', $foundIds)
                ->get();

            // Blocked: have employees
            $blocked   = $departments->where('employee_details_count', '>', 0);
            // Deletable: no employees
            $deletable = $departments->where('employee_details_count', 0);

            $deletableIds = $deletable->pluck('id')->all();

            $deleted = 0;
            if (!empty($deletableIds)) {
                $deleted = Department::whereIn('id', $deletableIds)->delete();
            }

            DB::commit();

            $blockedCount = $blocked->count();

            $messageParts   = [];
            $messageParts[] = "$deleted item(s) deleted.";
            if ($blockedCount > 0) {
                $blockedList   = $blocked->pluck('dpt_name')->implode(', ');
                $messageParts[] = "$blockedCount item(s) were not deleted because Employees are tagged under them: $blockedList";
            }
            $finalMessage = implode(' ', $messageParts);

            return $request->wantsJson()
                ? response()->json([
                    'status'        => 'success',
                    'deleted'       => $deleted,
                    'blocked'       => $blockedCount,
                    'blocked_names' => $blocked->pluck('dpt_name')->values(),
                    'deleted_ids'   => $deletableIds,
                    'message'       => $finalMessage,
                ])
                : redirect()->route('departments.index')->with('success', $finalMessage);

        } catch (\Throwable $e) {

            DB::rollBack();
            \Log::error('bulkDestroy error', ['error' => $e->getMessage()]);

            return $request->wantsJson()
                ? response()->json([
                    'status'  => 'error',
                    'message' => 'Bulk delete failed.',
                    'error'   => $e->getMessage()
                ], 500)
                : back()->withErrors('Bulk delete failed: ' . $e->getMessage());
        }
    }
}
