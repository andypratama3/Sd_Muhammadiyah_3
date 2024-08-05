@extends('layouts.user')
@section('title', 'Home')
@push('meta_user')
<meta name="description"
    content="Sekolah Kreatif Muhammadiyah 3 Samarinda - Komitmen pada pembelajaran inovatif dan pengembangan karakter siswa.">
<meta name="keywords" content="Sekolah, Kreatif, Muhammadiyah, Samarinda, Pendidikan, SD, Pembelajaran Inovatif">
<meta property="og:title" content="Sekolah Kreatif Muhammadiyah 3 Samarinda">
<meta property="og:description" content="Pembelajaran inovatif dan pengembangan karakter siswa.">
<meta property="og:image" content="{{ asset('asset/img/SD3_logo.png') }}">
@endpush
@push('css_user')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
<style>
    .about .swiper {
        padding: 10px 0;
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
                        <div class="text-sm-center text-md-start">
                            <h3 class="text-white text-uppercase fw-bold mb-2 mt-2">SD MUHAMMADIYAH 3 SAMARINDA </h3>
                            <h4 class="display-2 text-white mb-4">{{ $hero->name }}</h4>
                            <p class="mb-5 fs-5">{{ $hero->desc }}
                            </p>
                            <div class="d-flex justify-content-center justify-content-md-start flex-shrink-0 mb-4">
                                @if($hero->youtube != null)
                                <a class="btn btn-light rounded-pill py-3 px-4 px-md-5 me-2"
                                    href="{{ $hero->youtube }}"><i class="fas fa-play-circle me-2"></i> Watch Video</a>
                                @endif
                                @if($hero->link != null)
                                <a class="btn btn-dark rounded-pill py-3 px-4 px-md-5 ms-2"
                                    href="{{ $hero->link }}">Kunjungi Halaman</a>
                                @endif

                            </div>
                        </div>
                    </div>
                    <div class="col-lg-5 animated fadeInRight">
                        <div class="calrousel-img" style="object-fit: cover;">
                            <img src="{{ asset('asset/img/SD3_logo.png') }}" class="img-fluid w-50" alt="">
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
        <div class="text-center mx-auto pb-5 wow fadeInRight" data-wow-delay="0.2s" style="max-width: 800px;">
            <h4 class="text-primary">SD MUHAMMADIYAH 3 SAMARINDA</h4>
            <h1 class="display-4">Program Unggulan</h1>
        </div>
        <div class="row g-4 wow  fadeInUp" data-wow-delay="0.2s">
            <div class="col-md-8 float-end">
                <h4 class="text-dark"><i class="fa fa-check text-primary me-3"></i>Tahifdz Al - Qur'an 2 Juz (29 - 30)
                </h4>
                <h4 class="text-dark"><i class="fa fa-check text-primary me-3"></i>Pembiasaan Akhlak Iskami Sejak Dini
                </h4>
                <h4 class="text-dark"><i class="fa fa-check text-primary me-3"></i>Pembiasaan Sholat Wajib dan Sunnah
                </h4>
                <h4 class="text-dark"><i class="fa fa-check text-primary me-3"></i>Pembiasaan Ngaji Morning Metode
                    Tilawati</h4>
                <h4 class="text-dark"><i class="fa fa-check text-primary me-3"></i>Pembiasaan Menulis Al - Qur'an Dengan
                    Metode IMLA</h4>
                <h4 class="text-dark"><i class="fa fa-check text-primary me-3"></i>Pembinaan Psikologi Untuk Mengetahui
                    Minat & Bakat Anak</h4>
                <div class="form-group">
                    <h4 class="text-dark"><i class="fa fa-check text-primary me-3"></i>Pembelajaran Berbasis Edutainment
                        <br></h4>
                    <p class="text-dark" style="margin-left: 30px; margin-top: -10px; margin-bottom: 0;">( Belajar
                        Menyenangkan Dengan Menyeimbankan Otak Kanan Dan Kiri )</p>
                </div>
                <h4 class="text-dark"><i class="fa fa-check text-primary me-3"></i>Lulus Dengan 3 Ijazah</h4>
            </div>
            <div class="col-md-4">
                <img src="{{ asset('asset/img/sekolah-penggerak.jpeg') }}" class="img-fluid" alt=""
                    style="object-fit: contain !important; mix-blend-mode: multiply; margin-top: 20px;">
            </div>
        </div>
        <div class="text-center mx-auto pb-5 wow fadeInUp" data-wow-delay="0.2s" style="max-width: 800px;">
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
                <div class="bg-white rounded-0 p-3 h-100 mt-5">
                    <h1 class="display-4 mb-4 mt-0">Aktivitas Kami</h1>
                    <p class="text-primary">Aktivitas SD Muhammadiyah 3 Samarinda</p>
                    <div class="row g-4">
                        @foreach ($gallerys as $gallery)
                        <div class="col-md-3">
                            <a href="{{ asset('storage/img/gallery/'. $gallery->foto) }}"
                                data-lightbox="{{ $gallery->foto }}" class="">
                                <div class="card">
                                    <div class="card-body">
                                        <img src="{{ asset('storage/img/gallery/'. $gallery->foto) }}" alt=""
                                            class="img-fluid ">
                                        <h4 class="card-title">{{ $gallery->name }}</h4>
                                    </div>
                                </div>
                            </a>
                        </div>
                        @endforeach
                        <div class="col-md-12">
                            <a href="{{ route('gallery.index') }}" class="btn btn-primary">Lihat Semua</a>
                        </div>
                    </div>
                    {{-- <h4 class="text-primary">SD Muhammadiyah 3 Samarinda</h4> --}}
                </div>
            </div>


        </div>
    </div>
</div>
<!-- Feature End -->

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
            <div class="bg-white rounded pt-3 pb-5 wow fadeInUp" data-wow-delay="0.2s">
                <div class="row g-4">
                    <div class="d-flex justify-content-center">
                        <div class="col-md-4">
                            <div class="card bg-primary border-0">
                                <h6 class="text-black text-center mt-2">Cooperation And Support</h6>
                            </div>
                        </div>
                    </div>

                    <div class="swiper init-swiper">
                        <div class="swiper-wrapper align-items-center">
                            <div class="swiper-slide"><img src="{{ asset('asset/img/client-1.png') }}" class="img-fluid"
                                    alt=""></div>
                            <div class="swiper-slide"><img src="{{ asset('asset/img/client-1.png') }}" class="img-fluid"
                                    alt=""></div>
                            <div class="swiper-slide"><img src="{{ asset('asset/img/client-1.png') }}" class="img-fluid"
                                    alt=""></div>
                            <div class="swiper-slide"><img src="{{ asset('asset/img/client-1.png') }}" class="img-fluid"
                                    alt=""></div>
                            <div class="swiper-slide"><img src="{{ asset('asset/img/client-1.png') }}" class="img-fluid"
                                    alt=""></div>
                            <div class="swiper-slide"><img src="{{ asset('asset/img/client-1.png') }}" class="img-fluid"
                                    alt=""></div>
                            <div class="swiper-slide"><img src="{{ asset('asset/img/client-1.png') }}" class="img-fluid"
                                    alt=""></div>
                            <div class="swiper-slide"><img src="{{ asset('asset/img/client-1.png') }}" class="img-fluid"
                                    alt=""></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded pt-3 pb-5 wow fadeInUp" data-wow-delay="0.2s">
                <div class="row g-4">
                    <div class="d-flex justify-content-center">
                        <div class="col-md-4">
                            <div class="card bg-primary border-0 ">
                                <h6 class="text-black text-center mt-2">Achivement</h6>
                            </div>
                        </div>
                    </div>
                    <div class="swiper init-swiper ">
                        <div class="swiper-wrapper justify-content-center align-items-center">
                            <div class="swiper-slide"><img src="{{ asset('asset/img/client-1.png') }}" class="img-fluid"
                                    alt=""></div>
                            <div class="swiper-slide"><img src="{{ asset('asset/img/client-1.png') }}" class="img-fluid"
                                    alt=""></div>
                            <div class="swiper-slide"><img src="{{ asset('asset/img/client-1.png') }}" class="img-fluid"
                                    alt=""></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- About End -->

<!-- FAQs Start -->
<div class="container-fluid faq-section bg-light py-2">
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
            <div class="col-xl-6 col-md-12  wow fadeInRight" data-wow-delay="0.4s">
                <a href="{{ asset('asset/img/carousel-2.png')}}" data-lightbox="kepala-sekolah">
                    <img src="{{ asset('asset/img/kepala-sekolah.jpeg') }}" class="img-fluid w-40 mb-2"
                        alt="kepala-sekolah">
                </a>
                <h4 class="text-center" data-wow-delay="0.4s">Gr. Ansar HS. S.Pd.,M.M. </h4>
                <p class="text-center text-primary" data-wow-delay="0.4s">Kepala Sekolah SD Muhammadiyah 3 Samarinda</p>

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
<div class="container-fluid testimonial pb-5">
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
</div>
<!-- Testimonial End -->


@push('js_user')
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
    const swiper_1 = new Swiper('.init-swiper', {
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
    const swiper2 = new Swiper('.init-swiper', {
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
