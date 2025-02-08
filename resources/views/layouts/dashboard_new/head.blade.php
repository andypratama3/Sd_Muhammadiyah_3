<head>
    <meta charset="utf-8" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

    <title>@yield('title')</title>

    <meta name="description" content="" />

    <!-- Favicon -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="{{ asset('asset_dashboard/img/SD3_logo.png') }}" rel="icon">
    <link href="{{ asset('asset_dashboard/img/SD3_logo.png') }}" rel="apple-touch-icon">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet" />

    <!-- Icons. Uncomment required icon fonts -->
    <link rel="stylesheet" href="{{ asset('asset_dashboard_new/vendor/fonts/boxicons.css')}}" />

    <!-- Core CSS -->
    <link rel="stylesheet" href="{{ asset('asset_dashboard_new/vendor/css/core.css')}}" class="template-customizer-core-css" />
    <link rel="stylesheet" href="{{ asset('asset_dashboard_new/vendor/css/theme-default.css')}}" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="{{ asset('asset_dashboard_new/css/demo.css')}}" />

    <!-- Vendors CSS -->
    <link rel="stylesheet" href="{{ asset('asset_dashboard_new/vendor/libs/perfect-scrollbar/perfect-scrollbar.css')}}" />

    <link rel="stylesheet" href="{{ asset('asset_dashboard_new/vendor/libs/apex-charts/apex-charts.css')}}" />

    <!-- Page CSS -->

    <!-- Helpers -->
    <script src="{{ asset('asset_dashboard_new/vendor/js/helpers.js') }}"></script>
    <link href="{{ asset('asset_dashboard_new/vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet" type="text/css">
    <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js') }} in the <head> section -->
    <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
    <script src="{{ asset('asset_dashboard_new/js/config.js') }}"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />


    <style type="text/css">
        .select2 {
            width:100% !important;
        }
        .select2-selection__rendered {
            line-height: 37px !important;

        }
        .select2-container .select2-selection--single {
            height: 37px !important;
        }
        /* .select2-selection__arrow {
            height: 35px !important;
        } */

        .select2-container--default .select2-selection--single {
            border: 0.1px solid #d0d0d0 !important;
        }
    </style>


    @stack('css')
</head>
