@extends('layouts.user_new')
@section('title', 'Home')

@push('css_user')

@endpush
@section('content')
    <div class="main-banner" id="top">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="owl-carousel owl-banner">
                        @foreach ($beritas as $berita)
                        <div class="item"
                            style="background-image: url('{{ asset('storage/img/berita/' . $berita->foto) }}'); background-size: cover; loading: lazy">
                            <div class="header-text">
                                <span class="category">Berita </span>
                                <h2>{{ $berita->judul }}</h2>
                                <p>{{ $berita->desc }}</p>
                                <div class="buttons">
                                    <div class="main-button">
                                        <a href="{{ route('berita.show', $berita->slug) }}">Lihat </a>
                                    </div>
                                    {{-- <div class="icon-button">
                        <a href="#"><i class="fa fa-play"></i> What's Scholar?</a>
                    </div> --}}
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="services section" id="services">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 col-md-6">
                    <div class="service-item">
                        <div class="icon">
                            <i class="fa-solid fa-a fa-6x text-white mt-4" style="padding-top: 17px !important;"></i>
                        </div>
                        <div class="main-content">
                            <h4>AKREDITAS</h4>
                            <p>Sekolah Kreatif Muhammadiyah 3 Samarinda memegang akreditasi tinggi yang menegaskan bahwa kami memenuhi standar pendidikan yang sangat baik. Akreditasi ini mencerminkan upaya kami dalam menyediakan lingkungan belajar yang optimal, dengan pengajaran berkualitas tinggi, fasilitas yang memadai, dan pengelolaan yang efisien. Kami berkomitmen untuk terus meningkatkan kualitas pendidikan agar siswa kami siap menghadapi tantangan di masa depan.</p>
                            <div class="main-button">
                                {{-- <a href="#">Read More</a> --}}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="service-item">
                        <div class="icon">
                            <img src="{{ asset('asset_new/images/service-02.png') }}" alt="short courses">
                        </div>
                        <div class="main-content">
                            <h4>PRESTASI</h4>
                            <p>Siswa dan siswi Sekolah Kreatif Muhammadiyah 3 Samarinda telah meraih berbagai prestasi yang membanggakan baik di tingkat lokal maupun nasional. Kami bangga atas pencapaian mereka dalam kompetisi sains, seni, olahraga, serta digital marketing. Keberhasilan ini merupakan hasil dari pendekatan pembelajaran yang kreatif dan inovatif, yang dirancang untuk mengembangkan bakat dan keterampilan siswa secara menyeluruh.</p>
                            <div class="main-button">
                                <a href="{{ route('prestasi.siswa.index') }}" aria-label="Lihat Prestasi">Lihat</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="service-item">
                        <div class="icon">
                            <img src="{{ asset('asset_new/images/service-03.png') }}" alt="web experts">
                        </div>
                        <div class="main-content">
                            <h4>FASILITAS</h4>
                            <p>Sekolah Kreatif Muhammadiyah 3 Samarinda menyediakan berbagai </p>
                            <ol>
                                <li>Ruang kelas yang modern dan dilengkapi dengan teknologi terkini.</li>
                                <li>Laboratorium sains yang lengkap untuk eksperimen dan penelitian.</li>
                                <li>Perpustakaan dengan koleksi buku yang luas dan terbaru.</li>
                                <li>Lapangan olahraga yang luas untuk berbagai kegiatan fisik.</li>
                                <li>Area bermain yang aman dan nyaman untuk siswa.</li>
                            </ol>
                            <div class="main-button mt-4">
                                <a href="{{ route('fasilitas.index') }}" aria-label="Read More">Lihat</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="section about-us">
        <div class="container">
            <div class="row">
                <div class="col-lg-7 offset-lg-1">
                    <div class="accordion" id="accordionExample">
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingOne">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                    VISI & MISI
                                </button>
                            </h2>
                            <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne"
                                data-bs-parent="#accordionExample">
                                <div class="accordion-body">
                                    <p><strong>VISI <code>*</code></strong></p>
                                    <p>Sesuai dengan prinsip – prinsip pengembangan dan acuan operasional penyusunan Kurikulum Tingkat Satuan Pendidikan maka, Visi sekolah SD Muhammadiyah 3 Samarinda adalah sebagai berikut:</p>
                                    <p><strong>“Terwujudnya Siswa Hafidz/Hafidzah Yang Beriman Bertaqwa Kepada Allah SWT Berakhlak Mulia, Cerdas, Aktif, Kreatif, Berbudaya Lingkungan Serta Unggul Dalam Prestasi Demi Terwujudnya Masyarakat Islam Yang Sebenar – benarnya ”</strong></p>

                                    <p><strong>MISI <code>*</code></strong></p>
                                    <p> Misi SD Muhammadiyah 3 Samarinda adalah :</p>
                                    <ol>
                                        <li>1. Membentuk Siswa Siswi hafidz dan hafidzah melalui progam tahfidz dan program penanaman iman dan taqwa sejak dini.</li>
                                        <li>2. Membentuk Siswa Siswi yang cerdas melalui program edutainment dengan meningkatkan sarana prasarana pendidikan yang mendukung pengembangan kecerdasan Siswa sesuai potensi Siswa.</li>
                                        <li>3. Membentuk Siswa yang kreatif melalui program pengembangan ekstrakurikuler Sekolah sesuai minat bakat Siswa.</li>
                                        <li>4. Melakukan upaya melindungi dan megelola lingkungan hidup dengan program Adiwiyata di Sekolah.</li>
                                        <li>5. Membentuk Siswa yang berprestasi dengan program pengembangan kemampuan anak dibidang masing-masing sejak dini.</li>
                                        <li>6. Membentuk kebiasaan-kebiasaan warga Sekolah yang islami demi terwujudnya masyarakat islam yang sebenar-benarnya sesuai dengan tujuan Muhammadiyah.</li>
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingTwo">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                    Tujuan Sekolah
                                </button>
                            </h2>
                            <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo"
                                data-bs-parent="#accordionExample">
                                <div class="accordion-body">
                                    Tujuan Sekolah <strong>SD Muhammadiyah 3 Samarinda Samarinda</strong>
                                    <ol>
                                        <li>1. Terwujudnya Siswa Siswi hafidz dan hafidzah melalui progam tahfidz dan program penanaman iman dan taqwa sejak dini.</li>
                                        <li>2. Terwujudnya Siswa Siswi yang cerdas melalui program edutainment dengan meningkatkan sarana prasarana pendidikan yang mendukung pengembangan kecerdasan Siswa sesuai potensi Siswa.</li>
                                        <li>3. Terwujudnya Siswa yang kreatif melalui program pengembangan ekstrakurikuler sekolah sesuai minat bakat Siswa.</li>
                                        <li>4. Terwujudnya upaya melindungi dan megelola lingkungan hidup dengan program Adiwiyata di Sekolah.</li>
                                        <li>5. Terwujudnya Siswa yang berprestasi dengan program pengembangan kemampuan anak dibidang masing-masing sejak dini.</li>
                                        <li>6. Terwujudnya kebiasaan-kebiasaan warga Sekolah yang islami demi terwujudnya masyarakat islam yang sebenar-benarnya sesuai dengan tujuan Muhammadiyah.</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingThree">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                    Tujuan
                                </button>
                            </h2>
                            <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree"
                                data-bs-parent="#accordionExample">
                                <div class="accordion-body">
                                    There are more than one hundred responsive HTML templates to choose from
                                    <strong>Template</strong>Mo website. You can browse by different tags or categories.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingFour">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                                    Do we get the best support?
                                </button>
                            </h2>
                            <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour"
                                data-bs-parent="#accordionExample">
                                <div class="accordion-body">
                                    You can also search on Google with specific keywords such as <code>templatemo business
                                        templates, templatemo gallery templates, admin dashboard templatemo, 3-column
                                        templatemo, etc.</code>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 align-self-center">
                    <div class="section-heading">
                        <h6>Tentang</h6>
                        <h2 style="font-size: 22px;">SD Muhammadiyah 3 Samarinda</h2>
                        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut
                            labore et dolore magna aliqua. Quis ipsum suspendisse ultrices gravid risus commodo.</p>
                        <div class="main-button">
                            <a href="#">Discover More</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <section class="section courses" id="courses">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 text-center">
                    <div class="section-heading">
                        <h6>Prestasi Terakhir</h6>
                        <h2>Prestasi Terakhir</h2>
                    </div>
                </div>
            </div>
            <ul class="event_filter">
                <li>
                    <a class="is_active" href="#!" data-filter="*">Semua</a>
                </li>
                <li>
                    <a href="#!" data-filter=".1">Siswa</a>
                </li>
                <li>
                    <a href="#!" data-filter=".2">Sekolah</a>

                </li>
            </ul>
            <div class="row event_box">
                @foreach ($prestasis as $prestasi)
                <div class="col-lg-4 col-md-6 align-self-center mb-30 event_outer col-md-6 {{ $prestasi->status }}">
                    <div class="events_item">
                        <div class="thumb">
                            <a href="#"><img src="{{ asset('storage/img/prestasi/' . $prestasi->foto) }}" alt=""></a>
                            <span class="price">
                                <h6 style="font-size: 15px;">{{ $prestasi->status == 1 ? 'Siswa' : 'Sekolah' }}</h6>
                            </span>
                        </div>
                        <div class="down-content">
                            <span class="author">{{ $prestasi->status == 1 ? 'Siswa' : 'Sekolah' }}</span>
                            <h4>{{ $prestasi->name }}</h4>
                        </div>
                    </div>
                </div>
               @endforeach
            </div>
        </div>
    </section>

    <div class="section fun-facts">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="wrapper">
                        <div class="row">
                            <div class="col-lg-3 col-md-6">
                                <div class="counter">
                                    <h2 class="timer count-title count-number" data-to="{{ $siswas }}" data-speed="1000"></h2>
                                    <p class="count-text ">Total Siswa</p>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <div class="counter">
                                <h2 class="timer count-title count-number" data-to="{{ $guru }}" data-speed="1000"></h2>
                                    <p class="count-text ">Total Guru </p>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <div class="counter">
                                    <h2 class="timer count-title count-number" data-to="{{ $fasilitas }}" data-speed="1000"></h2>
                                    <p class="count-text ">Total Fasilitas</p>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <div class="counter end">
                                    <h2 class="timer count-title count-number" data-to="{{ $esktrakurikuler }}" data-speed="1000"></h2>
                                    <p class="count-text ">Total Esktrakurikuler</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

{{--
    <div class="section testimonials">
        <div class="container">
            <div class="row">
                <div class="col-lg-7">
                    <div class="owl-carousel owl-testimonials">
                        <div class="item">
                            <p>“Please tell your friends or collegues about TemplateMo website. Anyone can access the
                                website to download free templates. Thank you for visiting.”</p>
                            <div class="author">
                                <img src="{{ asset('asset_new/images/testimonial-author.jpg') }}" alt="">
                                <span class="category">Full Stack Master</span>
                                <h4>Claude David</h4>
                            </div>
                        </div>
                        <div class="item">
                            <p>“Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut
                                labore et dolore magna aliqua. Quis ipsum suspendisse ultrices gravid.”</p>
                            <div class="author">
                                <img src="{{ asset('asset_new/images/testimonial-author.jpg') }}" alt="">
                                <span class="category">UI Expert</span>
                                <h4>Thomas Jefferson</h4>
                            </div>
                        </div>
                        <div class="item">
                            <p>“Scholar is free website template provided by TemplateMo for educational related websites.
                                This CSS layout is based on Bootstrap v5.3.0 framework.”</p>
                            <div class="author">
                                <img src="{{ asset('asset_new/images/testimonial-author.jpg') }}" alt="">
                                <span class="category">Digital Animator</span>
                                <h4>Stella Blair</h4>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-5 align-self-center">
                    <div class="section-heading">
                        <h6>TESTIMONIALS</h6>
                        <h2>What they say about us?</h2>
                        <p>You can search free CSS templates on Google using different keywords such as templatemo
                            portfolio, templatemo gallery, templatemo blue color, etc.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="section events" id="events">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 text-center">
                    <div class="section-heading">
                        <h6>Schedule</h6>
                        <h2>Upcoming Events</h2>
                    </div>
                </div>
                <div class="col-lg-12 col-md-6">
                    <div class="item">
                        <div class="row">
                            <div class="col-lg-3">
                                <div class="image">
                                    <img src="{{ asset('asset_new/images/event-01.jpg') }}" alt="">
                                </div>
                            </div>
                            <div class="col-lg-9">
                                <ul>
                                    <li>
                                        <span class="category">Web Design</span>
                                        <h4>UI Best Practices</h4>
                                    </li>
                                    <li>
                                        <span>Date:</span>
                                        <h6>16 Feb 2036</h6>
                                    </li>
                                    <li>
                                        <span>Duration:</span>
                                        <h6>22 Hours</h6>
                                    </li>
                                    <li>
                                        <span>Price:</span>
                                        <h6>$120</h6>
                                    </li>
                                </ul>
                                <a href="#"><i class="fa fa-angle-right"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-12 col-md-6">
                    <div class="item">
                        <div class="row">
                            <div class="col-lg-3">
                                <div class="image">
                                    <img src="{{ asset('asset_new/images/event-02.jpg') }}" alt="">
                                </div>
                            </div>
                            <div class="col-lg-9">
                                <ul>
                                    <li>
                                        <span class="category">Front End</span>
                                        <h4>New Design Trend</h4>
                                    </li>
                                    <li>
                                        <span>Date:</span>
                                        <h6>24 Feb 2036</h6>
                                    </li>
                                    <li>
                                        <span>Duration:</span>
                                        <h6>30 Hours</h6>
                                    </li>
                                    <li>
                                        <span>Price:</span>
                                        <h6>$320</h6>
                                    </li>
                                </ul>
                                <a href="#"><i class="fa fa-angle-right"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-12 col-md-6">
                    <div class="item">
                        <div class="row">
                            <div class="col-lg-3">
                                <div class="image">
                                    <img src="{{ asset('asset_new/images/event-03.jpg') }}" alt="">
                                </div>
                            </div>
                            <div class="col-lg-9">
                                <ul>
                                    <li>
                                        <span class="category">Full Stack</span>
                                        <h4>Web Programming</h4>
                                    </li>
                                    <li>
                                        <span>Date:</span>
                                        <h6>12 Mar 2036</h6>
                                    </li>
                                    <li>
                                        <span>Duration:</span>
                                        <h6>48 Hours</h6>
                                    </li>
                                    <li>
                                        <span>Price:</span>
                                        <h6>$440</h6>
                                    </li>
                                </ul>
                                <a href="#"><i class="fa fa-angle-right"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div> --}}

{{-- </div> --}}
@push('js_user')
<script>
    $(document).ready(function () {
        // let owl = $('.owl-carousel');
        // owl.owlCarousel({
        //     items: 4,
        //     loop: true,
        //     margin: 10,
        //     autoplay: true,
        //     autoplayTimeout: 1000,
        //     autoplayHoverPause: true
        // });
        // $('.play').on('click', function () {
        //     owl.trigger('play.owl.autoplay', [1000])
        // })
        // $('.stop').on('click', function () {
        //     owl.trigger('stop.owl.autoplay');
        // });
    });
</script>
@endpush
@endsection
