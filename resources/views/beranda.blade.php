@extends('layouts.user')

@push('meta_user')
    <meta name="description" content="Sekolah Kreatif Muhammadiyah 3 Samarinda menawarkan pembelajaran inovatif, pendidikan berbasis karakter, dan lingkungan belajar yang menyenangkan untuk siswa.">
    <meta name="keywords" content="Sekolah Kreatif Muhammadiyah 3, SD Muhammadiyah 3 Samarinda, Sekolah Islam Samarinda, Sekolah Kreatif, Pendidikan Inovatif, SD Muhammadiyah, Pembelajaran Berbasis Karakter, Sekolah Dasar Samarinda, Sekolah Kreatif Samarinda, Sd Muhammadiyah Samarinda, Sekolah Terdepan Samarinda,Sekolah Kreatif Muhammadiyah 3 Samarinda, Sekolah Sd Terdekat Samarinda Seberang, Sekolah Terbaik Di Samarinda, sekolah sd terbaik di samarinda, sd muhammadiyah, gambar sd muhammadiyah 3 samarinda, Sekolah Kreatif Muhammadiyah 3 Samarinda">
    <meta name="author" content="Sekolah Kreatif Muhammadiyah 3 Samarinda">
    <meta name="robots" content="index, follow">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta property="og:type" content="website">
    <meta property="og:title" content="Sekolah Kreatif Muhammadiyah 3 Samarinda - Pembelajaran Inovatif dan Berkarakter">
    <meta property="og:description" content="Gabung dengan Sekolah Kreatif Muhammadiyah 3 Samarinda untuk pengalaman belajar yang inovatif dan berbasis karakter!">
    <meta property="og:image" content="https://sdmuhammadiyah3smd.com/asset_new/images/SD3_logo1.png">
    <meta property="og:url" content="https://sdmuhammadiyah3smd.com">

    <meta property="og:site_name" content="Sekolah Kreatif Muhammadiyah 3 Samarinda">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Sekolah Kreatif Muhammadiyah 3 Samarinda - Pembelajaran Inovatif & Berkarakter">
    <meta name="twitter:description" content="Sekolah dengan metode pembelajaran inovatif dan pendidikan karakter terbaik di Samarinda.">
    <meta name="twitter:image" content="{{ asset('asset_new/images/SD3_logo1.png') }}">
@endpush

@push('css_user')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
<style>
    .about .swiper {
        padding: 10px 0;
    }

    .carousel-custom {
        object-fit: cover;
        justify-content: center !important;
        align-items: center !important;
    }

    .image-custom-feature {
        object-fit: contain !important;
        mix-blend-mode: multiply !important;
        margin-top: 20px !important;
    }
    .owl-carousel {
        -ms-touch-action: pan-y;
        touch-action: pan-y;
    }

    .about .swiper-wrapper {
        height: auto;
    }

    .about .swiper-slide img {
        transition: 0.3s;
    }

    .about .swiper-slide img:hover {
        transform: scale(1.1);
    }
    .img-header-kepala-sekolah {
        border-radius: 10px;
        object-fit: cover;
        width: 80%;
        height: 100%;
        object-position: center;
        object-fit: cover;
        mix-blend-mode: multiply;
    }

    @media screen and (max-width: 768px) {
        .img-header-kepala-sekolah {
            height: auto;
            width: 100%;
        }
        .cooperation-item h6 {
            margin: 15px;
        }
        .achivement-item h6 {
            margin: 15px;
        }
    }

    @media screen and (max-width: 576px) {
        .header-carousel-item {

            background-size: cover !important;
            background-position: center !important;
        }

        .carousel-img img {
            width: 80% !important;
            max-width: 250px;
            display: block;
            margin: 0 auto;
        }

        .carousel-caption {
            font-size: 13px;
            text-align: center;

        }
        .cooperation-item h6 {
            margin: 15px;
        }
        .achivement-item h6 {
            margin: 15px;
        }
    }

</style>
@endpush
@section('content')

<!-- Carousel Start -->

<div class="header-carousel owl-carousel">
    @foreach($heroes as $hero)
    <div class="header-carousel-item" style="background-image: url({{ asset('storage/img/hero/'. $hero->image) }})">
        <div class="carousel-caption">
            <div class="container">
                <div class="row g-4 align-items-center">
                    <div class="col-lg-7 animated fadeInLeft">
                        <div class="text-sm-center">
                            <h3 class="text-white text-uppercase fw-bold mb-2 mt-2">SD MUHAMMADIYAH 3 SAMARINDA </h3>
                            <h4 class="display-2 text-white mb-4">{{ $hero->name }}</h4>
                            <p class="mb-5 fs-5">{{ $hero->desc }}
                            </p>
                            <div class="d-flex justify-content-center flex-shrink-0 mb-4">
                                @if($hero->youtube != null)
                                <a class="btn btn-light rounded-pill py-3 px-4 px-md-5 me-2"
                                    href="{{ $hero->youtube }}" target="__blank"><i class="fas fa-play-circle me-2"></i> Watch Video</a>
                                @endif
                                @if($hero->link != null)
                                <a class="btn btn-dark rounded-pill py-3 px-4 px-md-5 ms-2"
                                    href="{{ $hero->link }}" target="__blank">Kunjungi Halaman</a>
                                @endif

                            </div>
                        </div>
                    </div>
                    <div class="col-lg-5 animated fadeInRight">
                        <div class="carousel-img carousel-custom">
                            <img src="{{ asset('asset_new/images/SD3_logo1.png') }}" class="img-fluid img-logo" alt="" style="width: 60%;" loading="lazy">
                            <div class="jargon mx-4" style="display: flex; gap: 4px; font-family: times new roman; justify-content: center; margin-top: 15px;">
                                {{-- Jargon SD MUHAMMADIYAH 3 SAMARINDA --}}
                                    <p>S</p>
                                    <p style="margin-right: 5px;">D</p>
                                    <p>M</p>
                                    <p>U</p>
                                    <p>H</p>
                                    <p>A</p>
                                    <p>M</p>
                                    <p>M</p>
                                    <p>A</p>
                                    <p>D</p>
                                    <p>I</p>
                                    <p>Y</p>
                                    <p>A</p>
                                    <p style="margin-right: 5px;">H</p>
                                    <p style="margin-right: 5px;">3</p>
                                    <p>S</p>
                                    <p>A</p>
                                    <p>M</p>
                                    <p>A</p>
                                    <p>R</p>
                                    <p>I</p>
                                    <p>N</p>
                                    <p>D</p>
                                    <p>A</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>
<!-- Carousel End -->

<!-- Feature Start -->
<div class="container-fluid feature bg-light py-5">
    <div class="container py-5">
        <div class="container-prorgam-unggulan">
            <div class="text-center mx-auto pb-5 wow fadeInRight" data-wow-delay="0.2s" style="max-width: 800px;">
                <h3 class="text-primary">SD MUHAMMADIYAH 3 SAMARINDA</h3>
                <h1 class="display-4">Program Unggulan</h1>
            </div>
            <div class="row g-4 wow  fadeInUp" data-wow-delay="0.2s">
                <div class="col-md-8 float-end">
                    <h4 class="text-dark d-flex align-items-center justify-content-start">
                        <i class="fa fa-check text-primary me-3"></i>
                        <p class="my-0 py-0">
                            Tahifdz Al - Qur'an 2 Juz (29 - 30)
                        </p>
                    </h4>
                    <h4 class="text-dark d-flex align-items-center justify-content-start">
                        <i class="fa fa-check text-primary me-3"></i>
                        <p class="my-0 py-0">
                            Pembiasaan Akhlak Iskami Sejak Dini
                        </p>
                    </h4>
                    <h4 class="text-dark d-flex align-items-center justify-content-start">
                        <i class="fa fa-check text-primary me-3"></i>
                        <p class="my-0 py-0">
                            Pembiasaan Sholat Wajib dan Sunnah
                        </p>
                    </h4>
                    <h4 class="text-dark d-flex align-items-center justify-content-start">
                        <i class="fa fa-check text-primary me-3"></i>
                        <p class="my-0 py-0">
                            Pembiasaan Ngaji Morning Metode Tilawati
                        </p>
                    </h4>
                    <h4 class="text-dark d-flex align-items-center justify-content-start">
                        <i class="fa fa-check text-primary me-3"></i>
                        <p class="my-0 py-0">Pembiasaan Menulis Al - Qur'an Dengan Metode IMLA</p>
                    </h4>
                    <h4 class="text-dark d-flex align-items-center justify-content-start">
                        <i class="fa fa-check text-primary me-3"></i>
                        <p class="my-0 py-0">Pembinaan Psikologi Untuk Mengetahui Minat & Bakat Anak</p>
                    </h4>
                    <h4 class="text-dark d-flex align-items-center justify-content-start">
                        <i class="fa fa-check text-primary me-3"></i>
                        <p class="my-0 py-0">Pembelajaran Berbasis Edutainment</p>
                    </h4>
                    <h4 class="text-dark d-flex my-0">
                        <i class="fa fa-check text-primary me-3" style="opacity: 0;"></i>
                        <p class="text-dark" style="font-size: 16px;">
                            ( Belajar Menyenangkan Dengan Menyeimbankan Otak Kanan Dan Kiri )
                        </p>
                    </h4>
                    <h4 class="text-dark d-flex align-items-center justify-content-start">
                        <i class="fa fa-check text-primary me-3"></i>
                        <p class="my-0 py-0">Lulus Dengan 3 Ijazah</p>
                    </h4>
                </div>
                <div class="col-md-4">
                    <img src="{{ asset('asset/img/sekolah-penggerak.jpeg') }}" class="img-fluid image-custom-feature" alt="">
                </div>
            </div>
            <div class="text-center mx-auto pb-5 wow fadeInUp" data-wow-delay="0.2s" style="max-width: 800px;">
            </div>
        </div>

            <div class="row g-4">
                <div class="col-md-6 col-lg-6 col-xl-3 wow fadeInUp" data-wow-delay="0.2s">
                    <div class="feature-item p-4 pt-0">
                        <div class="feature-icon p-4 mb-4">
                            <i class="fa-solid fa-a fa-3x"></i>
                        </div>
                        <h4 class="mb-4" style="font-size: 22px;">AKREDITAS UNGGUL</h4>
                    </div>
                </div>
                <div class="col-md-6 col-lg-6 col-xl-3 wow fadeInUp" data-wow-delay="0.8s">
                    <div class="feature-item p-4 pt-0">
                        <div class="feature-icon p-4 mb-4">
                            <i class="fa fa-building fa-3x"></i>
                        </div>
                        <h4 class="mb-4">FASILITAS</h4>
                        <a class="btn btn-primary rounded-pill py-2 px-4" href="{{ route('fasilitas.index') }}"
                            aria-label="lihat-fasilitas">Lihat</a>
                    </div>
                </div>
                <div class="col-md-6 col-lg-6 col-xl-3 wow fadeInUp" data-wow-delay="0.4s">
                    <div class="feature-item p-4 pt-0">
                        <div class="feature-icon p-4 mb-4">
                            <i class="fa fa-trophy fa-3x"></i>
                        </div>
                        <h4 class="mb-4">PRESTASI SISWA</h4>
                        <a class="btn btn-primary rounded-pill py-2 px-4" href="{{ route('prestasi.siswa.index') }}"
                            aria-label="Lihat-prestasi Siswa">Lihat</a>
                    </div>
                </div>
                <div class="col-md-6 col-lg-6 col-xl-3 wow fadeInUp" data-wow-delay="0.6s">
                    <div class="feature-item p-4 pt-0">
                        <div class="feature-icon p-4 mb-4">
                            <i class="fa fa-school fa-3x"></i>
                        </div>
                        <h4 class="mb-4" style="font-size: 23px;">PRESTASI SEKOLAH</h4>
                        <a class="btn btn-primary rounded-pill py-2 px-4" href="{{  route('prestasi.sekolah.index') }}"
                            aria-label="Lihat-prestasi Sekolah">Lihat</a>
                    </div>
                </div>
                <div class="text-center mx-auto mt-3 wow fadeInRight" data-wow-delay="0.2s">
                    <div class="bg-white rounded-0 p-3 h-100 mt-5" style="border-radius: 10px !important;">
                        <h1 class="display-4 mb-4 mt-0">Aktivitas Kami</h1>
                        <p class="text-primary">Aktivitas SD Muhammadiyah 3 Samarinda</p>
                            <div class="row g-4">
                                @foreach ($gallerys as $gallery)
                                    @php
                                        $fotos = is_array($gallery->foto) ? $gallery->foto : explode(',', $gallery->foto);
                                        $firstFoto = trim($fotos[0] ?? 'default.jpg');
                                        $imgSrc = $gallery->cover
                                            ? asset('storage/img/gallery/cover/' . $gallery->cover)
                                            : asset('storage/img/gallery/' . $firstFoto);
                                        $lightboxKey = $gallery->cover ?? $firstFoto;
                                    @endphp

                                    <div class="col-md-3">
                                        <div class="card">
                                            <a href="{{ $imgSrc }}" data-lightbox="{{ $lightboxKey }}">
                                                <img src="{{ $imgSrc }}" alt="{{ $gallery->name }}" class="img-fluid" style="border-radius: 8px;">
                                            </a>
                                            <div class="card-body">
                                                <h5 class="card-title">{{ $gallery->name }}</h5>
                                                <a href="{{ route('gallery.show', $gallery->slug) }}" class="btn btn-primary">Lihat</a>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="col-md-12">
                                <a href="{{ route('gallery.index') }}" class="btn btn-primary">Lihat Semua</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    </div>
</div>
<!-- Feature End -->
{{-- <div class="container-fluid container-fluid bg-light about pb-5">
    <div class="container pb-5 bg-white p-3 wow fadeInUp" data-wow-delay="0.2s" style="border-radius: 10px;">
        <div class="section-title">
            <small>FITUR</small>
            <h3>Fitur Website SD Muhammadiyah 3 Samarinda
            </h3>
        </div>

        <ul class="nav nav-tabs nav-justified" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" data-toggle="tab" href="#communication">Pembayaran</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#berita-tab">Berita</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#artikel-tab">Artikel</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#prestasi-tab">Prestasi</a>
            </li>
        </ul>
        <div class="tab-content">
            <div class="tab-pane fade show active" id="communication">
                <div class="d-flex flex-column flex-lg-row mt-2">
                    <img src="{{ asset('asset/img/website_midtrans_resized.png') }}" alt="graphic" style="border-radius: 10px !important;" class="img-fluid align-self-start mr-lg-5 mb-5 mb-lg-0 p-2">
                    <div>
                        <h2>Fitur Pembayaran</h2>

                        <p>Pembayaran Online Menggunakan Pihak Ketiga Dari Midtrans Yang Dapat Memudahkan Pembayaran Dengan Tampilan Sederhana Dengan Support Banyak Pembayaran Seperti
                        </p>
                        <p> <p class="text-primary">OVO</p>
                        </p>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade" id="berita-tab">
                <div class="d-flex flex-column flex-lg-row">
                    <img src="images/graphic.png" alt="graphic" class="img-fluid rounded align-self-start mr-lg-5 mb-5 mb-lg-0">
                    <div>
                        <h2>Realtime Messaging service</h2>
                        <p class="lead">Uniquely underwhelm premium outsourcing with proactive leadership skills. </p>
                        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer rutrum, urna eu pellentesque pretium, nisi nisi fermentum enim, et sagittis dolor nulla vel sapien. Vestibulum sit amet mattis ante. Ut placerat dui eu nulla
                            congue tincidunt ac a nibh. Mauris accumsan pulvinar lorem placerat volutpat. Praesent quis facilisis elit. Sed condimentum neque quis ex porttitor,
                        </p>
                        <p> malesuada faucibus augue aliquet. Sed elit est, eleifend sed dapibus a, semper a eros. Vestibulum blandit vulputate pharetra. Phasellus lobortis leo a nisl euismod, eu faucibus justo sollicitudin. Mauris consectetur, tortor
                            sed tempor malesuada, sem nunc porta augue, in dictum arcu tortor id turpis. Proin aliquet vulputate aliquam.
                        </p>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade" id="artikel-tab">
                <div class="d-flex flex-column flex-lg-row">
                    <div>
                        <h2>Scheduling when you want</h2>
                        <p class="lead">Uniquely underwhelm premium outsourcing with proactive leadership skills. </p>
                        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer rutrum, urna eu pellentesque pretium, nisi nisi fermentum enim, et sagittis dolor nulla vel sapien. Vestibulum sit amet mattis ante. Ut placerat dui eu nulla
                            congue tincidunt ac a nibh. Mauris accumsan pulvinar lorem placerat volutpat. Praesent quis facilisis elit. Sed condimentum neque quis ex porttitor,
                        </p>
                        <p> malesuada faucibus augue aliquet. Sed elit est, eleifend sed dapibus a, semper a eros. Vestibulum blandit vulputate pharetra. Phasellus lobortis leo a nisl euismod, eu faucibus justo sollicitudin. Mauris consectetur, tortor
                            sed tempor malesuada, sem nunc porta augue, in dictum arcu tortor id turpis. Proin aliquet vulputate aliquam.
                        </p>
                    </div>
                    <img src="images/graphic.png" alt="graphic" class="img-fluid rounded align-self-start mr-lg-5 mb-5 mb-lg-0">
                </div>
            </div>

            <div class="tab-pane fade" id="prestasi-tab">
                <div class="d-flex flex-column flex-lg-row">
                    <div>
                        <h2>Live chat when you needed</h2>
                        <p class="lead">Uniquely underwhelm premium outsourcing with proactive leadership skills. </p>
                        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer rutrum, urna eu pellentesque pretium, nisi nisi fermentum enim, et sagittis dolor nulla vel sapien. Vestibulum sit amet mattis ante. Ut placerat dui eu nulla
                            congue tincidunt ac a nibh. Mauris accumsan pulvinar lorem placerat volutpat. Praesent quis facilisis elit. Sed condimentum neque quis ex porttitor,
                        </p>
                        <p> malesuada faucibus augue aliquet. Sed elit est, eleifend sed dapibus a, semper a eros. Vestibulum blandit vulputate pharetra. Phasellus lobortis leo a nisl euismod, eu faucibus justo sollicitudin. Mauris consectetur, tortor
                            sed tempor malesuada, sem nunc porta augue, in dictum arcu tortor id turpis. Proin aliquet vulputate aliquam.
                        </p>
                    </div>
                    <img src="images/graphic.png" alt="graphic" class="img-fluid rounded align-self-start mr-lg-5 mb-5 mb-lg-0">
                </div>
            </div>
        </div>


    </div>
</div> --}}

<!-- About Start -->
<div class="container-fluid bg-light about pb-5" id="tentang">
    <div class="container pb-5">
        <div class="row g-5">
            <div class="col-xl-6 wow fadeInLeft" data-wow-delay="0.2s">
                <div class="about-item-content bg-white rounded p-5 h-100">
                    <h4 class="text-primary">TENTANG SD MUHAMMADIYAH 3 SAMARINDA</h4>
                    <h1 class="display-4 mb-4">Pembelajaran Inovatif dan Pengembangan Karakter</h1>
                    <p>Sekolah Kreatif Muhammadiyah 3 Samarinda berkomitmen untuk menyediakan lingkungan belajar yang
                        dinamis dan menarik. Misi kami adalah untuk menumbuhkan kreativitas dan cinta belajar pada
                        setiap siswa. Dengan fokus pada pengembangan holistik, kami memastikan bahwa siswa kami unggul
                        secara akademis dan tumbuh menjadi individu yang berkarakter.</p>
                    <a href="{{ route('visimisi.index') }}" class="btn btn-primary">Lihat Visi & Misi</a>
                </div>
            </div>
            <div class="col-xl-6 wow fadeInRight" data-wow-delay="0.2s">
                <div class="bg-white rounded p-5 h-100">
                    <div class="row g-4 justify-content-center">
                        <div class="col-12">
                            <div class="rounded bg-light">
                                <img src="{{ asset('asset/img/carousel-2.png') }}" class="img-fluid rounded w-100"
                                    alt="">
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="counter-item bg-light rounded p-3 h-100">
                                <div class="counter-counting">
                                    <span class="text-primary fs-2 fw-bold"
                                        data-toggle="counter-up">{{ $siswas }}</span>
                                    <span class="h1 fw-bold text-primary">+</span>
                                </div>
                                <h4 class="mb-0 text-dark">Siswa</h4>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="counter-item bg-light rounded p-3 h-100">
                                <div class="counter-counting">
                                    <span class="text-primary fs-2 fw-bold"
                                        data-toggle="counter-up">{{ $prestasis_siswa }}</span>
                                    <span class="h1 fw-bold text-primary">+</span>
                                </div>
                                <h4 class="mb-0 text-dark">Prestasi Siswa</h4>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="counter-item bg-light rounded p-3 h-100">
                                <div class="counter-counting">
                                    <span class="text-primary fs-2 fw-bold"
                                        data-toggle="counter-up">{{ $prestasis_sekolah }}</span>
                                    <span class="h1 fw-bold text-primary">+</span>
                                </div>
                                <h4 class="mb-0 text-dark">Prestasi Sekolah </h4>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="counter-item bg-light rounded p-3 h-100">
                                <div class="counter-counting">
                                    <span class="text-primary fs-2 fw-bold"
                                        data-toggle="counter-up">{{ $fasilitas }}</span>
                                    <span class="h1 fw-bold text-primary">+</span>
                                </div>
                                <h4 class="mb-0 text-dark">Sarana & Prasarana</h4>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            <div class="bg-white rounded pt-3 pb-5 wow fadeInUp cooperation-item" data-wow-delay="0.2s">
                <div class="row g-4">
                    <div class="d-flex justify-content-center">
                        <div class="col-md-4">
                            <div class="card bg-primary border-0 cooperation">
                                <h6 class="text-black text-center mt-2">Dukunga Dan Kerja Sama</h6>
                            </div>
                        </div>
                    </div>

                    <div class="swiper init-swiper">
                        <div class="swiper-wrapper align-items-center">
                            @foreach ($cooperations as $cooperation)
                                <div class="swiper-slide">
                                    <img src="{{ asset('storage/img/cooperation/'. $cooperation->foto ) }}" class="img-fluid" style="border-radius: 10px;"  alt="">
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded pt-3 pb-5 wow fadeInUp achivement-item" data-wow-delay="0.2s">
                <div class="row g-4">
                    <div class="d-flex justify-content-center">
                        <div class="col-md-4">
                            <div class="card bg-primary border-0 ">
                                <h6 class="text-black text-center mt-2">Penghargaan</h6>
                            </div>
                        </div>
                    </div>
                    <div class="swiper init-swiper2">
                        <div class="swiper-wrapper justify-content-center align-items-center">
                            @forelse ($achivements as $achivement)
                            <div class="swiper-slide">
                                <img src="{{ asset('storage/img/achivement/'. $achivement->foto) }}" class="img-fluid" alt="" style="border-radius: 10px;">
                            </div>
                            @empty

                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- About End -->

<!-- FAQs Start -->
<div class="container-fluid faq-section bg-light py-2" data-wow-delay="0.4s">
    <div class="container py-5">
        <div class="row g-5 align-items-center justify-content-center">
            <div class="col-xl-6 wow fadeInLeft" data-wow-delay="0.2s">
                <div class="h-100">
                    <div class="mb-5">
                        {{-- <h4 class="text-primary"></h4> --}}
                        <h1 class="display-5 mb-0">Kepala Sekolah
                            SD Muhammadiyah 3 Samarinda
                        </h1>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 col-md-12  wow fadeInRight p-4">
                <a href="{{ asset('asset/img/kepala-sekolah.jpeg')}}" data-lightbox="kepala-sekolah" >
                    <img src="{{ asset('asset/img/kepala-sekolah.jpeg') }}" class="img-fluid mb-2 img-header-kepala-sekolah"
                        alt="kepala-sekolah">
                </a>
                <h4 class="text-center">Ansar HS. S.Pd.,M.M. Gr.</h4>
                <p class="text-center text-primary">Kepala Sekolah SD Muhammadiyah 3 Samarinda</p>

            </div>
        </div>
    </div>
</div>
<!-- FAQs End -->

<!-- Service Start -->
<div class="container-fluid service py-5">
    <div class="container py-5">
        <div class="text-center mx-auto pb-5 wow fadeInUp" data-wow-delay="0.2s" style="max-width: 800px;">
            <h4 class="text-primary">Prestasi Terakhir</h4>
            <h1 class="display-4 mb-4">Prestasi Terakhir Sang Juara</h1>
        </div>
        <div class="row g-4 justify-content-center">
            @foreach ($prestasi_terakhir as $prestasi)
            <div class="col-md-6 col-lg-6 col-xl-3 wow fadeInUp" data-wow-delay="0.2s">
                <div class="service-item">
                    <div class="service-img">
                        <img src="{{ asset('storage/img/prestasi/'. $prestasi->foto)}}"
                            class="img-fluid rounded-top w-100" alt="">
                        <div class="service-icon p-3">
                            <i class="fa fa-users fa-2x"></i>
                        </div>
                    </div>
                    <div class="service-content p-4">
                        <div class="service-content-inner">
                            <a href="#" class="d-inline-block h4 mb-4">{{ $prestasi->name }}</a>
                            <a class="btn btn-primary rounded-pill py-2 px-4"
                                href="{{ route('prestasi.siswa.show', $prestasi->slug) }}">Lihat</a>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach

            <div class="col-12 text-center wow fadeInUp" data-wow-delay="0.2s">
                <a class="btn btn-primary rounded-pill py-3 px-5 mb-2" href="{{ route('prestasi.sekolah.index') }}">Lihat
                    Prestasi Sekolah</a>
                <a class="btn btn-primary rounded-pill py-3 px-5 mb-2" href="{{ route('prestasi.siswa.index') }}">Lihat
                    Prestasi Siswa</a>
            </div>
        </div>
    </div>
</div>
<!-- Service End -->
<!-- Testimonial Start -->
{{-- <div class="container-fluid testimonial pb-5">
    <div class="container pb-5">
        <div class="text-center mx-auto pb-5 wow fadeInUp" data-wow-delay="0.2s" style="max-width: 800px;">
            <h4 class="text-primary">Testimonial</h4>
            <h1 class="display-4 mb-4">What Our Customers Are Saying</h1>
            <p class="mb-0">Lorem ipsum dolor, sit amet consectetur adipisicing elit. Tenetur adipisci facilis
                cupiditate recusandae aperiam temporibus corporis itaque quis facere, numquam, ad culpa deserunt sint
                dolorem autem obcaecati, ipsam mollitia hic.
            </p>
        </div>
        <div class="owl-carousel testimonial-carousel wow fadeInUp" data-wow-delay="0.2s">
            <div class="testimonial-item bg-light rounded">
                <div class="row g-0">
                    <div class="col-4  col-lg-4 col-xl-3">
                        <div class="h-100">
                            <img src="{{ asset('asset/img/testimonial-1.jpg') }}" class="img-fluid h-100 rounded"
                                style="object-fit: cover;" alt="">
                        </div>
                    </div>
                    <div class="col-8 col-lg-8 col-xl-9">
                        <div class="d-flex flex-column my-auto text-start p-4">
                            <h4 class="text-dark mb-0">Client Name</h4>
                            <p class="mb-3">Profession</p>
                            <div class="d-flex text-primary mb-3">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                            <p class="mb-0">Lorem, ipsum dolor sit amet consectetur adipisicing elit. Enim error
                                molestiae aut modi corrupti fugit eaque rem nulla incidunt temporibus quisquam,
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="testimonial-item bg-light rounded">
                <div class="row g-0">
                    <div class="col-4  col-lg-4 col-xl-3">
                        <div class="h-100">
                            <img src="{{ asset('asset/img/testimonial-2.jpg') }}" class="img-fluid h-100 rounded"
                                style="object-fit: cover;" alt="">
                        </div>
                    </div>
                    <div class="col-8 col-lg-8 col-xl-9">
                        <div class="d-flex flex-column my-auto text-start p-4">
                            <h4 class="text-dark mb-0">Client Name</h4>
                            <p class="mb-3">Profession</p>
                            <div class="d-flex text-primary mb-3">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star text-body"></i>
                            </div>
                            <p class="mb-0">Lorem, ipsum dolor sit amet consectetur adipisicing elit. Enim error
                                molestiae aut modi corrupti fugit eaque rem nulla incidunt temporibus quisquam,
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="testimonial-item bg-light rounded">
                <div class="row g-0">
                    <div class="col-4  col-lg-4 col-xl-3">
                        <div class="h-100">
                            <img src="{{ asset('asset/img/testimonial-3.jpg') }}" class="img-fluid h-100 rounded"
                                style="object-fit: cover;" alt="">
                        </div>
                    </div>
                    <div class="col-8 col-lg-8 col-xl-9">
                        <div class="d-flex flex-column my-auto text-start p-4">
                            <h4 class="text-dark mb-0">Client Name</h4>
                            <p class="mb-3">Profession</p>
                            <div class="d-flex text-primary mb-3">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star text-body"></i>
                                <i class="fas fa-star text-body"></i>
                            </div>
                            <p class="mb-0">Lorem, ipsum dolor sit amet consectetur adipisicing elit. Enim error
                                molestiae aut modi corrupti fugit eaque rem nulla incidunt temporibus quisquam,
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> --}}
<!-- Testimonial End -->


@push('js_user')
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
    const originalWarn = console.warn;
        console.warn = function (message) {
            if (!message.includes("Swiper Loop Warning")) {
                originalWarn.apply(console, arguments);
            }
        };

    const swiper_1 = new Swiper('.init-swiper', {
        loopFillGroupWithBlank: true,
        loop: true,
        speed: 600,
        autoplay: {
            delay: 5000,
        },
        slidesPerView: 'auto',
        pagination: {
            el: '.swiper-pagination',
            type: 'bullets',
            clickable: true,
        },
        breakpoints: {
            320: {
                slidesPerView: 2,
                spaceBetween: 40
            },
            480: {
                slidesPerView: 3,
                spaceBetween: 60
            },
            640: {
                slidesPerView: 4,
                spaceBetween: 80
            },
            992: {
                slidesPerView: 5,
                spaceBetween: 120
            },
            1200: {
                slidesPerView: 6,
                spaceBetween: 120
            }
        }
    });
    const swiper2 = new Swiper('.init-swiper2', {
        loopFillGroupWithBlank: true,
        loop: true,
        speed: 600,
        autoplay: {
            delay: 5000,
        },
        slidesPerView: 'auto',
        pagination: {
            el: '.swiper-pagination',
            type: 'bullets',
            clickable: true,
        },
        breakpoints: {
            320: {
                slidesPerView: 2,
                spaceBetween: 40
            },
            480: {
                slidesPerView: 3,
                spaceBetween: 60
            },
            640: {
                slidesPerView: 4,
                spaceBetween: 80
            },
            992: {
                slidesPerView: 5,
                spaceBetween: 120
            },
            1200: {
                slidesPerView: 6,
                spaceBetween: 120
            }
        }
    });
</script>
@endpush
@endsection
