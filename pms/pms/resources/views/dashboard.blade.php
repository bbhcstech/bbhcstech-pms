@extends('admin.layout.app')

@section('title', 'Admin Dashboard')

@section('content')

<style>
    /* ===== CSS Variables & Base Styles ===== */
    :root {
        --primary: #7C3AED;
        --primary-light: #8B5CF6;
        --primary-dark: #6D28D9;
        --secondary: #F59E0B;
        --success: #10B981;
        --danger: #EF4444;
        --warning: #F59E0B;
        --info: #3B82F6;
        --dark: #1F2937;
        --light: #F9FAFB;
        --gray: #6B7280;
        --gray-light: #E5E7EB;

        --gradient-primary: linear-gradient(135deg, #7C3AED 0%, #8B5CF6 100%);
        --gradient-success: linear-gradient(135deg, #10B981 0%, #34D399 100%);
        --gradient-warning: linear-gradient(135deg, #F59E0B 0%, #FBBF24 100%);
        --gradient-danger: linear-gradient(135deg, #EF4444 0%, #F87171 100%);
        --gradient-info: linear-gradient(135deg, #3B82F6 0%, #60A5FA 100%);

        --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.07);
        --shadow-md: 0 4px 20px rgba(0, 0, 0, 0.08);
        --shadow-lg: 0 10px 40px rgba(0, 0, 0, 0.12);
        --shadow-xl: 0 20px 60px rgba(0, 0, 0, 0.15);

        --radius-sm: 10px;
        --radius-md: 16px;
        --radius-lg: 24px;

        --transition-fast: 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        --transition-base: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        --transition-slow: 0.5s cubic-bezier(0.4, 0, 0.2, 1);
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 15%, #f7fafc 100%);
        min-height: 100vh;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        background-attachment: fixed;
    }

    /* ===== Animations ===== */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes slideInLeft {
        from {
            opacity: 0;
            transform: translateX(-30px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    @keyframes slideInRight {
        from {
            opacity: 0;
            transform: translateX(30px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    @keyframes float {
        0%, 100% {
            transform: translateY(0) rotate(0deg);
        }
        33% {
            transform: translateY(-10px) rotate(2deg);
        }
        66% {
            transform: translateY(5px) rotate(-2deg);
        }
    }

    @keyframes pulse {
        0%, 100% {
            transform: scale(1);
            opacity: 1;
        }
        50% {
            transform: scale(1.05);
            opacity: 0.8;
        }
    }

    @keyframes shimmer {
        0% {
            background-position: -200% center;
        }
        100% {
            background-position: 200% center;
        }
    }

    @keyframes progressFill {
        from {
            width: 0;
        }
    }

    @keyframes bounceIn {
        0% {
            opacity: 0;
            transform: scale(0.3) translateY(20px);
        }
        50% {
            opacity: 0.9;
            transform: scale(1.05);
        }
        80% {
            opacity: 1;
            transform: scale(0.95);
        }
        100% {
            opacity: 1;
            transform: scale(1);
        }
    }

    @keyframes gradientShift {
        0%, 100% {
            background-position: 0% 50%;
        }
        50% {
            background-position: 100% 50%;
        }
    }

    /* ===== Main Container ===== */
    #main {
        min-height: 100vh;
        position: relative;
        overflow-x: hidden;
    }

    .content-wrapper {
        padding: 2rem 2.5rem;
        max-width: 100%;
        position: relative;
        z-index: 1;
        animation: fadeInUp 0.8s ease-out;
    }

    /* ===== Floating Background Elements ===== */
    .floating-elements {
        position: fixed;
        width: 100%;
        height: 100%;
        pointer-events: none;
        z-index: 0;
    }

    .floating-element {
        position: absolute;
        border-radius: 50%;
        opacity: 0.3;
        filter: blur(40px);
        animation: float 20s infinite ease-in-out;
    }

    .floating-element:nth-child(1) {
        width: 400px;
        height: 400px;
        background: var(--primary);
        top: 10%;
        left: 5%;
        animation-delay: 0s;
    }

    .floating-element:nth-child(2) {
        width: 300px;
        height: 300px;
        background: var(--success);
        bottom: 20%;
        right: 10%;
        animation-delay: 5s;
    }

    .floating-element:nth-child(3) {
        width: 200px;
        height: 200px;
        background: var(--warning);
        top: 40%;
        right: 20%;
        animation-delay: 10s;
    }

    /* ===== Dashboard Tabs ===== */
    #dashboardTabs {
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: var(--radius-lg);
        padding: 0.5rem;
        margin-bottom: 2.5rem;
        box-shadow: var(--shadow-lg);
        position: relative;
        overflow: hidden;
        animation: slideInLeft 0.6s ease-out;
    }

    #dashboardTabs::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, var(--primary), var(--secondary), var(--success), var(--info));
        background-size: 400% 400%;
        animation: gradientShift 8s ease infinite;
    }

    .nav-pills .nav-link {
        padding: 1.125rem 1.5rem;
        border-radius: var(--radius-sm);
        color: var(--dark);
        font-weight: 600;
        transition: all var(--transition-base);
        position: relative;
        overflow: hidden;
        z-index: 1;
        border: 2px solid transparent;
        margin: 0 0.25rem;
    }

    .nav-pills .nav-link::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
        transition: left 0.7s ease;
        z-index: -1;
    }

    .nav-pills .nav-link:hover::before {
        left: 100%;
    }

    .nav-pills .nav-link:hover {
        transform: translateY(-3px);
        box-shadow: var(--shadow-md);
        background: rgba(124, 58, 237, 0.05);
        border-color: var(--primary-light);
    }

    .nav-pills .nav-link.active {
        background: var(--gradient-primary) !important;
        color: white !important;
        box-shadow: 0 10px 30px rgba(124, 58, 237, 0.3);
        transform: translateY(-2px);
        animation: pulse 3s infinite;
        border: 2px solid white;
    }

    .nav-pills .nav-link i {
        font-size: 1.25rem;
        margin-right: 0.75rem;
        transition: transform var(--transition-base);
    }

    .nav-pills .nav-link:hover i {
        transform: scale(1.2) rotate(10deg);
    }

    /* ===== Welcome Card ===== */
    .welcome-section {
        margin-bottom: 2.5rem;
        animation: fadeInUp 0.8s ease-out 0.2s both;
    }

    .welcome-card {
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.9) 0%, rgba(255, 255, 255, 0.7) 100%);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: var(--radius-lg);
        overflow: hidden;
        position: relative;
        box-shadow: var(--shadow-xl);
        transition: all var(--transition-base);
    }

    .welcome-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-xl), 0 30px 60px rgba(124, 58, 237, 0.1);
    }

    .welcome-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 5px;
        background: var(--gradient-primary);
        background-size: 400% 400%;
        animation: gradientShift 6s ease infinite;
    }

    .welcome-content {
        padding: 2.5rem;
    }

    .welcome-title {
        font-size: 2.25rem;
        font-weight: 800;
        background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        margin-bottom: 1rem;
        line-height: 1.2;
    }

    .welcome-text {
        font-size: 1.125rem;
        color: var(--gray);
        line-height: 1.6;
        max-width: 600px;
        margin-bottom: 1.5rem;
    }

    .welcome-badges {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .welcome-badge {
        padding: 0.5rem 1.25rem;
        border-radius: 50px;
        font-weight: 600;
        font-size: 0.875rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        transition: all var(--transition-fast);
    }

    .welcome-badge:nth-child(1) {
        background: rgba(124, 58, 237, 0.1);
        color: var(--primary);
        border: 2px solid rgba(124, 58, 237, 0.2);
    }

    .welcome-badge:nth-child(2) {
        background: rgba(16, 185, 129, 0.1);
        color: var(--success);
        border: 2px solid rgba(16, 185, 129, 0.2);
    }

    .welcome-badge:nth-child(3) {
        background: rgba(245, 158, 11, 0.1);
        color: var(--warning);
        border: 2px solid rgba(245, 158, 11, 0.2);
    }

    .welcome-badge:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-sm);
    }

    .welcome-illustration {
        padding: 2rem;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
    }

    .welcome-illustration::before {
        content: '';
        position: absolute;
        width: 300px;
        height: 300px;
        background: radial-gradient(circle, rgba(124, 58, 237, 0.1) 0%, rgba(124, 58, 237, 0) 70%);
        border-radius: 50%;
        animation: pulse 4s infinite;
    }

    .welcome-illustration img {
        animation: float 6s infinite ease-in-out;
        filter: drop-shadow(0 20px 40px rgba(0, 0, 0, 0.15));
        max-height: 220px;
        position: relative;
        z-index: 1;
    }

    /* ===== Statistics Cards ===== */
    .stats-section {
        margin-bottom: 2.5rem;
        animation: fadeInUp 0.8s ease-out 0.3s both;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
    }

    .stat-card {
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.9) 0%, rgba(255, 255, 255, 0.7) 100%);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.4);
        border-radius: var(--radius-lg);
        padding: 1.75rem;
        position: relative;
        overflow: hidden;
        transition: all var(--transition-base);
        box-shadow: var(--shadow-md);
    }

    .stat-card:hover {
        transform: translateY(-8px) scale(1.02);
        box-shadow: var(--shadow-xl);
        border-color: rgba(124, 58, 237, 0.3);
    }

    .stat-card:nth-child(1):hover { border-color: rgba(124, 58, 237, 0.3); }
    .stat-card:nth-child(2):hover { border-color: rgba(16, 185, 129, 0.3); }
    .stat-card:nth-child(3):hover { border-color: rgba(245, 158, 11, 0.3); }
    .stat-card:nth-child(4):hover { border-color: rgba(239, 68, 68, 0.3); }

    .stat-card::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: var(--gradient-primary);
        opacity: 0;
        transition: opacity var(--transition-base);
    }

    .stat-card:hover::after {
        opacity: 1;
    }

    .stat-card:nth-child(1) .stat-icon { background: var(--gradient-primary); }
    .stat-card:nth-child(2) .stat-icon { background: var(--gradient-success); }
    .stat-card:nth-child(3) .stat-icon { background: var(--gradient-warning); }
    .stat-card:nth-child(4) .stat-icon { background: var(--gradient-danger); }

    .stat-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1.5rem;
    }

    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: var(--radius-md);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        font-size: 1.5rem;
        position: relative;
        overflow: hidden;
    }

    .stat-icon::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.3), transparent);
        transform: rotate(45deg);
        transition: all var(--transition-base);
    }

    .stat-card:hover .stat-icon::before {
        left: 100%;
    }

    .stat-dropdown .btn {
        color: var(--gray);
        transition: all var(--transition-fast);
    }

    .stat-dropdown .btn:hover {
        color: var(--primary);
        transform: rotate(90deg);
    }

    .stat-title {
        font-size: 0.875rem;
        color: var(--gray);
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }

    .stat-title a {
        color: inherit;
        text-decoration: none;
        transition: color var(--transition-fast);
    }

    .stat-title a:hover {
        color: var(--primary);
    }

    .stat-value {
        font-size: 2.5rem;
        font-weight: 800;
        color: var(--dark);
        line-height: 1;
        margin-bottom: 0.75rem;
        background: linear-gradient(135deg, var(--dark) 0%, var(--gray) 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .stat-progress {
        margin-top: 1rem;
    }

    .progress-container {
        height: 6px;
        background: rgba(0, 0, 0, 0.05);
        border-radius: 3px;
        overflow: hidden;
        margin-bottom: 0.5rem;
    }

    .progress-bar {
        height: 100%;
        border-radius: 3px;
        animation: progressFill 1.5s ease-out;
        position: relative;
        overflow: hidden;
    }

    .stat-card:nth-child(1) .progress-bar { background: var(--gradient-primary); }
    .stat-card:nth-child(2) .progress-bar { background: var(--gradient-success); }
    .stat-card:nth-child(3) .progress-bar { background: var(--gradient-warning); }
    .stat-card:nth-child(4) .progress-bar { background: var(--gradient-danger); }

    .progress-bar::after {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
        animation: shimmer 2s infinite;
    }

    .stat-trend {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.875rem;
        font-weight: 600;
    }

    .stat-trend.positive {
        color: var(--success);
    }

    /* ===== Content Cards Section ===== */
    .content-section {
        animation: fadeInUp 0.8s ease-out 0.4s both;
    }

    .content-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
        gap: 1.5rem;
    }

    .content-card {
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(255, 255, 255, 0.85) 100%);
        backdrop-filter: blur(15px);
        -webkit-backdrop-filter: blur(15px);
        border: 1px solid rgba(255, 255, 255, 0.4);
        border-radius: var(--radius-lg);
        overflow: hidden;
        transition: all var(--transition-base);
        box-shadow: var(--shadow-md);
    }

    .content-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-xl);
        border-color: rgba(124, 58, 237, 0.2);
    }

    .content-card:nth-child(1) { animation: slideInRight 0.6s ease-out 0.1s both; }
    .content-card:nth-child(2) { animation: slideInRight 0.6s ease-out 0.2s both; }
    .content-card:nth-child(3) { animation: slideInRight 0.6s ease-out 0.3s both; }

    .card-header {
        padding: 1.5rem 1.75rem;
        background: rgba(124, 58, 237, 0.05);
        border-bottom: 1px solid rgba(124, 58, 237, 0.1);
        display: flex;
        align-items: center;
        justify-content: space-between;
        position: relative;
        overflow: hidden;
    }

    .card-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 5px;
        height: 100%;
        background: var(--gradient-primary);
    }

    .card-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--dark);
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .card-title i {
        color: var(--primary);
        font-size: 1.5rem;
    }

    .card-action {
        padding: 0.5rem 1.25rem;
        background: var(--gradient-primary);
        color: white;
        border-radius: var(--radius-sm);
        text-decoration: none;
        font-weight: 600;
        font-size: 0.875rem;
        transition: all var(--transition-base);
        box-shadow: 0 4px 15px rgba(124, 58, 237, 0.2);
    }

    .card-action:hover {
        transform: translateX(5px) scale(1.05);
        box-shadow: 0 6px 25px rgba(124, 58, 237, 0.3);
    }

    .card-body {
        padding: 1.75rem;
        max-height: 400px;
        overflow-y: auto;
    }

    .card-body::-webkit-scrollbar {
        width: 6px;
    }

    .card-body::-webkit-scrollbar-track {
        background: rgba(0, 0, 0, 0.05);
        border-radius: 3px;
    }

    .card-body::-webkit-scrollbar-thumb {
        background: var(--gradient-primary);
        border-radius: 3px;
    }

    /* ===== List Items ===== */
    .list-item {
        padding: 1.25rem;
        border-bottom: 1px solid rgba(124, 58, 237, 0.1);
        transition: all var(--transition-base);
        border-radius: var(--radius-sm);
        margin-bottom: 0.75rem;
        position: relative;
        overflow: hidden;
    }

    .list-item:hover {
        background: rgba(124, 58, 237, 0.03);
        transform: translateX(8px);
        box-shadow: var(--shadow-sm);
    }

    .list-item::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 3px;
        background: var(--gradient-primary);
        opacity: 0;
        transition: opacity var(--transition-base);
    }

    .list-item:hover::before {
        opacity: 1;
    }

    .list-item:last-child {
        border-bottom: none;
        margin-bottom: 0;
    }

    .list-item-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        margin-bottom: 0.75rem;
    }

    .list-item-title {
        font-weight: 700;
        color: var(--dark);
        font-size: 1rem;
        margin-bottom: 0.5rem;
        transition: color var(--transition-fast);
    }

    .list-item:hover .list-item-title {
        color: var(--primary);
    }

    .list-item-meta {
        font-size: 0.875rem;
        color: var(--gray);
        display: flex;
        align-items: center;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .list-item-meta span {
        display: flex;
        align-items: center;
        gap: 0.375rem;
    }

    .badge {
        padding: 0.375rem 0.875rem;
        border-radius: 50px;
        font-weight: 700;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        white-space: nowrap;
        transition: all var(--transition-fast);
    }

    .badge-high {
        background: rgba(239, 68, 68, 0.1);
        color: var(--danger);
        border: 2px solid rgba(239, 68, 68, 0.2);
    }

    .badge-medium {
        background: rgba(245, 158, 11, 0.1);
        color: var(--warning);
        border: 2px solid rgba(245, 158, 11, 0.2);
    }

    .badge-low {
        background: rgba(59, 130, 246, 0.1);
        color: var(--info);
        border: 2px solid rgba(59, 130, 246, 0.2);
    }

    .badge:hover {
        transform: scale(1.05);
    }

    /* ===== Timeline ===== */
    .timeline {
        position: relative;
        padding-left: 1.75rem;
    }

    .timeline::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 3px;
        background: linear-gradient(to bottom, var(--primary), var(--secondary), var(--success));
        border-radius: 1.5px;
    }

    .timeline-item {
        position: relative;
        padding-bottom: 1.5rem;
        padding-left: 2rem;
    }

    .timeline-item:last-child {
        padding-bottom: 0;
    }

    .timeline-item::before {
        content: '';
        position: absolute;
        left: -1.75rem;
        top: 0.375rem;
        width: 14px;
        height: 14px;
        border-radius: 50%;
        background: var(--primary);
        border: 3px solid white;
        box-shadow: 0 0 0 4px var(--primary-light);
        animation: pulse 2s infinite;
    }

    .timeline-item:nth-child(2)::before { background: var(--secondary); box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.3); }
    .timeline-item:nth-child(3)::before { background: var(--success); box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.3); }

    .timeline-content {
        background: rgba(255, 255, 255, 0.5);
        padding: 1rem 1.25rem;
        border-radius: var(--radius-sm);
        border: 1px solid rgba(124, 58, 237, 0.1);
        transition: all var(--transition-fast);
    }

    .timeline-content:hover {
        transform: translateX(5px);
        background: white;
        box-shadow: var(--shadow-sm);
    }

    .timeline-title {
        font-weight: 600;
        color: var(--dark);
        margin-bottom: 0.375rem;
    }

    .timeline-project {
        font-weight: 700;
        color: var(--primary);
        margin-bottom: 0.25rem;
    }

    .timeline-time {
        font-size: 0.875rem;
        color: var(--gray);
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-top: 0.5rem;
    }

    /* ===== Empty States ===== */
    .empty-state {
        text-align: center;
        padding: 3rem 1.5rem;
        color: var(--gray);
        position: relative;
        overflow: hidden;
    }

    .empty-state::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 200px;
        height: 200px;
        background: radial-gradient(circle, rgba(124, 58, 237, 0.05) 0%, rgba(124, 58, 237, 0) 70%);
        border-radius: 50%;
    }

    .empty-state i {
        font-size: 3.5rem;
        margin-bottom: 1.25rem;
        opacity: 0.3;
        position: relative;
        z-index: 1;
        animation: float 4s infinite ease-in-out;
    }

    .empty-state p {
        font-size: 1rem;
        margin: 0;
        position: relative;
        z-index: 1;
    }

    /* ===== Responsive Design ===== */
    @media (max-width: 1200px) {
        .content-wrapper {
            padding: 1.5rem;
        }

        .content-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 992px) {
        .welcome-section .row {
            flex-direction: column;
        }

        .welcome-illustration {
            padding-top: 0;
        }

        .welcome-illustration img {
            max-height: 180px;
        }

        .nav-pills .nav-link {
            padding: 1rem;
            font-size: 0.875rem;
        }

        .nav-pills .nav-link i {
            font-size: 1.125rem;
            margin-right: 0.5rem;
        }
    }

    @media (max-width: 768px) {
        .content-wrapper {
            padding: 1.25rem;
        }

        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }

        .content-grid {
            grid-template-columns: 1fr;
            gap: 1.25rem;
        }

        .welcome-title {
            font-size: 1.75rem;
        }

        .welcome-content {
            padding: 1.75rem;
        }

        .stat-card {
            padding: 1.5rem;
        }

        .stat-value {
            font-size: 2rem;
        }
    }

    @media (max-width: 576px) {
        .content-wrapper {
            padding: 1rem;
        }

        .stats-grid {
            grid-template-columns: 1fr;
        }

        #dashboardTabs {
            padding: 0.375rem;
        }

        .nav-pills .nav-link {
            padding: 0.875rem 0.5rem;
            font-size: 0.8125rem;
        }

        .nav-pills .nav-link i {
            font-size: 1rem;
            margin-right: 0.375rem;
        }

        .welcome-badges {
            flex-direction: column;
            gap: 0.75rem;
        }

        .card-header {
            flex-direction: column;
            gap: 1rem;
            align-items: flex-start;
        }

        .card-action {
            align-self: flex-start;
        }
    }

    /* ===== Loading Animation ===== */
    .loading-shimmer {
        background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
        background-size: 400% 100%;
        animation: shimmer 1.5s infinite linear;
        border-radius: var(--radius-sm);
    }
</style>

<!-- Floating Background Elements -->
<div class="floating-elements">
    <div class="floating-element"></div>
    <div class="floating-element"></div>
    <div class="floating-element"></div>
</div>

<main id="main" class="main">

    <div class="content-wrapper">

        <!-- Dashboard Tabs -->
        <ul class="nav nav-pills nav-fill" id="dashboardTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <a class="nav-link fw-bold py-3 {{ request('tab') === 'project' ? 'active' : '' }}"
                   href="{{ route('dashboard', ['tab' => 'project']) }}">
                   <i class="bx bx-pie-chart-alt-2"></i> Overview
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link fw-bold py-3 {{ Route::currentRouteName() === 'dashproject' ? 'active' : '' }}"
                   href="{{ route('dashproject') }}">
                   <i class="bx bx-briefcase"></i> Projects
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link fw-bold py-3 {{ Route::currentRouteName() === 'dashboard.client' ? 'active' : '' }}"
                   href="{{ route('dashboard.client') }}">
                   <i class="bx bx-user"></i> Clients
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link fw-bold py-3 {{ Route::currentRouteName() === 'hr.dashboard' ? 'active' : '' }}"
                   href="{{ route('hr.dashboard') }}">
                   <i class="bx bx-group"></i> HR
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link fw-bold py-3 {{ Route::currentRouteName() === 'dashboard.ticket' ? 'active' : '' }}"
                   href="{{ route('dashboard.ticket') }}">
                   <i class="bx bx-support"></i> Tickets
                </a>
            </li>
        </ul>

        <!-- Welcome Section -->
        <div class="welcome-section">
            <div class="welcome-card">
                <div class="row g-0">
                    <div class="col-lg-7">
                        <div class="welcome-content">
                            <h1 class="welcome-title">Welcome to PMS Dashboard</h1>
                            <p class="welcome-text">Manage your projects, team, and clients efficiently with our comprehensive dashboard. Track progress, monitor performance, and make data-driven decisions.</p>
                            <div class="welcome-badges">
                                <div class="welcome-badge">
                                    <i class="bx bx-trending-up"></i>
                                    Real-time Analytics
                                </div>
                                <div class="welcome-badge">
                                    <i class="bx bx-shield-quarter"></i>
                                    Secure & Reliable
                                </div>
                                <div class="welcome-badge">
                                    <i class="bx bx-rocket"></i>
                                    Performance Boost
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <div class="welcome-illustration">
                            <img src="{{ asset('admin/assets/img/illustrations/man-with-laptop.png')}}" class="img-fluid" alt="Dashboard Illustration"/>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Section -->
        <div class="stats-section">
            <div class="stats-grid">
                <!-- Total Employees -->
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <i class="bx bx-group"></i>
                        </div>
                        <div class="stat-dropdown">
                            <button class="btn p-0" type="button" data-bs-toggle="dropdown">
                                <i class="bx bx-dots-vertical-rounded"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a class="dropdown-item" href="{{ route('employees.index') }}">View All</a>
                                <a class="dropdown-item" href="#">Export Report</a>
                            </div>
                        </div>
                    </div>
                    <p class="stat-title"><a href="{{ route('employees.index') }}">Total Employees</a></p>
                    <div class="stat-value">{{ $totalEmployees ?? 0 }}</div>
                    <div class="stat-trend positive">
                        <i class="bx bx-up-arrow-alt"></i>
                        <span>All Active</span>
                    </div>
                    <div class="stat-progress">
                        <div class="progress-container">
                            <div class="progress-bar" style="width: 100%"></div>
                        </div>
                    </div>
                </div>

                <!-- Total Attendance -->
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <i class="bx bx-calendar-check"></i>
                        </div>
                        <div class="stat-dropdown">
                            <button class="btn p-0" type="button" data-bs-toggle="dropdown">
                                <i class="bx bx-dots-vertical-rounded"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a class="dropdown-item" href="{{ route('attendance.report') }}">View Report</a>
                                <a class="dropdown-item" href="#">Daily Logs</a>
                            </div>
                        </div>
                    </div>
                    <p class="stat-title"><a href="{{ route('attendance.report') }}">Today's Attendance</a></p>
                    <div class="stat-value">{{ $presentCount ?? 0 }}/{{ $totalEmployees ?? 0 }}</div>
                    <div class="stat-trend positive">
                        <i class="bx bx-up-arrow-alt"></i>
                        <span>{{ round(($presentCount/$totalEmployees)*100) ?? 0 }}% Present</span>
                    </div>
                    <div class="stat-progress">
                        <div class="progress-container">
                            <div class="progress-bar" style="width: {{ ($presentCount/$totalEmployees)*100 ?? 0 }}%"></div>
                        </div>
                    </div>
                </div>

                <!-- Total Clients -->
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <i class="bx bx-user-circle"></i>
                        </div>
                        <div class="stat-dropdown">
                            <button class="btn p-0" type="button" data-bs-toggle="dropdown">
                                <i class="bx bx-dots-vertical-rounded"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a class="dropdown-item" href="{{ route('clients.index') }}">View All</a>
                                <a class="dropdown-item" href="#">Add New</a>
                            </div>
                        </div>
                    </div>
                    <p class="stat-title"><a href="{{ route('clients.index') }}">Active Clients</a></p>
                    <div class="stat-value">{{ $totalClient ?? 0 }}</div>
                    <div class="stat-trend positive">
                        <i class="bx bx-up-arrow-alt"></i>
                        <span>All Engaged</span>
                    </div>
                    <div class="stat-progress">
                        <div class="progress-container">
                            <div class="progress-bar" style="width: 100%"></div>
                        </div>
                    </div>
                </div>

                <!-- Total Projects -->
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <i class="bx bx-briefcase-alt"></i>
                        </div>
                        <div class="stat-dropdown">
                            <button class="btn p-0" type="button" data-bs-toggle="dropdown">
                                <i class="bx bx-dots-vertical-rounded"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a class="dropdown-item" href="{{ route('projects.index') }}">View All</a>
                                <a class="dropdown-item" href="#">Create New</a>
                            </div>
                        </div>
                    </div>
                    <p class="stat-title"><a href="{{ route('projects.index') }}">Active Projects</a></p>
                    <div class="stat-value">{{ $totalProject ?? 0 }}</div>
                    <div class="stat-trend positive">
                        <i class="bx bx-up-arrow-alt"></i>
                        <span>All Running</span>
                    </div>
                    <div class="stat-progress">
                        <div class="progress-container">
                            <div class="progress-bar" style="width: 100%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content Section -->
        <div class="content-section">
            <div class="content-grid">
                <!-- Open Tickets -->
                <div class="content-card">
                    <div class="card-header">
                        <div class="card-title">
                            <i class="bx bx-support"></i>
                            Open Tickets
                        </div>
                        <a href="{{ route('tickets.index', ['status' => 'open']) }}" class="card-action">
                            View All <i class="bx bx-chevron-right"></i>
                        </a>
                    </div>
                    <div class="card-body">
                        @forelse($openTickets as $ticket)
                            <div class="list-item">
                                <div class="list-item-header">
                                    <div>
                                        <h6 class="list-item-title">{{ $ticket->subject ?? 'No Subject' }}</h6>
                                        <div class="list-item-meta">
                                            <span><i class="bx bx-user"></i> {{ $ticket->requester_name ?? 'Unknown' }}</span>
                                            <span><i class="bx bx-folder"></i> {{ $ticket->project?->name ?? 'No Project' }}</span>
                                        </div>
                                    </div>
                                    <span class="badge badge-{{ strtolower($ticket->priority ?? 'low') }}">
                                        {{ ucfirst($ticket->priority ?? 'Low') }}
                                    </span>
                                </div>
                                <div class="list-item-meta">
                                    <span><i class="bx bx-calendar"></i> {{ \Carbon\Carbon::parse($ticket->created_at)->format('d M, Y') }}</span>
                                    <span><i class="bx bx-time"></i> {{ \Carbon\Carbon::parse($ticket->created_at)->format('h:i A') }}</span>
                                </div>
                            </div>
                        @empty
                            <div class="empty-state">
                                <i class="bx bx-message-square-check"></i>
                                <p>All tickets are resolved! 🎉</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Pending Tasks -->
                <div class="content-card">
                    <div class="card-header">
                        <div class="card-title">
                            <i class="bx bx-task"></i>
                            Pending Tasks
                        </div>
                        <a href="{{ route('tasks.index', ['exclude_completed' => true]) }}" class="card-action">
                            View All <i class="bx bx-chevron-right"></i>
                        </a>
                    </div>
                    <div class="card-body">
                        @forelse($pendingTasksTotal as $task)
                            <div class="list-item">
                                <div class="list-item-header">
                                    <div>
                                        <h6 class="list-item-title">{{ $task->title ?? 'N/A' }}</h6>
                                        <div class="list-item-meta">
                                            <span><i class="bx bx-folder"></i> {{ $task->project->name ?? 'N/A' }}</span>
                                            <span><i class="bx bx-calendar"></i> {{ \Carbon\Carbon::parse($task->start_date)->format('d M') }}</span>
                                        </div>
                                    </div>
                                    <span class="badge badge-low">
                                        {{ $task->status ?? 'Pending' }}
                                    </span>
                                </div>
                            </div>
                        @empty
                            <div class="empty-state">
                                <i class="bx bx-check-circle"></i>
                                <p>No pending tasks! Great work! 🚀</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Project Activities -->
                <div class="content-card">
                    <div class="card-header">
                        <div class="card-title">
                            <i class="bx bx-time-five"></i>
                            Recent Activities
                        </div>
                        <a href="#" class="card-action">
                            View All <i class="bx bx-chevron-right"></i>
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            @forelse($activities as $activity)
                                <div class="timeline-item">
                                    <div class="timeline-content">
                                        <div class="timeline-title">{{ $activity->activity ?? 'No activity' }}</div>
                                        <div class="timeline-project">{{ $activity->project_name ?? 'N/A' }}</div>
                                        <div class="timeline-time">
                                            <i class="bx bx-time"></i>
                                            {{ \Carbon\Carbon::parse($activity->created_at)->format('h:i A • d M') }}
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="empty-state">
                                    <i class="bx bx-time"></i>
                                    <p>No recent activities</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

</main>

<script>
    // Initialize animations and interactions
    document.addEventListener('DOMContentLoaded', function() {
        // Animate numbers in stat cards
        const statValues = document.querySelectorAll('.stat-value');
        statValues.forEach(value => {
            const originalText = value.textContent;
            const isFraction = originalText.includes('/');

            if (isFraction) {
                const [numerator, denominator] = originalText.split('/');
                animateFraction(value, parseInt(numerator), parseInt(denominator));
            } else {
                animateNumber(value, parseInt(originalText.replace(/\D/g, '')) || 0);
            }
        });

        function animateNumber(element, target) {
            let current = 0;
            const increment = target / 30;
            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    element.textContent = target.toLocaleString();
                    clearInterval(timer);
                } else {
                    element.textContent = Math.floor(current).toLocaleString();
                }
            }, 40);
        }

        function animateFraction(element, numerator, denominator) {
            let currentNum = 0;
            let currentDen = 0;
            const incrementNum = numerator / 20;
            const incrementDen = denominator / 20;

            const timer = setInterval(() => {
                currentNum += incrementNum;
                currentDen += incrementDen;

                if (currentNum >= numerator && currentDen >= denominator) {
                    element.textContent = `${numerator}/${denominator}`;
                    clearInterval(timer);
                } else {
                    element.textContent = `${Math.floor(currentNum)}/${Math.floor(currentDen)}`;
                }
            }, 50);
        }

        // Add hover effects to cards
        const cards = document.querySelectorAll('.stat-card, .content-card');
        cards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.zIndex = '10';
            });

            card.addEventListener('mouseleave', function() {
                this.style.zIndex = '1';
            });
        });

        // Add click ripple effect to tabs
        const tabs = document.querySelectorAll('.nav-link');
        tabs.forEach(tab => {
            tab.addEventListener('click', function(e) {
                // Remove active class from all tabs
                tabs.forEach(t => t.classList.remove('active'));
                // Add active class to clicked tab
                this.classList.add('active');

                // Create ripple effect
                const rect = this.getBoundingClientRect();
                const ripple = document.createElement('span');
                const size = Math.max(rect.width, rect.height);
                const x = e.clientX - rect.left - size / 2;
                const y = e.clientY - rect.top - size / 2;

                ripple.style.cssText = `
                    position: absolute;
                    border-radius: 50%;
                    background: rgba(255, 255, 255, 0.6);
                    transform: scale(0);
                    animation: ripple 0.6s linear;
                    width: ${size}px;
                    height: ${size}px;
                    top: ${y}px;
                    left: ${x}px;
                    pointer-events: none;
                    z-index: 0;
                `;

                this.appendChild(ripple);
                setTimeout(() => ripple.remove(), 600);
            });
        });

        // Add ripple animation style
        const style = document.createElement('style');
        style.textContent = `
            @keyframes ripple {
                to {
                    transform: scale(4);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);

        // Add parallax effect to floating elements
        document.addEventListener('mousemove', function(e) {
            const x = (e.clientX / window.innerWidth - 0.5) * 20;
            const y = (e.clientY / window.innerHeight - 0.5) * 20;

            const elements = document.querySelectorAll('.floating-element');
            elements.forEach((element, index) => {
                const speed = 0.5 + (index * 0.2);
                element.style.transform = `translate(${x * speed}px, ${y * speed}px)`;
            });
        });

        // Add intersection observer for scroll animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        // Observe all animated elements
        document.querySelectorAll('.stat-card, .content-card').forEach(card => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            observer.observe(card);
        });

        // Update active tab based on current route
        function updateActiveTab() {
            const currentPath = window.location.pathname;
            tabs.forEach(tab => {
                const href = tab.getAttribute('href');
                if (href && currentPath.includes(href.split('?')[0])) {
                    tabs.forEach(t => t.classList.remove('active'));
                    tab.classList.add('active');
                }
            });
        }

        updateActiveTab();
    });

    // Add keyboard navigation
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
                menu.classList.remove('show');
            });
        }
    });
</script>

@endsection
