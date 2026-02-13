@extends('admin.layout.app')

@section('content')
<div class="employee-show-container">

    {{-- Back Button with Animation --}}
    <div class="back-button-wrapper animate-slideDown">
        <a href="{{ route('employees.index') }}" class="btn-back">
            <i class="fas fa-arrow-left"></i>
            <span>Back to Employee List</span>
        </a>
    </div>

    {{-- Main Profile Card with Animation --}}
    <div class="profile-card animate-fadeIn">
        <div class="profile-card-inner">

            {{-- Left Section - Profile Image & Basic Info --}}
            <div class="profile-left">
                <div class="profile-image-wrapper animate-float">
                    @if(!empty($employee->profile_image))
                        <img src="{{ asset($employee->profile_image) }}" alt="{{ $employee->name }}" class="profile-image">
                    @else
                        <div class="profile-placeholder">
                            <i class="fas fa-user"></i>
                        </div>
                    @endif
                </div>

                <div class="profile-basic-info">
                    <h1 class="employee-name">{{ $employee->name }}</h1>
                    <span class="employee-designation-badge">
                        <i class="fas fa-briefcase"></i>
                        {{ $employee->employeeDetail->designation->name ?? 'N/A' }}
                    </span>

                    @php
                        $status = $employee->employeeDetail?->status ?? 'N/A';
                    @endphp

                    <span class="status-badge {{ strtolower($status) }}">
                        <span class="status-dot"></span>
                        {{ $status }}
                    </span>
                </div>

                {{-- Quick Stats --}}
                <div class="quick-stats">
                    <div class="stat-item">
                        <div class="stat-icon">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div class="stat-info">
                            <span class="stat-label">Joined</span>
                            <span class="stat-value">{{ \Carbon\Carbon::parse($employee->employeeDetail->joining_date)->format('d M, Y') }}</span>
                        </div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-icon">
                            <i class="fas fa-id-card"></i>
                        </div>
                        <div class="stat-info">
                            <span class="stat-label">Employee ID</span>
                            <span class="stat-value">{{ $employee->employeeDetail->employee_id }}</span>
                        </div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="stat-info">
                            <span class="stat-label">Email</span>
                            <span class="stat-value">{{ $employee->email }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Section - Detailed Information --}}
            <div class="profile-right">
                <div class="section-header">
                    <div class="section-title-wrapper">
                        <div class="section-icon">
                            <i class="fas fa-user-details"></i>
                        </div>
                        <h2 class="section-title">Employee Details</h2>
                    </div>
                    <div class="section-actions">
                        <a href="{{ route('employees.edit', $employee->id) }}" class="btn-edit">
                            <i class="fas fa-edit"></i>
                            <span>Edit Profile</span>
                        </a>
                    </div>
                </div>

                @php
                    $detail = $employee->employeeDetail;
                @endphp

                <div class="details-grid">
                    <!-- Personal Information -->
                    <div class="details-card animate-slideUp" style="animation-delay: 0.1s;">
                        <div class="card-header">
                            <i class="fas fa-user-circle"></i>
                            <h3>Personal Information</h3>
                        </div>
                        <div class="card-content">
                            <div class="detail-row">
                                <span class="detail-label">Date of Birth</span>
                                <span class="detail-value">{{ $detail->dob }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Gender</span>
                                <span class="detail-value">{{ $detail->gender }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Marital Status</span>
                                <span class="detail-value">{{ ucfirst($detail->marital_status) }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Mobile Number</span>
                                <span class="detail-value">{{ $detail->mobile }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Language</span>
                                <span class="detail-value">{{ $detail->language }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Employment Information -->
                    <div class="details-card animate-slideUp" style="animation-delay: 0.2s;">
                        <div class="card-header">
                            <i class="fas fa-briefcase"></i>
                            <h3>Employment Information</h3>
                        </div>
                        <div class="card-content">
                            <div class="detail-row">
                                <span class="detail-label">Department</span>
                                <span class="detail-value">{{ $detail->department->dpt_name ?? 'N/A' }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Designation</span>
                                <span class="detail-value">{{ $detail->designation->name ?? 'N/A' }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Employment Type</span>
                                <span class="detail-value badge-type">{{ ucfirst(str_replace('_', ' ', $detail->employment_type)) }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Reporting To</span>
                                <span class="detail-value">{{ $detail->reportingTo->name ?? 'N/A' }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Hourly Rate</span>
                                <span class="detail-value">{{ $detail->hourly_rate }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Location Information -->
                    <div class="details-card animate-slideUp" style="animation-delay: 0.3s;">
                        <div class="card-header">
                            <i class="fas fa-map-marker-alt"></i>
                            <h3>Location Information</h3>
                        </div>
                        <div class="card-content">
                            <div class="detail-row">
                                <span class="detail-label">Country</span>
                                <span class="detail-value">{{ $detail->country }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Address</span>
                                <span class="detail-value">{{ $detail->address }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Business Address</span>
                                <span class="detail-value">{{ $detail->business_address }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Skills & Settings -->
                    <div class="details-card animate-slideUp" style="animation-delay: 0.4s;">
                        <div class="card-header">
                            <i class="fas fa-cog"></i>
                            <h3>Skills & Settings</h3>
                        </div>
                        <div class="card-content">
                            <div class="detail-row">
                                <span class="detail-label">Skills</span>
                                <span class="detail-value skills">
                                    @if($detail->skills)
                                        @foreach(explode(',', $detail->skills) as $skill)
                                            <span class="skill-tag">{{ trim($skill) }}</span>
                                        @endforeach
                                    @else
                                        N/A
                                    @endif
                                </span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Login Allowed</span>
                                <span class="detail-value toggle-status {{ $detail->login_allowed ? 'active' : 'inactive' }}">
                                    <i class="fas fa-{{ $detail->login_allowed ? 'check-circle' : 'times-circle' }}"></i>
                                    {{ $detail->login_allowed ? 'Yes' : 'No' }}
                                </span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Email Notifications</span>
                                <span class="detail-value toggle-status {{ $detail->email_notifications ? 'active' : 'inactive' }}">
                                    <i class="fas fa-{{ $detail->email_notifications ? 'check-circle' : 'times-circle' }}"></i>
                                    {{ $detail->email_notifications ? 'Yes' : 'No' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- LIGHT PURPLE THEME - EMPLOYEE SHOW PAGE --}}
<style>
    /* ===== LIGHT PURPLE THEME - EMPLOYEE SHOW PAGE ===== */
    /* Soft, Animated & Eye-Friendly Design */
    /* 100% Original Functionality - Only UI Enhanced */

    /* Main Container */
    .employee-show-container {
        padding: 35px 40px;
        max-width: 1400px;
        margin: 0 auto;
        background: linear-gradient(145deg, #f9f5ff 0%, #f3ebff 50%, #f1e7ff 100%);
        min-height: 100vh;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        position: relative;
        overflow-x: hidden;
    }

    /* Soft Purple Overlay Effect */
    .employee-show-container::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: radial-gradient(circle at 10% 20%, rgba(170, 140, 250, 0.08) 0%, transparent 40%),
                    radial-gradient(circle at 90% 70%, rgba(150, 120, 250, 0.08) 0%, transparent 40%),
                    radial-gradient(circle at 30% 80%, rgba(180, 150, 255, 0.06) 0%, transparent 45%);
        pointer-events: none;
    }

    /* ===== ANIMATIONS ===== */
    @keyframes slideDown {
        0% { opacity: 0; transform: translateY(-20px); }
        100% { opacity: 1; transform: translateY(0); }
    }

    @keyframes slideUp {
        0% { opacity: 0; transform: translateY(30px); }
        100% { opacity: 1; transform: translateY(0); }
    }

    @keyframes fadeIn {
        0% { opacity: 0; }
        100% { opacity: 1; }
    }

    @keyframes float {
        0% { transform: translateY(0px); }
        50% { transform: translateY(-8px); }
        100% { transform: translateY(0px); }
    }

    @keyframes pulse {
        0% { box-shadow: 0 0 0 0 rgba(139, 92, 246, 0.4); }
        70% { box-shadow: 0 0 0 12px rgba(139, 92, 246, 0); }
        100% { box-shadow: 0 0 0 0 rgba(139, 92, 246, 0); }
    }

    @keyframes shimmer {
        0% { background-position: -1000px 0; }
        100% { background-position: 1000px 0; }
    }

    .animate-slideDown {
        animation: slideDown 0.6s cubic-bezier(0.23, 1, 0.32, 1) forwards;
    }

    .animate-slideUp {
        animation: slideUp 0.6s cubic-bezier(0.23, 1, 0.32, 1) forwards;
    }

    .animate-fadeIn {
        animation: fadeIn 0.8s cubic-bezier(0.23, 1, 0.32, 1) forwards;
    }

    .animate-float {
        animation: float 4s ease-in-out infinite;
    }

    .animate-pulse {
        animation: pulse 2s infinite;
    }

    /* ===== BACK BUTTON ===== */
    .back-button-wrapper {
        margin-bottom: 25px;
    }

    .btn-back {
        display: inline-flex;
        align-items: center;
        padding: 12px 24px;
        background: white;
        border: 1px solid rgba(167, 139, 250, 0.3);
        border-radius: 100px;
        color: #6d28d9;
        font-size: 0.95rem;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s cubic-bezier(0.23, 1, 0.32, 1);
        box-shadow: 0 4px 12px rgba(139, 92, 246, 0.08);
    }

    .btn-back i {
        margin-right: 10px;
        font-size: 0.9rem;
        transition: transform 0.3s ease;
    }

    .btn-back:hover {
        background: linear-gradient(145deg, #8b5cf6, #7c3aed);
        color: white;
        border-color: transparent;
        transform: translateX(-5px);
        box-shadow: 0 8px 20px rgba(124, 58, 237, 0.25);
    }

    .btn-back:hover i {
        transform: translateX(-3px);
    }

    /* ===== PROFILE CARD ===== */
    .profile-card {
        background: white;
        border-radius: 32px;
        border: 1px solid rgba(167, 139, 250, 0.2);
        box-shadow: 0 20px 40px rgba(139, 92, 246, 0.08);
        overflow: hidden;
        transition: all 0.4s cubic-bezier(0.23, 1, 0.32, 1);
        position: relative;
        z-index: 1;
    }

    .profile-card:hover {
        box-shadow: 0 30px 60px rgba(139, 92, 246, 0.12);
        border-color: rgba(167, 139, 250, 0.3);
    }

    .profile-card-inner {
        display: flex;
        flex-wrap: wrap;
        min-height: 100%;
    }

    /* ===== LEFT SECTION - PROFILE ===== */
    .profile-left {
        width: 30%;
        background: linear-gradient(145deg, #faf7ff, #f5f0ff);
        padding: 40px 30px;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        border-right: 1px solid rgba(167, 139, 250, 0.15);
        position: relative;
        overflow: hidden;
    }

    .profile-left::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 6px;
        background: linear-gradient(90deg, #8b5cf6, #c084fc, #a78bfa);
    }

    .profile-image-wrapper {
        width: 160px;
        height: 160px;
        border-radius: 50%;
        padding: 5px;
        background: linear-gradient(145deg, #c084fc, #a78bfa);
        box-shadow: 0 15px 35px rgba(167, 139, 250, 0.3);
        margin-bottom: 25px;
        transition: all 0.4s cubic-bezier(0.23, 1, 0.32, 1);
    }

    .profile-image {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid white;
        transition: transform 0.6s cubic-bezier(0.23, 1, 0.32, 1);
    }

    .profile-image-wrapper:hover .profile-image {
        transform: scale(1.05);
    }

    .profile-placeholder {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        background: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 3.5rem;
        color: #8b5cf6;
        border: 4px solid white;
    }

    .profile-basic-info {
        margin-bottom: 30px;
        width: 100%;
    }

    .employee-name {
        font-size: 1.8rem;
        font-weight: 700;
        color: #2d1b4e;
        margin-bottom: 10px;
        letter-spacing: -0.02em;
    }

    .employee-designation-badge {
        display: inline-flex;
        align-items: center;
        padding: 8px 20px;
        background: #ede9fe;
        border-radius: 100px;
        color: #6d28d9;
        font-size: 0.95rem;
        font-weight: 600;
        margin-bottom: 15px;
    }

    .employee-designation-badge i {
        margin-right: 8px;
        font-size: 0.9rem;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 8px 20px;
        border-radius: 100px;
        font-size: 0.9rem;
        font-weight: 600;
    }

    .status-badge.active {
        background: #d1fae5;
        color: #065f46;
        border: 1px solid #a7f3d0;
    }

    .status-badge.inactive {
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid #fecaca;
    }

    .status-badge.na {
        background: #f3f4f6;
        color: #4b5563;
        border: 1px solid #e5e7eb;
    }

    .status-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        margin-right: 8px;
    }

    .status-badge.active .status-dot {
        background: #10b981;
        box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2);
    }

    .status-badge.inactive .status-dot {
        background: #ef4444;
        box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.2);
    }

    /* Quick Stats */
    .quick-stats {
        width: 100%;
        background: white;
        border-radius: 20px;
        padding: 20px;
        margin-top: 20px;
        border: 1px solid rgba(167, 139, 250, 0.2);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.02);
    }

    .stat-item {
        display: flex;
        align-items: center;
        padding: 12px 0;
        border-bottom: 1px solid rgba(167, 139, 250, 0.1);
    }

    .stat-item:last-child {
        border-bottom: none;
    }

    .stat-icon {
        width: 40px;
        height: 40px;
        background: #f5f0ff;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #8b5cf6;
        margin-right: 12px;
    }

    .stat-info {
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .stat-label {
        font-size: 0.75rem;
        font-weight: 600;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 2px;
    }

    .stat-value {
        font-size: 0.95rem;
        font-weight: 600;
        color: #1f2937;
        word-break: break-word;
    }

    /* ===== RIGHT SECTION - DETAILS ===== */
    .profile-right {
        width: 70%;
        padding: 40px 35px;
        background: white;
    }

    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
    }

    .section-title-wrapper {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .section-icon {
        width: 48px;
        height: 48px;
        background: linear-gradient(145deg, #f5f0ff, #ede9fe);
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #8b5cf6;
        font-size: 1.3rem;
    }

    .section-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #2d1b4e;
        margin: 0;
        letter-spacing: -0.02em;
    }

    .btn-edit {
        display: inline-flex;
        align-items: center;
        padding: 10px 20px;
        background: linear-gradient(145deg, #8b5cf6, #7c3aed);
        border-radius: 12px;
        color: white;
        font-size: 0.9rem;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s cubic-bezier(0.23, 1, 0.32, 1);
        box-shadow: 0 8px 18px rgba(124, 58, 237, 0.25);
    }

    .btn-edit i {
        margin-right: 8px;
    }

    .btn-edit:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 25px rgba(124, 58, 237, 0.35);
    }

    /* Details Grid */
    .details-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 25px;
    }

    .details-card {
        background: #faf9ff;
        border-radius: 20px;
        border: 1px solid rgba(167, 139, 250, 0.15);
        overflow: hidden;
        transition: all 0.3s cubic-bezier(0.23, 1, 0.32, 1);
    }

    .details-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(139, 92, 246, 0.1);
        border-color: rgba(167, 139, 250, 0.3);
    }

    .card-header {
        padding: 18px 20px;
        background: white;
        border-bottom: 1px solid rgba(167, 139, 250, 0.15);
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .card-header i {
        font-size: 1.1rem;
        color: #8b5cf6;
    }

    .card-header h3 {
        font-size: 1rem;
        font-weight: 700;
        color: #2d1b4e;
        margin: 0;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .card-content {
        padding: 20px;
    }

    .detail-row {
        display: flex;
        margin-bottom: 15px;
        padding-bottom: 15px;
        border-bottom: 1px solid rgba(167, 139, 250, 0.1);
    }

    .detail-row:last-child {
        margin-bottom: 0;
        padding-bottom: 0;
        border-bottom: none;
    }

    .detail-label {
        width: 40%;
        font-size: 0.85rem;
        font-weight: 600;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .detail-value {
        width: 60%;
        font-size: 0.95rem;
        font-weight: 500;
        color: #1f2937;
        word-break: break-word;
    }

    .badge-type {
        display: inline-block;
        padding: 4px 12px;
        background: #ede9fe;
        border-radius: 100px;
        color: #6d28d9;
        font-size: 0.85rem;
        font-weight: 600;
    }

    .skill-tag {
        display: inline-block;
        padding: 4px 12px;
        background: #e0e7ff;
        border-radius: 100px;
        color: #4f46e5;
        font-size: 0.8rem;
        font-weight: 600;
        margin: 0 4px 4px 0;
    }

    .toggle-status {
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .toggle-status.active {
        color: #059669;
    }

    .toggle-status.inactive {
        color: #b91c1c;
    }

    .toggle-status i {
        font-size: 1rem;
    }

    /* ===== RESPONSIVE DESIGN ===== */
    @media (max-width: 1200px) {
        .employee-show-container {
            padding: 25px 30px;
        }

        .profile-left {
            width: 35%;
        }

        .profile-right {
            width: 65%;
        }

        .details-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 992px) {
        .profile-card-inner {
            flex-direction: column;
        }

        .profile-left {
            width: 100%;
            border-right: none;
            border-bottom: 1px solid rgba(167, 139, 250, 0.15);
        }

        .profile-right {
            width: 100%;
        }

        .profile-image-wrapper {
            width: 140px;
            height: 140px;
        }

        .employee-name {
            font-size: 1.6rem;
        }
    }

    @media (max-width: 768px) {
        .employee-show-container {
            padding: 20px;
        }

        .section-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 15px;
        }

        .details-grid {
            grid-template-columns: 1fr;
        }

        .detail-row {
            flex-direction: column;
        }

        .detail-label {
            width: 100%;
            margin-bottom: 5px;
        }

        .detail-value {
            width: 100%;
        }

        .quick-stats {
            margin-top: 10px;
        }
    }

    @media (max-width: 576px) {
        .employee-show-container {
            padding: 15px;
        }

        .profile-left {
            padding: 30px 20px;
        }

        .profile-right {
            padding: 30px 20px;
        }

        .profile-image-wrapper {
            width: 120px;
            height: 120px;
        }

        .employee-name {
            font-size: 1.4rem;
        }

        .btn-back {
            width: 100%;
            justify-content: center;
        }
    }

    /* ===== CUSTOM SCROLLBAR ===== */
    ::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }

    ::-webkit-scrollbar-track {
        background: #f5f0ff;
        border-radius: 10px;
    }

    ::-webkit-scrollbar-thumb {
        background: #d4c2ff;
        border-radius: 10px;
    }

    ::-webkit-scrollbar-thumb:hover {
        background: #b79aff;
    }

    /* ===== PRINT STYLES ===== */
    @media print {
        .employee-show-container {
            background: white;
            padding: 20px;
        }

        .btn-back,
        .btn-edit,
        .quick-stats {
            display: none !important;
        }

        .profile-card {
            box-shadow: none;
            border: 1px solid #ddd;
        }

        .profile-left {
            background: white;
        }
    }
</style>

<!-- Font Awesome -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

{{-- Add custom class to body for better styling --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.body.classList.add('employee-show-body');
    });
</script>

<style>
    /* Global body style for this page only */
    .employee-show-body {
        background: linear-gradient(145deg, #f9f5ff 0%, #f3ebff 100%);
    }
</style>

@endsection
