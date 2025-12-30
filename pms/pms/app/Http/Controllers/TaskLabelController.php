<?php

namespace App\Http\Controllers;
use App\Models\TaskLabel;

use Illuminate\Http\Request;

class TaskLabelController extends Controller
{
   public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:191',
            'color' => 'required|string|max:20',
            'project_id' => 'nullable|integer|exists:projects,id',
            'description' => 'nullable|string|max:255',
        ]);

        TaskLabel::create([
            'label_name' => $request->name,
            'color' => $request->color,
            'project_id' => $request->project_id,
            'description' => $request->description,
        ]);

        return redirect()->back()->with('success', 'Label added successfully.');
    }

    public function destroy($id)
    {
        TaskLabel::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Label deleted.');
    }
}
