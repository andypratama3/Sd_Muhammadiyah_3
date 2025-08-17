<nav class="navbar navbar-expand-lg navbar-light">

    <a href="{{ route('index') }}" class="p-0 navbar-brand d-flex justify-content-center align-items-center">
        <img src="{{ asset('asset_new/images/SD3_logo1.png') }}" alt="Logo">
        <h4 class="pt-1 mb-0 text-primary" style="margin-left: 10px;"> SD Muhammadiyah 3</h4>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
        <span class="fa fa-bars"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarCollapse">
        <div class="mx-0 navbar-nav mx-lg-auto">
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
                    <a href="{{ route('profil.index') }}" class="dropdown-item">Profil Sekolah</a>
                    <a href="{{ route('guru.index') }}" class="dropdown-item">Guru</a>
                    {{-- <a href="{{ route('visimisi.index') }}" class="dropdown-item">Visi & Misi</a> --}}
                    <a href="{{ route('gallery.index') }}" class="dropdown-item">Gallery Aktivitas</a>
                    <a href="{{ route('fasilitas.index') }}" class="dropdown-item">Sarana & Prasarana</a>
                    <a href="{{ route('prestasi.siswa.index') }}" class="dropdown-item">Prestasi Siswa</a>
                    <a href="{{ route('esktrakurikuler.index') }}" class="dropdown-item">Ekstrakurikuler</a>
                    <a href="{{ route('prestasi.sekolah.index') }}" class="dropdown-item">Prestasi Sekolah</a>
                    <a href="{{ route('tenagapendidikan.index') }}" class="dropdown-item">Tenaga Pendidikan</a>

                </div>
            </div>
            <a href="{{ route('jadwal.index') }}" class="nav-item nav-link {{ Request::routeIS('jadwal.index') ? 'active' : '' }}">Jadwal</a>
            <a href="{{ route('pembayaran.index') }}" class="nav-item nav-link {{ Request::routeIS('pembayaran.index') ? 'active' : '' }}">Pembayaran</a>
            <a href="{{ route('berita.index') }}" class="nav-item nav-link {{ Request::routeIS('berita.index') ? 'active' : '' }}">Berita</a>
            <a href="{{ route('spmb.index') }}" class="nav-item nav-link {{ Request::routeIS('spmb.index') ? 'active' : '' }}">SPMB</a>
            <a href="{{ route('pengisian.index') }}" class="nav-item nav-link {{ Request::routeIS('pengisian.index') ? 'active' : '' }}">Pengisian Whatsaap</a>
            <a href="{{ route('kontak.index') }}" class="nav-item nav-link">Kontak</a>
            {{-- <div class="px-3 nav-btn">
                <button class="flex-shrink-0 btn-search btn btn-primary btn-md-square rounded-circle" data-bs-toggle="modal" data-bs-target="#searchModal"><i class="fas fa-search"></i></button>
                <a href="#" class="flex-shrink-0 px-4 py-2 btn btn-primary rounded-pill ms-3"> Get a Quote</a>
            </div> --}}
        </div>
    </div>
    <div class="flex-shrink-0 d-none d-xl-flex ps-4">
        <a href="https://wa.me/6285250443151" class="btn btn-light btn-lg-square rounded-circle position-relative wow tada" data-wow-delay=".9s">
            <img src="{{ asset('asset/img/SD3_logo1.png') }}" alt="" class="p-1 img-fluid">
            <div class="position-absolute" style="top: 7px; right: 12px;">
            </div>
        </a>
        {{-- <div class="d-flex flex-column ms-3">
            <span>Contact As</span>
            <a href="https://wa.me/6281234567890"><span class="text-dark">0123 456 7890</span></a>
        </div> --}}
    </div>
</nav>
