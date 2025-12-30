<?php

namespace App\Http\Controllers;

use App\Models\ParentDepartment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;

class ParentDepartmentController extends Controller
{
    /**
     * Display a listing of parent departments.
     */
    public function index(Request $request)
    {
        $departments = ParentDepartment::orderBy('id', 'desc')->get();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['data' => $departments]);
        }

        return view('admin.parent_departments.index', compact('departments'));
    }

    /**
     * Show the form for creating a new parent department.
     * Pre-computes next code for preview.
     */
    public function create()
    {
        $nextCode = $this->computeNextCode();
        return view('admin.parent_departments.create', compact('nextCode'));
    }

    /**
     * Return next available code as JSON for AJAX usage.
     */
    public function nextCode(Request $request): JsonResponse
    {
        $nextCode = $this->computeNextCode();
        return response()->json(['next_code' => $nextCode]);
    }

    /**
     * Store a newly created parent department in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'dpt_name' => 'required|string|max:255',
        ]);

        $prefix = 'DEP-';
        $pad = 4;

        DB::beginTransaction();
        try {
            // compute numeric max safely within transaction to avoid race conditions
            $max = ParentDepartment::where('dpt_code', 'like', $prefix . '%')
                ->selectRaw('COALESCE(MAX(CAST(SUBSTRING(dpt_code, ?) AS UNSIGNED)), 0) as mx', [strlen($prefix) + 1])
                ->lockForUpdate()
                ->value('mx');

            $nextNumber = ((int) $max) + 1;
            $generatedCode = $prefix . str_pad($nextNumber, $pad, '0', STR_PAD_LEFT);

            // create in parent_departments table via model
            $dpt = ParentDepartment::create([
                'dpt_name' => $request->dpt_name,
                'dpt_code' => $generatedCode,
                'added_by' => Auth::id(),
            ]);

            DB::commit();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['status' => 'success', 'dpt' => $dpt]);
            }

            return redirect()->route('parent-departments.index')->with('success', 'Parent department created successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            logger()->error('ParentDepartment store error: ' . $e->getMessage(), ['exception' => $e]);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['status' => 'error', 'message' => 'Failed to create department.', 'error' => $e->getMessage()], 500);
            }

            return back()->withErrors('Failed to create department: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Show the form for editing the specified parent department.
     */
    public function edit(ParentDepartment $parentDepartment)
    {
        // pass nextCode too if your form expects it; not required for edits
        $nextCode = $this->computeNextCode();
        return view('admin.parent_departments.create', compact('parentDepartment', 'nextCode'));
    }

    /**
     * Update specified parent department.
     */
    public function update(Request $request, ParentDepartment $parentDepartment)
    {
        $request->validate([
            'dpt_name' => 'required|string|max:255',
        ]);

        try {
            $parentDepartment->update([
                'dpt_name' => $request->dpt_name,
                'last_updated_by' => Auth::id(),
            ]);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['status' => 'success', 'dpt' => $parentDepartment->fresh()]);
            }

            return redirect()->route('parent-departments.index')->with('success', 'Updated successfully.');
        } catch (\Throwable $e) {
            logger()->error('ParentDepartment update error: ' . $e->getMessage(), ['exception' => $e]);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['status' => 'error', 'message' => 'Update failed.', 'error' => $e->getMessage()], 500);
            }

            return back()->withErrors('Update failed: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified parent department.
     * Prevent deletion if sub-departments exist.
     */
    public function destroy(Request $request, ParentDepartment $parentDepartment)
    {
        try {
            if ($parentDepartment->departments()->exists()) {
                $message = 'Parent Department cannot be deleted because it has Sub Departments linked to it.';

                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json(['status' => 'error', 'message' => $message], 422);
                }

                return back()->withErrors($message);
            }

            $parentDepartment->delete();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['status' => 'success', 'message' => 'Deleted successfully.']);
            }

            return redirect()->route('parent-departments.index')->with('success', 'Deleted successfully.');
        } catch (\Throwable $e) {
            logger()->error('ParentDepartment delete error: ' . $e->getMessage(), ['exception' => $e]);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['status' => 'error', 'message' => 'Delete failed.', 'error' => $e->getMessage()], 500);
            }

            return back()->withErrors('Delete failed: ' . $e->getMessage());
        }
    }

    /**
     * Bulk delete multiple ParentDepartment records.
     * Input expects bulk_ids => array or comma separated string.
     */
    public function bulkDestroy(Request $request)
    {
        $ids = $request->input('bulk_ids', []);

        if (!is_array($ids)) {
            if (is_string($ids)) {
                $ids = array_filter(array_map('trim', explode(',', $ids)));
            } else {
                $ids = [$ids];
            }
        }

        $ids = array_values(array_filter(array_map(function ($v) {
            return is_numeric($v) ? (int) $v : null;
        }, $ids)));

        if (empty($ids)) {
            $msg = 'No departments selected for deletion.';
            return $request->wantsJson() || $request->ajax()
                ? response()->json(['status' => 'error', 'message' => $msg], 422)
                : back()->withErrors($msg);
        }

        DB::beginTransaction();
        try {
            $parents = ParentDepartment::withCount('departments')
                ->whereIn('id', $ids)
                ->get();

            $foundIds = $parents->pluck('id')->map(fn($v) => (int) $v)->toArray();
            $missing = array_values(array_diff($ids, $foundIds));

            if (!empty($missing)) {
                $msg = 'Some selected items do not exist: ' . implode(', ', $missing);
                DB::rollBack();
                return $request->wantsJson() || $request->ajax()
                    ? response()->json(['status' => 'error', 'message' => $msg], 422)
                    : back()->withErrors($msg);
            }

            $blocked = $parents->where('departments_count', '>', 0);
            $deletable = $parents->where('departments_count', 0);
            $deletableIds = $deletable->pluck('id')->all();

            $deletedCount = 0;
            if (!empty($deletableIds)) {
                $deletedCount = ParentDepartment::whereIn('id', $deletableIds)->delete();
            }

            DB::commit();

            $blockedCount = $blocked->count();
            $messageParts = [];
            $messageParts[] = "{$deletedCount} item(s) deleted.";
            if ($blockedCount > 0) {
                $blockedList = $blocked->pluck('dpt_name')->implode(', ');
                $messageParts[] = "{$blockedCount} item(s) were not deleted because they have Sub Departments: {$blockedList}";
            }
            $finalMessage = implode(' ', $messageParts);

            return $request->wantsJson() || $request->ajax()
                ? response()->json([
                    'status' => 'success',
                    'deleted' => $deletedCount,
                    'blocked' => $blockedCount,
                    'blocked_names' => $blocked->pluck('dpt_name')->values(),
                    'deleted_ids' => $deletableIds,
                    'message' => $finalMessage,
                ])
                : redirect()->route('parent-departments.index')->with('success', $finalMessage);
        } catch (\Throwable $e) {
            DB::rollBack();
            logger()->error('ParentDepartment bulk destroy error: ' . $e->getMessage(), ['exception' => $e]);

            return $request->wantsJson() || $request->ajax()
                ? response()->json(['status' => 'error', 'message' => 'Bulk delete failed.', 'error' => $e->getMessage()], 500)
                : back()->withErrors('Bulk delete failed: ' . $e->getMessage());
        }
    }

    /**
     * Compute next department code using numeric max of existing suffixes.
     * Returns string like DEP-0001
     */
    protected function computeNextCode(string $prefix = 'DEP-', int $pad = 4): string
    {
        $max = ParentDepartment::where('dpt_code', 'like', $prefix . '%')
            ->selectRaw('COALESCE(MAX(CAST(SUBSTRING(dpt_code, ?) AS UNSIGNED)), 0) as mx', [strlen($prefix) + 1])
            ->value('mx');

        $next = ((int) $max) + 1;
        return $prefix . str_pad($next, $pad, '0', STR_PAD_LEFT);
    }
}
