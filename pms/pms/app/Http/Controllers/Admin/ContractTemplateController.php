<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContractTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContractTemplateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = ContractTemplate::with('creator')
            ->latest();

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $perPage = $request->get('per_page', 50);
        $templates = $query->paginate($perPage);

        return view('admin.contract-templates.index', compact('templates'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $types = ['normal' => 'Normal', 'special' => 'Special', 'fixed_price' => 'Fixed Price', 'time_material' => 'Time & Material'];
        $currencies = ['INR' => 'INR', 'USD' => 'USD', 'EUR' => 'EUR', 'GBP' => 'GBP'];

        return view('admin.contract-templates.create', compact('types', 'currencies'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|in:normal,special,fixed_price,time_material',
            'default_value' => 'nullable|numeric|min:0',
            'currency' => 'required|string|max:3',
            'duration_days' => 'required|integer|min:1',
            'terms' => 'nullable|string',
        ]);

        $validated['created_by'] = Auth::id();
        $validated['is_active'] = $request->has('is_active');

        ContractTemplate::create($validated);

        return redirect()->route('admin.contract-templates.index')
            ->with('success', 'Contract template created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(ContractTemplate $contractTemplate)
    {
        return view('admin.contract-templates.show', compact('contractTemplate'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ContractTemplate $contractTemplate)
    {
        $types = ['normal' => 'Normal', 'special' => 'Special', 'fixed_price' => 'Fixed Price', 'time_material' => 'Time & Material'];
        $currencies = ['INR' => 'INR', 'USD' => 'USD', 'EUR' => 'EUR', 'GBP' => 'GBP'];

        return view('admin.contract-templates.edit', compact('contractTemplate', 'types', 'currencies'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ContractTemplate $contractTemplate)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|in:normal,special,fixed_price,time_material',
            'default_value' => 'nullable|numeric|min:0',
            'currency' => 'required|string|max:3',
            'duration_days' => 'required|integer|min:1',
            'terms' => 'nullable|string',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $contractTemplate->update($validated);

        return redirect()->route('admin.contract-templates.index')
            ->with('success', 'Contract template updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ContractTemplate $contractTemplate)
    {
        $contractTemplate->delete();

        return redirect()->route('admin.contract-templates.index')
            ->with('success', 'Contract template deleted successfully.');
    }

    /**
     * Toggle template status.
     */
    public function toggleStatus(ContractTemplate $contractTemplate)
    {
        $contractTemplate->update([
            'is_active' => !$contractTemplate->is_active
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Template status updated successfully.',
            'is_active' => $contractTemplate->is_active
        ]);
    }

    /**
     * Get template content for use in contract creation.
     */
    public function getTemplateContent(ContractTemplate $contractTemplate)
    {
        return response()->json([
            'subject' => $contractTemplate->subject,
            'content' => $contractTemplate->content,
            'type' => $contractTemplate->type,
            'default_value' => $contractTemplate->default_value,
            'currency' => $contractTemplate->currency,
            'terms' => $contractTemplate->terms,
            'duration_days' => $contractTemplate->duration_days,
        ]);
    }
}
