<!DOCTYPE html>
<html lang="en">

  <head>
    @include('layouts.user_new.head')
  </head>

<body>
    {{-- <ul class="background-page">
      <li></li>
      <li></li>
      <li></li>
      <li></li>
      <li></li>
      <li></li>
    </ul> --}}

  <!-- ***** Preloader Start ***** -->
  {{-- <div id="js-preloader" class="js-preloader">
      <span></span>
      <span></span>
      <span class="logo-loading"><img src="{{ asset('asset_new/images/SD3_logo.png') }}" alt=""></span>
  </div> --}}
  <!-- ***** Preloader End ***** -->

  <!-- ***** Header Area Start ***** -->
  @include('layouts.user_new.header')
  <!-- ***** Header Area End ***** -->
  {{-- Start Content --}}

    @yield('content')

  {{-- End Content --}}

@include('layouts.user_new.footer')



  <!-- Scripts -->
  <!-- Bootstrap core JavaScript -->
  @include('layouts.user_new.script')

  </body>
</html>
