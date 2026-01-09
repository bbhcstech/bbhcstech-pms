@extends('admin.layout.app')

@section('content')

<style>
.form-grid {
    display:grid;
    grid-template-columns: 1fr 1fr;
    gap:20px;
}
.section {
    border-bottom:1px solid #eee;
    padding-bottom:15px;
    margin-bottom:15px;
}
.back-button-container {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 20px;
}
</style>

{{-- FIXED FORM ACTION - Use the correct route --}}
<form method="POST" action="{{ route('leads.contacts.store') }}">
@csrf

<div class="card p-4">

    <div class="form-grid section">
        <div>
            <label>Salutation</label>
            <select class="form-select" name="salutation">
                <option value="">Select</option>
                <option value="Mr">Mr</option>
                <option value="Ms">Ms</option>
                <option value="Mrs">Mrs</option>
                <option value="Dr">Dr</option>
                <option value="Prof">Prof</option>
            </select>
        </div>

        <div>
            <label>Name *</label>
            <input type="text" name="contact_name" class="form-control" required>
        </div>

        <div>
            <label>Email</label>
            <input type="email" name="email" class="form-control">
        </div>

        <div>
            <label>Lead Source</label>
            <select class="form-select" name="lead_source">
                <option value="website">Website</option>
                <option value="referral">Referral</option>
                <option value="email">Email</option>
                <option value="phone">Phone</option>
                <option value="social_media">Social Media</option>
                <option value="other">Other</option>
            </select>
        </div>

        <div>
            <label>Added By</label>
            <input type="text" class="form-control" value="{{ auth()->user()->name ?? 'Admin' }}" disabled>
            <input type="hidden" name="added_by" value="{{ auth()->id() ?? 1 }}">
        </div>

        <div>
            <label>Lead Owner *</label>
            <select name="lead_owner_id" class="form-select" required>
                <option value="">Select Lead Owner</option>
                @foreach($users ?? [] as $user)
                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- CREATE DEAL --}}
    <div class="section">
        <label>
            <input type="checkbox" id="createDeal" name="create_deal"> Create Deal
        </label>

        <div id="dealFields" style="display:none" class="form-grid mt-3">
            <input name="deal_name" class="form-control" placeholder="Deal Name">
            <input name="deal_value" class="form-control" placeholder="Deal Value" type="number" step="0.01">
        </div>
    </div>

    {{-- COMPANY DETAILS --}}
    <details class="section">
        <summary><strong>Company Details</strong></summary>
        <div class="form-grid mt-3">
            <div>
                <label>Company Name</label>
                <input name="company_name" class="form-control" placeholder="Company Name">
            </div>
            <div>
                <label>Website</label>
                <input name="website" class="form-control" placeholder="Website URL" type="url">
            </div>
            <div>
                <label>Phone</label>
                <input name="phone" class="form-control" placeholder="Phone Number">
            </div>
            <div>
                <label>Industry</label>
                <input name="industry" class="form-control" placeholder="Industry">
            </div>
            <div>
                <label>Address</label>
                <textarea name="address" class="form-control" placeholder="Full Address" rows="2"></textarea>
            </div>
            <div>
                <label>City</label>
                <input name="city" class="form-control" placeholder="City">
            </div>
            <div>
                <label>State</label>
                <input name="state" class="form-control" placeholder="State">
            </div>
            <div>
                <label>Country</label>
                <input name="country" class="form-control" placeholder="Country">
            </div>
        </div>
    </details>

    {{-- ADDITIONAL FIELDS --}}
    <details class="section">
        <summary><strong>Additional Information</strong></summary>
        <div class="form-grid mt-3">
            <div>
                <label>Mobile</label>
                <input name="mobile" class="form-control" placeholder="Mobile Number">
            </div>
            <div>
                <label>Status</label>
                <select name="status" class="form-select">
                    <option value="new">New</option>
                    <option value="contacted">Contacted</option>
                    <option value="qualified">Qualified</option>
                    <option value="proposal">Proposal Sent</option>
                    <option value="negotiation">Negotiation</option>
                    <option value="won">Won</option>
                    <option value="lost">Lost</option>
                </select>
            </div>
            <div>
                <label>Description / Notes</label>
                <textarea name="description" class="form-control" placeholder="Add notes here..." rows="3"></textarea>
            </div>
        </div>
    </details>

    <div class="mt-4 d-flex gap-2  back-button-container">
        <button type="submit" class="btn btn-primary">Save Lead Contact</button>
        <button type="submit" name="save_and_add_more" value="1" class="btn btn-outline-primary">Save & Add More</button>
        <!-- <a href="{{ url('/admin/leads/contacts') }}" class="btn btn-outline-secondary">Cancel</a> -->
         <button type="button" class="btn btn-outline-secondary btn-sm" id="backButton">
            <i class="fas fa-arrow-left"></i> Back
        </button>
    </div>

</div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle deal fields
    const createDealCheckbox = document.getElementById('createDeal');
    const dealFields = document.getElementById('dealFields');

    createDealCheckbox.addEventListener('change', function() {
        dealFields.style.display = this.checked ? 'grid' : 'none';
    });

    // Form validation
    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
        const contactName = document.querySelector('input[name="contact_name"]');
        const leadOwner = document.querySelector('select[name="lead_owner_id"]');

        if (!contactName.value.trim()) {
            e.preventDefault();
            alert('Please enter contact name');
            contactName.focus();
            return false;
        }

        if (!leadOwner.value) {
            e.preventDefault();
            alert('Please select a lead owner');
            leadOwner.focus();
            return false;
        }

        // Optional: Add email validation
        const emailField = document.querySelector('input[name="email"]');
        if (emailField.value && !isValidEmail(emailField.value)) {
            e.preventDefault();
            alert('Please enter a valid email address');
            emailField.focus();
            return false;
        }
    });

    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }



    // ========== BACK BUTTON FUNCTIONALITY ==========
    const backButton = document.getElementById('backButton');
    if (backButton) {
        backButton.addEventListener('click', function() {
            if (window.history.length > 1) {
                window.history.back();
            } else {
                window.location.href = '/'; // Fallback to home
            }
        });
    }
});
</script>

@endsection
