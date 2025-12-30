<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Client;
use App\Models\Project;
use App\Models\TicketGroup;
use App\Models\Reply;
use App\Models\TicketActivity;
use App\Notifications\TicketCreatedNotification;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Notification;
use Carbon\Carbon;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $agents = User::where('role', 'employee')->get();
        $projects = Project::all();

        $tickets = Ticket::query();

        if ($request->filled('duration')) {
            $duration = $request->duration;

            if (str_contains($duration, ' to ')) {
                [$start, $end] = explode(' to ', $duration);
                try {
                    $startDate = Carbon::parse($start)->startOfDay();
                    $endDate = Carbon::parse($end)->endOfDay();
                    $tickets->whereBetween('created_at', [$startDate, $endDate]);
                } catch (\Exception $e) {
                    // invalid format - ignore
                }
            } else {
                switch ($duration) {
                    case 'Today':
                        $startDate = Carbon::today();
                        $endDate = Carbon::today()->endOfDay();
                        break;
                    case 'Last 30 Days':
                        $startDate = Carbon::now()->subDays(29)->startOfDay();
                        $endDate = Carbon::now()->endOfDay();
                        break;
                    case 'This Month':
                        $startDate = Carbon::now()->startOfMonth();
                        $endDate = Carbon::now()->endOfMonth();
                        break;
                    case 'Last Month':
                        $startDate = Carbon::now()->subMonth()->startOfMonth();
                        $endDate = Carbon::now()->subMonth()->endOfMonth();
                        break;
                    case 'Last 90 Days':
                        $startDate = Carbon::now()->subDays(89)->startOfDay();
                        $endDate = Carbon::now()->endOfDay();
                        break;
                    case 'Last 6 Months':
                        $startDate = Carbon::now()->subMonths(6)->startOfMonth();
                        $endDate = Carbon::now()->endOfDay();
                        break;
                    case 'Last 1 Year':
                        $startDate = Carbon::now()->subYear()->startOfMonth();
                        $endDate = Carbon::now()->endOfDay();
                        break;
                    default:
                        $startDate = null;
                        $endDate = null;
                }

                if ($startDate && $endDate) {
                    $tickets->whereBetween('created_at', [$startDate, $endDate]);
                }
            }
        }

        if ($request->filled('status')) {
            $tickets->where('status', $request->status);
        }

        $tickets = $tickets->latest()->get();

        return view('admin.tickets.index', compact('tickets', 'agents', 'projects'));
    }

    public function create()
    {
        $agents = User::where('role', 'employee')->get();
        $projects = Project::all();
        $clients = User::where('role', 'client')->get();
        $employees = User::where('role', 'employee')->get();
        $ticketgroup  = TicketGroup::all();

        return view('admin.tickets.create', compact('agents', 'projects','ticketgroup','clients', 'employees'));
    }

   public function store(Request $request)
{
    $request->validate([
        'requester_type' => 'required|in:client,employee',
        'requester_name' => 'required|exists:users,id',
        'group_id'       => 'nullable',
        'subject'        => 'required|string|max:255',
        'description'    => 'required|string',
        'attachment'     => 'nullable|file|mimes:jpeg,png,pdf,docx|max:2048',
        'priority'       => 'nullable|in:low,medium,high,critical',
        'channel'        => 'nullable|string|max:100',
        'tags'           => 'nullable|string|max:255',
        'type_id'        => 'nullable|string|max:100',
        'agent_id'       => 'nullable|exists:users,id',
        'project_id'     => 'nullable|exists:projects,id',
        'status'         => 'nullable|in:open,pending,closed,resolved',
    ]);

    // create ticket
    $ticket = new Ticket();

    $ticket->requester_type = $request->requester_type;
    $ticket->requester_id = $request->requester_name; // actual user ID selected
    $ticket->requester_name = \App\Models\User::find($request->requester_name)->name ?? null;
    $ticket->group_id = $request->group_id;
    $ticket->agent_id = $request->agent_id;
    $ticket->project_id = $request->project_id;
    $ticket->type_id = $request->type_id;
    $ticket->subject = $request->subject;
    $ticket->description = $request->description;
    $ticket->priority = $request->priority;
    $ticket->channel = $request->channel;
    $ticket->tags = $request->tags;
    $ticket->status = $request->status ?? 'open';

    if ($request->hasFile('attachment')) {
        $file = $request->file('attachment');
        $filename = time() . '-' . $file->getClientOriginalName();
        $file->move(public_path('admin/uploads/tickets'), $filename);
        $ticket->attachment = 'admin/uploads/tickets/' . $filename;
    }

    $ticket->save();

    // store ticket activity
    TicketActivity::create([
        'ticket_id' => $ticket->id,
        'project_id' => $request->project_id,
        'user_id' => auth()->id(),
        'assigned_to' => $ticket->assigned_to ?? null,
        'channel_id' => $ticket->channel_id ?? null,
        'group_id' => $ticket->group_id,
        'type_id' => $ticket->type_id,
        'status' => $ticket->status,
        'priority' => $ticket->priority,
        'type' => 'Status Update',
        'content' => 'Ticket status changed to ' . $ticket->status,
    ]);

    // --------------------------
    // notify assigned agent (database notification) with fallback
    // --------------------------
    if ($ticket->agent_id) {
        $agent = User::find($ticket->agent_id);

        // Temporary debug helpers (uncomment to debug)
        // dd($ticket->agent_id);
        // dd($agent);

        if ($agent) {
            // 1) try normal notify() which will insert into notifications table
            try {
                $agent->notify(new TicketCreatedNotification($ticket));
            } catch (\Throwable $e) {
                // Logging error but continue to fallback insert
                \Log::error('Ticket notification failed: ' . $e->getMessage());
            }

            // 2) quick check: if a notification for this ticket & agent already exists, skip fallback insert
            $exists = \DB::table('notifications')
                ->where('notifiable_type', 'App\\Models\\User')
                ->where('notifiable_id', $agent->id)
                ->where('type', 'App\\Notifications\\TicketCreatedNotification')
                ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(data, '$.ticket_id')) = ?", [(string) $ticket->id])
                ->exists();

            if (! $exists) {
                // Insert fallback notification row (safe, idempotent for this ticket/agent)
                \DB::table('notifications')->insert([
                    'id' => (string) \Illuminate\Support\Str::uuid(),
                    'type' => 'App\\Notifications\\TicketCreatedNotification',
                    'notifiable_type' => 'App\\Models\\User',
                    'notifiable_id' => $agent->id,
                    'data' => json_encode([
                        'ticket_id' => $ticket->id,
                        'title' => 'New Ticket Assigned',
                        'message' => 'A new ticket has been assigned to you: ' . $ticket->subject,
                        'url' => url('/admin/tickets/' . $ticket->id),
                        'created_by' => auth()->id(),
                    ]),
                    'read_at' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // If you want to immediately inspect the inserted notification, uncomment:
            // dd(\DB::table('notifications')->where('notifiable_type', 'App\\Models\\User')->where('notifiable_id', $agent->id)->latest('created_at')->first());
        }
    }

    return redirect()->route('tickets.index')->with('success', 'Ticket created successfully');
}

    public function edit($id)
    {
        $ticket = Ticket::findOrFail($id);
        $agents = User::where('role', 'employee')->get();
        $projects = Project::all();
        $clients = Client::all();
        $users = User::where('role', 'employee')->get();
        $clients = User::where('role', 'client')->get();
        $employees = User::where('role', 'employee')->get();
        $ticketgroup  = TicketGroup::all();

        return view('admin.tickets.edit', compact('ticket', 'agents', 'projects', 'clients', 'users','ticketgroup','clients', 'employees'));
    }

    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'requester_type' => 'required|in:client,employee',
                'requester_name' => 'required|exists:users,id',
                'group_id' => 'required',
                'subject' => 'required|string|max:255',
                'description' => 'required|string',
                'attachment' => 'nullable|file|mimes:jpeg,png,pdf,docx|max:2048',
                'priority' => 'nullable|in:low,medium,high,critical',
                'channel' => 'nullable|string|max:100',
                'tags' => 'nullable|string|max:255',
                'type_id' => 'nullable|string|max:100',
                'agent_id' => 'nullable|exists:users,id',
                'project_id' => 'nullable|exists:projects,id',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            dd($e->errors());
        }

        $ticket = Ticket::findOrFail($id);

        $ticket->requester_type = $request->requester_type;
        $ticket->requester_id = $request->requester_name;
        $ticket->requester_name = User::find($request->requester_name)->name ?? null;
        $ticket->group_id = $request->group_id;
        $ticket->agent_id = $request->agent_id;
        $ticket->project_id = $request->project_id;
        $ticket->type_id = $request->type_id;
        $ticket->subject = $request->subject;
        $ticket->description = $request->description;
        $ticket->priority = $request->priority;
        $ticket->channel = $request->channel;
        $ticket->tags = $request->tags;

        if ($request->hasFile('attachment')) {
            if ($ticket->attachment && file_exists(public_path($ticket->attachment))) {
                unlink(public_path($ticket->attachment));
            }

            $file = $request->file('attachment');
            $filename = time() . '-' . $file->getClientOriginalName();
            $file->move(public_path('admin/uploads/tickets'), $filename);
            $ticket->attachment = 'admin/uploads/tickets/' . $filename;
        }

        $ticket->save();

        TicketActivity::create([
            'ticket_id' => $ticket->id,
            'project_id' => $request->project_id,
            'user_id' => auth()->id(),
            'assigned_to' => $ticket->assigned_to ?? null,
            'channel_id' => $ticket->channel_id ?? null,
            'group_id' => $ticket->group_id,
            'type_id' => $ticket->type_id,
            'status' => $ticket->status,
            'priority' => $ticket->priority,
            'type' => 'Status Update',
            'content' => 'Ticket status changed to ' . $ticket->status,
        ]);

        return redirect()->route('tickets.index')->with('success', 'Ticket updated successfully');
    }

    public function changeStatus(Request $request)
    {
        $request->validate([
            'ticketId' => 'required|exists:tickets,id',
            'status' => 'required|in:open,pending,resolved,closed'
        ]);

        $ticket = Ticket::findOrFail($request->ticketId);
        $ticket->status = $request->status;
        $ticket->save();

        return response()->json(['status' => 'success', 'message' => 'Status updated']);
    }

    public function destroy($ticketId)
    {
        $ticket = Ticket::findOrFail($ticketId);
        $ticket->delete();

        return redirect()->route('tickets.index')->with('success', 'Ticket removed successfully.');
    }

    public function storeGroup(Request $request)
    {
        $request->validate([
            'group_name' => 'required|string|max:255|unique:ticket_groups,group_name'
        ]);

        $group = TicketGroup::create([
            'group_name' => $request->group_name
        ]);

        return response()->json(['status' => 'success', 'group' => $group]);
    }

    public function fetchGroups()
    {
        $groups = TicketGroup::all();
        return response()->json($groups);
    }

    public function destroygroup($id)
    {
        $group = TicketGroup::findOrFail($id);
        $group->delete();

        return response()->json(['status' => 'success']);
    }

    public function show($id)
    {
        $ticket = Ticket::with(['requester', 'agent', 'project', 'group'])->findOrFail($id);
        $agents = User::where('role', 'employee')->get();
        $groups = TicketGroup::all();
        $replies  = Reply::all();

        return view('admin.tickets.show', compact('ticket','agents', 'groups','replies'));
    }

    public function reply(Request $request, $id)
    {
        $request->validate([
            'message' => 'required|string',
            'attachment' => 'nullable|file|max:2048',
        ]);

        $ticket = Ticket::findOrFail($id);

        $attachmentPath = null;

        if ($request->hasFile('attachment')) {
            if ($ticket->attachment && file_exists(public_path($ticket->attachment))) {
                unlink(public_path($ticket->attachment));
            }

            $file = $request->file('attachment');
            $filename = time() . '-' . $file->getClientOriginalName();
            $file->move(public_path('admin/uploads/ticket_replies'), $filename);
            $attachmentPath = 'admin/uploads/ticket_replies/' . $filename;
        }

        Reply::create([
            'ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'message' => $request->message,
            'attachment' => $attachmentPath,
        ]);

        return redirect()->back()->with('success', 'Reply added successfully.');
    }

    public function updateDetails(Request $request, $id)
    {
        $request->validate([
            'agent_id' => 'nullable|exists:users,id',
            'group_id' => 'nullable|exists:ticket_groups,id',
            'priority' => 'nullable|in:low,medium,high,critical',
            'status' => 'nullable|in:open,pending,resolved,closed',
            'type_id' => 'nullable|in:1,2,3,4,5,6,7,8,9',
        ]);

        $ticket = Ticket::findOrFail($id);

        $ticket->agent_id = $request->agent_id;
        $ticket->group_id = $request->group_id;
        $ticket->priority = $request->priority;
        $ticket->status = $request->status;
        $ticket->type_id = $request->type_id;

        $ticket->save();

        return redirect()->back()->with('success', 'Ticket details updated.');
    }

    public function bulkAction(Request $request)
    {
        $tickets = Ticket::whereIn('id', $request->tickets);

        if ($request->action == 'delete') {
            $tickets->delete();
        }

        if ($request->action == 'change_status' && $request->status) {
            $tickets->update(['status' => $request->status]);
        }

        return response()->json(['status' => 'success']);
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids', []);

        if (!is_array($ids) || empty($ids)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'No tickets selected',
            ], 422);
        }

        Ticket::whereIn('id', $ids)->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Tickets deleted successfully',
        ]);
    }
}
