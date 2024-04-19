<!DOCTYPE html>
<html lang="en">

<head>
    @include('layouts.user.head')

</head>

<body class="body">
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
