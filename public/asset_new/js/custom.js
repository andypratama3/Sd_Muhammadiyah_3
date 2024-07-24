(function ($) {

    "use strict";

    function loader() {
        $(window).on("load", function () {
            $("#js-preloader").addClass("loaded");
            $("#backgorund-loading").css("display" ,"none");
            $("#js-preloader").delay(2000).fadeOut();
        });
    }

    // Handle window resizing
    $(window).on('resize', function() {
        var width = $(window).width();
        if (width > 767 && $(window).width() < 767 || width < 767 && $(window).width() > 767) {
            location.reload();
        }else{
            document.querySelector("ul>li").classList.remove("display-none");
        }
    });

    // Initialize Isotope
    const elem = document.querySelector('.event_box');
    const filtersElem = document.querySelector('.event_filter');
    if (elem) {
        const rdn_events_list = new Isotope(elem, {
            itemSelector: '.event_outer',
            layoutMode: 'masonry'
        });
        if (filtersElem) {
            filtersElem.addEventListener('click', function(event) {
                if (!event.target.matches('a')) {
                    return;
                }
                const filterValue = event.target.getAttribute('data-filter');
                rdn_events_list.arrange({
                    filter: filterValue
                });
                filtersElem.querySelector('.is_active').classList.remove('is_active');
                event.target.classList.add('is_active');
                event.preventDefault();
            });
        }
    }

    // Initialize Owl Carousel
    $('.owl-banner').owlCarousel({
        center: true,
        items: 1,
        loop: true,
        nav: true,
        navText: ['<i class="fa fa-angle-left" aria-hidden="true"></i>', '<i class="fa fa-angle-right" aria-hidden="true"></i>'],
        margin: 30,
        responsive: {
            992: { items: 1 },
            1200: { items: 1 }
        },
        autoplay: true,
        autoplayTimeout: 3500,
        autoplayHoverPause: true,
    });

    $('.owl-testimonials').owlCarousel({
        center: true,
        items: 1,
        loop: true,
        nav: true,
        navText: ['<i class="fa fa-angle-left" aria-hidden="true"></i>', '<i class="fa fa-angle-right" aria-hidden="true"></i>'],
        margin: 30,
        responsive: {
            992: { items: 1 },
            1200: { items: 1 }
        }
    });

    // Menu Dropdown Toggle
    if ($('.menu-trigger').length) {
        $(".menu-trigger").on('click', function() {
            $(this).toggleClass('active');
            $('.header-area .nav').slideToggle(200);
        });
    }

    // Smooth Scroll
    $('.scroll-to-section a[href*="#"]:not([href="#"])').on('click', function(e) {
        var target = $(this.hash);
        target = target.length ? target : $('[name=' + this.hash.slice(1) +']');
        if (target.length) {
            var width = $(window).width();
            if (width < 767) {
                $('.menu-trigger').removeClass('active');
                $('.header-area .nav').slideUp(200);
            }
            $('html, body').animate({
                scrollTop: target.offset().top - 80
            }, 700);
            return false;
        }
    });

    // $(document).ready(function () {
    //     // Smooth scroll for section links
    //     $('.scroll-to-section a[href^="#"]').on('click', function(e) {
    //         e.preventDefault();
    //         $('.scroll-to-section a').removeClass('active');
    //         $(this).addClass('active');
    //         var target = $(this.hash);
    //         $('html, body').stop().animate({
    //             scrollTop: target.offset().top - 79
    //         }, 500, 'swing', function() {
    //             window.location.hash = target;
    //         });
    //     });
    // });

    // Page loading animation
    $(window).on('load', function() {
        if ($('.cover').length) {
            $('.cover').parallax({
                imageSrc: $('.cover').data('image'),
                zIndex: '1'
            });
        }
        $("#js-preloader").animate({
            'opacity': '0'
        }, 600, function() {
            setTimeout(function() {
                $("#js-preloader").css("visibility", "hidden").fadeOut();
            }, 300);
        });
    });

    // Open/Close Submenus
    const dropdownOpener = $('.main-nav ul.nav .has-sub > a');
    if (dropdownOpener.length) {
        dropdownOpener.on('tap click', function(e) {
            var thisItemParent = $(this).parent('li'),
                thisItemParentSiblingsWithDrop = thisItemParent.siblings('.has-sub');

            if (thisItemParent.hasClass('has-sub')) {
                var submenu = thisItemParent.find('> ul.sub-menu');

                if (submenu.is(':visible')) {
                    submenu.slideUp(450, 'easeInOutQuad');
                    thisItemParent.removeClass('is-open-sub');
                } else {
                    thisItemParent.addClass('is-open-sub');
                    if (thisItemParentSiblingsWithDrop.length === 0) {
                        thisItemParent.find('.sub-menu').slideUp(400, 'easeInOutQuad', function () {
                            submenu.slideDown(250, 'easeInOutQuad');
                        });
                    } else {
                        thisItemParent.siblings().removeClass('is-open-sub').find('.sub-menu').slideUp(250, 'easeInOutQuad', function () {
                            submenu.slideDown(250, 'easeInOutQuad');
                        });
                    }
                }
            }
            e.preventDefault();
        });
    }
     /**
     * Mobile nav dropdowns activate
     */
    // on('click', '.navbar .dropdown > a', function(e) {
    //     if (select('#navbar').classList.contains('navbar-mobile')) {
    //     e.preventDefault()
    //     this.nextElementSibling.classList.toggle('dropdown-active')
    //     }
    // }, true)


    loader();
})(window.jQuery);
