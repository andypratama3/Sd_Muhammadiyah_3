  <!-- Vendor JS Files -->
  <script src="{{asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js')}}"></script>
  <script src="{{asset('assets/vendor/swiper/swiper-bundle.min.js')}}"></script>
  <script src="{{asset('assets/vendor/glightbox/js/glightbox.min.js')}}"></script>
  <script src="{{asset('assets/vendor/aos/aos.js')}}"></script>
  <script src="{{asset('assets/vendor/php-email-form/validate.js')}}"></script>
  <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
  {{-- <script src="{{ asset('asset_dashboard/vendor/jquery/jquery.min.js') }}"></script> --}}
  @stack('js_user')
  <!-- Template Main JS File -->
  <script src="{{asset('assets/js/main.js')}}"></script>
  <script>
      $(document).ready(function () {
        $(window).on('load', function() {

            setTimeout(() => {
                $('.loading-screen').fadeOut(1000);
                $('#header').addClass('fixed-top');
            }, 800);
        });
        function checkInternetConnection() {
            return navigator.onLine;
        }
      });

</script>
