@extends('layouts.user')

@section('title', 'Beranda')

@push('meta_user')
    <meta name="description" content="Sekolah Kreatif Muhammadiyah 3 Samarinda menawarkan pembelajaran inovatif, pendidikan berbasis karakter, dan lingkungan belajar yang menyenangkan untuk siswa.">
    <meta name="keywords" content="Sekolah Kreatif Muhammadiyah 3 Samarinda, SD Muhammadiyah 3 Samarinda, Sekolah Islam Samarinda, Sekolah Dasar Islam Kreatif, Pendidikan Karakter Samarinda, Sekolah Dasar Inovatif, SD Islam Terbaik, Sekolah Dasar Terdekat Samarinda Seberang, Sekolah Swasta Samarinda, Pendaftaran SD Muhammadiyah, SD Unggulan Samarinda, Sekolah Kreatif Samarinda, SD Muhammadiyah Keren, Sekolah Dasar Favorit di Samarinda">

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
    /* PHONE CARD */
    .custom-phone-card {
        width: 210px;
        height: 400px;
        background: black;
        border-radius: 35px;
        border: 2px solid rgb(40, 40, 40);
        padding: 7px;
        position: relative;
        box-shadow: 2px 5px 15px rgba(0, 0, 0, 0.486);
        overflow: hidden;
        margin: auto;
    }

    .card-int {
        background-size: 200% 200%;
        background-position: 0% 0%;
        height: 100%;
        border-radius: 25px;
        transition: all 0.6s ease-out;
        overflow: hidden;
        position: relative;
    }

    .card-int video {
        position: absolute;
        top: 50%;
        left: 50%;
        width: 100%;
        height: 100%;
        object-fit: cover;
        transform: translate(-50%, -50%);
        border-radius: inherit;
        z-index: 0;
    }

    .custom-phone-card:hover .card-int {
        background-position: 100% 100%;
    }

    .custom-top {
        position: absolute;
        top: 0;
        right: 50%;
        transform: translate(50%, 0%);
        width: 35%;
        height: 18px;
        background-color: black;
        border-bottom-left-radius: 10px;
        border-bottom-right-radius: 10px;
        z-index: 2;
    }

    .speaker {
        position: absolute;
        top: 2px;
        right: 50%;
        transform: translate(50%, 0%);
        width: 40%;
        height: 2px;
        border-radius: 2px;
        background-color: rgb(20, 20, 20);
    }

    .camera {
        position: absolute;
        top: 6px;
        right: 84%;
        transform: translate(50%, 0%);
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background-color: rgba(255, 255, 255, 0.048);
    }

    .int {
        position: absolute;
        width: 3px;
        height: 3px;
        border-radius: 50%;
        top: 50%;
        right: 50%;
        transform: translate(50%, -50%);
        background-color: rgba(255, 255, 255, 0.212);
    }

    .btn1,
    .btn2,
    .btn3,
    .btn4 {
        position: absolute;
        width: 2px;
        background-image: linear-gradient(to right, #111111, #222222, #333333, #464646, #595959);
    }

    .btn1 {
        height: 45px;
        top: 30%;
        right: -4px;
    }

    .btn2,
    .btn3 {
        height: 30px;
        left: -4px;
        transform: scale(-1);
    }

    .btn2 {
        top: 26%;
    }

    .btn3 {
        top: 36%;
    }

    .hidden {
        display: block;
        opacity: 0;
        transition: all 0.3s ease-in;
    }

    .custom-phone-card:hover .hidden {
        opacity: 1;
    }

    .custom-phone-card:hover .hello {
        transform: translateY(-20px);
    }

    /* ABOUT WRAPPER */
    .wrapper {
        display: flex;
        flex-wrap: wrap;
        gap: 2rem;
        justify-content: center;
        align-items: center;
    }

    /* RESPONSIVE LAYOUT */
    .left-text,
    .right-text {
        margin: 20px;
        flex: 1 1 300px;
        max-width: 400px;
    }

    .feature-title {
        color: #ef3f3f;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.875rem;
    }

    .feature-text {
        color: #343a40;
        font-size: 0.95rem;
        line-height: 1.6;
    }

    .btn-dark-custom {
        display: inline-block;
        background-color: #000;
        color: #fff;
        padding: 0.5rem 1.25rem;
        border-radius: 50rem;
        text-decoration: none;
        margin-top: 1rem;
        transition: background-color 0.3s;
    }

    .btn-dark-custom:hover {
        background-color: #333;
    }


    .feature-title {
        color: #ef3f3f;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.875rem;
    }

    .feature-text {
        color: #343a40;
        font-size: 0.95rem;
        line-height: 1.6;
    }

    .btn-dark-custom {
        display: inline-block;
        background-color: #000;
        color: #fff;
        padding: 0.5rem 1.25rem;
        border-radius: 50rem;
        text-decoration: none;
        margin-top: 1rem;
        transition: background-color 0.3s;
    }

    .btn-dark-custom:hover {
        background-color: #333;
    }
   .star-rating {
    direction: rtl;
    display: flex;
    justify-content: center;
    font-size: 2.2rem; /* PERBESAR bintang */
    gap: 5px;
}

.star-rating input[type="radio"] {
    display: none;
}

.star-rating label {
    color: #ccc;
    cursor: pointer;
    transition: color 0.2s ease-in-out;
}

.star-rating input:checked ~ label,
.star-rating label:hover,
.star-rating label:hover ~ label {
    color: #ffc107;
}

    .feature-title {
        margin-top: 1rem;
        font-weight: 600;
        text-transform: uppercase;
        color: #ef3f3f;
        font-size: 0.9rem;
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
                            <h3 class="mt-2 mb-2 text-white text-uppercase fw-bold">SD MUHAMMADIYAH 3 SAMARINDA </h3>
                            <h4 class="mb-4 text-white display-2">{{ $hero->name }}</h4>
                            <p class="mb-5 fs-5">{{ $hero->desc }}
                            </p>
                            <div class="flex-shrink-0 mb-4 d-flex justify-content-center">
                                @if($hero->youtube != null)
                                <a class="px-4 py-3 btn btn-light rounded-pill px-md-5 me-2"
                                    href="{{ $hero->youtube }}" target="__blank"><i class="fas fa-play-circle me-2"></i> Watch Video</a>
                                @endif
                                @if($hero->link != null)
                                <a class="px-4 py-3 btn btn-dark rounded-pill px-md-5 ms-2"
                                    href="{{ $hero->link }}" target="__blank">Kunjungi Halaman</a>
                                @endif

                            </div>
                        </div>
                    </div>
                    <div class="col-lg-5 animated fadeInRight">
                        <div class="carousel-img carousel-custom">
                            <img src="{{ asset('asset_new/images/SD3_logo1.png') }}" class="img-fluid img-logo" alt="" style="width: 60%;" loading="lazy">
                            <div class="mx-4 jargon" style="display: flex; gap: 4px; font-family: times new roman; justify-content: center; margin-top: 15px;">
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
<div class="py-5 container-fluid feature bg-light">
    <div class="container py-5">
        <div class="container-prorgam-unggulan">
            <div class="pb-5 mx-auto text-center wow fadeInRight" data-wow-delay="0.2s" style="max-width: 800px;">
                <h3 class="text-primary">SD MUHAMMADIYAH 3 SAMARINDA</h3>
                <h1 class="display-4">Program Unggulan</h1>
            </div>
            <div class="row g-4 wow fadeInUp" data-wow-delay="0.2s">
                <div class="col-md-8 float-end">
                    <h4 class="text-dark d-flex align-items-center justify-content-start">
                        <i class="fa fa-check text-primary me-3"></i>
                        <p class="py-0 my-0">
                            Tahifdz Al - Qur'an 2 Juz (29 - 30)
                        </p>
                    </h4>
                    <h4 class="text-dark d-flex align-items-center justify-content-start">
                        <i class="fa fa-check text-primary me-3"></i>
                        <p class="py-0 my-0">
                            Pembiasaan Akhlak Iskami Sejak Dini
                        </p>
                    </h4>
                    <h4 class="text-dark d-flex align-items-center justify-content-start">
                        <i class="fa fa-check text-primary me-3"></i>
                        <p class="py-0 my-0">
                            Pembiasaan Sholat Wajib dan Sunnah
                        </p>
                    </h4>
                    <h4 class="text-dark d-flex align-items-center justify-content-start">
                        <i class="fa fa-check text-primary me-3"></i>
                        <p class="py-0 my-0">
                            Pembiasaan Ngaji Morning Metode Tilawati
                        </p>
                    </h4>
                    <h4 class="text-dark d-flex align-items-center justify-content-start">
                        <i class="fa fa-check text-primary me-3"></i>
                        <p class="py-0 my-0">Pembiasaan Menulis Al - Qur'an Dengan Metode IMLA</p>
                    </h4>
                    <h4 class="text-dark d-flex align-items-center justify-content-start">
                        <i class="fa fa-check text-primary me-3"></i>
                        <p class="py-0 my-0">Pembinaan Psikologi Untuk Mengetahui Minat & Bakat Anak</p>
                    </h4>
                    <h4 class="text-dark d-flex align-items-center justify-content-start">
                        <i class="fa fa-check text-primary me-3"></i>
                        <p class="py-0 my-0">Pembelajaran Berbasis Edutainment</p>
                    </h4>
                    <h4 class="my-0 text-dark d-flex">
                        <i class="fa fa-check text-primary me-3" style="opacity: 0;"></i>
                        <p class="text-dark" style="font-size: 16px;">
                            ( Belajar Menyenangkan Dengan Menyeimbankan Otak Kanan Dan Kiri )
                        </p>
                    </h4>
                    <h4 class="text-dark d-flex align-items-center justify-content-start">
                        <i class="fa fa-check text-primary me-3"></i>
                        <p class="py-0 my-0">Lulus Dengan 3 Ijazah</p>
                    </h4>
                </div>
                <div class="col-md-4">
                    <img src="{{ asset('asset/img/sekolah-penggerak.jpeg') }}" class="img-fluid image-custom-feature" alt="">
                </div>
            </div>
            <div class="pb-5 mx-auto text-center wow fadeInUp" data-wow-delay="0.2s" style="max-width: 800px;">
            </div>
        </div>

            <div class="row g-4">
                <div class="col-md-6 col-lg-6 col-xl-3 wow fadeInUp" data-wow-delay="0.2s">
                    <div class="p-4 pt-0 feature-item">
                        <div class="p-4 mb-4 feature-icon">
                            <i class="fa-solid fa-a fa-3x"></i>
                        </div>
                        <h4 class="mb-4" style="font-size: 22px;">AKREDITAS UNGGUL</h4>
                    </div>
                </div>
                <div class="col-md-6 col-lg-6 col-xl-3 wow fadeInUp" data-wow-delay="0.8s">
                    <div class="p-4 pt-0 feature-item">
                        <div class="p-4 mb-4 feature-icon">
                            <i class="fa fa-building fa-3x"></i>
                        </div>
                        <h4 class="mb-4">FASILITAS</h4>
                        <a class="px-4 py-2 btn btn-primary rounded-pill" href="{{ route('fasilitas.index') }}"
                            aria-label="lihat-fasilitas">Lihat</a>
                    </div>
                </div>
                <div class="col-md-6 col-lg-6 col-xl-3 wow fadeInUp" data-wow-delay="0.4s">
                    <div class="p-4 pt-0 feature-item">
                        <div class="p-4 mb-4 feature-icon">
                            <i class="fa fa-trophy fa-3x"></i>
                        </div>
                        <h4 class="mb-4">PRESTASI SISWA</h4>
                        <a class="px-4 py-2 btn btn-primary rounded-pill" href="{{ route('prestasi.siswa.index') }}"
                            aria-label="Lihat-prestasi Siswa">Lihat</a>
                    </div>
                </div>
                <div class="col-md-6 col-lg-6 col-xl-3 wow fadeInUp" data-wow-delay="0.6s">
                    <div class="p-4 pt-0 feature-item">
                        <div class="p-4 mb-4 feature-icon">
                            <i class="fa fa-school fa-3x"></i>
                        </div>
                        <h4 class="mb-4" style="font-size: 23px;">PRESTASI SEKOLAH</h4>
                        <a class="px-4 py-2 btn btn-primary rounded-pill" href="{{  route('prestasi.sekolah.index') }}"
                            aria-label="Lihat-prestasi Sekolah">Lihat</a>
                    </div>
                </div>
                <div class="mx-auto mt-3 text-center wow fadeInRight" data-wow-delay="0.2s">
                    <div class="p-3 mt-5 bg-white rounded-0 h-100" style="border-radius: 10px !important;">
                        <h1 class="mt-0 mb-4 display-4">Aktivitas Kami</h1>
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
                                                <h5 class="card-title">{{ Str::limit($gallery->name, 30) }}</h5>
                                                <a href="{{ route('gallery.show', $gallery->slug) }}" class="btn btn-primary">Lihat</a>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="mt-3 col-md-12">
                                <a href="{{ route('gallery.index') }}" class="btn btn-primary">Lihat Semua</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    </div>
</div>
<!-- Feature End -->
<div class="pb-5 container-fluid bg-light about" id="feature">
  <div class="container p-3 pb-5 bg-white wow fadeInUp" data-wow-delay="0.2s" style="border-radius: 10px;">
    <div class="wrapper">


      <!-- Phone dengan video -->
      <div class="custom-phone-card">
        <div class="btn1"></div>
        <div class="btn2"></div>
        <div class="btn3"></div>
        <div class="btn4"></div>

        <div class="card-int">
          <video src="{{ asset('asset/video/opening1.mp4') }}"  controls
                {{-- autoplay --}}
                muted
                loop
                playsinline>
            </video>
        </div>

        <div class="custom-top">
          <div class="camera">
            <div class="int"></div>
          </div>
          <div class="speaker"></div>
        </div>
      </div>

      <!-- Kanan -->
     <div class="right-text">
        <p class="feature-title">Informasi Lengkap Sekolah</p>
        <p class="feature-text">
            Website menyediakan profil sekolah, data guru, galeri kegiatan, jadwal pelajaran, serta berbagai informasi penting lainnya yang mudah diakses oleh orang tua dan siswa.
        </p>

        <p class="feature-title">Pembayaran Digital</p>
        <p class="feature-text">
            Dilengkapi sistem pembayaran online melalui Midtrans, termasuk fitur virtual account dan Snap, memudahkan orang tua dalam membayar biaya sekolah secara aman dan transparan.
        </p>

        <p class="feature-title">Pemberitahuan Pembayaran via WhatsApp</p>
        <p class="feature-text">
            Fitur pemberitahuan pembayaran via WhatsApp memudahkan orang tua menerima notifikasi tagihan dan melakukan pembayaran dengan kode QR.


    </div>
  </div>
</div>

<!-- About Start -->
<div class="pb-5 container-fluid bg-light about" id="tentang">
    <div class="container pb-5">
        <div class="row g-5">
            <div class="col-xl-6 wow fadeInLeft" data-wow-delay="0.2s">
                <div class="p-5 bg-white rounded about-item-content h-100">
                    <h4 class="text-primary">TENTANG SD MUHAMMADIYAH 3 SAMARINDA</h4>
                    <h1 class="mb-4 display-4">Pembelajaran Inovatif dan Pengembangan Karakter</h1>
                    <p>Sekolah Kreatif Muhammadiyah 3 Samarinda berkomitmen untuk menyediakan lingkungan belajar yang
                        dinamis dan menarik. Misi kami adalah untuk menumbuhkan kreativitas dan cinta belajar pada
                        setiap siswa. Dengan fokus pada pengembangan holistik, kami memastikan bahwa siswa kami unggul
                        secara akademis dan tumbuh menjadi individu yang berkarakter.</p>
                    <a href="{{ route('profil.index') }}" class="btn btn-primary">Profil Selengkapnya</a>
                </div>
            </div>
            <div class="col-xl-6 wow fadeInRight" data-wow-delay="0.2s">
                <div class="p-5 bg-white rounded h-100">
                    <div class="row g-4 justify-content-center">
                        <div class="col-12">
                            <div class="rounded bg-light">
                                <img src="{{ asset('asset/img/carousel-2.png') }}" class="rounded img-fluid w-100"
                                    alt="">
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="p-3 rounded counter-item bg-light h-100">
                                <div class="counter-counting">
                                    <span class="text-primary fs-2 fw-bold"
                                        data-toggle="counter-up">{{ $siswas }}</span>
                                    <span class="h1 fw-bold text-primary">+</span>
                                </div>
                                <h4 class="mb-0 text-dark">Siswa</h4>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="p-3 rounded counter-item bg-light h-100">
                                <div class="counter-counting">
                                    <span class="text-primary fs-2 fw-bold"
                                        data-toggle="counter-up">{{ $prestasis_siswa }}</span>
                                    <span class="h1 fw-bold text-primary">+</span>
                                </div>
                                <h4 class="mb-0 text-dark">Prestasi Siswa</h4>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="p-3 rounded counter-item bg-light h-100">
                                <div class="counter-counting">
                                    <span class="text-primary fs-2 fw-bold"
                                        data-toggle="counter-up">{{ $prestasis_sekolah }}</span>
                                    <span class="h1 fw-bold text-primary">+</span>
                                </div>
                                <h4 class="mb-0 text-dark">Prestasi Sekolah </h4>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="p-3 rounded counter-item bg-light h-100">
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
            <div class="pt-3 pb-5 bg-white rounded wow fadeInUp cooperation-item" data-wow-delay="0.2s">
                <div class="row g-4">
                    <div class="d-flex justify-content-center">
                        <div class="col-md-4">
                            <div class="border-0 card bg-primary cooperation">
                                <h6 class="mt-2 text-center text-black">Dukungan Dan Kerja Sama</h6>
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

            <div class="pt-3 pb-5 bg-white rounded wow fadeInUp achivement-item" data-wow-delay="0.2s">
                <div class="row g-4">
                    <div class="d-flex justify-content-center">
                        <div class="col-md-4">
                            <div class="border-0 card bg-primary ">
                                <h6 class="mt-2 text-center text-black">Penghargaan</h6>
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
<div class="py-2 container-fluid faq-section bg-light" data-wow-delay="0.4s">
    <div class="container py-5">
        <div class="row g-5 align-items-center justify-content-center">
            <div class="col-xl-6 wow fadeInLeft" data-wow-delay="0.2s">
                <div class="h-100">
                    <div class="mb-5">
                        {{-- <h4 class="text-primary"></h4> --}}
                        <h1 class="mb-0 display-5">Kepala Sekolah
                            SD Muhammadiyah 3 Samarinda
                        </h1>
                    </div>
                </div>
            </div>
            <div class="p-4 col-lg-6 col-md-12 wow fadeInRight">
                <a href="{{ asset('asset/img/kepala-sekolah.jpeg')}}" data-lightbox="kepala-sekolah" >
                    <img src="{{ asset('asset/img/kepala-sekolah.jpeg') }}" class="mb-2 img-fluid img-header-kepala-sekolah"
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
<div class="py-5 container-fluid service">
    <div class="container py-5">
        <div class="pb-5 mx-auto text-center wow fadeInUp" data-wow-delay="0.2s" style="max-width: 800px;">
            <h4 class="text-primary">Prestasi Terakhir</h4>
            <h1 class="mb-4 display-4">Prestasi Terakhir Sang Juara</h1>
        </div>
        <div class="row g-4 justify-content-center">
            @foreach ($prestasi_terakhir as $prestasi)
            <div class="col-md-6 col-lg-6 col-xl-3 wow fadeInUp" data-wow-delay="0.2s">
                <div class="service-item">
                    <div class="service-img">
                        <img src="{{ asset('storage/img/prestasi/'. $prestasi->foto)}}"
                            class="img-fluid rounded-top w-100" alt="">
                        <div class="p-3 service-icon">
                            <i class="fa fa-users fa-2x"></i>
                        </div>
                    </div>
                    <div class="p-4 service-content">
                        <div class="service-content-inner">
                            <a href="#" class="mb-4 d-inline-block h5">{{ Str::limit($prestasi->name, 30) }}</a>
                            <a class="px-4 py-2 btn btn-primary rounded-pill"
                                href="{{ route('prestasi.siswa.show', $prestasi->slug) }}">Lihat</a>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach

            <div class="text-center col-12 wow fadeInUp" data-wow-delay="0.2s">
                <a class="px-5 py-3 mb-2 btn btn-primary rounded-pill" href="{{ route('prestasi.sekolah.index') }}">Lihat
                    Prestasi Sekolah</a>
                <a class="px-5 py-3 mb-2 btn btn-primary rounded-pill" href="{{ route('prestasi.siswa.index') }}">Lihat
                    Prestasi Siswa</a>
            </div>
        </div>
    </div>
</div>
<!-- Service End -->
<!-- Testimonial Start -->
{{-- <div class="pb-5 container-fluid testimonial">
    <div class="container pb-5">
        <div class="pb-5 mx-auto text-center wow fadeInUp" data-wow-delay="0.2s" style="max-width: 800px;">
            <h4 class="text-primary">Testimonial</h4>
            <h1 class="mb-4 display-4">What Our Customers Are Saying</h1>
            <p class="mb-0">Lorem ipsum dolor, sit amet consectetur adipisicing elit. Tenetur adipisci facilis
                cupiditate recusandae aperiam temporibus corporis itaque quis facere, numquam, ad culpa deserunt sint
                dolorem autem obcaecati, ipsam mollitia hic.
            </p>
        </div>
        <div class="owl-carousel testimonial-carousel wow fadeInUp" data-wow-delay="0.2s">
            <div class="rounded testimonial-item bg-light">
                <div class="row g-0">
                    <div class="col-4 col-lg-4 col-xl-3">
                        <div class="h-100">
                            <img src="{{ asset('asset/img/testimonial-1.jpg') }}" class="rounded img-fluid h-100"
                                style="object-fit: cover;" alt="">
                        </div>
                    </div>
                    <div class="col-8 col-lg-8 col-xl-9">
                        <div class="p-4 my-auto d-flex flex-column text-start">
                            <h4 class="mb-0 text-dark">Client Name</h4>
                            <p class="mb-3">Profession</p>
                            <div class="mb-3 d-flex text-primary">
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
            <div class="rounded testimonial-item bg-light">
                <div class="row g-0">
                    <div class="col-4 col-lg-4 col-xl-3">
                        <div class="h-100">
                            <img src="{{ asset('asset/img/testimonial-2.jpg') }}" class="rounded img-fluid h-100"
                                style="object-fit: cover;" alt="">
                        </div>
                    </div>
                    <div class="col-8 col-lg-8 col-xl-9">
                        <div class="p-4 my-auto d-flex flex-column text-start">
                            <h4 class="mb-0 text-dark">Client Name</h4>
                            <p class="mb-3">Profession</p>
                            <div class="mb-3 d-flex text-primary">
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
            <div class="rounded testimonial-item bg-light">
                <div class="row g-0">
                    <div class="col-4 col-lg-4 col-xl-3">
                        <div class="h-100">
                            <img src="{{ asset('asset/img/testimonial-3.jpg') }}" class="rounded img-fluid h-100"
                                style="object-fit: cover;" alt="">
                        </div>
                    </div>
                    <div class="col-8 col-lg-8 col-xl-9">
                        <div class="p-4 my-auto d-flex flex-column text-start">
                            <h4 class="mb-0 text-dark">Client Name</h4>
                            <p class="mb-3">Profession</p>
                            <div class="mb-3 d-flex text-primary">
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
        speed: 400,
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
