@extends('admin.layout.app')

@section('title', 'Apply Leave')

@section('content')
<div class="container mt-4">
    <h4>Edit Leave</h4>
    
    @if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('leaves.update', $leave->id) }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')

     <div class="row">
        @if(auth()->user()->role === 'admin')
        <div class="col-md-4 mb-3">
            <label for="user_id" class="form-label">Select Employee <span class="text-danger">*</span></label>
            <select name="user_id" class="form-select" required>
                <option value="">-- Select Employee --</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ $leave->user_id == $user->id ? 'selected' : '' }}>
                        {{ $user->name }} ({{ $user->designation ?? 'N/A' }})
                    </option>
                @endforeach
            </select>
        </div>
        @endif

        <div class="col-md-4 mb-3">
            <label>Leave Type <span class="text-danger">*</span></label>
            <select name="type" class="form-select" required>
                <option value="">-- Select Type --</option>
                <option value="sick" {{ $leave->type == 'sick' ? 'selected' : '' }}>Sick Leave</option>
                <option value="casual" {{ $leave->type == 'casual' ? 'selected' : '' }}>Casual Leave</option>
                <option value="leave-without-pay" {{ $leave->type == 'leave-without-pay' ? 'selected' : '' }}>Leave Without Pay</option>
            </select>
        </div>
        
        <div class="col-md-4 mb-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-select" required >
                <option value="pending" {{ $leave->status == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="approved" {{ $leave->status == 'approved' ? 'selected' : '' }}>Approved</option>
                
            </select>
        </div>

        <div class="col-md-4 mb-3">
            <label class="form-label">Select Duration <span class="text-danger">*</span></label>
            <select name="duration" class="form-select" required onchange="toggleDateFields(this.value)">
                <option value="full-day" {{ $leave->duration == 'full-day' ? 'selected' : '' }}>Full Day</option>
                <option value="multiple" {{ $leave->duration == 'multiple' ? 'selected' : '' }}>Multiple Days</option>
                <option value="first-half" {{ $leave->duration == 'first-half' ? 'selected' : '' }}>First Half</option>
                <option value="second-half" {{ $leave->duration == 'second-half' ? 'selected' : '' }}>Second Half</option>
            </select>
        </div>

       {{-- ✅ Single Date (only if not multiple) --}}
        <div class="col-md-4 mb-3 {{ $leave->duration == 'multiple' ? 'd-none' : '' }}" id="single-date">
            <label class="form-label">Date</label>
            <input type="date" name="date" class="form-control" 
                   value="{{ $leave->duration !== 'multiple' ? $leave->date : '' }}">
        </div>
        
        {{-- ✅ Multi Date (only if multiple) --}}
        <div class="col-md-4 mb-3 {{ $leave->duration == 'multiple' ? '' : 'd-none' }}" id="multi-date">
            <label class="form-label">Start Date</label>
            <input type="date" name="start_date" class="form-control mb-2" 
                   value="{{ $leave->duration == 'multiple' ? $leave->start_date : '' }}">
            <label class="form-label">End Date</label>
            <input type="date" name="end_date" class="form-control" 
                   value="{{ $leave->duration == 'multiple' ? $leave->end_date : '' }}">
        </div>


        <div class="col-md-4 mb-3">
            <label>Reason for absence <span class="text-danger">*</span></label>
            <textarea name="reason" class="form-control" rows="3" required placeholder="e.g. Feeling not well">{{ $leave->reason }}</textarea>
        </div>

        <div class="col-md-4 mb-3">
            <label class="form-label">Add File</label>
            <input type="file" name="files" class="form-control" placeholder="Choose a file">
            @if($leave->files)
                <a href="{{ asset($leave->files) }}" target="_blank" class="d-block mt-1">View Current File</a>
            @endif
        </div>
    </div>

    <button class="btn btn-primary">Save</button>
    
    <a href="{{ route('leaves.index') }}" class="btn btn-secondary">Cancel</a>
</form>

</div>

@push('js')

<script>
function toggleDateFields(value) {
    if (value === 'multiple') {
        document.getElementById('multi-date').classList.remove('d-none');
        document.getElementById('single-date').classList.add('d-none');
    } else {
        document.getElementById('multi-date').classList.add('d-none');
        document.getElementById('single-date').classList.remove('d-none');
    }
}
</script>

@endpush
@endsection
