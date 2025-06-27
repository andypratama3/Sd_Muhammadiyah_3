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
            <div class="col-lg-6 mb-4 mb-lg-0">
                <div class="profile-card p-4 text-center">
                    <div class="school-logo">
                        <img src="{{ asset('asset_new/images/SD3_logo1.png') }}" alt="Logo SD Muhammadiyah 3 Samarinda" class="img-fluid mb-3">
                    </div>
                    <h4 class="fw-bold text-dark mb-2">SD Muhammadiyah 3 Samarinda</h4>
                    <div class="badge bg-primary fs-6 mb-3 px-3 py-2 rounded-pill">
                        <i class="fas fa-star me-1"></i>
                        Terakreditasi A
                    </div>
                    <p class="text-muted mb-0">Sekolah Kreatif - Unggul dalam Prestasi, Kokoh dalam Iman</p>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="old-school text-center">
                    <img src="{{ asset('asset_new/images/foto_sekolah.jpeg') }}" alt="Foto SD Muhammadiyah 3" class="img-fluid rounded">
                    <div class="mt-3">
                        <span class="badge bg-light text-dark fs-6 px-3 py-2 rounded-pill">
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
        <div class="row mb-5">
            <div class="col-lg-12">
                <div class="section-card animate-fade-in">
                    <div class="d-flex align-items-center mb-4">
                        <div class="icon-box me-3">
                            <i class="fas fa-history text-black"></i>
                        </div>
                        <div class="container m-0">
                            <div style="height: 2px; background: linear-gradient(90deg, transparent, #2E8B57, transparent); margin: 1rem 0;"></div>
                            <h3 class="fw-bold mb-0">Sejarah & Perjalanan</h3>
                            <div style="height: 2px; background: linear-gradient(90deg, transparent, #2E8B57, transparent); margin: 1rem 0;"></div>
                        </div>
                    </div>
                    <div class="row align-items-center">
                        <div class="col-md-5 mb-4 mb-md-0">
                            <div class="position-relative">
                                <img src="{{ asset('asset_new/images/old_foto.webp') }}" alt="Foto sekolah tahun 2010" class="img-fluid rounded-3 shadow">
                                <div class="position-absolute bottom-0 start-0 end-0 p-3 bg-dark bg-opacity-75 text-white rounded-bottom-3">
                                    <small><i class="fas fa-camera me-1"></i>Dokumentasi Tahun 2010</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-7">
                            <div class="timeline-item mb-4">
                                <div class="d-flex align-items-center mb-2">
                                    <span class="badge bg-primary rounded-pill px-3 py-2 me-3">Tahun 1979</span>
                                    <h6 class="fw-bold mb-0">Pendirian Sekolah</h6>
                                </div>
                                <p class="text-muted small">
                                    SD Muhammadiyah 3 Samarinda didirikan pada tanggal 19 Agustus 1979 dengan SK Pendirian No. 3855/I-13/KTM-57/1979.
                                </p>
                            </div>
                            <div class="timeline-item mb-4">
                                <div class="d-flex align-items-center mb-2">
                                    <span class="badge bg-success rounded-pill px-3 py-2 me-3">Sampai Tahun Sekarang</span>
                                    <h6 class="fw-bold mb-0">Prestasi Gemilang</h6>
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
        <div class="row mb-5">
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stats-card text-center">
                    <i class="fas fa-calendar-check fa-2x mb-3"></i>
                    <h2 class="fw-bold counter" data-target="44">0</h2>
                    <p class="mb-0">Tahun Pengalaman</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stats-card text-center">
                    <i class="fas fa-user-graduate fa-2x mb-3"></i>
                    <h2 class="fw-bold counter" data-target="2500">0</h2>
                    <p class="mb-0">Alumni Sukses</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stats-card text-center">
                    <i class="fas fa-chalkboard fa-2x mb-3"></i>
                    <h2 class="fw-bold counter" data-target="18">0</h2>
                    <p class="mb-0">Ruang Kelas</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stats-card text-center">
                    <i class="fas fa-users fa-2x mb-3"></i>
                    <h2 class="fw-bold counter" data-target="600">0</h2>
                    <p class="mb-0">Siswa Aktif</p>
                </div>
            </div>
        </div>

        <!-- Enhanced Vision & Mission -->
        <div class="row mb-5">
            <div class="col-lg-6 mb-4">
                <div class="vision-mission animate-fade-in">
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon-box me-3" style="background: rgba(255,255,255,0.2);">
                            <i class="fas fa-eye text-white"></i>
                        </div>
                        <h4 class="fw-bold mb-0 text-white">Visi Kami</h4>
                    </div>
                    <p class="text-white">Sesuai dengan prinsip – prinsip pengembangan dan acuan operasional penyusunan Kurikulum Tingkat Satuan Pendidikan maka, Visi sekolah SD Muhammadiyah 3 Samarinda adalah sebagai berikut: <br>
                        <strong class="text-white">“Terwujudnya Siswa Hafidz/Hafidzah Yang Beriman Bertaqwa Kepada Allah SWT Berakhlak Mulia, Cerdas, Aktif, Kreatif, Berbudaya Lingkungan Serta Unggul Dalam Prestasi Demi Terwujudnya Masyarakat Islam Yang Sebenar – benarnya ”</strong>
                    </p>
                </div>
            </div>
            <div class="col-lg-6 mb-4">
                <div class="section-card animate-fade-in">
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon-box me-3">
                            <i class="fas fa-bullseye text-success"></i>
                        </div>
                        <h4 class="fw-bold mb-0">Misi Kami</h4>
                    </div>
                    <ul class="list-unstyled mb-0">
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
             <div class="col-lg-12 mb-4">
                <div class="vision-mission animate-fade-in mt-3">
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon-box me-3" style="background: rgba(255, 255, 255, 0.2);">
                            <i class="fas fa-eye text-white"></i>
                        </div>
                        <h4 class="fw-bold mb-0 text-white">Tujuan</h4>
                    </div>
                    <p class="text-white">TUJUAN <code>*</code></p>
                    <p class="text-white"><i class="fa fa-check text-primary me-3"></i>Terwujudnya Siswa Siswi hafidz dan hafidzah melalui progam tahfidz dan program penanaman iman dan taqwa sejak dini.</p>
                    <p class="text-white"><i class="fa fa-check text-primary me-3"></i>Terwujudnya Siswa Siswi yang cerdas melalui program edutainment dengan meningkatkan sarana prasarana pendidikan yang mendukung pengembangan kecerdasan Siswa sesuai potensi Siswa.</p>
                    <p class="text-white mb-4"><i class="fa fa-check text-primary me-3"></i> Terwujudnya Siswa yang kreatif melalui program pengembangan ekstrakurikuler sekolah sesuai minat bakat Siswa.</p>
                    <p class="text-white mb-4"><i class="fa fa-check text-primary me-3"></i> Terwujudnya upaya melindungi dan megelola lingkungan hidup dengan program Adiwiyata di Sekolah.</p>
                    <p class="text-white mb-4"><i class="fa fa-check text-primary me-3"></i> Terwujudnya Siswa yang berprestasi dengan program pengembangan kemampuan anak dibidang masing-masing sejak dini.</p>
                    <p class="text-white mb-4"><i class="fa fa-check text-primary me-3"></i> Terwujudnya kebiasaan-kebiasaan warga Sekolah yang islami demi terwujudnya masyarakat islam yang sebenar-benarnya sesuai dengan tujuan Muhammadiyah.</p>
                </div>
            </div>

        </div>

        <!-- Enhanced Facilities Section -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="section-card animate-fade-in">
                    <div class="text-center mb-5">
                        <h3 class="fw-bold mb-3">Fasilitas Unggulan</h3>
                        <p class="text-muted">Fasilitas modern dan lengkap untuk mendukung pembelajaran optimal</p>
                    </div>
                    <div class="row g-4">
                        @php
                            $facilities = [
                                ['icon' => 'chalkboard-teacher', 'title' => 'Ruang Kelas Modern', 'desc' => '18 ruang kelas ber-AC dengan teknologi smart board', 'color' => '#2E8B57'],
                                ['icon' => 'laptop', 'title' => 'Lab. Komputer', 'desc' => 'Teknologi pembelajaran digital terdepan', 'color' => '#f093fb'],
                                ['icon' => 'book', 'title' => 'Perpustakaan Digital', 'desc' => 'Koleksi buku fisik dan digital lengkap', 'color' => '#56ab2f'],
                                ['icon' => 'mosque', 'title' => 'Musholla Nyaman', 'desc' => 'Tempat ibadah dan pembinaan rohani', 'color' => '#ff6b6b'],
                                ['icon' => 'running', 'title' => 'Lapangan Olahraga', 'desc' => 'Fasilitas olahraga dan aktivitas fisik', 'color' => '#4ecdc4'],
                                ['icon' => 'paint-brush', 'title' => 'Studio Seni', 'desc' => 'Ruang kreativitas dan pengembangan bakat', 'color' => '#45b7d1'],
                                ['icon' => 'utensils', 'title' => 'Kantin Sehat', 'desc' => 'Makanan bergizi dan higienis tersertifikasi', 'color' => '#f39c12'],
                                ['icon' => 'shield-alt', 'title' => 'Keamanan 24/7', 'desc' => 'Sistem keamanan terintegrasi dan CCTV', 'color' => '#e74c3c']
                            ];
                        @endphp
                        @foreach($facilities as $index => $facility)
                        <div class="col-lg-3 col-md-6" style="animation-delay: {{ $index * 0.1 }}s;">
                            <div class="facility-item">
                                <i class="fas fa-{{ $facility['icon'] }} fa-2x mb-3" style="color: {{ $facility['color'] }};"></i>
                                <h6 class="fw-bold mb-2">{{ $facility['title'] }}</h6>
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
                <div class="section-card text-center animate-fade-in" style="background: linear-gradient(135deg, #2E8B57, #1B5E20); color: white; position: relative; overflow: hidden;">
                    <div style="position: absolute; top: -50%; right: -50%; width: 200%; height: 200%; background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%); animation: rotate 20s linear infinite;"></div>
                    <div class="position-relative" style="z-index: 2;">
                        <i class="fas fa-graduation-cap fa-3x mb-4"></i>
                        <h3 class="fw-bold mb-3">Bergabunglah dengan Keluarga Besar SD Muhammadiyah 3 Samarinda</h3>
                        <p class="mb-4 fs-5">Wujudkan impian pendidikan terbaik untuk putra-putri Anda di Sekolah Kreatif kami</p>
                        <div class="d-flex justify-content-center gap-3 flex-wrap mb-4">
                            <a href="{{ route('spmb.index') }}" class="btn btn-light btn-lg rounded-pill px-4 py-3">
                                <i class="fas fa-user-plus me-2"></i>Pendaftaran Siswa Baru
                            </a>
                            <a href="https://api.whatsapp.com/send?phone=6285250443151" class="btn btn-outline-light btn-lg rounded-pill px-4 py-3">
                                <i class="fas fa-phone me-2"></i>Hubungi Kami
                            </a>
                        </div>
                        <div class="row justify-content-center">
                            <div class="col-md-8">
                                <div class="d-flex justify-content-center align-items-center gap-4 flex-wrap">
                                    {{-- <div class="text-center">
                                        <i class="fas fa-map-marker-alt fa-lg mb-2"></i>
                                        <p class="mb-0 small">Jl. Dato Iba RT. 04, Sungai Keledang<br>Samarinda Seberang, Kota Samarinda</p>
                                    </div> --}}
                                    <div class="text-center">
                                        {{-- <i class="fas fa-envelope fa-lg mb-2"></i> --}}
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
