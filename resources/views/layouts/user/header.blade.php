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
                             class="bi bi-chevron-down dropdown-indicator"></i>
                     </a>
                     <ul>
                         <li><a href="{{ route('guru.index') }}">Guru</a></li>
                         <li><a href="{{ route('fasilitas.index')}}">Sarana &  Prasarana</a></li>
                         <li><a href="{{ route('esktrakurikuler.index') }}">Ekstrakurikuler</a></li>
                         <li><a href="{{ route('tenagapendidikan.index') }}">Tenaga Pendidikan</a></li>
                     </ul>
                 </li>
                 <li class="dropdown"><a href="#"><span>Informasi</span> <i
                             class="bi bi-chevron-down dropdown-indicator"></i></a>
                     <ul>
                         <li><a href="#">Pembayaran SPP</a></li>
                         <li><a href="#">Nilai Siswa</a></li>
                         <li><a href="#">Berita</a></li>
                         <li><a href="#">Prestasi Siswa</a></li>
                         <li><a href="{{ route('jadwal.index') }}">Jadwal Sekolah</a></li>
                     </ul>
                 </li>
                 <li><a href="contact.html">Kontak</a></li>

                 <li><a href="{{ route('artikel.index') }}">Artikel</a></li>
                 @auth
                    <li><a href="{{ route('dashboard') }}">Dashboard</a></li>
                 @else
                    <li><a href="{{ route('login') }}">Masuk</a></li>
                 @endauth
             </ul>
         </nav><!-- .navbar -->
         <div class="position-relative">
             <a href="#" class="mx-2"><span class="bi-facebook"></span></a>
             <a href="https://www.instagram.com/sekolahkreatifsamarinda/" class="mx-2"><span class="bi-instagram"></span></a>

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
 </header>
