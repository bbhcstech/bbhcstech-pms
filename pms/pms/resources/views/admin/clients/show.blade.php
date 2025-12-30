@extends('admin.layout.app')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>{{ $client->salutation }} {{ $client->name }}</h4>
        <a href="{{ route('clients.index') }}" class="btn btn-outline-secondary">← Back</a>
    </div>

    <div class="row">
        <!-- Left Profile Info -->
        <div class="col-md-6">
            <div class="card mb-4">
              
                <div class="card-header bg-primary text-white">Profile Info</div>
                  &nbsp;
                <div class="card-body">
                    <p><strong>Full Name:</strong> {{ $client->salutation }} {{ $client->name }}</p>
                    <p><strong>Email:</strong> {{ $client->email }}</p>
                    <p><strong>Mobile:</strong> {{ $client->mobile ?? '--' }}</p>
                    <p><strong>Gender:</strong> {{ $client->gender ?? '--' }}</p>
                    <p><strong>Language:</strong> {{ ucfirst($client->language) ?? '--' }}</p>
                    <p><strong>Client Category:</strong> {{ $client->category->name ?? '--' }}</p>
                    <p><strong>Client Subcategory:</strong> {{ $client->subcategory->name ?? '--' }}</p>
                    <p><strong>Login Allowed:</strong> {{ $client->login_allowed ? 'Yes' : 'No' }}</p>
                    <p><strong>Email Notifications:</strong> {{ $client->email_notifications ? 'Yes' : 'No' }}</p>

                    @if($client->profile_image)
                        <p><strong>Profile Picture:</strong></p>
                        <img src="{{ asset($client->profile_image) }}" alt="Profile Image" class="img-thumbnail" style="max-width: 120px;">
                    @endif
                </div>
            </div>
        </div>

        <!-- Right Company Info -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-secondary text-white">Company Info</div>
                &nbsp;
                <div class="card-body">
                    <p><strong>Company Name:</strong> {{ $client->company_name ?? '--' }}</p>
                    <p><strong>Official Website:</strong>
                        @if($client->website)
                            <a href="{{ $client->website }}" target="_blank">{{ $client->website }}</a>
                        @else
                            --
                        @endif
                    </p>
                    <p><strong>Tax Name:</strong> {{ $client->tax_name ?? '--' }}</p>
                    <p><strong>GST/VAT Number:</strong> {{ $client->tax_number ?? '--' }}</p>
                    <p><strong>Office Phone:</strong> {{ $client->office_phone ?? '--' }}</p>
                    <p><strong>City:</strong> {{ $client->city ?? '--' }}</p>
                    <p><strong>State:</strong> {{ $client->state ?? '--' }}</p>
                    <p><strong>Postal Code:</strong> {{ $client->postal_code ?? '--' }}</p>
                    <p><strong>Address:</strong> {{ $client->company_address ?? '--' }}</p>
                    <p><strong>Shipping Address:</strong> {{ $client->shipping_address ?? '--' }}</p>
                    <p><strong>Note:</strong> {{ $client->note ?? '--' }}</p>

                    @if($client->company_logo)
                        <p><strong>Company Logo:</strong></p>
                        <img src="{{ asset($client->company_logo) }}" alt="Company Logo" class="img-thumbnail" style="max-width: 120px;">
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
