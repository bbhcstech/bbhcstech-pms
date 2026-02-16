<!-- Premium Navbar -->
<nav class="bbh-navbar navbar navbar-expand-lg" id="bbhNavbar">
    <div class="container">
        <!-- Logo -->
        <a class="navbar-brand" href="{{ url('/') }}">
            <div class="brand-logo">
                <span class="brand-icon">
                    <i class="fas fa-cube"></i>
                </span>
                <span class="brand-text">BBH<span class="text-purple">PMS</span></span>
            </div>
        </a>

        <!-- Mobile Toggle -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar">
            <div class="hamburger">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </button>

        <!-- Navbar Items -->
        <div class="collapse navbar-collapse" id="mainNavbar">
            <ul class="navbar-nav mx-auto">
                <!-- Product Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        Product <i class="fas fa-chevron-down ms-1"></i>
                    </a>
                    <div class="dropdown-menu mega-menu">
                        <div class="mega-menu-grid">
                            <div class="mega-menu-col">
                                <h6 class="dropdown-header">Project Management</h6>
                                <a class="dropdown-item" href="{{ route('product.tasks') }}">
                                    <i class="fas fa-tasks"></i>
                                    <div>
                                        <span>Task Management</span>
                                        <small>Organize and track tasks</small>
                                    </div>
                                </a>
                                <a class="dropdown-item" href="{{ route('product.gantt') }}">
                                    <i class="fas fa-chart-line"></i>
                                    <div>
                                        <span>Gantt Charts</span>
                                        <small>Visual project timelines</small>
                                    </div>
                                </a>
                                <a class="dropdown-item" href="{{ route('product.kanban') }}">
                                    <i class="fas fa-columns"></i>
                                    <div>
                                        <span>Kanban Boards</span>
                                        <small>Agile workflow</small>
                                    </div>
                                </a>
                            </div>
                            <div class="mega-menu-col">
                                <h6 class="dropdown-header">HR Management</h6>
                                <a class="dropdown-item" href="{{ route('product.attendance') }}">
                                    <i class="fas fa-clock"></i>
                                    <div>
                                        <span>Attendance</span>
                                        <small>Track employee hours</small>
                                    </div>
                                </a>
                                <a class="dropdown-item" href="{{ route('product.leave') }}">
                                    <i class="fas fa-calendar-alt"></i>
                                    <div>
                                        <span>Leave Management</span>
                                        <small>Manage time off</small>
                                    </div>
                                </a>
                                <a class="dropdown-item" href="{{ route('product.performance') }}">
                                    <i class="fas fa-chart-bar"></i>
                                    <div>
                                        <span>Performance</span>
                                        <small>Employee reviews</small>
                                    </div>
                                </a>
                            </div>
                            <div class="mega-menu-col">
                                <h6 class="dropdown-header">Analytics</h6>
                                <a class="dropdown-item" href="{{ route('product.reports') }}">
                                    <i class="fas fa-file-alt"></i>
                                    <div>
                                        <span>Reports</span>
                                        <small>Custom reporting</small>
                                    </div>
                                </a>
                                <a class="dropdown-item" href="{{ route('product.dashboard') }}">
                                    <i class="fas fa-tachometer-alt"></i>
                                    <div>
                                        <span>Dashboard</span>
                                        <small>Real-time insights</small>
                                    </div>
                                </a>
                                <a class="dropdown-item" href="{{ route('product.analytics') }}">
                                    <i class="fas fa-chart-pie"></i>
                                    <div>
                                        <span>Analytics</span>
                                        <small>Data visualization</small>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </li>

                <!-- Solutions Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        Solutions <i class="fas fa-chevron-down ms-1"></i>
                    </a>
                    <div class="dropdown-menu">
                        <a class="dropdown-item" href="{{ route('solutions.enterprise') }}">
                            <i class="fas fa-building"></i> For Enterprises
                        </a>
                        <a class="dropdown-item" href="{{ route('solutions.startups') }}">
                            <i class="fas fa-rocket"></i> For Startups
                        </a>
                        <a class="dropdown-item" href="{{ route('solutions.hr') }}">
                            <i class="fas fa-users"></i> For HR Teams
                        </a>
                        <a class="dropdown-item" href="{{ route('solutions.developers') }}">
                            <i class="fas fa-code"></i> For Developers
                        </a>
                        <a class="dropdown-item" href="{{ route('solutions.remote') }}">
                            <i class="fas fa-home"></i> For Remote Teams
                        </a>
                    </div>
                </li>

                <!-- Features -->
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('features') }}">Features</a>
                </li>

                <!-- Pricing -->
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('pricing') }}">Pricing</a>
                </li>

                <!-- Resources Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        Resources <i class="fas fa-chevron-down ms-1"></i>
                    </a>
                    <div class="dropdown-menu">
                        <a class="dropdown-item" href="{{ route('resources.blog') }}">
                            <i class="fas fa-blog"></i> Blog
                        </a>
                        <a class="dropdown-item" href="{{ route('resources.docs') }}">
                            <i class="fas fa-book"></i> Documentation
                        </a>
                        <a class="dropdown-item" href="{{ route('resources.api') }}">
                            <i class="fas fa-code-branch"></i> API
                        </a>
                        <a class="dropdown-item" href="{{ route('resources.help') }}">
                            <i class="fas fa-question-circle"></i> Help Center
                        </a>
                        <a class="dropdown-item" href="{{ route('resources.faq') }}">
                            <i class="fas fa-comments"></i> FAQ
                        </a>
                    </div>
                </li>

                <!-- Company Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        Company <i class="fas fa-chevron-down ms-1"></i>
                    </a>
                    <div class="dropdown-menu">
                        <a class="dropdown-item" href="{{ route('company.about') }}">
                            <i class="fas fa-info-circle"></i> About Us
                        </a>
                        <a class="dropdown-item" href="{{ route('company.careers') }}">
                            <i class="fas fa-briefcase"></i> Careers
                        </a>
                        <a class="dropdown-item" href="{{ route('company.contact') }}">
                            <i class="fas fa-envelope"></i> Contact
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="{{ route('company.privacy') }}">
                            <i class="fas fa-shield-alt"></i> Privacy Policy
                        </a>
                        <a class="dropdown-item" href="{{ route('company.terms') }}">
                            <i class="fas fa-file-contract"></i> Terms
                        </a>
                    </div>
                </li>
            </ul>

            <!-- Right Side Buttons -->
            <div class="navbar-actions">
                @auth
                    <div class="dropdown">
                        <button class="btn btn-outline-purple dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle"></i> {{ Auth::user()->name }}
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ route('dashboard') }}">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a></li>
                            <li><a class="dropdown-item" href="{{ route('profile.edit') }}">
                                <i class="fas fa-user-edit"></i> Profile
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="fas fa-sign-out-alt"></i> Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="btn btn-outline-purple">
                        <i class="fas fa-lock"></i> Login
                    </a>
                    <a href="{{ route('register') }}" class="btn btn-purple btn-glow">
                        Get Started <i class="fas fa-arrow-right"></i>
                    </a>
                @endauth
            </div>
        </div>
    </div>
</nav>
