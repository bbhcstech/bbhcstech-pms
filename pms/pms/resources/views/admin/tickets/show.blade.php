@extends('admin.layout.app')

@section('content')
<div class="container mt-4">
    <div class="mb-3">
        <h4>Ticket #{{ $ticket->id }}</h4>
        <small class="text-muted">Home • Tickets • Ticket #{{ $ticket->id }}</small>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <p><strong>Ticket Subject:</strong> {{ $ticket->subject }}<p>
            <p class="text-muted"><strong>Project Name:</strong> {{ $ticket->project->name ?? 'No Project' }}</p>
            <p><strong>Requester:</strong> {{ $ticket->requester_name }}</p>
            <p><strong>Company:</strong> {{ $ticket->company_name ?? 'N/A' }}</p>

            <div class="mb-3">
                <strong>Status:</strong> 
                <span class="badge bg-info">{{ ucfirst($ticket->status) }}</span>
                <strong class="ms-3">Priority:</strong> 
                <span class="badge bg-warning">{{ ucfirst($ticket->priority) }}</span>
            </div>

            <p><strong>Requested On:</strong> {{ $ticket->created_at->format('d-m-Y h:i A') }}</p>
        </div>
    </div>

  
   <!-- Replies -->
    <div class="card mb-4">
        <div class="card-header">Conversation</div>
        <div class="card-body">
            @forelse($replies as $reply)
                <div class="mb-4 border-bottom pb-2">
                    <strong>{{ $reply->user->name }}</strong>
                    <small class="text-muted float-end">{{ $reply->created_at->diffForHumans() }}</small>
                    <p>{{ $reply->message }}</p>
                    
                    @if($reply->attachment)
                        <a href="{{ asset($reply->attachment) }}" target="_blank">
                            <i class="fas fa-paperclip me-1"></i> {{ basename($reply->attachment) }}
                        </a>

                    @endif
                </div>
            @empty
                <p class="text-muted">No replies yet.</p>
            @endforelse
        </div>
    </div>

    <!-- Reply / Note Form -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('tickets.reply', $ticket->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <textarea name="message" class="form-control mb-2" rows="3" placeholder="Write a reply or add note..."></textarea>
                <input type="file" name="attachment" class="form-control mb-2" />
                <button type="submit" class="btn btn-primary">Reply</button>
            </form>
        </div>
    </div>

    <!-- Ticket Settings -->
    
    <div class="card">
        <div class="card-header">Details</div>
        <div class="card-body">
            <form method="POST" action="{{ route('tickets.updateDetails', $ticket->id) }}">
                    @csrf
                    @method('PUT')

                <div class="row g-3">
                    <div class="col-md-6">
                        <label>Assign Agent</label>
                        <select name="agent_id" class="form-select">
                            <option value="">Unassigned</option>
                            @foreach($agents as $agent)
                                <option value="{{ $agent->id }}" {{ $ticket->agent_id == $agent->id ? 'selected' : '' }}>
                                    {{ $agent->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label>Assign Group</label>
                        <select name="group_id" class="form-select">
                            <option value="">Unassigned</option>
                            @foreach($groups as $group)
                                <option value="{{ $group->id }}" {{ $ticket->group_id == $group->id ? 'selected' : '' }}>
                                    {{ $group->group_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label>Priority</label>
                        <select name="priority" class="form-select">
                            @foreach(['low', 'medium', 'high', 'critical'] as $priority)
                                <option value="{{ $priority }}" {{ $ticket->priority == $priority ? 'selected' : '' }}>
                                    {{ ucfirst($priority) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label>Status</label>
                        <select name="status" class="form-select">
                            @foreach(['open', 'pending', 'resolved', 'closed'] as $status)
                                <option value="{{ $status }}" {{ $ticket->status == $status ? 'selected' : '' }}>
                                    {{ ucfirst($status) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label>Type</label>
                        
                        <select class="form-control selectpicker" name="type_id" id="ticket_type_id" data-live-search="true" data-size="8">
                        <option value="">Type</option>
                        @php
                            $types = ['1'=>'Bug','2'=>'Suggestion','3'=>'Question','4'=>'Sales','5'=>'Code','6'=>'Management','7'=>'Problem','8'=>'Incident','9'=>'Feature Request'];
                        @endphp
                        @foreach($types as $key => $val)
                            <option value="{{ $key }}" {{ $ticket->type_id == $key ? 'selected' : '' }}>{{ $val }}</option>
                        @endforeach
                    </select>
                    </div>
                </div>

                <div class="mt-3">
                    <button type="submit" class="btn btn-success">Update</button>
                    <a href="{{ route('tickets.index') }}" class="btn btn-secondary">Back</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
