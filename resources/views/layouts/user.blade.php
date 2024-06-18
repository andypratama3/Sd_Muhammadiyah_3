<!DOCTYPE html>
<html lang="en">

<head>
    @include('layouts.user.head')

</head>

<body class="body">

    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-T28Z28V9"
    height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->

    <div class="loading-screen">
        <img class="loading-image" src="{{asset('assets/img/SD3_logo.png')}}" alt="Loading">
    </div>

    @include('layouts.user.header')
    <main id="main">
        @yield('content')
    </main>

    <!-- ======= Footer ======= -->
   @include('layouts.user.footer')
    <a href="#" class="scroll-top d-flex align-items-center justify-content-center">
        <i class="bi bi-arrow-up-short"></i>
    </a>
    @include('layouts.user.script')
</body>
</html>
