<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ClientSubCategory;
use App\Models\ClientCategory;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Country;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        // base query
        $query = Client::query();

        // status filter: active|inactive|pending|all
        if ($request->filled('status') && $request->status !== 'all') {
            $statuses = explode(',', $request->status);
            $query->whereIn('status', $statuses);
        }

        // name search
        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        // duration filter
        if ($request->filled('duration')) {
            $duration = $request->duration;

            if (str_contains($duration, ' to ')) {
                [$start, $end] = explode(' to ', $duration);
                try {
                    $startDate = Carbon::parse($start)->startOfDay();
                    $endDate   = Carbon::parse($end)->endOfDay();
                    $query->whereBetween('created_at', [$startDate, $endDate]);
                } catch (\Exception $e) {
                    // ignore invalid format
                }
            } else {
                switch ($duration) {
                    case 'Today':
                        $startDate = Carbon::today();
                        $endDate   = Carbon::today()->endOfDay();
                        break;
                    case 'Last 30 Days':
                        $startDate = Carbon::now()->subDays(29)->startOfDay();
                        $endDate   = Carbon::now()->endOfDay();
                        break;
                    case 'This Month':
                        $startDate = Carbon::now()->startOfMonth();
                        $endDate   = Carbon::now()->endOfMonth();
                        break;
                    case 'Last Month':
                        $startDate = Carbon::now()->subMonth()->startOfMonth();
                        $endDate   = Carbon::now()->subMonth()->endOfMonth();
                        break;
                    case 'Last 90 Days':
                        $startDate = Carbon::now()->subDays(89)->startOfDay();
                        $endDate   = Carbon::now()->endOfDay();
                        break;
                    case 'Last 6 Months':
                        $startDate = Carbon::now()->subMonths(6)->startOfMonth();
                        $endDate   = Carbon::now()->endOfDay();
                        break;
                    case 'Last 1 Year':
                        $startDate = Carbon::now()->subYear()->startOfMonth();
                        $endDate   = Carbon::now()->endOfDay();
                        break;
                    default:
                        $startDate = $endDate = null;
                }

                if ($startDate && $endDate) {
                    $query->whereBetween('created_at', [$startDate, $endDate]);
                }
            }
        }

        $clients    = $query->latest()->paginate(25);
        $categories = ClientCategory::all();
        $countries  = Country::all();

        return view('admin.clients.index', compact('clients', 'categories', 'countries'));
    }   

  public function create() {
    $categories    = ClientCategory::all();
    $subcategories = ClientSubCategory::with('category')->get();
    $users         = User::all();
    $countries     = Country::all();

    // preview next client code (same logic as in Client model boot method)
    $nextId = (Client::max('id') ?? 0) + 1;
    $nextClientCode = 'XINK-CL-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

    return view('admin.clients.create', compact(
        'categories',
        'subcategories',
        'users',
        'countries',
        'nextClientCode'
    ));
}

    public function store(Request $request)
    {
        \Log::info('Client Form Data:', $request->all());

     $request->validate([
    'salutation'             => 'nullable|string|max:10',
    'name'                   => 'required|string|max:255',
    'email'                  => 'required|email|unique:clients,email',
    'password'               => 'nullable|string|min:6',
    'country'                => 'nullable',
     'mobile'                => ['required', 'regex:/^\+91[0-9]{10}$/'],

    'profile_picture'        => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
    'gender'                 => 'nullable',
    'language'               => 'nullable',
    'client_category_id'     => 'nullable|exists:client_categories,id',
    'client_sub_category_id' => 'nullable|exists:client_sub_categories,id',
    'login_allowed'          => 'nullable|boolean',
    'email_notifications'    => 'nullable|boolean',
    'company_name'           => 'nullable|string|max:255',
    'website'                => 'nullable|url|max:255',
    'tax_name'               => 'nullable',
    'tax_number'             => 'nullable',
    'office_phone'           => ['nullable','regex:/^\+91[0-9]{10}$/'],
    'city'                   => 'nullable',
    'state'                  => 'nullable',
    'postal_code'            => 'nullable',
    'added_by'               => 'nullable|integer|exists:users,id',
    'company_address'        => 'nullable|string',
    'shipping_address'       => 'nullable|string',
    'note'                   => 'nullable|string',
    'company_logo'           => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
], [
    'office_phone.regex' => 'Office phone must start with +91 and have 10 digits.',
]);

        $data = $request->all();

        // never allow client_uid from request (it will be auto generated in model)
        unset($data['client_uid']);

        // password handling (same for user and client)
        if ($request->filled('password')) {
            $password = Hash::make($request->input('password'));
        } else {
            // default password (optional – change as needed)
            $password = Hash::make('123456');
        }

        $data['password'] = $password;

        // profile picture upload
        $profileImagePath = null;
        $data['profile_picture'] = null;

        if ($request->hasFile('profile_picture')) {
            $image     = $request->file('profile_picture');
            $imageName = time() . '-' . $image->getClientOriginalName();
            $image->move(public_path('admin/uploads/clients-image'), $imageName);

            $profileImagePath            = 'admin/uploads/clients-image/' . $imageName;
            $data['profile_picture']     = $profileImagePath;
        }

        // create linked user
        $user = User::create([
            'name'          => $request->name,
            'email'         => $request->email,
            'mobile'        => $request->mobile,
            'password'      => $password,
            'role'          => 'client',
            'profile_image' => $profileImagePath,
        ]);

        // company logo upload
        $data['company_logo'] = null;

        if ($request->hasFile('company_logo')) {
            $image     = $request->file('company_logo');
            $imageName = time() . '-' . $image->getClientOriginalName();
            $image->move(public_path('admin/uploads/clients-logo'), $imageName);

            $data['company_logo'] = 'admin/uploads/clients-logo/' . $imageName;
        }

        // create client (client_uid is auto-generated in Client model boot())
        Client::create($data);

        return redirect()->route('clients.index')->with('success', 'Client added successfully.');
    }

    public function edit(Client $client)
    {
        $categories    = ClientCategory::all();
        $subcategories = ClientSubCategory::with('category')->get();
        $users         = User::all();
        $countries     = Country::all();

        return view('admin.clients.edit', compact('client', 'categories', 'subcategories', 'users', 'countries'));
    }

    public function update(Request $request, $id)
    {
        $client = Client::findOrFail($id);
        $user   = User::where('email', $client->email)->first();

     $request->validate([
    'salutation'             => 'nullable|string|max:10',
    'name'                   => 'required|string|max:255',
    'email'                  => 'required|email|unique:clients,email',
    'password'               => 'nullable|string|min:6',
    'country'                => 'nullable',
    'mobile'                 => 'nullable',
    'profile_picture'        => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
    'gender'                 => 'nullable',
    'language'               => 'nullable',
    'client_category_id'     => 'nullable|exists:client_categories,id',
    'client_sub_category_id' => 'nullable|exists:client_sub_categories,id',
    'login_allowed'          => 'nullable|boolean',
    'email_notifications'    => 'nullable|boolean',
    'company_name'           => 'nullable|string|max:255',
    'website'                => 'nullable|url|max:255',
    'tax_name'               => 'nullable',
    'tax_number'             => 'nullable',
    'office_phone'           => ['nullable','regex:/^\+91[0-9]{10}$/'],
    'city'                   => 'nullable',
    'state'                  => 'nullable',
    'postal_code'            => 'nullable',
    'added_by'               => 'nullable|integer|exists:users,id',
    'company_address'        => 'nullable|string',
    'shipping_address'       => 'nullable|string',
    'note'                   => 'nullable|string',
    'company_logo'           => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
], [
    'office_phone.regex' => 'Office phone must start with +91 and have 10 digits.',
]);


        $data = $request->except(['password', 'profile_picture', 'company_logo']);

        // never allow client_uid to be updated via form
        unset($data['client_uid']);

        // password update
        if ($request->filled('password')) {
            $hashedPassword = Hash::make($request->password);
            if ($user) {
                $user->password = $hashedPassword;
            }
            $client->password = $hashedPassword;
        }

        // profile picture
        if ($request->hasFile('profile_picture')) {
            $image     = $request->file('profile_picture');
            $imageName = time() . '-' . $image->getClientOriginalName();
            $image->move(public_path('admin/uploads/clients-image'), $imageName);

            $data['profile_picture'] = 'admin/uploads/clients-image/' . $imageName;

            if ($user) {
                $user->profile_image = $data['profile_picture'];
            }
        }

        // company logo
        if ($request->hasFile('company_logo')) {
            $logo     = $request->file('company_logo');
            $logoName = time() . '-' . $logo->getClientOriginalName();
            $logo->move(public_path('admin/uploads/clients-logo'), $logoName);

            $data['company_logo'] = 'admin/uploads/clients-logo/' . $logoName;
        }

        // update client
        $client->update(array_merge($data, ['email' => $request->email]));

        // update user
        if ($user) {
            $user->name   = $request->name;
            $user->email  = $request->email;
            $user->mobile = $request->mobile;
            $user->save();
        }

        return redirect()->route('clients.index')->with('success', 'Client updated successfully.');
    }

    public function show(Client $client)
    {
        $client->load('category', 'subcategory');
        return view('admin.clients.show', compact('client'));
    }

    public function destroy(Client $client)
    {
        $client->delete();
        return redirect()->route('clients.index')->with('success', 'Client deleted successfully.');
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'client_ids' => 'required|array',
            'action'     => 'required|string',
        ]);

        $ids = $request->client_ids;

        if ($request->action === 'change-status' && $request->filled('status')) {
            Client::whereIn('id', $ids)
                ->update([
                    'login_allowed' => $request->status === 'Active' ? 1 : 0,
                    'status'        => $request->status,
                ]);

            return response()->json(['success' => true, 'message' => 'Status updated successfully']);
        }

        if ($request->action === 'delete') {
            Client::whereIn('id', $ids)->delete();
            return response()->json(['success' => true, 'message' => 'Clients deleted successfully']);
        }

        return response()->json(['success' => false, 'message' => 'Invalid action'], 400);
    }

    public function pending(Request $request)
    {
        $query = Client::where('status', 'pending');

        // client filter (currently using id)
        if ($request->filled('name')) {
            $query->where('id', $request->name);
        }

        // duration filter
        if ($request->filled('duration')) {
            $duration = $request->duration;

            if (str_contains($duration, ' to ')) {
                [$start, $end] = explode(' to ', $duration);
                try {
                    $startDate = Carbon::parse($start)->startOfDay();
                    $endDate   = Carbon::parse($end)->endOfDay();
                    $query->whereBetween('created_at', [$startDate, $endDate]);
                } catch (\Exception $e) {
                    // skip invalid format
                }
            } else {
                switch ($duration) {
                    case 'Today':
                        $startDate = Carbon::today();
                        $endDate   = Carbon::today()->endOfDay();
                        break;
                    case 'Last 30 Days':
                        $startDate = Carbon::now()->subDays(29)->startOfDay();
                        $endDate   = Carbon::now()->endOfDay();
                        break;
                    case 'This Month':
                        $startDate = Carbon::now()->startOfMonth();
                        $endDate   = Carbon::now()->endOfMonth();
                        break;
                    case 'Last Month':
                        $startDate = Carbon::now()->subMonth()->startOfMonth();
                        $endDate   = Carbon::now()->subMonth()->endOfMonth();
                        break;
                    case 'Last 90 Days':
                        $startDate = Carbon::now()->subDays(89)->startOfDay();
                        $endDate   = Carbon::now()->endOfDay();
                        break;
                    case 'Last 6 Months':
                        $startDate = Carbon::now()->subMonths(6)->startOfMonth();
                        $endDate   = Carbon::now()->endOfDay();
                        break;
                    case 'Last 1 Year':
                        $startDate = Carbon::now()->subYear()->startOfMonth();
                        $endDate   = Carbon::now()->endOfDay();
                        break;
                    default:
                        $startDate = $endDate = null;
                }

                if ($startDate && $endDate) {
                    $query->whereBetween('created_at', [$startDate, $endDate]);
                }
            }
        }

        $clients    = $query->get();
        $categories = ClientCategory::all();
        $countries  = Country::all();

        return view('admin.clients.verification-pending', compact('clients', 'categories', 'countries'));
    }

    public function pendingbulkAction(Request $request)
    {
        $ids    = $request->client_ids;
        $action = $request->action;
        $status = $request->status;

        if ($action === 'change-status') {
            Client::whereIn('id', $ids)->update(['status' => $status]);
            return response()->json(['success' => true, 'message' => 'Status updated successfully']);
        }

        if ($action === 'delete') {
            Client::whereIn('id', $ids)->delete();
            return response()->json(['success' => true, 'message' => 'Clients deleted successfully']);
        }

        return response()->json(['success' => false, 'message' => 'Invalid action']);
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'client_ids'   => 'required|array',
            'client_ids.*' => 'integer|exists:clients,id',
        ]);

        $ids = $request->input('client_ids', []);
        Client::whereIn('id', $ids)->delete();

        return response()->json([
            'success'       => true,
            'message'       => 'Clients deleted successfully',
            'deleted_count' => count($ids),
        ]);
    }
}
