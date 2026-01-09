<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LeadContact;
use App\Models\User;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LeadsExport;
use App\Imports\LeadsImport;

class LeadContactController extends Controller
{
        public function index(Request $request)
        {
            if (!auth()->check() || auth()->user()->role !== 'admin') {
                abort(403, 'Unauthorized');
            }

            // Get parameters from request
            $type = $request->get('type', 'all');
            $perPage = $request->get('per_page', 10);
            $search = $request->get('search', '');

            // Validate per_page value
            $validPerPage = in_array($perPage, [10, 20, 50, 100]) ? $perPage : 10;

            // Start query
            $query = LeadContact::with(['owner', 'creator', 'dealAgent']);

            // Apply type filter
            if ($type === 'lead') {
                $query->where('type', 'lead');
            } elseif ($type === 'client') {
                $query->where('type', 'client');
            }
            // If type is 'all', show all records

            // Apply search filter
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('contact_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%");
                });
            }

            // Order and paginate
            $leads = $query->latest()
                        ->paginate($validPerPage)
                        ->appends($request->query());

            $users = User::select('id','name')->get();

            return view('admin.leads.contacts.index', compact('leads', 'users'));
        }

    public function create()
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403);
        }

        $users = User::select('id','name')->get();

        return view('admin.leads.contacts.create', compact('users'));
    }

    public function store(Request $request)
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403);
        }

        // Updated validation rules for all fields
        $data = $request->validate([
            // Contact Information
            'salutation' => 'nullable|string|max:20',
            'contact_name' => 'required|string|max:255',
            'email' => 'required|email',
            'mobile' => 'nullable|string|max:20',

            // Company Information
            'company_name' => 'nullable|string|max:255',
            'website' => 'nullable|url',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'industry' => 'nullable|string|max:100',

            // Lead Source & Status
            'lead_source' => 'required|string|max:100',
            'status' => 'nullable|string|max:50',
            'lead_score' => 'nullable|integer|min:0|max:100',
            'tags' => 'nullable|string',

            // Assignment
            'lead_owner_id' => 'required|exists:users,id',

            // Deal Information
            'create_deal' => 'nullable|boolean',
            'deal_name' => 'nullable|string|max:255',
            'deal_value' => 'nullable|numeric|min:0',
            'deal_currency' => 'nullable|string|max:10',
            'deal_agent_id' => 'nullable|exists:users,id',
            'pipeline' => 'nullable|string|max:100',
            'deal_stage' => 'nullable|string|max:100',
            'deal_category' => 'nullable|string|max:100',
            'close_date' => 'nullable|date',

            // Additional Information
            'description' => 'nullable|string',
        ]);

        // Process create_deal checkbox
        $data['create_deal'] = $request->has('create_deal');

        // Set added_by
        $data['added_by'] = auth()->id();

        // Process products array
        if ($request->has('products') && is_array($request->products)) {
            $data['products'] = json_encode($request->products);
        }

        // Validate deal fields if create_deal is checked
        if ($data['create_deal']) {
            $request->validate([
                'deal_name' => 'required|string|max:255',
                'deal_value' => 'required|numeric|min:0',
                'deal_stage' => 'required|string|max:100',
            ]);
        }

        // Handle lead_owner_designation and added_by_designation
        if ($request->has('lead_owner_id')) {
            $owner = User::find($request->lead_owner_id);
            if ($owner) {
                $data['lead_owner_designation'] = $owner->designation ?? null;
            }
        }

        if (auth()->check()) {
            $data['added_by_designation'] = auth()->user()->designation ?? null;
        }

        // Create the lead contact
        LeadContact::create($data);

        // Check if it's Save & Add More
        if ($request->has('save_and_add_more')) {
            return redirect('leads/contacts/create')
                ->with('success', 'Lead added successfully. Add another lead.');
        }

        return redirect('leads/contacts')
            ->with('success', 'Lead added successfully');
    }

    public function show($id)
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403);
        }

        $lead = LeadContact::with(['owner', 'creator', 'dealAgent'])->findOrFail($id);

        return view('admin.leads.contacts.show', compact('lead'));
    }

    public function edit($id)
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403);
        }

        $lead = LeadContact::findOrFail($id);
        $users = User::select('id','name')->get();

        return view('admin.leads.contacts.edit', compact('lead', 'users'));
    }

    public function update(Request $request, $id)
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403);
        }

        $lead = LeadContact::findOrFail($id);

        $data = $request->validate([
            // Contact Information
            'salutation' => 'nullable|string|max:20',
            'contact_name' => 'required|string|max:255',
            'email' => 'required|email',
            'mobile' => 'nullable|string|max:20',

            // Company Information
            'company_name' => 'nullable|string|max:255',
            'website' => 'nullable|url',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'industry' => 'nullable|string|max:100',

            // Lead Source & Status
            'lead_source' => 'required|string|max:100',
            'status' => 'nullable|string|max:50',
            'lead_score' => 'nullable|integer|min:0|max:100',
            'tags' => 'nullable|string',

            // Assignment
            'lead_owner_id' => 'required|exists:users,id',

            // Deal Information
            'create_deal' => 'nullable|boolean',
            'deal_name' => 'nullable|string|max:255',
            'deal_value' => 'nullable|numeric|min:0',
            'deal_currency' => 'nullable|string|max:10',
            'deal_agent_id' => 'nullable|exists:users,id',
            'pipeline' => 'nullable|string|max:100',
            'deal_stage' => 'nullable|string|max:100',
            'deal_category' => 'nullable|string|max:100',
            'close_date' => 'nullable|date',

            // Additional Information
            'description' => 'nullable|string',
        ]);

        // Process create_deal checkbox
        $data['create_deal'] = $request->has('create_deal');

        // Process products array
        if ($request->has('products') && is_array($request->products)) {
            $data['products'] = json_encode($request->products);
        }

        // Handle lead_owner_designation
        if ($request->has('lead_owner_id')) {
            $owner = User::find($request->lead_owner_id);
            if ($owner) {
                $data['lead_owner_designation'] = $owner->designation ?? null;
            }
        }

        $lead->update($data);

        return redirect('leads/contacts')
            ->with('success', 'Lead updated successfully');
    }

   public function destroy($id)
{
    if (!auth()->check() || auth()->user()->role !== 'admin') {
        abort(403);
    }

    $lead = LeadContact::findOrFail($id);
    $lead->delete();

    return redirect()->route('leads.contacts.index')
        ->with('success', 'Lead deleted successfully');
}

// public function bulkDelete(Request $request)
// {
//     if (!auth()->check() || auth()->user()->role !== 'admin') {
//         return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
//     }

//     $request->validate([
//         'ids' => 'required|array',
//         'ids.*' => 'exists:lead_contacts,id'
//     ]);

//     LeadContact::whereIn('id', $request->ids)->delete();

//     return response()->json([
//         'success' => true,
//         'message' => count($request->ids) . ' lead(s) deleted successfully'
//     ]);
// }



        public function bulkDelete(Request $request)
        {
            if (!auth()->check() || auth()->user()->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'exists:lead_contacts,id'
            ]);

            try {
                LeadContact::whereIn('id', $request->ids)->delete();

                return response()->json([
                    'success' => true,
                    'message' => count($request->ids) . ' contact(s) deleted successfully!'
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error deleting contacts: ' . $e->getMessage()
                ], 500);
            }
        }

  public function export(Request $request)
        {
            if (!auth()->check() || auth()->user()->role !== 'admin') {
                abort(403, 'Unauthorized');
            }

            $type = $request->get('type', 'all');
            $ids = $request->get('ids', []);

            return Excel::download(new LeadsExport($type, $ids), 'leads_contacts_' . date('Y-m-d_H-i') . '.xlsx');
        }
    public function import(Request $request)
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403);
        }

        $request->validate([
            'file' => 'required|mimes:csv,xlsx,xls|max:2048'
        ]);

        try {
            Excel::import(new LeadsImport, $request->file('file'));

            return redirect('leads/contacts')
                ->with('success', 'Leads imported successfully');
        } catch (\Exception $e) {
            return redirect('leads/contacts')
                ->with('error', 'Error importing leads: ' . $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403);
        }

        $template = [
            ['contact_name', 'email', 'company_name', 'phone', 'lead_source', 'status', 'lead_owner_id']
        ];

        return Excel::download(new LeadsExport($template), 'leads-template.xlsx');
    }

    public function convertToClient(Request $request)
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'lead_id' => 'required|exists:lead_contacts,id'
        ]);

        $lead = LeadContact::find($request->lead_id);
        $lead->update(['status' => 'client']);

        return response()->json([
            'success' => true,
            'message' => 'Lead converted to client successfully'
        ]);
    }



    public function convert(Request $request)
{
    if (!auth()->check() || auth()->user()->role !== 'admin') {
        return response()->json([
            'success' => false,
            'message' => 'Unauthorized'
        ], 403);
    }

    $request->validate([
        'lead_id' => 'required|exists:lead_contacts,id',
        'action' => 'required|in:convert_to_client,convert_to_lead'
    ]);

    $lead = LeadContact::findOrFail($request->lead_id);

    if ($request->action == 'convert_to_client') {
        $lead->type = 'client';
        $lead->converted_at = now();
        $lead->converted_by = auth()->id();
        $message = 'Lead converted to client successfully!';
    } else {
        $lead->type = 'lead';
        $lead->converted_at = null;
        $lead->converted_by = null;
        $message = 'Client converted to lead successfully!';
    }

    $lead->save();

    return response()->json([
        'success' => true,
        'message' => $message
    ]);
   }
}
