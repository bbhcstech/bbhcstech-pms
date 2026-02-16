(function($) {
    "use strict";

    // Initialize AOS
    AOS.init({
        duration: 1000,
        once: true,
        offset: 100,
        easing: 'ease-in-out'
    });

    // Preloader
    $(window).on('load', function() {
        $('#preloader').fadeOut('slow');
    });

    // Sticky Navbar
    $(window).scroll(function() {
        if ($(this).scrollTop() > 100) {
            $('#bbhNavbar').addClass('sticky');
            $('#backToTop').addClass('show');
        } else {
            $('#bbhNavbar').removeClass('sticky');
            $('#backToTop').removeClass('show');
        }
    });

    // Back to Top
    $('#backToTop').click(function() {
        $('html, body').animate({
            scrollTop: 0
        }, 1000, 'easeInOutExpo');
        return false;
    });

    // Counter Up
    $('.stat-number').counterUp({
        delay: 10,
        time: 2000
    });

    // Testimonials Carousel
    $('.testimonial-carousel').owlCarousel({
        loop: true,
        margin: 30,
        nav: false,
        dots: true,
        autoplay: true,
        autoplayTimeout: 5000,
        autoplayHoverPause: true,
        smartSpeed: 1000,
        responsive: {
            0: {
                items: 1
            },
            768: {
                items: 2
            },
            992: {
                items: 3
            }
        }
    });

    // Portfolio Isotope
    var $portfolioContainer = $('.portfolio-container');
    if ($portfolioContainer.length) {
        var $portfolioIsotope = $portfolioContainer.isotope({
            itemSelector: '.portfolio-item',
            layoutMode: 'fitRows'
        });

        $('#portfolio-flters li').on('click', function() {
            $('#portfolio-flters li').removeClass('active');
            $(this).addClass('active');

            $portfolioIsotope.isotope({
                filter: $(this).data('filter')
            });
        });
    }

    // Magnific Popup for Portfolio
    $('.portfolio-overlay .btn').magnificPopup({
        type: 'image',
        gallery: {
            enabled: true
        },
        zoom: {
            enabled: true,
            duration: 300
        }
    });

    // Smooth Scroll for Anchor Links
    $('a[href*="#"]').on('click', function(e) {
        if ($(this.hash).length && $(this.hash).offset().top) {
            e.preventDefault();
            $('html, body').animate({
                scrollTop: $(this.hash).offset().top - 100
            }, 1000, 'easeInOutExpo');
        }
    });

    // Navbar Mobile Menu Close on Click
    $('.navbar-nav .nav-link').on('click', function() {
        if ($('.navbar-collapse').hasClass('show')) {
            $('.navbar-toggler').click();
        }
    });

    // Add Active Class to Current Nav Item
    var currentLocation = window.location.pathname;
    $('.navbar-nav .nav-link').each(function() {
        var $this = $(this);
        if ($this.attr('href') === currentLocation) {
            $this.addClass('active');
        }
    });

    // Newsletter Form Submit
    $('.newsletter-form').on('submit', function(e) {
        e.preventDefault();
        var email = $(this).find('input[type="email"]').val();
        if (email) {
            // Show success message
            alert('Thank you for subscribing to our newsletter!');
            $(this).find('input[type="email"]').val('');
        }
    });

    // Scroll Reveal Animation
    function revealOnScroll() {
        var windowHeight = $(window).height();
        var scrollTop = $(window).scrollTop();

        $('.wow').each(function() {
            var elementOffset = $(this).offset().top;
            var elementHeight = $(this).outerHeight();

            if (elementOffset < scrollTop + windowHeight - 100) {
                $(this).addClass('animated');
            }
        });
    }

    $(window).on('scroll', revealOnScroll);
    revealOnScroll();

    // Hover Effects
    $('.service-item, .feature-card, .team-card').hover(
        function() {
            $(this).find('i').addClass('fa-beat');
        },
        function() {
            $(this).find('i').removeClass('fa-beat');
        }
    );

    // Dropdown Hover for Desktop
    if ($(window).width() > 991) {
        $('.dropdown').hover(
            function() {
                $(this).find('.dropdown-menu').stop(true, true).delay(100).fadeIn(300);
            },
            function() {
                $(this).find('.dropdown-menu').stop(true, true).delay(100).fadeOut(300);
            }
        );
    }

    // Parallax Effect
    $(window).scroll(function() {
        var scroll = $(window).scrollTop();
        $('.hero-section').css('background-position-y', scroll * 0.5 + 'px');
    });

    // Typed Effect for Hero Title (Optional)
    // You can uncomment this if you want typing animation
    /*
    var typed = new Typed('.typed-text', {
        strings: ['Projects', 'Tasks', 'Teams', 'Success'],
        typeSpeed: 100,
        backSpeed: 60,
        loop: true
    });
    */

})(jQuery);
