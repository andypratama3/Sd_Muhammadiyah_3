<!DOCTYPE html>
<html lang="en">

@include('layouts.user.head')

<body data-bs-mode="light">
    <div class="google_translate_element">

    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-T28Z28V9" height="0" width="0"
            style="display:none;visibility:hidden"></iframe></noscript>

         <!-- Navbar & Hero End -->
    {{-- <ul class="background-page">
        <li></li>
        <li></li>
        <li></li>
        <li></li>
        <li></li>
        <li></li>
    </ul> --}}

    <!-- Spinner Start -->
    <div id="spinner"
        class="bg-white show position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div>
    <!-- Spinner End -->

    <!-- Topbar Start -->
    @include('layouts.user.topbar')
    <!-- Topbar End -->

    <!-- Navbar & Hero Start -->
    <div class="px-0 container-fluid nav-bar px-lg-4 py-lg-0">
        <div class="container">
            @include('layouts.user.navbar')
        </div>
    </div>

    <!-- Navbar & Hero End -->

    <div class="gtranslate_wrapper">
        @yield('content')
    </div>
    <!-- Back to Top -->
    <a href="#" class="float-right btn btn-primary btn-lg-square rounded-circle back-to-top"><i class="fa fa-arrow-up"></i></a>

    @include('layouts.user.footer')
    @include('layouts.user.script')
    </div>

</body>

</html>
