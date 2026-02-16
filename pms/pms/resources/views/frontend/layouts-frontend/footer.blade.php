<!-- Footer -->
<footer class="bbh-footer">
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-4 col-md-6">
                <div class="footer-widget">
                    <h4>About BBH PMS</h4>
                    <p class="text-white-50 mb-4">The complete project management solution for modern businesses. Streamline your workflow, boost productivity, and achieve more with our comprehensive platform.</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
            </div>

            <div class="col-lg-2 col-md-6">
                <div class="footer-widget">
                    <h4>Quick Links</h4>
                    <ul class="footer-links">
                        <li><a href="{{ route('company.about') }}"><i class="fas fa-chevron-right"></i> About Us</a></li>
                        <li><a href="{{ route('features') }}"><i class="fas fa-chevron-right"></i> Features</a></li>
                        <li><a href="{{ route('pricing') }}"><i class="fas fa-chevron-right"></i> Pricing</a></li>
                        <li><a href="{{ route('resources.blog') }}"><i class="fas fa-chevron-right"></i> Blog</a></li>
                        <li><a href="{{ route('company.contact') }}"><i class="fas fa-chevron-right"></i> Contact</a></li>
                    </ul>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="footer-widget">
                    <h4>Support</h4>
                    <ul class="footer-links">
                        <li><a href="{{ route('resources.help') }}"><i class="fas fa-chevron-right"></i> Help Center</a></li>
                        <li><a href="{{ route('resources.docs') }}"><i class="fas fa-chevron-right"></i> Documentation</a></li>
                        <li><a href="{{ route('resources.api') }}"><i class="fas fa-chevron-right"></i> API Reference</a></li>
                        <li><a href="{{ route('resources.faq') }}"><i class="fas fa-chevron-right"></i> FAQ</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right"></i> Status</a></li>
                    </ul>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="footer-widget">
                    <h4>Contact Info</h4>
                    <div class="footer-contact">
                        <p><i class="fas fa-map-marker-alt"></i> 123 Business Avenue, New York, NY 10001</p>
                        <p><i class="fas fa-phone-alt"></i> +1 (555) 123-4567</p>
                        <p><i class="fas fa-envelope"></i> info@bbhpms.com</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="copyright">
                        &copy; {{ date('Y') }} <a href="#">BBH PMS</a>. All Rights Reserved.
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="footer-menu">
                        <a href="{{ route('company.privacy') }}">Privacy Policy</a>
                        <a href="{{ route('company.terms') }}">Terms of Service</a>
                        <a href="#">Cookie Policy</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>
