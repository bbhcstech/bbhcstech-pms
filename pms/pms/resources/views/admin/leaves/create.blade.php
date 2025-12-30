@extends('admin.layout.app')

@section('title', 'Apply Leave')

@section('content')
<div class="container mt-4">
    <h4>Apply for Leave</h4>
    
    @if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

    <form method="POST" action="{{ route('leaves.store') }}" enctype="multipart/form-data">
        @csrf
        
            <div class="row">
                    @if(auth()->user()->role === 'admin')
                {{-- Admin: Show dropdown with all employees --}}
                <div class="col-md-4 mb-3">
                    <label for="user_id" class="form-label">Select Employee <span class="text-danger">*</span></label>
                    <select name="user_id" class="form-select" required>
                        <option value="">-- Select Employee --</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->designation ?? 'N/A' }})</option>
                        @endforeach
                    </select>
                </div>
            @else
                {{-- Employee: Auto-select their own name --}}
                <div class="col-md-4 mb-3">
                    <label for="user_id" class="form-label">Employee</label>
                    <input type="text" class="form-control" value="{{ auth()->user()->name }} ({{ auth()->user()->designation ?? 'N/A' }})" disabled>
                    <input type="hidden" name="user_id" value="{{ auth()->id() }}">
                </div>
            @endif


        <div class="col-md-4 mb-3">
            <label>Leave Type <span class="text-danger">*</span></label>
            <select name="type" class="form-select" required>
                <option value="">-- Select Type --</option>
                <option value="sick">Sick Leave</option>
                <option value="casual">Casual Leave</option>
                <option value="leave-without-pay">Leave Without Pay</option>
            </select>
        </div>
             @if(auth()->user()->role === 'admin')
        <div class="col-md-4 mb-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-select" required >
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                
            </select>
        </div>
        
        @endif

        <div class="col-md-4 mb-3">
            <label class="form-label">Select Duration <span class="text-danger">*</span></label>
            <select name="duration" class="form-select" required onchange="toggleDateFields(this.value)">
                <option value="full-day">Full Day</option>
                <option value="multiple">Multiple Days</option>
                <option value="first-half">First Half</option>
                <option value="second-half">Second Half</option>
            </select>
        </div>

        {{-- ✅ Single Date --}}
        <div class="col-md-4 mb-3" id="single-date">
            <label class="form-label">Date <span class="text-danger">*</span></label>
            <input type="date" name="date" class="form-control">
        </div>

        {{-- ✅ Multi Date --}}
        <div class="col-md-4 mb-3 d-none" id="multi-date">
            <label class="form-label">Start Date</label>
            <input type="date" name="start_date" class="form-control mb-2">
            <label class="form-label">End Date</label>
            <input type="date" name="end_date" class="form-control">
        </div>

        <div class="col-md-4 mb-3">
            <label>Reason for absence <span class="text-danger">*</span></label>
            <textarea name="reason" class="form-control" rows="3" placeholder="e.g. Feeling not well" required></textarea>
        </div>

        <div class="col-md-4 mb-3">
            <label class="form-label">Add File</label>
            <input type="file" name="files" class="form-control" placeholder="Choose a file">
        </div>
    </div>
        <button class="btn btn-primary">Submit</button>
        <a href="{{ route('leaves.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>

@push('js')

<script>
function toggleDateFields(value) {
    const singleDate = document.getElementById('single-date');
    const multiDate = document.getElementById('multi-date');

    if (value === 'multiple') {
        singleDate.classList.add('d-none');
        multiDate.classList.remove('d-none');
    } else {
        singleDate.classList.remove('d-none');
        multiDate.classList.add('d-none');
    }
}
</script>


@endpush
@endsection
