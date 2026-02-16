@extends('frontend.layouts-frontend.app')

@section('title', 'BBH PMS - Professional Project Management System')

@section('content')
<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6" data-aos="fade-right">
                <div class="hero-badge">
                    <i class="fas fa-rocket"></i>
                    <span>#1 Project Management Solution</span>
                </div>
                <h1 class="hero-title">
                    All in one <span class="gradient-text">PMS tool</span> to grow your business rapidly
                </h1>
                <p class="hero-description">
                    Streamline your projects, manage tasks efficiently, and boost team productivity with BBH PMS.
                    The complete solution for modern businesses.
                </p>
                <div class="hero-buttons">
                    <a href="{{ route('register') }}" class="btn btn-purple btn-glow btn-lg">
                        Get Started Free <i class="fas fa-arrow-right"></i>
                    </a>
                    <a href="#" class="btn btn-outline-purple btn-lg">
                        <i class="fas fa-play-circle"></i> Watch Demo
                    </a>
                </div>
                <div class="hero-stats">
                    <div class="stat-item">
                        <span class="stat-number counter">10000</span>
                        <span class="stat-label">Active Users</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number counter">500</span>
                        <span class="stat-label">Companies</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number counter">98</span>
                        <span class="stat-label">Satisfaction</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-6" data-aos="fade-left" data-aos-delay="200">
                <div class="hero-image-wrapper">
                    <img src="{{ asset('frontend/img/hero-dashboard.png') }}" alt="BBH PMS Dashboard" class="hero-image">
                    <div class="floating-card card-1">
                        <i class="fas fa-check-circle"></i>
                        <span>Tasks Completed: 124</span>
                    </div>
                    <div class="floating-card card-2">
                        <i class="fas fa-users"></i>
                        <span>Team: 12 Members</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="container-xxl py-5">
    <div class="container px-lg-5">
        <div class="section-title" data-aos="fade-up">
            <span class="section-subtitle">Why Choose Us</span>
            <h2>Powerful Features for Modern Teams</h2>
            <p>Everything you need to manage projects, teams, and tasks in one place</p>
        </div>
        <div class="row g-4">
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
                <div class="feature-card">
                    <div class="card-icon">
                        <i class="fas fa-tasks"></i>
                    </div>
                    <h3>Project Management</h3>
                    <p>Organize projects, set milestones, and track progress with intuitive tools.</p>
                    <a href="#" class="card-link">Learn More <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
                <div class="feature-card">
                    <div class="card-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3>Team Collaboration</h3>
                    <p>Work together seamlessly with real-time updates and communication tools.</p>
                    <a href="#" class="card-link">Learn More <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="300">
                <div class="feature-card">
                    <div class="card-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3>Advanced Analytics</h3>
                    <p>Get insights into performance with detailed reports and analytics.</p>
                    <a href="#" class="card-link">Learn More <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="400">
                <div class="feature-card">
                    <div class="card-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h3>Time Tracking</h3>
                    <p>Track time spent on tasks and projects for better productivity insights.</p>
                    <a href="#" class="card-link">Learn More <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="500">
                <div class="feature-card">
                    <div class="card-icon">
                        <i class="fas fa-file-invoice"></i>
                    </div>
                    <h3>Resource Management</h3>
                    <p>Allocate and manage resources efficiently across all projects.</p>
                    <a href="#" class="card-link">Learn More <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="600">
                <div class="feature-card">
                    <div class="card-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3>Enterprise Security</h3>
                    <p>Bank-level security to protect your sensitive project data.</p>
                    <a href="#" class="card-link">Learn More <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Services Section -->
<section class="container-xxl py-5 bg-light">
    <div class="container px-lg-5">
        <div class="section-title" data-aos="fade-up">
            <span class="section-subtitle">Our Services</span>
            <h2>Comprehensive Solutions</h2>
            <p>Tailored project management solutions for every business need</p>
        </div>
        <div class="row g-4">
            <div class="col-lg-4 col-md-6" data-aos="zoom-in" data-aos-delay="100">
                <div class="service-item">
                    <div class="service-icon">
                        <i class="fas fa-tasks"></i>
                    </div>
                    <h3>PMS Optimization</h3>
                    <p>Streamline your project management workflow with our expert solutions.</p>
                    <a href="#" class="card-link">Read More <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
            <div class="col-lg-4 col-md-6" data-aos="zoom-in" data-aos-delay="200">
                <div class="service-item">
                    <div class="service-icon">
                        <i class="fas fa-code"></i>
                    </div>
                    <h3>Custom Development</h3>
                    <p>Tailored solutions integrated with your existing systems.</p>
                    <a href="#" class="card-link">Read More <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
            <div class="col-lg-4 col-md-6" data-aos="zoom-in" data-aos-delay="300">
                <div class="service-item">
                    <div class="service-icon">
                        <i class="fas fa-chart-pie"></i>
                    </div>
                    <h3>Data Analytics</h3>
                    <p>Transform your project data into actionable insights.</p>
                    <a href="#" class="card-link">Read More <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
            <div class="col-lg-4 col-md-6" data-aos="zoom-in" data-aos-delay="400">
                <div class="service-item">
                    <div class="service-icon">
                        <i class="fas fa-mobile-alt"></i>
                    </div>
                    <h3>Mobile Access</h3>
                    <p>Manage projects on the go with our mobile-optimized platform.</p>
                    <a href="#" class="card-link">Read More <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
            <div class="col-lg-4 col-md-6" data-aos="zoom-in" data-aos-delay="500">
                <div class="service-item">
                    <div class="service-icon">
                        <i class="fas fa-cloud"></i>
                    </div>
                    <h3>Cloud Integration</h3>
                    <p>Secure cloud storage with real-time synchronization.</p>
                    <a href="#" class="card-link">Read More <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
            <div class="col-lg-4 col-md-6" data-aos="zoom-in" data-aos-delay="600">
                <div class="service-item">
                    <div class="service-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h3>24/7 Support</h3>
                    <p>Round-the-clock customer support for your peace of mind.</p>
                    <a href="#" class="card-link">Read More <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Portfolio/Projects Section -->
<section class="container-xxl py-5">
    <div class="container px-lg-5">
        <div class="section-title" data-aos="fade-up">
            <span class="section-subtitle">Our Projects</span>
            <h2>Recently Launched Projects</h2>
            <p>Check out some of our successful project implementations</p>
        </div>
        <div class="row mt-n2" data-aos="fade-up" data-aos-delay="100">
            <div class="col-12 text-center">
                <ul class="list-inline mb-5" id="portfolio-flters">
                    <li class="active" data-filter="*">All</li>
                    <li data-filter=".design">Design</li>
                    <li data-filter=".development">Development</li>
                    <li data-filter=".marketing">Marketing</li>
                </ul>
            </div>
        </div>
        <div class="row g-4 portfolio-container">
            @for($i = 1; $i <= 6; $i++)
                <div class="col-lg-4 col-md-6 portfolio-item {{ $i % 2 == 0 ? 'design' : 'development' }}" data-aos="zoom-in" data-aos-delay="{{ 100 + ($i * 50) }}">
                    <div class="portfolio-item">
                        <img src="{{ asset('frontend/img/portfolio-' . $i . '.jpg') }}" alt="Project {{ $i }}">
                        <div class="portfolio-overlay">
                            <h4>Project Name {{ $i }}</h4>
                            <p>{{ $i % 2 == 0 ? 'Design' : 'Development' }}</p>
                            <a href="{{ asset('frontend/img/portfolio-' . $i . '.jpg') }}" class="btn">
                                <i class="fas fa-plus"></i>
                            </a>
                        </div>
                    </div>
                </div>
            @endfor
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section class="testimonial-section">
    <div class="container px-lg-5">
        <div class="section-title" data-aos="fade-up">
            <span class="section-subtitle" style="background: rgba(255,255,255,0.2); color: white;">Testimonials</span>
            <h2 class="text-white">What Our Clients Say</h2>
            <p class="text-white-50">Don't just take our word for it - hear from our satisfied customers</p>
        </div>
        <div class="owl-carousel testimonial-carousel" data-aos="fade-up" data-aos-delay="100">
            @for($i = 1; $i <= 4; $i++)
                <div class="testimonial-card">
                    <i class="fas fa-quote-left"></i>
                    <p>"BBH PMS has transformed how we manage projects. The intuitive interface and powerful features have increased our team productivity by 40%."</p>
                        <div class="testimonial-author">
                            <div class="avatar">
                                <img src="{{ asset('frontend/img/testimonial-' . $i . '.jpg') }}" alt="Client {{ $i }}">
                            </div>
                            <div class="author-info">

                            <h5>John Doe {{ $i }}</h5>
                            <span>CEO, Company {{ $i }}</span>
                        </div>
                    </div>
                </div>
            @endfor
        </div>
    </div>
</section>

<!-- Team Section -->
<section class="container-xxl py-5">
    <div class="container px-lg-5">
        <div class="section-title" data-aos="fade-up">
            <span class="section-subtitle">Our Team</span>
            <h2>Meet Our Team Members</h2>
            <p>The dedicated professionals behind BBH PMS</p>
        </div>
        <div class="row g-4">
            @php
                $team = [
                    ['name' => 'John Doe', 'position' => 'CEO & Founder', 'image' => 'team-1.jpg'],
                    ['name' => 'Emma William', 'position' => 'Project Manager', 'image' => 'team-2.jpg'],
                    ['name' => 'Noah Michael', 'position' => 'Lead Designer', 'image' => 'team-3.jpg'],
                    ['name' => 'Olivia Smith', 'position' => 'Senior Developer', 'image' => 'team-4.jpg']
                ];
            @endphp

            @foreach($team as $index => $member)
                <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="{{ 100 + ($index * 100) }}">
                    <div class="team-card">
                        <div class="team-image">
                            <img src="{{ asset('frontend/img/' . $member['image']) }}" alt="{{ $member['name'] }}">
                            <div class="team-social">
                                <a href="#"><i class="fab fa-facebook-f"></i></a>
                                <a href="#"><i class="fab fa-twitter"></i></a>
                                <a href="#"><i class="fab fa-linkedin-in"></i></a>
                                <a href="#"><i class="fab fa-instagram"></i></a>
                            </div>
                        </div>
                        <div class="team-info">
                            <h4>{{ $member['name'] }}</h4>
                            <p>{{ $member['position'] }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Newsletter Section -->
<section class="newsletter-section">
    <div class="container">
        <div class="newsletter-content" data-aos="fade-up">
            <h2>Ready to Get Started?</h2>
            <p>Join thousands of companies already using BBH PMS to streamline their project management.</p>
            <form class="newsletter-form">
                <input type="email" placeholder="Enter your work email" required>
                <button type="submit">
                    Get Started <i class="fas fa-arrow-right"></i>
                </button>
            </form>
            <p class="mt-3 small text-white-50">14-day free trial. No credit card required.</p>
        </div>
    </div>
</section>
@endsection
