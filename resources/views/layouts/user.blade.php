<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>@yield('title')</title>
    <meta content="" name="description">
    <meta content="" name="keywords">

    <!-- Favicons -->
    <link href="{{asset('assets/img/SD3_logo.png')}}" rel="icon">
    <link href="{{asset('assets/img/apple-touch-icon.png')}}" rel="apple-touch-icon">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=EB+Garamond:wght@400;500&family=Inter:wght@400;500&family=Playfair+Display:ital,wght@0,400;0,700;1,400;1,700&display=swap"
        rel="stylesheet">

    <!-- Vendor CSS Files -->
    <link href="{{asset('assets/vendor/bootstrap/css/bootstrap.min.css')}}" rel="stylesheet">
    <link href="{{asset('assets/vendor/bootstrap-icons/bootstrap-icons.css')}}" rel="stylesheet">
    <link href="{{asset('assets/vendor/swiper/swiper-bundle.min.css')}}" rel="stylesheet">
    <link href="{{asset('assets/vendor/glightbox/css/glightbox.min.css')}}" rel="stylesheet">
    <link href="{{asset('assets/vendor/aos/aos.css')}}" rel="stylesheet">

    <!-- Template Main CSS Files -->
    <link href="{{asset('assets/css/variables.css')}}" rel="stylesheet">
    <link href="{{asset('assets/css/main.css')}}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css" />
    <style>
        .loading-screen {
            width: 100%;
            height: 100%;
            position: fixed;
            padding-top: 8%;
            background: #6feb1d;
            padding-left: 38%;
            margin: 0 auto;
            z-index: 99999;
        }

        .loading-image {
            animation: bounce 2s infinite;
        }

        @keyframes bounce {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-40px);
            }
        }
    </style>
</head>

<body>
    <div class="loading-screen">
        <img class="loading-image" src="{{asset('assets/img/SD3_logo.png')}}" alt="Loading">
    </div>
    <!-- ======= Header ======= -->
    {{-- <header id="header" class="header d-flex align-items-center fixed-top"> --}}
    <header id="header" class="header d-flex align-items-center">
        <div class="container-fluid container-xl d-flex align-items-center justify-content-between">

            <a href="{{ route('index') }}" class="logo d-flex align-items-center">
                <!-- Uncomment the line below if you also wish to use an image logo -->
                <img src="{{asset('assets/img/SD3_logo.png')}}" alt="">
                <h1>SD MUHAMMADIYAH 3</h1>
            </a>
            <nav id="navbar" class="navbar">
                <ul>
                    <li><a href="{{ route('index') }}">Beranda</a></li>
                    <li class="dropdown"><a href="#"><span>Profil</span> <i
                                class="bi bi-chevron-down dropdown-indicator"></i></a>
                        <ul>
                            <li><a href="/guru">Guru</a></li>
                            <li><a href="/fasilitas">Fasilitas</a></li>
                            <li><a href="/ekstrakurikuler">Ekstrakurikuler</a></li>
                        </ul>
                    </li>
                    <li class="dropdown"><a href="#"><span>Informasi</span> <i
                                class="bi bi-chevron-down dropdown-indicator"></i></a>
                        <ul>
                            <li><a href="#">Pembayaran SPP</a></li>
                            <li><a href="#">Nilai Siswa</a></li>
                            <li><a href="#">Berita</a></li>
                            <li><a href="#">Prestasi Siswa</a></li>
                            <li><a href="#">Jadwal Sekolah</a></li>
                        </ul>
                    </li>
                    <li><a href="about.html">Masuk</a></li>
                    <li><a href="contact.html">Kontak</a></li>
                </ul>
            </nav><!-- .navbar -->

            <div class="position-relative">
                <a href="#" class="mx-2"><span class="bi-facebook"></span></a>
                <a href="#" class="mx-2"><span class="bi-instagram"></span></a>

                <a href="#" class="mx-2 js-search-open"><span class="bi-search"></span></a>
                <i class="bi bi-list mobile-nav-toggle"></i>

                <!-- ======= Search Form ======= -->
                <div class="search-form-wrap js-search-form-wrap">
                    <form action="search-result.html" class="search-form">
                        <span class="icon bi-search"></span>
                        <input type="text" placeholder="Search" class="form-control">
                        <button class="btn js-search-close"><span class="bi-x"></span></button>
                    </form>
                </div><!-- End Search Form -->
            </div>
        </div>

    </header><!-- End Header -->
    <main id="main">

        @yield('content')
    </main><!-- End #main -->
    <!-- ======= Footer ======= -->
    <footer id="footer" class="footer">

        <div class="footer-content">
            <div class="container">

                <div class="row g-5">
                    <div class="col-lg-4">
                        <h3 class="footer-heading">Tentang SD Muhammadiyah 3 Samarinda</h3>
                        <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Magnam ab, perspiciatis beatae autem
                            deleniti voluptate nulla a dolores, exercitationem eveniet libero laudantium recusandae
                            officiis qui aliquid blanditiis omnis quae. Explicabo?</p>
                        <p><a href="#about" class="footer-link-more">Learn More</a></p>
                    </div>
                    <div class="col-6 col-lg-2">
                        <h3 class="footer-heading">Navigasi</h3>
                        <ul class="footer-links list-unstyled">
                            <li><a href="/"><i class="bi bi-chevron-right"></i> Home</a></li>
                            <li><a href="#about"><i class="bi bi-chevron-right"></i> Tentang</a></li>
                            <li><a href="#"><i class="bi bi-chevron-right"></i> Visi & Misi</a></li>
                            <li><a href="/kontak"><i class="bi bi-chevron-right"></i> Contact</a></li>
                        </ul>
                    </div>
                    <div class="col-lg-4">
                        <h3 class="footer-heading">Postingan Terakhir</h3>

                        <ul class="footer-links footer-blog-entry list-unstyled">
                            <li>

                                <a href="single-post.html" class="d-flex align-items-center">
                                    <img src="{{asset('assets/img/post-sq-1.jpg')}}" alt="" class="img-fluid me-3">
                                    <div>
                                        <div class="post-meta d-block"><span class="date">Culture</span> <span
                                                class="mx-1">&bullet;</span> <span>Jul 5th '22</span></div>
                                        <span>5 Great Startup Tips for Female Founders</span>
                                    </div>
                                </a>
                            </li>

                            <li>
                                <a href="single-post.html" class="d-flex align-items-center">
                                    <img src="{{asset('assets/img/post-sq-2.jpg')}}" alt="" class="img-fluid me-3">
                                    <div>
                                        <div class="post-meta d-block"><span class="date">Culture</span> <span
                                                class="mx-1">&bullet;</span> <span>Jul 5th '22</span></div>
                                        <span>What is the son of Football Coach John Gruden, Deuce Gruden doing
                                            Now?</span>
                                    </div>
                                </a>
                            </li>

                            <li>
                                <a href="single-post.html" class="d-flex align-items-center">
                                    <img src="{{asset('assets/img/post-sq-3.jpg')}}" alt="" class="img-fluid me-3">
                                    <div>
                                        <div class="post-meta d-block"><span class="date">Culture</span> <span
                                                class="mx-1">&bullet;</span> <span>Jul 5th '22</span></div>
                                        <span>Life Insurance And Pregnancy: A Working Mom’s Guide</span>
                                    </div>
                                </a>
                            </li>

                            <li>
                                <a href="single-post.html" class="d-flex align-items-center">
                                    <img src="{{asset('assets/img/post-sq-4.jpg')}}" alt="" class="img-fluid me-3">
                                    <div>
                                        <div class="post-meta d-block"><span class="date">Culture</span> <span
                                                class="mx-1">&bullet;</span> <span>Jul 5th '22</span></div>
                                        <span>How to Avoid Distraction and Stay Focused During Video Calls?</span>
                                    </div>
                                </a>
                            </li>

                        </ul>

                    </div>
                </div>
            </div>
        </div>

        <div class="footer-legal">
            <div class="container">

                <div class="row justify-content-between">
                    <div class="col-md-6 text-center text-md-start mb-3 mb-md-0">
                        <div class="copyright">
                            © Copyright <strong><span>SD Muhammadiyah 3 Samarinda</span></strong>. All Rights Reserved
                        </div>

                    </div>

                    <div class="col-md-6">
                        <div class="social-links mb-3 mb-lg-0 text-center text-md-end">
                            <a href="#" class="twitter"><i class="bi bi-twitter"></i></a>
                            <a href="#" class="facebook"><i class="bi bi-facebook"></i></a>
                            <a href="#" class="instagram"><i class="bi bi-instagram"></i></a>
                            <a href="#" class="google-plus"><i class="bi bi-skype"></i></a>
                            <a href="#" class="linkedin"><i class="bi bi-linkedin"></i></a>
                        </div>

                    </div>

                </div>

            </div>
        </div>

    </footer>

    <a href="#" class="scroll-top d-flex align-items-center justify-content-center"><i
            class="bi bi-arrow-up-short"></i></a>

    <!-- Vendor JS Files -->
    <script src="{{asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js')}}"></script>
    <script src="{{asset('assets/vendor/swiper/swiper-bundle.min.js')}}"></script>
    <script src="{{asset('assets/vendor/glightbox/js/glightbox.min.js')}}"></script>
    <script src="{{asset('assets/vendor/aos/aos.js')}}"></script>
    <script src="{{asset('assets/vendor/php-email-form/validate.js')}}"></script>
    <script src="{{ asset('asset_dashboard/vendor/jquery/jquery.min.js') }}"></script>
    <!-- Template Main JS File -->
    <script src="{{asset('assets/js/main.js')}}"></script>
    <script>
        $(document).ready(function () {
            setTimeout(() => {
                $('.loading-screen').fadeOut(1000);
            }, 800);
        });
    </script>
</body>

</html>
