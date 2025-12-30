<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Project;
use App\Models\User;
use App\Models\TaskLabel;
use App\Models\TaskCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; // Add this line to import DB facade
use Carbon\Carbon;

class TaskCategoryController extends Controller
{
  public function store(Request $request)
{
    $request->validate([
        'category_name' => 'required|string|max:191',
    ]);

    TaskCategory::create([
        'category_name' => $request->category_name,
        'company_id' => auth()->user()->company_id ?? null,
        'added_by' => auth()->id(),
    ]);

    return redirect()->back()->with('success', 'Task category added.');
}

public function destroy($id)
{
    TaskCategory::findOrFail($id)->delete();
    return redirect()->back()->with('success', 'Category deleted.');
}
  
}