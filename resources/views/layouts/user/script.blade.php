<!-- JavaScript Libraries -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('asset/lib/wow/wow.min.js') }}"></script>
<script src="{{ asset('asset/lib/easing/easing.min.js') }}"></script>
<script src="{{ asset('asset/lib/waypoints/waypoints.min.js') }}"></script>
<script src="{{ asset('asset/lib/counterup/counterup.min.js') }}"></script>
<script src="{{ asset('asset/lib/lightbox/js/lightbox.min.js') }}"></script>
<script src="{{ asset('asset/lib/owlcarousel/owl.carousel.min.js') }}"></script>

<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<!-- Template Javascript -->
<script src="{{ asset('asset/js/main.js') }}"></script>
<script>
    window.gtranslateSettings = {
        "default_language": "id",
        "detect_browser_language": true,
        "languages": ["en","id","ar", "ru", "de"],
        "wrapper_selector": ".gtranslate_wrapper",
        "switcher_horizontal_position": "left",
        "switcher_vertical_position": "bottom",
        "float_switcher_open_direction": "bottom",
        "flag_style": "3d",
    }
</script>
<script src="https://cdn.gtranslate.net/widgets/latest/float.js" defer></script>
    

@stack('js_user')
