@extends('admin.layout.app')

@section('content')

<style>
.top-bar {
    display:flex;
    gap:10px;
    align-items:center;
    flex-wrap:wrap;
}
.top-bar select,
.top-bar input {
    height:36px;
    font-size:14px;
}
.bulk-actions,
.date-actions {
    display:none;
    gap:8px;
    align-items:center;
}
.table-avatar {
    display:flex;
    gap:8px;
    align-items:center;
}
.table-avatar img {
    width:32px;
    height:32px;
    border-radius:50%;
}
.action-dot {
    cursor:pointer;
    font-size:18px;
}
#customDateRange {
    display: none;
    gap:8px;
}

/* FILTER SIDEBAR STYLES */
.filter-sidebar {
    position: fixed;
    top: 0;
    right: -400px;
    width: 380px;
    height: 100vh;
    background: #fff;
    box-shadow: -2px 0 10px rgba(0,0,0,0.1);
    z-index: 1050;
    transition: right 0.3s ease;
    overflow-y: auto;
}
.filter-sidebar.active {
    right: 0;
}
.filter-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 1040;
    display: none;
}
.filter-overlay.active {
    display: block;
}
.filter-header {
    padding: 20px;
    border-bottom: 1px solid #eee;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.filter-header h5 {
    margin: 0;
    font-weight: 600;
}
.filter-close {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: #666;
}
.filter-body {
    padding: 20px;
}
.filter-section {
    margin-bottom: 25px;
}
.filter-section h6 {
    font-weight: 600;
    margin-bottom: 12px;
    color: #333;
}
.filter-actions {
    padding: 20px;
    border-top: 1px solid #eee;
    display: flex;
    gap: 10px;
}
.filter-actions .btn {
    flex: 1;
}
.form-check-label {
    font-size: 14px;
}
.form-select-sm {
    font-size: 13px;
}

/* IMPORT MODAL STYLES */
.import-modal .modal-dialog {
    max-width: 500px;
}
.file-upload-area {
    border: 2px dashed #ddd;
    border-radius: 8px;
    padding: 40px 20px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s;
}
.file-upload-area:hover {
    border-color: #0d6efd;
    background: #f8f9ff;
}
.file-upload-area i {
    font-size: 48px;
    color: #6c757d;
    margin-bottom: 15px;
}
.file-upload-area.dragover {
    border-color: #0d6efd;
    background: #e7f1ff;
}

/* LEAD FORM BUILDER MODAL STYLES */
.lead-form-builder-modal .modal-dialog {
    max-width: 1000px;
}
.lead-form-builder-modal .modal-body {
    padding: 0;
    min-height: 600px;
}
.form-builder-container {
    display: flex;
    height: 600px;
}
.form-fields-section {
    flex: 0 0 300px;
    background: #f8f9fa;
    padding: 20px;
    overflow-y: auto;
    border-right: 1px solid #dee2e6;
}
.form-preview-section {
    flex: 1;
    padding: 20px;
    background: #fff;
    overflow-y: auto;
}
.section-title {
    font-size: 16px;
    font-weight: 600;
    margin-bottom: 20px;
    color: #333;
    padding-bottom: 10px;
    border-bottom: 1px solid #dee2e6;
}
.field-group {
    margin-bottom: 25px;
}
.field-group-title {
    font-size: 14px;
    font-weight: 600;
    color: #495057;
    margin-bottom: 15px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.field-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 15px;
    margin-bottom: 10px;
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s;
}
.field-item:hover {
    border-color: #0d6efd;
    background: #f8f9ff;
}
.field-item .field-name {
    font-size: 14px;
    color: #333;
}
.toggle-switch {
    position: relative;
    display: inline-block;
    width: 40px;
    height: 20px;
}
.toggle-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}
.toggle-slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    transition: .4s;
    border-radius: 20px;
}
.toggle-slider:before {
    position: absolute;
    content: "";
    height: 14px;
    width: 14px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: .4s;
    border-radius: 50%;
}
.toggle-switch input:checked + .toggle-slider {
    background-color: #0d6efd;
}
.toggle-switch input:checked + .toggle-slider:before {
    transform: translateX(20px);
}
.preview-form {
    max-width: 600px;
    margin: 0 auto;
}
.preview-field {
    margin-bottom: 20px;
    display: none;
}
.preview-field.active {
    display: block;
}
.preview-field label {
    display: block;
    margin-bottom: 6px;
    font-weight: 500;
    color: #333;
    font-size: 14px;
}
.preview-field label .required {
    color: #dc3545;
}
.preview-field input,
.preview-field textarea,
.preview-field select {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}
.preview-field input:focus,
.preview-field textarea:focus,
.preview-field select:focus {
    outline: none;
    border-color: #0d6efd;
    box-shadow: 0 0 0 2px rgba(13, 110, 253, 0.1);
}
.form-preview-title {
    text-align: center;
    margin-bottom: 30px;
    color: #333;
    font-weight: 600;
}
.form-status {
    background: #e7f1ff;
    padding: 15px;
    border-radius: 6px;
    margin-bottom: 30px;
}
.form-status h6 {
    font-weight: 600;
    margin-bottom: 10px;
    color: #0d6efd;
}

/* PAGINATION STYLES */
.pagination-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 0;
    margin-top: 20px;
    border-top: 1px solid #e0e0e0;
    gap: 20px;
}
.left-pagination {
    display: flex;
    align-items: center;
    gap: 8px;
    flex: 1;
}
.show-entries-label {
    font-size: 14px;
    color: #666;
    white-space: nowrap;
}
.entries-select {
    padding: 6px 12px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    background-color: white;
    font-size: 14px;
    color: #374151;
    cursor: pointer;
    min-width: 70px;
    transition: all 0.2s;
}
.entries-select:hover {
    border-color: #9ca3af;
}
.entries-select:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}
.pagination-info {
    font-size: 14px;
    color: #666;
    margin-left: 20px;
    white-space: nowrap;
}
.right-pagination {
    display: flex;
    align-items: center;
    gap: 8px;
}
.pagination-btn {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    background-color: white;
    color: #374151;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
}
.pagination-btn:hover:not(:disabled) {
    background-color: #f9fafb;
    border-color: #9ca3af;
}
.pagination-btn:active:not(:disabled) {
    background-color: #f3f4f6;
}
.pagination-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}
.pagination-btn svg {
    width: 16px;
    height: 16px;
}
.prev-btn {
    padding-left: 12px;
}
.next-btn {
    padding-right: 12px;
}
.page-numbers {
    display: flex;
    align-items: center;
    gap: 4px;
    margin: 0 8px;
}
.page-number {
    min-width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0 8px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    background-color: white;
    color: #374151;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.2s;
}
.page-number:hover:not(.active) {
    background-color: #f9fafb;
    border-color: #9ca3af;
}
.page-number.active {
    background-color: #3b82f6;
    border-color: #3b82f6;
    color: white;
    font-weight: 600;
}
.page-dots {
    padding: 0 8px;
    color: #9ca3af;
}
@media (max-width: 768px) {
    .pagination-container {
        flex-direction: column;
        align-items: stretch;
    }
    .left-pagination {
        justify-content: center;
        margin-bottom: 10px;
    }
    .right-pagination {
        justify-content: center;
    }
}
@media (max-width: 480px) {
    .pagination-info {
        display: none;
    }
    .page-numbers {
        margin: 0 4px;
    }
    .page-number {
        min-width: 32px;
        height: 32px;
        font-size: 13px;
    }
}

/* TYPE BADGES */
.type-badge {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
}
.type-lead {
    background-color: #fef3c7;
    color: #92400e;
}
.type-client {
    background-color: #d1fae5;
    color: #065f46;
}
</style>

<div class="container-fluid">

    {{-- FILTER OVERLAY --}}
    <div class="filter-overlay" id="filterOverlay"></div>

    {{-- FILTER SIDEBAR --}}
    <div class="filter-sidebar" id="filterSidebar">
        <div class="filter-header">
            <h5>Filter Leads</h5>
            <button type="button" class="filter-close" id="closeFilter">&times;</button>
        </div>

        <div class="filter-body">
            {{-- Date Filter Section --}}
            <div class="filter-section">
                <h6>Date Filter</h6>
                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="sidebarDateFilter">
                        <label class="form-check-label" for="sidebarDateFilter">
                            Filter by Date
                        </label>
                    </div>
                </div>

                <div id="sidebarDateFields" style="display: none;">
                    <div class="mb-3">
                        <label class="form-label small">Filter On</label>
                        <select class="form-select form-select-sm" id="filterOn">
                            <option value="created_at">Created On</option>
                            <option value="updated_at">Updated On</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small">Date Range</label>
                        <select class="form-select form-select-sm" id="sidebarDateRange">
                            <option value="today">Today</option>
                            <option value="yesterday">Yesterday</option>
                            <option value="last7">Last 7 Days</option>
                            <option value="last30">Last 30 Days</option>
                            <option value="thisMonth">This Month</option>
                            <option value="lastMonth">Last Month</option>
                            <option value="custom">Custom Range</option>
                        </select>
                    </div>

                    <div id="sidebarCustomDateRange" style="display: none; gap: 10px;" class="flex-column">
                        <div>
                            <label class="form-label small">From Date</label>
                            <input type="date" class="form-control form-control-sm" id="sidebarStartDate">
                        </div>
                        <div>
                            <label class="form-label small">To Date</label>
                            <input type="date" class="form-control form-control-sm" id="sidebarEndDate">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Lead Source Section --}}
            <div class="filter-section">
                <h6>Lead Source</h6>
                <select class="form-select form-select-sm" id="leadSourceFilter" multiple size="5">
                    <option value="email">Email</option>
                    <option value="google">Google</option>
                    <option value="facebook">Facebook</option>
                    <option value="linkedin">LinkedIn</option>
                    <option value="twitter">Twitter</option>
                    <option value="referral">Referral</option>
                    <option value="website">Website Form</option>
                    <option value="phone">Phone Call</option>
                    <option value="walkin">Walk-in</option>
                    <option value="other">Other</option>
                </select>
                <small class="text-muted">Hold Ctrl/Cmd to select multiple sources</small>
            </div>

            {{-- Added By Section --}}
            <div class="filter-section">
                <h6>Added By</h6>
                <select class="form-select form-select-sm" id="addedByFilter">
                    <option value="">All Users</option>
                    @foreach($users ?? [] as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="filter-actions">
            <button type="button" class="btn btn-secondary btn-sm" id="clearFilterBtn">Clear</button>
            <button type="button" class="btn btn-primary btn-sm" id="applyFilterBtn">Apply Filter</button>
        </div>
    </div>

    {{-- IMPORT MODAL --}}
    <div class="modal fade import-modal" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="importModalLabel">Import Leads</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('leads.contacts.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="file-upload-area" id="fileUploadArea">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <h5>Drag & Drop your file here</h5>
                            <p class="text-muted">or click to browse</p>
                            <input type="file" name="file" id="fileInput" accept=".csv,.xlsx,.xls" style="display: none;">
                            <div id="fileName" class="mt-2"></div>
                        </div>

                        <div class="mt-4">
                            <h6>Supported Formats:</h6>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-file-excel text-success"></i> Excel (.xlsx, .xls)</li>
                                <li><i class="fas fa-file-csv text-primary"></i> CSV (.csv)</li>
                            </ul>

                            <div class="alert alert-info mt-3">
                                <h6><i class="fas fa-info-circle"></i> Import Instructions:</h6>
                                <ul class="mb-0">
                                    <li>First row should contain column headers</li>
                                    <li>Required columns: Name, Email</li>
                                    <li>Download <a href="{{ route('leads.contacts.template') }}">sample template</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="importSubmitBtn" disabled>Import</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- LEAD FORM BUILDER MODAL --}}
    <div class="modal fade lead-form-builder-modal" id="leadFormBuilderModal" tabindex="-1" aria-labelledby="leadFormBuilderModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="leadFormBuilderModalLabel">Lead Form Builder</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-builder-container">
                        {{-- Left Side: Available Fields with Toggle Switches --}}
                        <div class="form-fields-section">
                            <div class="section-title">Form Fields</div>

                            {{-- Basic Information Section --}}
                            <div class="field-group">
                                <div class="field-group-title">
                                    <span>Basic Information</span>
                                </div>
                                <div class="field-item" data-field="name">
                                    <span class="field-name">Name</span>
                                    <label class="toggle-switch">
                                        <input type="checkbox" checked>
                                        <span class="toggle-slider"></span>
                                    </label>
                                </div>
                                <div class="field-item" data-field="email">
                                    <span class="field-name">Email</span>
                                    <label class="toggle-switch">
                                        <input type="checkbox" checked>
                                        <span class="toggle-slider"></span>
                                    </label>
                                </div>
                                <div class="field-item" data-field="company_name">
                                    <span class="field-name">Company Name</span>
                                    <label class="toggle-switch">
                                        <input type="checkbox" checked>
                                        <span class="toggle-slider"></span>
                                    </label>
                                </div>
                                <div class="field-item" data-field="website">
                                    <span class="field-name">Website</span>
                                    <label class="toggle-switch">
                                        <input type="checkbox">
                                        <span class="toggle-slider"></span>
                                    </label>
                                </div>
                                <div class="field-item" data-field="address">
                                    <span class="field-name">Address</span>
                                    <label class="toggle-switch">
                                        <input type="checkbox">
                                        <span class="toggle-slider"></span>
                                    </label>
                                </div>
                                <div class="field-item" data-field="mobile">
                                    <span class="field-name">Mobile</span>
                                    <label class="toggle-switch">
                                        <input type="checkbox" checked>
                                        <span class="toggle-slider"></span>
                                    </label>
                                </div>
                                <div class="field-item" data-field="message">
                                    <span class="field-name">Message</span>
                                    <label class="toggle-switch">
                                        <input type="checkbox">
                                        <span class="toggle-slider"></span>
                                    </label>
                                </div>
                            </div>

                            {{-- Location Information Section --}}
                            <div class="field-group">
                                <div class="field-group-title">
                                    <span>Location Information</span>
                                </div>
                                <div class="field-item" data-field="city">
                                    <span class="field-name">City</span>
                                    <label class="toggle-switch">
                                        <input type="checkbox" checked>
                                        <span class="toggle-slider"></span>
                                    </label>
                                </div>
                                <div class="field-item" data-field="state">
                                    <span class="field-name">State</span>
                                    <label class="toggle-switch">
                                        <input type="checkbox" checked>
                                        <span class="toggle-slider"></span>
                                    </label>
                                </div>
                                <div class="field-item" data-field="country">
                                    <span class="field-name">Country</span>
                                    <label class="toggle-switch">
                                        <input type="checkbox" checked>
                                        <span class="toggle-slider"></span>
                                    </label>
                                </div>
                            </div>

                            {{-- Status Section --}}
                            <div class="field-group">
                                <div class="field-group-title">
                                    <span>Status</span>
                                </div>
                                <div class="field-item" data-field="status_name">
                                    <span class="field-name">Status Name</span>
                                    <label class="toggle-switch">
                                        <input type="checkbox" checked>
                                        <span class="toggle-slider"></span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        {{-- Right Side: Form Preview --}}
                        <div class="form-preview-section">
                            <div class="section-title">Form Preview</div>

                            <div class="preview-form">
                                <div class="form-preview-title">Lead Form</div>

                                {{-- Status Section Preview --}}
                                <div class="form-status">
                                    <h6>Status</h6>
                                    <div class="preview-field active" id="preview-status_name">
                                        <label>Status Name <span class="required">*</span></label>
                                        <input type="text" placeholder="Enter status name">
                                    </div>
                                </div>

                                {{-- Basic Information Preview --}}
                                <div class="preview-field active" id="preview-name">
                                    <label>Name <span class="required">*</span></label>
                                    <input type="text" placeholder="Enter full name">
                                </div>

                                <div class="preview-field active" id="preview-email">
                                    <label>Email <span class="required">*</span></label>
                                    <input type="email" placeholder="Enter email address">
                                </div>

                                <div class="preview-field active" id="preview-company_name">
                                    <label>Company Name <span class="required">*</span></label>
                                    <input type="text" placeholder="Enter company name">
                                </div>

                                <div class="preview-field" id="preview-website">
                                    <label>Website</label>
                                    <input type="url" placeholder="Enter website URL">
                                </div>

                                <div class="preview-field" id="preview-address">
                                    <label>Address</label>
                                    <textarea placeholder="Enter full address" rows="2"></textarea>
                                </div>

                                <div class="preview-field active" id="preview-mobile">
                                    <label>Mobile <span class="required">*</span></label>
                                    <input type="tel" placeholder="Enter mobile number">
                                </div>

                                <div class="preview-field" id="preview-message">
                                    <label>Message</label>
                                    <textarea placeholder="Enter your message" rows="3"></textarea>
                                </div>

                                {{-- Location Information Preview --}}
                                <div class="preview-field active" id="preview-city">
                                    <label>City <span class="required">*</span></label>
                                    <input type="text" placeholder="Enter city">
                                </div>

                                <div class="preview-field active" id="preview-state">
                                    <label>State <span class="required">*</span></label>
                                    <input type="text" placeholder="Enter state">
                                </div>

                                <div class="preview-field active" id="preview-country">
                                    <label>Country <span class="required">*</span></label>
                                    <select>
                                        <option value="">Select Country</option>
                                        <option value="us">United States</option>
                                        <option value="uk">United Kingdom</option>
                                        <option value="ca">Canada</option>
                                        <option value="au">Australia</option>
                                        <option value="in">India</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveFormConfig">Save Configuration</button>
                </div>
            </div>
        </div>
    </div>

    {{-- BACK BUTTON --}}
    <div class="back-button-container">
        <h4 class="fw-semibold mb-0">Lead Contacts</h4>
    </div>

    {{-- PAGE TITLE --}}
    <div class="mb-3">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
    </div>

    {{-- TOP FILTER BAR --}}
    <div class="top-bar mb-3">

        {{-- Filter Button --}}
        <button class="btn btn-outline-secondary btn-sm" id="openFilterBtn">
            <i class="fas fa-filter"></i> Filter
        </button>

        {{-- Enable Date Filter --}}
        <label>
            <input type="checkbox" id="enableDateFilter"> Enable Date Filter
        </label>

        {{-- Date Filter Section --}}
        <div id="dateFilterContainer" class="date-actions">
            <select class="form-select w-auto" id="dateRangeSelect">
                <option value="today">Today</option>
                <option value="last30">Last 30 Days</option>
                <option value="thisMonth">This Month</option>
                <option value="lastMonth">Last Month</option>
                <option value="last90">Last 90 Days</option>
                <option value="last6Months">Last 6 Months</option>
                <option value="last1Year">Last 1 Year</option>
                <option value="custom">Custom Range</option>
            </select>

            <div id="customDateRange">
                <input type="date" id="startDate" class="form-control">
                <span>To</span>
                <input type="date" id="endDate" class="form-control">
            </div>

            <button id="applyDateFilter" class="btn btn-primary btn-sm">Apply</button>
            <button id="cancelDateFilter" class="btn btn-secondary btn-sm">Cancel</button>
        </div>

        {{-- Type Dropdown (Fixed) --}}
        <select class="form-select w-auto" id="typeFilter">
            <option value="all" {{ request('type') == 'all' || !request()->has('type') ? 'selected' : '' }}>All</option>
            <option value="lead" {{ request('type') == 'lead' ? 'selected' : '' }}>Lead</option>
            <option value="client" {{ request('type') == 'client' ? 'selected' : '' }}>Client</option>
        </select>

        {{-- Search --}}
        <input type="text" class="form-control w-25" placeholder="Search" id="searchInput" value="{{ request('search', '') }}">

        {{-- Right side --}}
        <div class="ms-auto d-flex gap-2 align-items-center">

            {{-- Bulk Action Section --}}
            <div class="bulk-actions">
                <select class="form-select form-select-sm w-auto" id="bulkActionSelect">
                    <option value="">Action</option>
                    <option value="delete">Delete Selected</option>
                    <option value="export">Export Selected</option>
                </select>
                <button class="btn btn-sm btn-primary" id="applyBulk">Apply</button>
            </div>

            {{-- Lead Form Builder Button --}}
            <button type="button" class="btn btn-outline-primary btn-sm" id="leadFormBuilderBtn">
                <i class="fas fa-cog"></i> Lead Form Builder
            </button>

            <a href="{{ route('leads.contacts.create') }}" class="btn btn-primary btn-sm">
                + Add Lead Contact
            </a>

            <button type="button" class="btn btn-outline-secondary btn-sm" id="importBtn">
                <i class="fas fa-file-import"></i> Import
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm" id="exportAllBtn">
                <i class="fas fa-file-export"></i> Export All
            </button>
        </div>
    </div>

    {{-- TABLE --}}
    <div class="card">
        <div class="card-body p-0">
            <table class="table table-hover mb-0 align-middle" id="leadsTable">
                <thead class="table-light">
                    <tr>
                        <th width="40">
                            <input type="checkbox" id="mainCheckbox">
                        </th>
                        <th>ID</th>
                        <th>Contact</th>
                        <th>Type</th>
                        <th>Email</th>
                        <th>Lead Owner</th>
                        <th>Added By</th>
                        <th>Created</th>
                        <th width="50">Action</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($leads as $lead)
                    <tr data-id="{{ $lead->id }}" data-type="{{ $lead->type }}">
                        <td>
                            <input type="checkbox" class="rowCheckbox" value="{{ $lead->id }}">
                        </td>
                        <td>{{ $lead->id }}</td>

                        <td>
                            <strong>{{ $lead->contact_name }}</strong><br>
                            <small class="text-muted">{{ $lead->company_name }}</small>
                        </td>

                        <td>
                            <span class="type-badge type-{{ $lead->type }}">
                                {{ ucfirst($lead->type) }}
                            </span>
                        </td>

                        <td>{{ $lead->email }}</td>

                        <td>
                            <div class="table-avatar">
                                <img src="https://ui-avatars.com/api/?name={{ urlencode($lead->owner->name ?? 'User') }}&background=random"
                                     onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name=User&background=random'">
                                <div>
                                    {{ $lead->owner->name ?? 'N/A' }}<br>
                                    <small class="text-muted">{{ $lead->lead_owner_designation ?? '' }}</small>
                                </div>
                            </div>
                        </td>

                        <td>
                            <div class="table-avatar">
                                <img src="https://ui-avatars.com/api/?name={{ urlencode($lead->creator->name ?? 'User') }}&background=random"
                                     onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name=User&background=random'">
                                <div>
                                    {{ $lead->creator->name ?? 'N/A' }}<br>
                                    <small class="text-muted">{{ $lead->added_by_designation ?? '' }}</small>
                                </div>
                            </div>
                        </td>

                        <td>{{ $lead->created_at->format('d M Y') }}</td>

                        <td>
                            <div class="dropdown">
                                <span class="action-dot" data-bs-toggle="dropdown">⋮</span>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="{{ route('leads.contacts.show', $lead->id) }}">View</a></li>
                                    <li><a class="dropdown-item" href="{{ route('leads.contacts.edit', $lead->id) }}">Edit</a></li>
                                    @if($lead->type == 'lead')
                                    <li><a class="dropdown-item convert-client-btn" href="#" data-id="{{ $lead->id }}">Convert to Client</a></li>
                                    @else
                                    <li><a class="dropdown-item convert-lead-btn" href="#" data-id="{{ $lead->id }}">Convert to Lead</a></li>
                                    @endif
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form action="{{ route('leads.contacts.destroy', $lead->id) }}" method="POST" class="d-inline delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dropdown-item text-danger">Delete</button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center py-4">
                            No Lead Contacts Found
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- PAGINATION COMPONENT --}}
    <div class="pagination-container">
        <!-- LEFT SIDE: Show Entries -->
        <div class="left-pagination">
            <span class="show-entries-label">Show</span>
            <select id="entries-per-page" class="entries-select">
                <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                <option value="20" {{ request('per_page', 10) == 20 ? 'selected' : '' }}>20</option>
                <option value="50" {{ request('per_page', 10) == 50 ? 'selected' : '' }}>50</option>
                <option value="100" {{ request('per_page', 10) == 100 ? 'selected' : '' }}>100</option>
            </select>
            <span class="show-entries-label">entries</span>
            <div class="pagination-info" id="pagination-info">
                @if($leads->count() > 0)
                    Showing {{ $leads->firstItem() }} to {{ $leads->lastItem() }} of {{ $leads->total() }} entries
                @else
                    Showing 0 to 0 of 0 entries
                @endif
            </div>
        </div>

        <!-- RIGHT SIDE: Prev/Next Buttons -->
        <div class="right-pagination">
            {{-- Previous Button --}}
            @if($leads->onFirstPage())
                <button id="prev-btn" class="pagination-btn prev-btn" disabled>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="15 18 9 12 15 6"></polyline>
                    </svg>
                    Previous
                </button>
            @else
                @php
                    $prevUrl = $leads->previousPageUrl();
                    if (request()->has('type') && request('type') != 'all') {
                        $prevUrl .= (strpos($prevUrl, '?') === false ? '?' : '&') . 'type=' . request('type');
                    }
                    if (request()->has('per_page')) {
                        $prevUrl .= (strpos($prevUrl, '?') === false ? '?' : '&') . 'per_page=' . request('per_page');
                    }
                    if (request()->has('search')) {
                        $prevUrl .= (strpos($prevUrl, '?') === false ? '?' : '&') . 'search=' . request('search');
                    }
                @endphp
                <a href="{{ $prevUrl }}" id="prev-btn" class="pagination-btn prev-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="15 18 9 12 15 6"></polyline>
                    </svg>
                    Previous
                </a>
            @endif

            {{-- Page Numbers --}}
            <div class="page-numbers" id="page-numbers">
                @php
                    $currentPage = $leads->currentPage();
                    $lastPage = $leads->lastPage();
                    $startPage = max(1, $currentPage - 2);
                    $endPage = min($lastPage, $startPage + 4);
                    $startPage = max(1, min($startPage, $lastPage - 4));
                @endphp

                {{-- First Page --}}
                @if($startPage > 1)
                    @php
                        $firstUrl = $leads->url(1);
                        if (request()->has('type') && request('type') != 'all') {
                            $firstUrl .= (strpos($firstUrl, '?') === false ? '?' : '&') . 'type=' . request('type');
                        }
                        if (request()->has('per_page')) {
                            $firstUrl .= (strpos($firstUrl, '?') === false ? '?' : '&') . 'per_page=' . request('per_page');
                        }
                        if (request()->has('search')) {
                            $firstUrl .= (strpos($firstUrl, '?') === false ? '?' : '&') . 'search=' . request('search');
                        }
                    @endphp
                    <a href="{{ $firstUrl }}" class="page-number">
                        1
                    </a>
                    @if($startPage > 2)
                        <span class="page-dots">...</span>
                    @endif
                @endif

                {{-- Page Numbers --}}
                @for($i = $startPage; $i <= $endPage; $i++)
                    @php
                        $pageUrl = $leads->url($i);
                        if (request()->has('type') && request('type') != 'all') {
                            $pageUrl .= (strpos($pageUrl, '?') === false ? '?' : '&') . 'type=' . request('type');
                        }
                        if (request()->has('per_page')) {
                            $pageUrl .= (strpos($pageUrl, '?') === false ? '?' : '&') . 'per_page=' . request('per_page');
                        }
                        if (request()->has('search')) {
                            $pageUrl .= (strpos($pageUrl, '?') === false ? '?' : '&') . 'search=' . request('search');
                        }
                    @endphp
                    @if($i == $currentPage)
                        <span class="page-number active">{{ $i }}</span>
                    @else
                        <a href="{{ $pageUrl }}" class="page-number">
                            {{ $i }}
                        </a>
                    @endif
                @endfor

                {{-- Last Page --}}
                @if($endPage < $lastPage)
                    @if($endPage < $lastPage - 1)
                        <span class="page-dots">...</span>
                    @endif
                    @php
                        $lastUrl = $leads->url($lastPage);
                        if (request()->has('type') && request('type') != 'all') {
                            $lastUrl .= (strpos($lastUrl, '?') === false ? '?' : '&') . 'type=' . request('type');
                        }
                        if (request()->has('per_page')) {
                            $lastUrl .= (strpos($lastUrl, '?') === false ? '?' : '&') . 'per_page=' . request('per_page');
                        }
                        if (request()->has('search')) {
                            $lastUrl .= (strpos($lastUrl, '?') === false ? '?' : '&') . 'search=' . request('search');
                        }
                    @endphp
                    <a href="{{ $lastUrl }}" class="page-number">
                        {{ $lastPage }}
                    </a>
                @endif
            </div>

            {{-- Next Button --}}
            @if($leads->hasMorePages())
                @php
                    $nextUrl = $leads->nextPageUrl();
                    if (request()->has('type') && request('type') != 'all') {
                        $nextUrl .= (strpos($nextUrl, '?') === false ? '?' : '&') . 'type=' . request('type');
                    }
                    if (request()->has('per_page')) {
                        $nextUrl .= (strpos($nextUrl, '?') === false ? '?' : '&') . 'per_page=' . request('per_page');
                    }
                    if (request()->has('search')) {
                        $nextUrl .= (strpos($nextUrl, '?') === false ? '?' : '&') . 'search=' . request('search');
                    }
                @endphp
                <a href="{{ $nextUrl }}" id="next-btn" class="pagination-btn next-btn">
                    Next
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </a>
            @else
                <button id="next-btn" class="pagination-btn next-btn" disabled>
                    Next
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </button>
            @endif
        </div>
    </div>

</div>

{{-- CSRF Token for AJAX --}}
<meta name="csrf-token" content="{{ csrf_token() }}">

<script>
document.addEventListener('DOMContentLoaded', function() {

    // ========== TYPE FILTER FUNCTIONALITY ==========
    const typeFilter = document.getElementById('typeFilter');

    if (typeFilter) {
        typeFilter.addEventListener('change', function() {
            const type = this.value;
            const currentUrl = new URL(window.location.href);

            if (type === 'all') {
                currentUrl.searchParams.delete('type');
            } else {
                currentUrl.searchParams.set('type', type);
            }

            // Go to first page when changing type
            currentUrl.searchParams.set('page', 1);

            // Show loading message
            showToast(`Loading ${type === 'all' ? 'All' : type} contacts...`);

            // Redirect to new URL
            window.location.href = currentUrl.toString();
        });
    }

    // ========== ENTRIES PER PAGE FUNCTIONALITY ==========
    const entriesPerPageSelect = document.getElementById('entries-per-page');

    if (entriesPerPageSelect) {
        entriesPerPageSelect.addEventListener('change', function() {
            const perPage = this.value;
            const currentUrl = new URL(window.location.href);

            // Update or add per_page parameter
            currentUrl.searchParams.set('per_page', perPage);

            // Go to first page when changing entries per page
            currentUrl.searchParams.set('page', 1);

            // Redirect to new URL
            window.location.href = currentUrl.toString();
        });
    }

    // Toast notification function
    function showToast(message, type = 'info') {
        // Remove existing toast
        const existingToast = document.querySelector('.custom-toast');
        if (existingToast) {
            existingToast.remove();
        }

        // Create toast element
        const toast = document.createElement('div');
        toast.className = `custom-toast alert alert-${type} alert-dismissible fade show`;
        toast.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            min-width: 300px;
            max-width: 400px;
            animation: slideInRight 0.3s ease;
        `;

        toast.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;

        document.body.appendChild(toast);

        // Auto remove after 3 seconds
        setTimeout(() => {
            if (toast.parentNode) {
                toast.remove();
            }
        }, 3000);
    }

    // ========== SINGLE DELETE FUNCTIONALITY ==========
    document.querySelectorAll('.delete-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            if (confirm('Are you sure you want to delete this contact?')) {
                // Store current scroll position
                const scrollPosition = window.pageYOffset || document.documentElement.scrollTop;
                localStorage.setItem('scrollPosition', scrollPosition);

                // Submit the form
                this.submit();
            }
        });
    });

    // Restore scroll position after page load
    window.addEventListener('load', function() {
        const scrollPosition = localStorage.getItem('scrollPosition');
        if (scrollPosition) {
            window.scrollTo(0, parseInt(scrollPosition));
            localStorage.removeItem('scrollPosition');
        }
    });

    // ========== LEAD FORM BUILDER FUNCTIONALITY ==========
    const leadFormBuilderBtn = document.getElementById('leadFormBuilderBtn');
    const leadFormBuilderModal = new bootstrap.Modal(document.getElementById('leadFormBuilderModal'));
    const saveFormConfigBtn = document.getElementById('saveFormConfig');

    // Open lead form builder modal
    if (leadFormBuilderBtn) {
        leadFormBuilderBtn.addEventListener('click', function() {
            leadFormBuilderModal.show();
        });
    }

    // Toggle field visibility in preview
    document.querySelectorAll('.field-item input[type="checkbox"]').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const fieldName = this.closest('.field-item').getAttribute('data-field');
            const previewField = document.getElementById(`preview-${fieldName}`);

            if (previewField) {
                if (this.checked) {
                    previewField.classList.add('active');
                } else {
                    previewField.classList.remove('active');
                }
            }
        });
    });

    // Save form configuration
    if (saveFormConfigBtn) {
        saveFormConfigBtn.addEventListener('click', function() {
            // Collect all field configurations
            const fieldConfigs = {};
            document.querySelectorAll('.field-item').forEach(fieldItem => {
                const fieldName = fieldItem.getAttribute('data-field');
                const isEnabled = fieldItem.querySelector('input[type="checkbox"]').checked;
                fieldConfigs[fieldName] = isEnabled;
            });

            // Save to localStorage for demo
            localStorage.setItem('leadFormConfig', JSON.stringify(fieldConfigs));

            showToast('Form configuration saved successfully!', 'success');
            leadFormBuilderModal.hide();
        });
    }

    // Load saved form configuration
    const savedFormConfig = localStorage.getItem('leadFormConfig');
    if (savedFormConfig) {
        try {
            const config = JSON.parse(savedFormConfig);
            document.querySelectorAll('.field-item').forEach(fieldItem => {
                const fieldName = fieldItem.getAttribute('data-field');
                const checkbox = fieldItem.querySelector('input[type="checkbox"]');
                if (config[fieldName] !== undefined) {
                    checkbox.checked = config[fieldName];
                    // Trigger change event to update preview
                    checkbox.dispatchEvent(new Event('change'));
                }
            });
        } catch (e) {
            console.error('Error loading form config:', e);
        }
    }

    // ========== IMPORT FUNCTIONALITY ==========
    const importBtn = document.getElementById('importBtn');
    const importModal = new bootstrap.Modal(document.getElementById('importModal'));
    const fileUploadArea = document.getElementById('fileUploadArea');
    const fileInput = document.getElementById('fileInput');
    const fileName = document.getElementById('fileName');
    const importSubmitBtn = document.getElementById('importSubmitBtn');

    // Open import modal
    if (importBtn) {
        importBtn.addEventListener('click', function() {
            importModal.show();
        });
    }

    // File upload area click
    if (fileUploadArea) {
        fileUploadArea.addEventListener('click', function() {
            fileInput.click();
        });
    }

    // File input change
    if (fileInput) {
        fileInput.addEventListener('change', function() {
            if (this.files.length > 0) {
                const file = this.files[0];
                fileName.innerHTML = `<div class="alert alert-success">
                    <i class="fas fa-file"></i> ${file.name} (${(file.size / 1024).toFixed(2)} KB)
                </div>`;
                importSubmitBtn.disabled = false;
            }
        });
    }

    // Drag and drop functionality
    if (fileUploadArea) {
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            fileUploadArea.addEventListener(eventName, preventDefaults, false);
        });

        ['dragenter', 'dragover'].forEach(eventName => {
            fileUploadArea.addEventListener(eventName, highlight, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            fileUploadArea.addEventListener(eventName, unhighlight, false);
        });

        fileUploadArea.addEventListener('drop', handleDrop, false);
    }

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    function highlight() {
        if (fileUploadArea) {
            fileUploadArea.classList.add('dragover');
        }
    }

    function unhighlight() {
        if (fileUploadArea) {
            fileUploadArea.classList.remove('dragover');
        }
    }

    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;

        if (files.length > 0) {
            fileInput.files = files;
            const file = files[0];
            fileName.innerHTML = `<div class="alert alert-success">
                <i class="fas fa-file"></i> ${file.name} (${(file.size / 1024).toFixed(2)} KB)
            </div>`;
            importSubmitBtn.disabled = false;
        }
    }

    // ========== EXPORT FUNCTIONALITY ==========
    const exportAllBtn = document.getElementById('exportAllBtn');

    if (exportAllBtn) {
        exportAllBtn.addEventListener('click', function() {
            const currentUrl = new URL(window.location.href);
            const type = currentUrl.searchParams.get('type');
            const perPage = currentUrl.searchParams.get('per_page');

            let exportUrl = "{{ route('leads.contacts.export') }}";
            const params = [];

            if (type && type !== 'all') {
                params.push(`type=${type}`);
            }
            if (perPage) {
                params.push(`per_page=${perPage}`);
            }

            if (params.length > 0) {
                exportUrl += '?' + params.join('&');
            }

            window.location.href = exportUrl;
        });
    }

    // ========== BULK ACTIONS ==========
    const mainCheckbox = document.getElementById('mainCheckbox');
    const rowCheckboxes = document.querySelectorAll('.rowCheckbox');
    const bulkActions = document.querySelector('.bulk-actions');
    const bulkActionSelect = document.getElementById('bulkActionSelect');
    const applyBulkBtn = document.getElementById('applyBulk');
    const dateFilterContainer = document.getElementById('dateFilterContainer');

    function toggleConditionalSections() {
        const checkedCount = document.querySelectorAll('.rowCheckbox:checked').length;
        const isMainChecked = mainCheckbox.checked;
        const show = checkedCount > 0 || isMainChecked;
        if (bulkActions) bulkActions.style.display = show ? 'flex' : 'none';
        if (dateFilterContainer) dateFilterContainer.style.display = show && document.getElementById('enableDateFilter').checked ? 'flex' : 'none';
    }

    if (mainCheckbox) {
        mainCheckbox.addEventListener('change', function () {
            rowCheckboxes.forEach(cb => cb.checked = this.checked);
            toggleConditionalSections();
        });
    }

    rowCheckboxes.forEach(cb => {
        cb.addEventListener('change', toggleConditionalSections);
    });

    // Apply bulk action
    if (applyBulkBtn) {
        applyBulkBtn.addEventListener('click', function() {
            const selectedIds = Array.from(document.querySelectorAll('.rowCheckbox:checked')).map(cb => cb.value);

            if (selectedIds.length === 0) {
                showToast('Please select at least one contact', 'warning');
                return;
            }

            const action = bulkActionSelect.value;

            if (!action) {
                showToast('Please select an action', 'warning');
                return;
            }

            if (action === 'delete') {
                if (confirm(`Are you sure you want to delete ${selectedIds.length} selected contact(s)?`)) {
                    // Store current state before deletion
                    const currentState = {
                        search: document.getElementById('searchInput').value,
                        type: document.getElementById('typeFilter').value,
                        page: "{{ request()->get('page', 1) }}",
                        scroll: window.pageYOffset || document.documentElement.scrollTop
                    };
                    localStorage.setItem('leadsPageState', JSON.stringify(currentState));

                    // Send AJAX request for bulk delete
                    fetch("{{ route('leads.contacts.bulk.delete') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ ids: selectedIds })
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            showToast(data.message, 'success');
                            // Remove deleted rows
                            selectedIds.forEach(id => {
                                const row = document.querySelector(`tr[data-id="${id}"]`);
                                if (row) row.remove();
                            });
                            toggleConditionalSections();

                            // Reload page if no rows left
                            const remainingRows = document.querySelectorAll('#leadsTable tbody tr');
                            if (remainingRows.length === 0) {
                                window.location.reload();
                            }
                        } else {
                            showToast(data.message || 'Error deleting contacts', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showToast('Error deleting contacts. Please check console for details.', 'error');
                    });
                }
            } else if (action === 'export') {
                // Export selected contacts
                const params = new URLSearchParams();
                selectedIds.forEach(id => params.append('ids[]', id));

                const currentUrl = new URL(window.location.href);
                const type = currentUrl.searchParams.get('type');
                if (type && type !== 'all') {
                    params.append('type', type);
                }

                window.location.href = "{{ route('leads.contacts.export') }}?" + params.toString();
            }
        });
    }

    // Restore page state after bulk delete
    window.addEventListener('load', function() {
        const savedState = localStorage.getItem('leadsPageState');
        if (savedState) {
            const state = JSON.parse(savedState);
            if (state.search) {
                document.getElementById('searchInput').value = state.search;
            }
            if (state.type) {
                document.getElementById('typeFilter').value = state.type;
            }
            if (state.scroll) {
                setTimeout(() => {
                    window.scrollTo(0, state.scroll);
                }, 100);
            }
            localStorage.removeItem('leadsPageState');
        }
    });

    // ========== CONVERT TO CLIENT ==========
    document.querySelectorAll('.convert-client-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const leadId = this.getAttribute('data-id');

            if (confirm('Are you sure you want to convert this lead to a client?')) {
                fetch("{{ route('leads.contacts.convert') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ lead_id: leadId, action: 'convert_to_client' })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message, 'success');
                        // Reload the page to reflect changes
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        showToast(data.message || 'Error converting lead', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Error converting lead', 'error');
                });
            }
        });
    });

    // ========== CONVERT TO LEAD ==========
    document.querySelectorAll('.convert-lead-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const leadId = this.getAttribute('data-id');

            if (confirm('Are you sure you want to convert this client back to a lead?')) {
                fetch("{{ route('leads.contacts.convert') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ lead_id: leadId, action: 'convert_to_lead' })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message, 'success');
                        // Reload the page to reflect changes
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        showToast(data.message || 'Error converting client', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Error converting client', 'error');
                });
            }
        });
    });

    // ========== SEARCH FUNCTIONALITY ==========
    const searchInput = document.getElementById('searchInput');
    let searchTimer;

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(() => {
                const searchTerm = this.value.trim();
                const currentUrl = new URL(window.location.href);

                if (searchTerm.length > 0) {
                    currentUrl.searchParams.set('search', searchTerm);
                } else {
                    currentUrl.searchParams.delete('search');
                }

                // Go to first page when searching
                currentUrl.searchParams.set('page', 1);

                // Store search state
                localStorage.setItem('leadsSearch', searchTerm);

                // Redirect to new URL
                window.location.href = currentUrl.toString();
            }, 800);
        });

        // Restore search on page load
        const savedSearch = localStorage.getItem('leadsSearch');
        if (savedSearch) {
            searchInput.value = savedSearch;
        }
    }

    // ========== FILTER SIDEBAR FUNCTIONALITY ==========
    const openFilterBtn = document.getElementById('openFilterBtn');
    const closeFilterBtn = document.getElementById('closeFilter');
    const filterSidebar = document.getElementById('filterSidebar');
    const filterOverlay = document.getElementById('filterOverlay');
    const clearFilterBtn = document.getElementById('clearFilterBtn');
    const applyFilterBtn = document.getElementById('applyFilterBtn');

    // Open filter sidebar
    if (openFilterBtn) {
        openFilterBtn.addEventListener('click', function() {
            filterSidebar.classList.add('active');
            filterOverlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        });
    }

    // Close filter sidebar
    function closeFilterSidebar() {
        filterSidebar.classList.remove('active');
        filterOverlay.classList.remove('active');
        document.body.style.overflow = '';
    }

    if (closeFilterBtn) {
        closeFilterBtn.addEventListener('click', closeFilterSidebar);
    }

    if (filterOverlay) {
        filterOverlay.addEventListener('click', closeFilterSidebar);
    }

    // Sidebar date filter toggle
    const sidebarDateFilter = document.getElementById('sidebarDateFilter');
    const sidebarDateFields = document.getElementById('sidebarDateFields');
    const sidebarDateRange = document.getElementById('sidebarDateRange');
    const sidebarCustomDateRange = document.getElementById('sidebarCustomDateRange');

    if (sidebarDateFilter) {
        sidebarDateFilter.addEventListener('change', function() {
            sidebarDateFields.style.display = this.checked ? 'block' : 'none';
        });
    }

    if (sidebarDateRange) {
        sidebarDateRange.addEventListener('change', function() {
            sidebarCustomDateRange.style.display = this.value === 'custom' ? 'flex' : 'none';
        });
    }

    // Clear all filters
    if (clearFilterBtn) {
        clearFilterBtn.addEventListener('click', function() {
            // Reset date filter
            sidebarDateFilter.checked = false;
            sidebarDateFields.style.display = 'none';
            sidebarDateRange.value = 'today';
            sidebarCustomDateRange.style.display = 'none';
            document.getElementById('filterOn').value = 'created_at';
            document.getElementById('sidebarStartDate').value = '';
            document.getElementById('sidebarEndDate').value = '';

            // Reset lead source
            const leadSourceSelect = document.getElementById('leadSourceFilter');
            for (let i = 0; i < leadSourceSelect.options.length; i++) {
                leadSourceSelect.options[i].selected = false;
            }

            // Reset added by
            document.getElementById('addedByFilter').value = '';

            showToast('All filters have been cleared', 'info');
        });
    }

    // Apply filters
    if (applyFilterBtn) {
        applyFilterBtn.addEventListener('click', function() {
            // Collect filter data
            const filters = {
                dateFilterEnabled: sidebarDateFilter.checked,
                filterOn: document.getElementById('filterOn').value,
                dateRange: sidebarDateRange.value,
                startDate: document.getElementById('sidebarStartDate').value,
                endDate: document.getElementById('sidebarEndDate').value,
                leadSources: Array.from(document.getElementById('leadSourceFilter').selectedOptions).map(opt => opt.value),
                addedBy: document.getElementById('addedByFilter').value
            };

            // Validate custom date range
            if (filters.dateFilterEnabled && filters.dateRange === 'custom') {
                if (!filters.startDate || !filters.endDate) {
                    showToast('Please select both start and end dates for custom range', 'warning');
                    return;
                }
                if (new Date(filters.startDate) > new Date(filters.endDate)) {
                    showToast('Start date cannot be after end date', 'warning');
                    return;
                }
            }

            // Store filters in localStorage
            localStorage.setItem('leadsFilters', JSON.stringify(filters));

            showToast('Filters applied! Page will reload to apply filters.', 'success');

            // Close sidebar
            closeFilterSidebar();

            // Reload page to apply filters (in real app, you'd make AJAX call)
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        });
    }

    // Restore filters on page load
    const savedFilters = localStorage.getItem('leadsFilters');
    if (savedFilters) {
        try {
            const filters = JSON.parse(savedFilters);
            // You would restore filter values here in a real implementation
            console.log('Restored filters:', filters);
        } catch (e) {
            console.error('Error parsing saved filters:', e);
        }
    }

    // ========== EXISTING DATE FILTER FUNCTIONALITY ==========
    const enableDateFilter = document.getElementById('enableDateFilter');
    const dateRangeSelect = document.getElementById('dateRangeSelect');
    const customDateRange = document.getElementById('customDateRange');
    const applyDateBtn = document.getElementById('applyDateFilter');
    const cancelBtn = document.getElementById('cancelDateFilter');

    if (enableDateFilter) {
        enableDateFilter.addEventListener('change', toggleConditionalSections);
    }

    if (dateRangeSelect) {
        dateRangeSelect.addEventListener('change', function() {
            if(this.value === 'custom'){
                customDateRange.style.display = 'flex';
            } else {
                customDateRange.style.display = 'none';
            }
        });
    }

    if (applyDateBtn) {
        applyDateBtn.addEventListener('click', function() {
            let start, end;
            const now = new Date();
            switch(dateRangeSelect.value){
                case 'today':
                    start = end = now;
                    break;
                case 'last30':
                    end = now;
                    start = new Date();
                    start.setDate(start.getDate() - 30);
                    break;
                case 'thisMonth':
                    start = new Date(now.getFullYear(), now.getMonth(), 1);
                    end = new Date(now.getFullYear(), now.getMonth() + 1, 0);
                    break;
                case 'lastMonth':
                    const lm = new Date(now.getFullYear(), now.getMonth() - 1, 1);
                    start = lm;
                    end = new Date(lm.getFullYear(), lm.getMonth() + 1, 0);
                    break;
                case 'last90':
                    end = now;
                    start = new Date();
                    start.setDate(start.getDate() - 90);
                    break;
                case 'last6Months':
                    end = now;
                    start = new Date();
                    start.setMonth(start.getMonth() - 6);
                    break;
                case 'last1Year':
                    end = now;
                    start = new Date();
                    start.setFullYear(start.getFullYear() - 1);
                    break;
                case 'custom':
                    start = document.getElementById('startDate').value;
                    end = document.getElementById('endDate').value;
                    break;
            }
            showToast(`Date filter applied: ${dateRangeSelect.options[dateRangeSelect.selectedIndex].text}`, 'success');
            // Here you can trigger your AJAX or form submission
        });
    }

    if (cancelBtn) {
        cancelBtn.addEventListener('click', function() {
            enableDateFilter.checked = false;
            toggleConditionalSections();
            showToast('Date filter cancelled', 'info');
        });
    }

});
</script>
@endsection
