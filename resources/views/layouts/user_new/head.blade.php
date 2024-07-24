<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<meta name="csrf-token" content="{{ csrf_token() }}">
@stack('meta_user')
<meta name="title" content="Sekolah Kreatif Muhammadiyah 3, Samarinda Kalimantan Timur Indonesia, SD Muhammadiyah 3, Samarinda, Kalimantan Timur">
<meta name="keywords" content="Sekolah Kreatif Muhammadiyah 3, Samarinda Kalimantan Timur Indonesia, SD Muhammadiyah 3, Samarinda, Kalimantan Timur">
<meta name="author" content="Sekolah Kreatif Muhammadiyah 3, Samarinda Kalimantan Timur Indonesia, SD Muhammadiyah 3, Samarinda, Kalimantan Timur">
<meta name="copyright" content="Sekolah Kreatif Muhammadiyah 3, Samarinda Kalimantan Timur Indonesia, SD Muhammadiyah 3, Samarinda, Kalimantan Timur">
<meta name="category" content="Sekolah Kreatif Muhammadiyah 3, Samarinda Kalimantan Timur Indonesia, SD Muhammadiyah 3, Samarinda, Kalimantan Timur">
<meta name="description" content="Sekolah Kreatif Muhammadiyah 3, Samarinda Kalimantan Timur Indonesia, SD Muhammadiyah 3, Samarinda, Kalimantan Timur">
<meta name="image" content="{{ asset('asset_new/images/SD3_logo.png') }}">
<meta name="og:title" content="Sekolah Kreatif Muhammadiyah 3, Samarinda Kalimantan Timur Indonesia, SD Muhammadiyah 3, Samarinda, Kalimantan Timur">
<meta name="og:description" content="Sekolah Kreatif Muhammadiyah 3, Samarinda Kalimantan Timur Indonesia, SD Muhammadiyah 3, Samarinda, Kalimantan Timur">
<meta name="og:image" content="{{ asset('asset_new/images/SD3_logo.png') }}">
<meta name="og:url" content="{{ route('index') }}">
<meta name="og:site_name" content="Sekolah Kreatif Muhammadiyah 3, Samarinda Kalimantan Timur Indonesia, SD Muhammadiyah 3, Samarinda, Kalimantan Timur">
<meta name="og:type" content="website">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="Sekolah Kreatif Muhammadiyah 3, Samarinda Kalimantan Timur Indonesia, SD Muhammadiyah 3, Samarinda, Kalimantan Timur">
<meta name="twitter:description" content="Sekolah Kreatif Muhammadiyah 3, Samarinda Kalimantan Timur Indonesia, SD Muhammadiyah 3, Samarinda, Kalimantan Timur">
<meta name="twitter:image" content="{{ asset('asset_new/images/SD3_logo.png') }}">
<meta name="twitter:url" content="{{ route('index') }}">
<meta name="twitter:site" content="Sekolah Kreatif Muhammadiyah 3, Samarinda Kalimantan Timur Indonesia, SD Muhammadiyah 3, Samarinda, Kalimantan Timur">
<meta name="twitter:creator" content="Sekolah Kreatif Muhammadiyah 3, Samarinda Kalimantan Timur Indonesia, SD Muhammadiyah 3, Samarinda, Kalimantan Timur">
<meta name="twitter:domain" content="Sekolah Kreatif Muhammadiyah 3, Samarinda Kalimantan Timur Indonesia, SD Muhammadiyah 3, Samarinda, Kalimantan Timur">
<meta name="twitter:app:name:googleplay" content="Sekolah Kreatif Muhammadiyah 3, Samarinda Kalimantan Timur Indonesia, SD Muhammadiyah 3, Samarinda, Kalimantan Timur">
<meta name="twitter:app:id:googleplay" content="Sekolah Kreatif Muhammadiyah 3, Samarinda Kalimantan Timur Indonesia, SD Muhammadiyah 3, Samarinda, Kalimantan Timur">
<meta name="twitter:app:url:googleplay" content="{{ route('index') }}">
<meta name="twitter:app:name:iphone" content="Sekolah Kreatif Muhammadiyah 3, Samarinda Kalimantan Timur Indonesia, SD Muhammadiyah 3, Samarinda, Kalimantan Timur">
<meta name="twitter:app:id:iphone" content="Sekolah Kreatif Muhammadiyah 3, Samarinda Kalimantan Timur Indonesia, SD Muhammadiyah 3, Samarinda, Kalimantan Timur">
<meta name="twitter:app:url:iphone" content="{{ route('index') }}">
<meta name="twitter:app:name:ipad" content="Sekolah Kreatif Muhammadiyah 3, Samarinda Kalimantan Timur Indonesia, SD Muhammadiyah 3, Samarinda, Kalimantan Timur">
<meta name="twitter:app:id:ipad" content="Sekolah Kreatif Muhammadiyah 3, Samarinda Kalimantan Timur Indonesia, SD Muhammadiyah 3, Samarinda, Kalimantan Timur">
<meta name="twitter:app:url:ipad" content="{{ route('index') }}">
<meta name="twitter:app:name:ipad" content="Sekolah Kreatif Muhammadiyah 3, Samarinda Kalimantan Timur Indonesia, SD Muhammadiyah 3, Samarinda, Kalimantan Timur">
<meta name="twitter:app:id:ipad" content="Sekolah Kreatif Muhammadiyah 3, Samarinda Kalimantan Timur Indonesia, SD Muhammadiyah 3, Samarinda, Kalimantan Timur">
<meta name="twitter:app:url:ipad" content="{{ route('index') }}">
<meta name="twitter:app:name:ipad" content="Sekolah Kreatif Muhammadiyah 3, Samarinda Kalimantan Timur Indonesia, SD Muhammadiyah 3, Samarinda, Kalimantan Timur">
<meta name="robots" content="index, follow">
<meta name="rating" content="general">
<meta name="distribution" content="global">
<title>@yield('title')</title>
<!-- Bootstrap core CSS -->
<link href="{{ asset('asset_new/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
<link rel="shortcut icon" href="{{ asset('asset_new/images/SD3_logo.png') }}" type="image/x-icon">

<!-- Additional CSS Files -->
<link rel="stylesheet" href="{{ asset('asset_new/css/fontawesome.css') }}">
<link rel="stylesheet" href="{{ asset('asset_new/css/templatemo-scholar.css') }}">
<link rel="stylesheet" href="{{ asset('asset_new/css/owl.css') }}">
<link rel="stylesheet" href="{{ asset('asset_new/css/animate.css') }}">

<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','GTM-T28Z28V9');</script>
    <!-- End Google Tag Manager -->


<link rel="stylesheet"href="https://unpkg.com/swiper@7/swiper-bundle.min.css"/>
@stack('css_user')
