@extends('admin.layout.app')

@section('content')
<style>
.form-container {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
}
.left-section, .right-section {
    background: #fff;
    border-radius: 8px;
    padding: 25px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}
.section-title {
    font-size: 18px;
    font-weight: 600;
    margin-bottom: 20px;
    color: #333;
    padding-bottom: 10px;
    border-bottom: 2px solid #0d6efd;
}
.form-group {
    margin-bottom: 20px;
}
.form-group label {
    display: block;
    margin-bottom: 6px;
    font-weight: 500;
    color: #333;
    font-size: 14px;
}
.form-group label .required {
    color: #dc3545;
}
</style>

<div class="container-fluid">
    <div class="mb-3">
        <h4 class="fw-semibold">Edit Lead Contact</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('leads.contacts.index') }}">Leads</a></li>
                <li class="breadcrumb-item active" aria-current="page">Edit {{ $lead->contact_name }}</li>
            </ol>
        </nav>
    </div>

    <form method="POST" action="{{ route('leads.contacts.update', $lead->id) }}">
        @csrf
        @method('PUT')

        <div class="form-container">
            {{-- Left Section --}}
            <div class="left-section">
                <div class="section-title">Lead Source & Company Details</div>

                {{-- Your form fields here (similar to create.blade.php) --}}
                {{-- Copy all the form fields from create.blade.php and add value attributes --}}

                {{-- Example: --}}
                <div class="form-group">
                    <label>Lead Source <span class="required">*</span></label>
                    <select name="lead_source" class="form-control" required>
                        <option value="">Select Lead Source</option>
                        <option value="website" {{ $lead->lead_source == 'website' ? 'selected' : '' }}>Website</option>
                        <option value="email" {{ $lead->lead_source == 'email' ? 'selected' : '' }}>Email</option>
                        {{-- Add more options --}}
                    </select>
                </div>

                {{-- Add all other fields with their values --}}
            </div>

            {{-- Right Section --}}
            <div class="right-section">
                <div class="section-title">Contact & Assignment</div>

                {{-- Copy all form fields from create.blade.php with values --}}
                <div class="form-group">
                    <label>Name <span class="required">*</span></label>
                    <input type="text" name="contact_name" class="form-control"
                           value="{{ old('contact_name', $lead->contact_name) }}" required>
                </div>

                {{-- Add all other fields --}}
            </div>
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-primary">Update Lead</button>
            <a href="{{ route('leads.contacts.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>

<script>
// Add the same JavaScript as create.blade.php for dynamic fields
</script>
@endsection
