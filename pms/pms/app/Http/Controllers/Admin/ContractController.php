<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\Client;
use App\Models\Project;
use App\Models\ContractTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContractController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Contract::with(['client', 'project', 'creator'])
            ->latest();

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                  ->orWhere('contract_number', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Date filter
        if ($request->filled('start_date')) {
            $query->whereDate('start_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('end_date', '<=', $request->end_date);
        }

        // Client filter
        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        // Type filter
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $perPage = $request->get('per_page', 50);
        $contracts = $query->paginate($perPage);

        $clients = Client::active()->get();
        $types = ['normal', 'special', 'fixed_price', 'time_material'];
        $statuses = ['draft', 'active', 'expired', 'terminated', 'completed'];

        return view('admin.contracts.index', compact('contracts', 'clients', 'types', 'statuses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $clients = Client::active()->get();
        $projects = Project::active()->get();
        $templates = ContractTemplate::active()->get();
        $types = ['normal' => 'Normal', 'special' => 'Special', 'fixed_price' => 'Fixed Price', 'time_material' => 'Time & Material'];
        $currencies = ['INR' => 'INR', 'USD' => 'USD', 'EUR' => 'EUR', 'GBP' => 'GBP'];

        $contractNumber = Contract::generateContractNumber();

        return view('admin.contracts.create', compact(
            'clients',
            'projects',
            'templates',
            'types',
            'currencies',
            'contractNumber'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'contract_number' => 'required|unique:contracts|max:50',
            'subject' => 'required|string|max:255',
            'client_id' => 'required|exists:clients,id',
            'project_id' => 'nullable|exists:projects,id',
            'description' => 'nullable|string',
            'type' => 'required|in:normal,special,fixed_price,time_material',
            'contract_value' => 'required|numeric|min:0',
            'currency' => 'required|string|max:3',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'terms' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $validated['created_by'] = Auth::id();
        $validated['status'] = 'draft';

        $contract = Contract::create($validated);

        return redirect()->route('admin.contracts.index')
            ->with('success', 'Contract created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Contract $contract)
    {
        $contract->load(['client', 'project', 'creator']);
        return view('admin.contracts.show', compact('contract'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Contract $contract)
    {
        $clients = Client::active()->get();
        $projects = Project::active()->get();
        $types = ['normal' => 'Normal', 'special' => 'Special', 'fixed_price' => 'Fixed Price', 'time_material' => 'Time & Material'];
        $currencies = ['INR' => 'INR', 'USD' => 'USD', 'EUR' => 'EUR', 'GBP' => 'GBP'];
        $statuses = ['draft' => 'Draft', 'active' => 'Active', 'expired' => 'Expired', 'terminated' => 'Terminated', 'completed' => 'Completed'];

        return view('admin.contracts.edit', compact(
            'contract',
            'clients',
            'projects',
            'types',
            'currencies',
            'statuses'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Contract $contract)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'client_id' => 'required|exists:clients,id',
            'project_id' => 'nullable|exists:projects,id',
            'description' => 'nullable|string',
            'type' => 'required|in:normal,special,fixed_price,time_material',
            'contract_value' => 'required|numeric|min:0',
            'currency' => 'required|string|max:3',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'required|in:draft,active,expired,terminated,completed',
            'terms' => 'nullable|string',
            'notes' => 'nullable|string',
            'is_signed' => 'boolean',
            'signed_date' => 'nullable|date',
            'signed_by' => 'nullable|string|max:255',
        ]);

        $contract->update($validated);

        return redirect()->route('admin.contracts.index')
            ->with('success', 'Contract updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Contract $contract)
    {
        $contract->delete();

        return redirect()->route('admin.contracts.index')
            ->with('success', 'Contract deleted successfully.');
    }

    /**
     * Export contracts to CSV.
     */
    public function export(Request $request)
    {
        $contracts = Contract::with(['client', 'project'])
            ->latest()
            ->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=contracts_' . date('Y-m-d') . '.csv',
        ];

        $callback = function () use ($contracts) {
            $file = fopen('php://output', 'w');

            // Add CSV headers
            fputcsv($file, [
                'Contract Number',
                'Subject',
                'Client',
                'Project',
                'Type',
                'Value',
                'Currency',
                'Start Date',
                'End Date',
                'Status',
                'Created At'
            ]);

            // Add data rows
            foreach ($contracts as $contract) {
                fputcsv($file, [
                    $contract->contract_number,
                    $contract->subject,
                    $contract->client->name ?? '',
                    $contract->project->name ?? '',
                    ucfirst(str_replace('_', ' ', $contract->type)),
                    $contract->contract_value,
                    $contract->currency,
                    $contract->start_date->format('Y-m-d'),
                    $contract->end_date->format('Y-m-d'),
                    ucfirst($contract->status),
                    $contract->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Update contract status.
     */
    public function updateStatus(Request $request, Contract $contract)
    {
        $request->validate([
            'status' => 'required|in:draft,active,expired,terminated,completed',
        ]);

        $contract->update(['status' => $request->status]);

        return response()->json([
            'success' => true,
            'message' => 'Contract status updated successfully.',
        ]);
    }

    /**
     * Sign contract.
     */
    public function signContract(Request $request, Contract $contract)
    {
        $request->validate([
            'signed_by' => 'required|string|max:255',
        ]);

        $contract->update([
            'is_signed' => true,
            'signed_date' => now(),
            'signed_by' => $request->signed_by,
            'status' => 'active',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Contract signed successfully.',
        ]);
    }

    /**
     * Get contracts by client.
     */
    public function getByClient(Client $client)
    {
        $contracts = $client->contracts()
            ->with(['project'])
            ->latest()
            ->get();

        return response()->json($contracts);
    }
}
