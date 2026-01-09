<?php

namespace App\Http\Controllers;

use App\Models\Deal;
use App\Models\DealCategory;
use App\Models\DealStage;
use App\Models\User;
// use App\Models\Lead; // Commented out - no separate leads table needed
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DealController extends Controller
{
    public function index(Request $request)
    {
        // Build query with filters
        // Removed 'lead' from with() - lead info is stored directly in deals table
        $query = Deal::with(['stage', 'category', 'agent', 'watchers']);

        // Apply main filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('deal_name', 'like', "%$search%")
                  ->orWhere('lead_name', 'like', "%$search%")
                  ->orWhere('contact_details', 'like', "%$search%");
            });
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('close_date', [$request->start_date, $request->end_date]);
        }

        if ($request->filled('pipeline') && $request->pipeline != 'All') {
            $query->where('pipeline', $request->pipeline);
        }

        if ($request->filled('category') && $request->category != 'All') {
            $query->whereHas('category', function($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        if ($request->filled('product') && $request->product != 'All') {
            $query->where('product', $request->product);
        }

        // ADDED: Sidebar Filters
        if ($request->filled('created_from') && $request->filled('created_to')) {
            $query->whereBetween('created_at', [
                $request->created_from,
                $request->created_to
            ]);
        }

        if ($request->filled('updated_from') && $request->filled('updated_to')) {
            $query->whereBetween('updated_at', [
                $request->updated_from,
                $request->updated_to
            ]);
        }

        if ($request->filled('min_value')) {
            $query->where('value', '>=', $request->min_value);
        }

        if ($request->filled('max_value')) {
            $query->where('value', '<=', $request->max_value);
        }

        if ($request->filled('stages')) {
            $query->whereHas('stage', function($q) use ($request) {
                $q->whereIn('slug', $request->stages);
            });
        }

        if ($request->filled('agent_id')) {
            $query->where('deal_agent_id', $request->agent_id);
        }

        if ($request->filled('watcher_id')) {
            $query->whereHas('watchers', function($q) use ($request) {
                $q->where('user_id', $request->watcher_id);
            });
        }

        // Commented out - no lead_id column in deals table
        // if ($request->filled('lead_id')) {
        //     $query->where('lead_id', $request->lead_id);
        // }

        // Check if kanban view requested
        if ($request->has('view') && $request->view == 'kanban') {
            $stages = DealStage::orderBy('order')->get();
            $dealsByStage = [];

            foreach ($stages as $stage) {
                $dealsByStage[$stage->id] = (clone $query)
                    ->where('deal_stage_id', $stage->id)
                    ->get();
            }

            $categories = DealCategory::all();
            $agents = User::all();
            $pipelines = ['Sales Pipeline', 'Marketing Pipeline', 'Other Pipeline'];
            // $leads = Lead::all(); // Commented out - no separate leads table

            return view('admin.deals.index', compact('stages', 'dealsByStage', 'categories', 'agents', 'pipelines'));
        }

        // For table view
        $perPage = $request->per_page ?? 10; // Added show entries functionality
        $deals = $query->latest()->paginate($perPage);

        $categories = DealCategory::all();
        $stages = DealStage::all();
        $agents = User::all();
        $pipelines = ['Sales Pipeline', 'Marketing Pipeline', 'Other Pipeline'];
        // $leads = Lead::all(); // Commented out - no separate leads table

        return view('admin.deals.index', compact('deals', 'categories', 'stages', 'agents', 'pipelines'));
    }

    // ADDED: Add Follow Up Method
    public function addFollowUp(Request $request, Deal $deal)
    {
        $request->validate([
            'follow_up_date' => 'required|date',
            'follow_up_notes' => 'nullable|string'
        ]);

        $deal->update([
            'next_follow_up' => $request->follow_up_date
        ]);

        // You can create a follow up activity if you have activities table
        // $deal->activities()->create([
        //     'type' => 'follow_up',
        //     'description' => 'Follow up added: ' . $request->follow_up_notes,
        //     'created_by' => auth()->id()
        // ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Follow up added successfully'
            ]);
        }

        return back()->with('success', 'Follow up added successfully.');
    }

    public function create()
    {
        $categories = DealCategory::all();
        $stages = DealStage::all();
        $agents = User::all();

        // FIXED: Changed 'deals.create' to 'admin.deals.create'
        return view('admin.deals.create', compact('categories', 'stages', 'agents'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'deal_name' => 'required|string|max:255',
            'lead_name' => 'required|string|max:255',
            'contact_details' => 'required|string',
            'value' => 'required|numeric|min:0',
            'close_date' => 'required|date',
            'deal_stage_id' => 'required|exists:deal_stages,id',
            'pipeline' => 'nullable|string|max:255',
            'product' => 'nullable|string|max:255',
            'deal_agent_id' => 'nullable|exists:users,id',
            'deal_category_id' => 'nullable|exists:deal_categories,id',
            'next_follow_up' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        Deal::create([
            'deal_name' => $request->deal_name,
            'lead_name' => $request->lead_name,
            'contact_details' => $request->contact_details,
            'value' => $request->value,
            'close_date' => $request->close_date,
            'deal_stage_id' => $request->deal_stage_id,
            'pipeline' => $request->pipeline ?? 'Sales Pipeline',
            'product' => $request->product,
            'deal_agent_id' => $request->deal_agent_id,
            'deal_category_id' => $request->deal_category_id,
            'next_follow_up' => $request->next_follow_up,
            'notes' => $request->notes,
            'is_active' => true,
        ]);

        return redirect()->route('admin.deals.index')
            ->with('success', 'Deal created successfully.');
    }

    public function show(Deal $deal)
    {
        $deal->load(['stage', 'category', 'agent']);

        // FIXED: Changed 'deals.show' to 'admin.deals.show'
        return view('admin.deals.show', compact('deal'));
    }

    public function edit(Deal $deal)
    {
        $categories = DealCategory::all();
        $stages = DealStage::all();
        $agents = User::all();


        return view('admin.deals.edit', compact('deal', 'categories', 'stages', 'agents'));
    }

    public function update(Request $request, Deal $deal)
    {
        $request->validate([
            'deal_name' => 'required|string|max:255',
            'lead_name' => 'required|string|max:255',
            'contact_details' => 'required|string',
            'value' => 'required|numeric|min:0',
            'close_date' => 'required|date',
            'deal_stage_id' => 'required|exists:deal_stages,id',
            'pipeline' => 'nullable|string|max:255',
            'product' => 'nullable|string|max:255',
            'deal_agent_id' => 'nullable|exists:users,id',
            'deal_category_id' => 'nullable|exists:deal_categories,id',
            'next_follow_up' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $deal->update([
            'deal_name' => $request->deal_name,
            'lead_name' => $request->lead_name,
            'contact_details' => $request->contact_details,
            'value' => $request->value,
            'close_date' => $request->close_date,
            'deal_stage_id' => $request->deal_stage_id,
            'pipeline' => $request->pipeline ?? 'Sales Pipeline',
            'product' => $request->product,
            'deal_agent_id' => $request->deal_agent_id,
            'deal_category_id' => $request->deal_category_id,
            'next_follow_up' => $request->next_follow_up,
            'notes' => $request->notes,
        ]);

        return redirect()->route('admin.deals.index')
            ->with('success', 'Deal updated successfully.');
    }

    public function destroy(Deal $deal)
    {
        $deal->delete();

        return redirect()->route('admin.deals.index')
            ->with('success', 'Deal deleted successfully.');
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|string',
            'ids' => 'required|array',
            'ids.*' => 'exists:deals,id'
        ]);

        switch ($request->action) {
            case 'delete':
                Deal::whereIn('id', $request->ids)->delete();
                $message = 'Selected deals deleted successfully.';
                break;

            case 'change_stage':
                $request->validate([
                    'stage_id' => 'required|exists:deal_stages,id'
                ]);
                Deal::whereIn('id', $request->ids)->update(['deal_stage_id' => $request->stage_id]);
                $message = 'Stage updated for selected deals.';
                break;

            case 'assign_agent':
                $request->validate([
                    'agent_id' => 'required|exists:users,id'
                ]);
                Deal::whereIn('id', $request->ids)->update(['deal_agent_id' => $request->agent_id]);
                $message = 'Agent assigned to selected deals.';
                break;

            default:
                return back()->with('error', 'Invalid action.');
        }

        return back()->with('success', $message);
    }

    public function updateStage(Request $request, Deal $deal)
    {
        $request->validate([
            'stage_id' => 'required|exists:deal_stages,id'
        ]);

        $deal->update(['deal_stage_id' => $request->stage_id]);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Stage updated successfully.']);
        }

        return back()->with('success', 'Stage updated successfully.');
    }

    public function export(Request $request)
    {
        // Simple CSV export
        $deals = Deal::with(['stage', 'category', 'agent'])->get();

        $fileName = 'deals-' . date('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ];

        $callback = function() use ($deals) {
            $file = fopen('php://output', 'w');

            // Header row
            fputcsv($file, [
                'Deal Name', 'Lead Name', 'Contact Details', 'Value',
                'Close Date', 'Next Follow Up', 'Stage', 'Category', 'Agent',
                'Pipeline', 'Product', 'Notes'
            ]);

            // Data rows
            foreach ($deals as $deal) {
                fputcsv($file, [
                    $deal->deal_name,
                    $deal->lead_name,
                    $deal->contact_details,
                    $deal->value,
                    $deal->close_date->format('Y-m-d'),
                    $deal->next_follow_up ? $deal->next_follow_up->format('Y-m-d') : '',
                    $deal->stage->name,
                    $deal->category->name ?? '',
                    $deal->agent->name ?? '',
                    $deal->pipeline,
                    $deal->product ?? '',
                    $deal->notes ?? ''
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt'
        ]);

        try {
            $file = $request->file('file');
            $csvData = file_get_contents($file->getRealPath());
            $rows = array_map('str_getcsv', explode("\n", $csvData));

            $header = array_shift($rows);

            foreach ($rows as $row) {
                if (count($row) > 0 && count($row) == count($header)) {
                    $row = array_combine($header, $row);

                    // Find stage
                    $stage = DealStage::where('name', $row['Stage'])->first();

                    // Find category
                    $category = null;
                    if (!empty($row['Category'])) {
                        $category = DealCategory::where('name', $row['Category'])->first();
                    }

                    // Find agent
                    $agent = null;
                    if (!empty($row['Agent'])) {
                        $agent = User::where('name', $row['Agent'])->first();
                    }

                    Deal::create([
                        'deal_name' => $row['Deal Name'] ?? 'Imported Deal',
                        'lead_name' => $row['Lead Name'] ?? 'Imported Lead',
                        'contact_details' => $row['Contact Details'] ?? '',
                        'value' => $row['Value'] ?? 0,
                        'close_date' => $row['Close Date'] ?? now(),
                        'next_follow_up' => !empty($row['Next Follow Up']) ? $row['Next Follow Up'] : null,
                        'deal_stage_id' => $stage->id ?? 1,
                        'deal_category_id' => $category->id ?? null,
                        'deal_agent_id' => $agent->id ?? null,
                        'pipeline' => $row['Pipeline'] ?? 'Sales Pipeline',
                        'product' => $row['Product'] ?? null,
                        'notes' => $row['Notes'] ?? null,
                    ]);
                }
            }

            return back()->with('success', 'Deals imported successfully.');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to import deals: ' . $e->getMessage());
        }
    }
}
