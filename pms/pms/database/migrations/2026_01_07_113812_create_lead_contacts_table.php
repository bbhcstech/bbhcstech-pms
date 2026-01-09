@extends('admin.layout.app')

@section('content')

<style>
.form-container {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
}
.left-section {
    background: #fff;
    border-radius: 8px;
    padding: 25px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}
.right-section {
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
.form-control {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}
.form-control:focus {
    outline: none;
    border-color: #0d6efd;
    box-shadow: 0 0 0 2px rgba(13, 110, 253, 0.1);
}
.checkbox-group {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 20px;
}
.checkbox-group label {
    margin: 0;
    font-weight: 500;
    cursor: pointer;
}
.deal-fields {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 6px;
    margin-top: 15px;
}
.currency-input {
    display: flex;
    align-items: center;
    gap: 10px;
}
.currency-input select {
    width: 120px;
}
.currency-input input {
    flex: 1;
}
.form-actions {
    display: flex;
    gap: 10px;
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #eee;
}
</style>

<form method="POST" action="/admin/leads/contacts/store">
@csrf

<div class="form-container">

    {{-- LEFT SECTION: Lead Source & Create Deal --}}
    <div class="left-section">
        <div class="section-title">Lead Source</div>

        <div class="form-group">
            <label>Lead Source</label>
            <select name="lead_source" class="form-control" required>
                <option value="">Select Lead Source</option>
                <option value="website">Website</option>
                <option value="email">Email</option>
                <option value="phone">Phone</option>
                <option value="social_media">Social Media</option>
                <option value="referral">Referral</option>
                <option value="walk_in">Walk-in</option>
                <option value="campaign">Campaign</option>
                <option value="other">Other</option>
            </select>
        </div>

        <div class="checkbox-group">
            <input type="checkbox" id="createDeal" name="create_deal" value="1">
            <label for="createDeal">Create Deal</label>
        </div>

        <div id="dealFields" class="deal-fields" style="display: none;">
            <div class="form-group">
                <label>Deal Name <span class="required">*</span></label>
                <input type="text" name="deal_name" class="form-control" placeholder="e.g., John Doe Deal">
            </div>

            <div class="form-group">
                <label>Deal Value <span class="required">*</span></label>
                <div class="currency-input">
                    <select name="deal_currency" class="form-control">
                        <option value="INR">INR (₹)</option>
                        <option value="USD">USD ($)</option>
                        <option value="EUR">EUR (€)</option>
                        <option value="GBP">GBP (£)</option>
                    </select>
                    <input type="number" name="deal_value" class="form-control" placeholder="0.00" step="0.01" min="0">
                </div>
            </div>

            <div class="form-group">
                <label>Deal Agent</label>
                <select name="deal_agent_id" class="form-control">
                    <option value="">Select Agent</option>
                    @foreach($users ?? [] as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label>Pipeline</label>
                <select name="pipeline" class="form-control">
                    <option value="sales">Sales Pipeline</option>
                    <option value="marketing">Marketing Pipeline</option>
                    <option value="support">Support Pipeline</option>
                </select>
            </div>

            <div class="form-group">
                <label>Deal Stage <span class="required">*</span></label>
                <select name="deal_stage" class="form-control">
                    <option value="lead">Lead</option>
                    <option value="qualification">Qualification</option>
                    <option value="proposal">Proposal</option>
                    <option value="negotiation">Negotiation</option>
                    <option value="closed_won">Closed Won</option>
                    <option value="closed_lost">Closed Lost</option>
                </select>
            </div>

            <div class="form-group">
                <label>Deal Category</label>
                <select name="deal_category" class="form-control">
                    <option value="">Select Category</option>
                    <option value="new_business">New Business</option>
                    <option value="existing_business">Existing Business</option>
                    <option value="renewal">Renewal</option>
                    <option value="upgrade">Upgrade</option>
                </select>
            </div>

            <div class="form-group">
                <label>Close Date</label>
                <input type="date" name="close_date" class="form-control">
            </div>

            <div class="form-group">
                <label>Products</label>
                <select name="products[]" class="form-control" multiple>
                    <option value="product1">Product 1</option>
                    <option value="product2">Product 2</option>
                    <option value="product3">Product 3</option>
                    <option value="product4">Product 4</option>
                </select>
                <small class="text-muted">Hold Ctrl/Cmd to select multiple products</small>
            </div>
        </div>

        <div class="section-title" style="margin-top: 30px;">Company Details</div>

        <div class="form-group">
            <label>Company Name</label>
            <input type="text" name="company_name" class="form-control" placeholder="Enter company name">
        </div>

        <div class="form-group">
            <label>Website</label>
            <input type="url" name="website" class="form-control" placeholder="https://example.com">
        </div>

        <div class="form-group">
            <label>Phone</label>
            <input type="tel" name="phone" class="form-control" placeholder="Enter phone number">
        </div>

        <div class="form-group">
            <label>Address</label>
            <textarea name="address" class="form-control" placeholder="Enter full address" rows="3"></textarea>
        </div>

        <div class="form-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
            <div class="form-group">
                <label>City</label>
                <input type="text" name="city" class="form-control" placeholder="City">
            </div>
            <div class="form-group">
                <label>State</label>
                <input type="text" name="state" class="form-control" placeholder="State">
            </div>
            <div class="form-group">
                <label>Country</label>
                <input type="text" name="country" class="form-control" placeholder="Country">
            </div>
            <div class="form-group">
                <label>Postal Code</label>
                <input type="text" name="postal_code" class="form-control" placeholder="Postal Code">
            </div>
        </div>
    </div>

    {{-- RIGHT SECTION: Contact & Additional Information --}}
    <div class="right-section">
        <div class="section-title">Contact Information</div>

        <div class="form-group">
            <label>Salutation</label>
            <select name="salutation" class="form-control">
                <option value="">Select</option>
                <option value="Mr">Mr</option>
                <option value="Ms">Ms</option>
                <option value="Mrs">Mrs</option>
                <option value="Dr">Dr</option>
                <option value="Prof">Prof</option>
            </select>
        </div>

        <div class="form-group">
            <label>Name <span class="required">*</span></label>
            <input type="text" name="contact_name" class="form-control" placeholder="e.g., John Doe" required>
        </div>

        <div class="form-group">
            <label>Email <span class="required">*</span></label>
            <input type="email" name="email" class="form-control" placeholder="john@example.com" required>
            <small class="text-muted">Email will be used to send proposals.</small>
        </div>

        <div class="form-group">
            <label>Mobile</label>
            <input type="tel" name="mobile" class="form-control" placeholder="Mobile number">
        </div>

        <div class="section-title" style="margin-top: 30px;">Assignment</div>

        <div class="form-group">
            <label>Added By</label>
            <input type="text" class="form-control" value="{{ auth()->user()->name ?? 'Admin' }}" disabled>
            <input type="hidden" name="added_by" value="{{ auth()->id() ?? 1 }}">
        </div>

        <div class="form-group">
            <label>Lead Owner <span class="required">*</span></label>
            <select name="lead_owner_id" class="form-control" required>
                <option value="">Select Lead Owner</option>
                @foreach($users ?? [] as $user)
                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="section-title" style="margin-top: 30px;">Additional Information</div>

        <div class="form-group">
            <label>Status</label>
            <select name="status" class="form-control">
                <option value="new">New</option>
                <option value="contacted">Contacted</option>
                <option value="qualified">Qualified</option>
                <option value="proposal">Proposal Sent</option>
                <option value="negotiation">Negotiation</option>
                <option value="won">Won</option>
                <option value="lost">Lost</option>
            </select>
        </div>

        <div class="form-group">
            <label>Industry</label>
            <select name="industry" class="form-control">
                <option value="">Select Industry</option>
                <option value="it">Information Technology</option>
                <option value="healthcare">Healthcare</option>
                <option value="finance">Finance</option>
                <option value="education">Education</option>
                <option value="retail">Retail</option>
                <option value="manufacturing">Manufacturing</option>
                <option value="real_estate">Real Estate</option>
                <option value="other">Other</option>
            </select>
        </div>

        <div class="form-group">
            <label>Lead Score</label>
            <input type="range" name="lead_score" class="form-control" min="0" max="100" value="50">
            <div style="display: flex; justify-content: space-between; font-size: 12px;">
                <span>0</span>
                <span>25</span>
                <span>50</span>
                <span>75</span>
                <span>100</span>
            </div>
        </div>

        <div class="form-group">
            <label>Tags</label>
            <input type="text" name="tags" class="form-control" placeholder="e.g., hot-lead, interested">
            <small class="text-muted">Separate tags with commas</small>
        </div>

        <div class="form-group">
            <label>Description / Notes</label>
            <textarea name="description" class="form-control" placeholder="Add notes here..." rows="4"></textarea>
        </div>
    </div>
</div>

<div class="form-actions">
    <button type="submit" class="btn btn-primary">Save Lead Contact</button>
    <button type="submit" name="save_and_add_more" value="1" class="btn btn-outline-primary">Save & Add More</button>
    <a href="/admin/leads/contacts" class="btn btn-outline-secondary">Cancel</a>
</div>

</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle deal fields
    const createDealCheckbox = document.getElementById('createDeal');
    const dealFields = document.getElementById('dealFields');

    if (createDealCheckbox && dealFields) {
        createDealCheckbox.addEventListener('change', function() {
            dealFields.style.display = this.checked ? 'block' : 'none';

            // Toggle required fields
            const requiredFields = dealFields.querySelectorAll('[required]');
            requiredFields.forEach(field => {
                field.disabled = !this.checked;
            });
        });
    }

    // Form validation
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            const contactName = document.querySelector('input[name="contact_name"]');
            const email = document.querySelector('input[name="email"]');
            const leadOwner = document.querySelector('select[name="lead_owner_id"]');
            const leadSource = document.querySelector('select[name="lead_source"]');
            const createDeal = document.getElementById('createDeal');

            // Basic validation
            if (!contactName.value.trim()) {
                e.preventDefault();
                alert('Please enter contact name');
                contactName.focus();
                return false;
            }

            if (!email.value.trim()) {
                e.preventDefault();
                alert('Please enter email address');
                email.focus();
                return false;
            }

            if (!leadOwner.value) {
                e.preventDefault();
                alert('Please select a lead owner');
                leadOwner.focus();
                return false;
            }

            if (!leadSource.value) {
                e.preventDefault();
                alert('Please select a lead source');
                leadSource.focus();
                return false;
            }

            // Email validation
            if (email.value && !isValidEmail(email.value)) {
                e.preventDefault();
                alert('Please enter a valid email address');
                email.focus();
                return false;
            }

            // Deal validation if create deal is checked
            if (createDeal && createDeal.checked) {
                const dealName = document.querySelector('input[name="deal_name"]');
                const dealValue = document.querySelector('input[name="deal_value"]');
                const dealStage = document.querySelector('select[name="deal_stage"]');

                if (!dealName.value.trim()) {
                    e.preventDefault();
                    alert('Please enter deal name');
                    dealName.focus();
                    return false;
                }

                if (!dealValue.value || parseFloat(dealValue.value) <= 0) {
                    e.preventDefault();
                    alert('Please enter a valid deal value');
                    dealValue.focus();
                    return false;
                }

                if (!dealStage.value) {
                    e.preventDefault();
                    alert('Please select a deal stage');
                    dealStage.focus();
                    return false;
                }
            }

            return true;
        });
    }

    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
});
</script>

@endsection
