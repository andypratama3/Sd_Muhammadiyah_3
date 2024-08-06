<nav class="navbar navbar-expand-lg navbar-light">

    <a href="{{ route('index') }}" class="navbar-brand d-flex p-0 justify-content-center align-items-center">
        <img src="{{ asset('asset/img/SD3_logo.png') }}" alt="Logo">
        <h4 class="text-primary mb-0 pt-1" style="margin-left: 10px;"> SD Muhammadiyah 3</h4>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
        <span class="fa fa-bars"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarCollapse">
        <div class="navbar-nav mx-0 mx-lg-auto">
            <a href="{{ route('index') }}" class="nav-item nav-link {{ Request::routeIS('index') ? 'active' : '' }}">Beranda</a>
            @if(!Request::routeIs('index'))
                <a href="/#tentang" class="nav-item nav-link">Tentang</a>
            @else
                <a href="#tentang" class="nav-item nav-link">Tentang</a>
            @endif
            <div class="nav-item dropdown">
                <a href="#" class="nav-link" data-bs-toggle="dropdown">
                    <span class="dropdown-toggle">Profil</span>
                </a>
                <div class="dropdown-menu">
                    <a href="{{ route('guru.index') }}" class="dropdown-item">Guru</a>
                    <a href="{{ route('visimisi.index') }}" class="dropdown-item">Visi & Misi</a>
                    <a href="{{ route('gallery.index') }}" class="dropdown-item">Gallery Aktivitas</a>
                    <a href="{{ route('fasilitas.index') }}" class="dropdown-item">Sarana & Prasarana</a>
                    <a href="{{ route('prestasi.siswa.index') }}" class="dropdown-item">Prestasi Siswa</a>
                    <a href="{{ route('esktrakurikuler.index') }}" class="dropdown-item">Ekstrakurikuler</a>
                    <a href="{{ route('prestasi.sekolah.index') }}" class="dropdown-item">Prestasi Sekolah</a>
                    <a href="{{ route('tenagapendidikan.index') }}" class="dropdown-item">Tenaga Pendidikan</a>

                </div>
            </div>
            <a href="{{ route('pembayaran.index') }}" class="nav-item nav-link {{ Request::routeIS('pembayaran.index') ? 'active' : '' }}">Pembayaran</a>
            <a href="{{ route('berita.index') }}" class="nav-item nav-link {{ Request::routeIS('berita.index') ? 'active' : '' }}">Berita</a>
            <a href="{{ route('artikel.index') }}" class="nav-item nav-link {{ Request::routeIS('artikel.index') ? 'active' : '' }}">Artikel</a>
            {{-- <div class="nav-item dropdown">
                <a href="#" class="nav-link" data-bs-toggle="dropdown">
                    <span class="dropdown-toggle">Pages</span>
                </a>
                <div class="dropdown-menu">
                    <a href="feature.html" class="dropdown-item">Our Features</a>
                    <a href="team.html" class="dropdown-item">Our team</a>
                    <a href="testimonial.html" class="dropdown-item">Testimonial</a>
                    <a href="FAQ.html" class="dropdown-item">FAQs</a>
                    <a href="404.html" class="dropdown-item">404 Page</a>
                </div>
            </div> --}}
            <a href="{{ route('kontak.index') }}" class="nav-item nav-link">Kontak</a>
            {{-- <div class="nav-btn px-3">
                <button class="btn-search btn btn-primary btn-md-square rounded-circle flex-shrink-0" data-bs-toggle="modal" data-bs-target="#searchModal"><i class="fas fa-search"></i></button>
                <a href="#" class="btn btn-primary rounded-pill py-2 px-4 ms-3 flex-shrink-0"> Get a Quote</a>
            </div> --}}
        </div>
    </div>
    <div class="d-none d-xl-flex flex-shrink-0 ps-4">
        <a href="#" class="btn btn-light btn-lg-square rounded-circle position-relative wow tada" data-wow-delay=".9s">
            <i class="fa fa-phone-alt fa-2x"></i>
            <div class="position-absolute" style="top: 7px; right: 12px;">
                <span><i class="fa fa-comment-dots text-secondary"></i></span>
            </div>
        </a>
        <div class="d-flex flex-column ms-3">
            <span>Call to Our Experts</span>
            <a href="tel:+ 0123 456 7890"><span class="text-dark">Free: + 0123 456 7890</span></a>
        </div>
    </div>
</nav>
