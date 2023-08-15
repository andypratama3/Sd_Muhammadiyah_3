<!DOCTYPE html>
<html lang="en">

<head>
    @include('layouts.dashboard.partial.header')
</head>

<body id="page-top">
    <div id="wrapper">
        <!-- Navbar -->
        @include('layouts.dashboard.partial.navbar')
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <!-- Sidebar -->
                @include('layouts.dashboard.partial.sidebar')

                <!-- Content -->
                <div class="container-fluid" id="container-wrapper">
                    @yield('content')
                </div>
            </div>
        </div>
    </div>
    <!-- Footer -->
    @include('layouts.dashboard.partial.footer')
    <!-- Footer -->
    </div>
    <!-- Scroll to top -->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>
    @include('layouts.dashboard.partial.script')
    @stack('js')
</body>
</html>
