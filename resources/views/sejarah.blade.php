@extends('layouts.user')

@section('title', 'Profil Sekolah | SD Muhammadiyah 3 Samarinda')

@section('meta')
    <meta name="description" content="Profil lengkap SD Muhammadiyah 3 Samarinda. Sekolah Kreatif unggul dalam prestasi dan kokoh dalam iman.">
@endsection

@push('css_user')
    <link rel="stylesheet" href="{{ asset('asset_new/css/sejarah.css') }}">
@endpush

@section('content')
<!-- Enhanced Hero Section -->
<section class="hero-section" role="region" aria-label="Hero">
    <div class="container">
        <div class="row align-items-center">
            <div class="mb-4 col-lg-6 mb-lg-0">
                <div class="p-4 text-center profile-card">
                    <div class="school-logo">
                        <img src="{{ asset('asset_new/images/SD3_logo1.png') }}" alt="Logo SD Muhammadiyah 3 Samarinda" class="mb-3 img-fluid">
                    </div>
                    <h4 class="mb-2 fw-bold text-dark">SD Muhammadiyah 3 Samarinda</h4>
                    <div class="px-3 py-2 mb-3 badge bg-primary fs-6 rounded-pill">
                        <i class="fas fa-star me-1"></i>
                        Terakreditasi A
                    </div>
                    <p class="mb-0 text-muted">Sekolah Kreatif - Unggul dalam Prestasi, Kokoh dalam Iman</p>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="text-center old-school">
                    <img src="{{ asset('asset_new/images/foto_sekolah.jpeg') }}" alt="Foto SD Muhammadiyah 3" class="rounded img-fluid">
                    <div class="mt-3">
                        <span class="px-3 py-2 badge bg-light text-dark fs-6 rounded-pill">
                            <i class="fas fa-calendar-alt me-1"></i>
                            Sejak 1979 - {{ date('Y') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Enhanced Profile Section -->
<section id="profil" class="py-5" role="region" aria-label="Profil Sekolah">
    <div class="container">
        <div class="mb-5 row">
            <div class="col-lg-12">
                <div class="section-card animate-fade-in">
                    <div class="mb-4 d-flex align-items-center">
                        <div class="icon-box me-3">
                            <i class="text-black fas fa-history"></i>
                        </div>
                        <div class="container m-0">
                            <div style="height: 2px; background: linear-gradient(90deg, transparent, #2E8B57, transparent); margin: 1rem 0;"></div>
                            <h3 class="mb-0 fw-bold">Sejarah & Perjalanan</h3>
                            <div style="height: 2px; background: linear-gradient(90deg, transparent, #2E8B57, transparent); margin: 1rem 0;"></div>
                        </div>
                    </div>
                    <div class="row align-items-center">
                        <div class="mb-4 col-md-5 mb-md-0">
                            <div class="position-relative">
                                <img src="{{ asset('asset_new/images/old_foto.webp') }}" alt="Foto sekolah tahun 2010" class="shadow img-fluid rounded-3">
                                <div class="bottom-0 p-3 text-white bg-opacity-75 rounded-md position-absolute start-0 end-0 bg-dark rounded-bottom-3">
                                    <small><i class="fas fa-camera me-1"></i>Dokumentasi Tahun 2010</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-7">
                            <div class="mb-4 timeline-item">
                                <div class="mb-2 d-flex align-items-center">
                                    <span class="px-3 py-2 badge bg-primary rounded-pill me-3">Tahun 1979</span>
                                    <h6 class="mb-0 fw-bold">Pendirian Sekolah</h6>
                                </div>
                                <p class="text-muted small">
                                    SD Muhammadiyah 3 Samarinda didirikan pada tanggal 19 Agustus 1979 dengan SK Pendirian No. 3855/I-13/KTM-57/1979.
                                </p>
                            </div>
                            <div class="mb-4 timeline-item">
                                <div class="mb-2 d-flex align-items-center">
                                    <span class="px-3 py-2 badge bg-success rounded-pill me-3">Sampai Tahun Sekarang</span>
                                    <h6 class="mb-0 fw-bold">Prestasi Gemilang</h6>
                                </div>
                                <p class="text-muted small">
                                    Berlokasi di Jl. Dato Iba RT. 04, Sungai Keledang, Samarinda Seberang, telah menjadi institusi pendidikan dasar terkemuka di Kalimantan Timur dengan akreditasi A.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enhanced Statistics -->
        <div class="mb-5 row">
            <div class="mb-4 col-lg-3 col-md-6">
                <div class="text-center stats-card">
                    <i class="mb-3 fas fa-calendar-check fa-2x"></i>
                    <h2 class="fw-bold counter" data-target="{{ $pengalaman }}">{{ $pengalaman }}</h2>
                    <p class="mb-0">Tahun Pengalaman</p>
                </div>
            </div>
            <div class="mb-4 col-lg-3 col-md-6">
                <div class="text-center stats-card">
                    <i class="mb-3 fas fa-user-graduate fa-2x"></i>
                    <h2 class="fw-bold counter" data-target="{{ $alumni }}">{{ $alumni }}</h2>
                    <p class="mb-0">Alumni Sukses</p>
                    {{-- <a href="{{ route('siswa-lulus.index') }}" class="btn btn-primary btn-sm">Lihat</a> --}}
                </div>
            </div>
            <div class="mb-4 col-lg-3 col-md-6">
                <div class="text-center stats-card">
                    <i class="mb-3 fas fa-chalkboard fa-2x"></i>
                    <h2 class="fw-bold counter" data-target="18">0</h2>
                    <p class="mb-0">Ruang Kelas</p>
                </div>
            </div>
            <div class="mb-4 col-lg-3 col-md-6">
                <div class="text-center stats-card">
                    <i class="mb-3 fas fa-users fa-2x"></i>
                    <h2 class="fw-bold counter" data-target="{{ $siswas }}">{{ $siswas }}</h2>
                    <p class="mb-0">Siswa Aktif</p>
                </div>
            </div>
        </div>

        <!-- Enhanced Vision & Mission -->
        <div class="mb-5 row">
            <div class="mb-4 col-lg-6">
                <div class="vision-mission animate-fade-in">
                    <div class="mb-3 d-flex align-items-center">
                        <div class="icon-box me-3" style="background: rgba(255,255,255,0.2);">
                            <i class="text-white fas fa-eye"></i>
                        </div>
                        <h4 class="mb-0 text-white fw-bold">Visi Kami</h4>
                    </div>
                    <p class="text-white">Sesuai dengan prinsip – prinsip pengembangan dan acuan operasional penyusunan Kurikulum Tingkat Satuan Pendidikan maka, Visi sekolah SD Muhammadiyah 3 Samarinda adalah sebagai berikut: <br>
                        <strong class="text-white">“Terwujudnya Siswa Hafidz/Hafidzah Yang Beriman Bertaqwa Kepada Allah SWT Berakhlak Mulia, Cerdas, Aktif, Kreatif, Berbudaya Lingkungan Serta Unggul Dalam Prestasi Demi Terwujudnya Masyarakat Islam Yang Sebenar – benarnya ”</strong>
                    </p>
                </div>
            </div>
            <div class="mb-4 col-lg-6">
                <div class="section-card animate-fade-in">
                    <div class="mb-3 d-flex align-items-center">
                        <div class="icon-box me-3">
                            <i class="fas fa-bullseye text-success"></i>
                        </div>
                        <h4 class="mb-0 fw-bold">Misi Kami</h4>
                    </div>
                    <ul class="mb-0 list-unstyled">
                        <li class="mb-2">
                            <p class="text-dark"><i class="fas fa-check-circle text-success me-2"></i>Membentuk Siswa Siswi hafidz dan hafidzah melalui progam tahfidz dan program penanaman iman dan taqwa sejak dini.</p>
                        </li>
                        <li class="mb-2">
                            <p class="text-dark"><i class="fas fa-check-circle text-success me-2"></i>Membentuk Siswa Siswi yang cerdas melalui program edutainment dengan meningkatkan sarana prasarana pendidikan yang mendukung pengembangan kecerdasan Siswa sesuai potensi Siswa.</p>
                        </li>
                        <li class="mb-2">
                            <p class="text-dark"><i class="fas fa-check-circle text-success me-2"></i>Membentuk Siswa yang kreatif melalui program pengembangan ekstrakurikuler Sekolah sesuai minat bakat Siswa.</p>
                        </li>
                        <li class="mb-2">
                            <p class="text-dark"><i class="fas fa-check-circle text-success me-2"></i> Melakukan upaya melindungi dan megelola lingkungan hidup dengan program Adiwiyata di Sekolah.</p>
                        </li>
                        <li class="mb-2">
                            <p class="text-dark"><i class="fas fa-check-circle text-success me-2"></i>Membentuk Siswa yang berprestasi dengan program pengembangan kemampuan anak dibidang masing-masing sejak dini.</p>
                        </li>
                        <li class="mb-0">
                            <p class="text-dark"><i class="fas fa-check-circle text-success me-2"></i>Membentuk kebiasaan-kebiasaan warga Sekolah yang islami demi terwujudnya masyarakat islam yang sebenar-benarnya sesuai dengan tujuan Muhammadiyah.</p>
                        </li>
                    </ul>
                </div>
            </div>
             <div class="mb-4 col-lg-12">
                <div class="mt-3 vision-mission animate-fade-in">
                    <div class="mb-3 d-flex align-items-center">
                        <div class="icon-box me-3" style="background: rgba(255, 255, 255, 0.2);">
                            <i class="text-white fas fa-eye"></i>
                        </div>
                        <h4 class="mb-0 text-white fw-bold">Tujuan</h4>
                    </div>
                    <p class="text-white">TUJUAN <code>*</code></p>
                    <p class="text-white"><i class="fa fa-check text-primary me-3"></i>Terwujudnya Siswa Siswi hafidz dan hafidzah melalui progam tahfidz dan program penanaman iman dan taqwa sejak dini.</p>
                    <p class="text-white"><i class="fa fa-check text-primary me-3"></i>Terwujudnya Siswa Siswi yang cerdas melalui program edutainment dengan meningkatkan sarana prasarana pendidikan yang mendukung pengembangan kecerdasan Siswa sesuai potensi Siswa.</p>
                    <p class="mb-4 text-white"><i class="fa fa-check text-primary me-3"></i> Terwujudnya Siswa yang kreatif melalui program pengembangan ekstrakurikuler sekolah sesuai minat bakat Siswa.</p>
                    <p class="mb-4 text-white"><i class="fa fa-check text-primary me-3"></i> Terwujudnya upaya melindungi dan megelola lingkungan hidup dengan program Adiwiyata di Sekolah.</p>
                    <p class="mb-4 text-white"><i class="fa fa-check text-primary me-3"></i> Terwujudnya Siswa yang berprestasi dengan program pengembangan kemampuan anak dibidang masing-masing sejak dini.</p>
                    <p class="mb-4 text-white"><i class="fa fa-check text-primary me-3"></i> Terwujudnya kebiasaan-kebiasaan warga Sekolah yang islami demi terwujudnya masyarakat islam yang sebenar-benarnya sesuai dengan tujuan Muhammadiyah.</p>
                </div>
            </div>

        </div>

        <!-- Enhanced Facilities Section -->
        <div class="mb-5 row">
            <div class="col-12">
                <div class="section-card animate-fade-in">
                    <div class="mb-5 text-center">
                        <h3 class="mb-3 fw-bold">Fasilitas Unggulan</h3>
                        <p class="text-muted">Fasilitas modern dan lengkap untuk mendukung pembelajaran optimal</p>
                    </div>
                    <div class="row g-4">
                        @php
                            $facilities = [
                                // ['icon' => 'laptop', 'title' => 'Lab. Komputer', 'desc' => 'Teknologi pembelajaran digital terdepan', 'color' => '#f093fb'],
                                ['icon' => 'book', 'title' => 'Perpustakaan Digital', 'desc' => 'Koleksi buku fisik dan digital lengkap', 'color' => '#56ab2f'],
                                ['icon' => 'mosque', 'title' => 'Musholla Nyaman', 'desc' => 'Tempat ibadah', 'color' => '#ff6b6b'],
                                ['icon' => 'running', 'title' => 'Lapangan Olahraga', 'desc' => 'Fasilitas olahraga dan aktivitas fisik', 'color' => '#4ecdc4'],
                                // ['icon' => 'paint-brush', 'title' => 'Studio Seni', 'desc' => 'Ruang kreativitas dan pengembangan bakat', 'color' => '#45b7d1'],
                                ['icon' => 'utensils', 'title' => 'Kantin Sehat', 'desc' => 'Makanan bergizi dan higienis tersertifikasi', 'color' => '#f39c12'],
                                ['icon' => 'shield-alt', 'title' => 'Keamanan 24/7', 'desc' => 'Sistem keamanan terintegrasi dan CCTV', 'color' => '#e74c3c']
                            ];
                        @endphp
                        @foreach($facilities as $index => $facility)
                        <div class="col-lg-3 col-md-6" style="animation-delay: {{ $index * 0.1 }}s;">
                            <div class="facility-item">
                                <i class="fas fa-{{ $facility['icon'] }} fa-2x mb-3" style="color: {{ $facility['color'] }};"></i>
                                <h6 class="mb-2 fw-bold">{{ $facility['title'] }}</h6>
                                <small class="text-muted">{{ $facility['desc'] }}</small>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Enhanced Call to Action -->
        <div class="row">
            <div class="col-12">
                <div class="text-center section-card animate-fade-in" style="background: linear-gradient(135deg, #2E8B57, #1B5E20); color: white; position: relative; overflow: hidden;">
                    <div style="position: absolute; top: -50%; right: -50%; width: 200%; height: 200%; background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%); animation: rotate 20s linear infinite;"></div>
                    <div class="position-relative" style="z-index: 2;">
                        <i class="mb-4 fas fa-graduation-cap fa-3x"></i>
                        <h3 class="mb-3 fw-bold">Bergabunglah dengan Keluarga Besar SD Muhammadiyah 3 Samarinda</h3>
                        <p class="mb-4 fs-5">Wujudkan impian pendidikan terbaik untuk putra-putri Anda di Sekolah Kreatif kami</p>
                        <div class="flex-wrap gap-3 mb-4 d-flex justify-content-center">
                            <a href="{{ route('spmb.index') }}" class="px-4 py-3 btn btn-light btn-lg rounded-pill">
                                <i class="fas fa-user-plus me-2"></i>Pendaftaran Siswa Baru
                            </a>
                            <a href="https://api.whatsapp.com/send?phone=6285250443151" class="px-4 py-3 btn btn-outline-light btn-lg rounded-pill">
                                <i class="fas fa-phone me-2"></i>Hubungi Kami
                            </a>
                        </div>
                        <div class="row justify-content-center">
                            <div class="col-md-8">
                                <div class="flex-wrap gap-4 d-flex justify-content-center align-items-center">
                                    {{-- <div class="text-center">
                                        <i class="mb-2 fas fa-map-marker-alt fa-lg"></i>
                                        <p class="mb-0 small">Jl. Dato Iba RT. 04, Sungai Keledang<br>Samarinda Seberang, Kota Samarinda</p>
                                    </div> --}}
                                    <div class="text-center">
                                        {{-- <i class="mb-2 fas fa-envelope fa-lg"></i> --}}
                                        <p class="mb-0 small">Jam Operasional: <br>Senin - Jumat: 07:00 - 15:00</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


@push('js_user')
    <script src="{{ asset('asset_new/js/sejarah.js') }}"></script>
@endpush
@endsection
