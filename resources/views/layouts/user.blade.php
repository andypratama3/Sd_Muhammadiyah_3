<!DOCTYPE html>
<html lang="en">

@include('layouts.user.head')

<body>
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-T28Z28V9" height="0" width="0"
            style="display:none;visibility:hidden"></iframe></noscript>

         <!-- Navbar & Hero End -->
    <ul class="background-page">
        <li></li>
        <li></li>
        <li></li>
        <li></li>
        <li></li>
        <li></li>
    </ul>

    <!-- Spinner Start -->
    <div id="spinner"
        class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div>
    <!-- Spinner End -->

    <!-- Topbar Start -->
    @include('layouts.user.topbar')
    <!-- Topbar End -->

    <!-- Navbar & Hero Start -->
    <div class="container-fluid nav-bar px-0 px-lg-4 py-lg-0">
        <div class="container">
            @include('layouts.user.navbar')
        </div>
    </div>
    <!-- Navbar & Hero End -->

    @yield('content')
    <!-- Back to Top -->
    <a href="#" class="btn btn-primary btn-lg-square rounded-circle back-to-top"><i class="fa fa-arrow-up"></i></a>

    @include('layouts.user.footer')
    @include('layouts.user.script')
</body>

</html>
