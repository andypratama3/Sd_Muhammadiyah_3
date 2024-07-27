<header class="header-area header-sticky background-area background-header">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <nav class="main-nav">
                    <!-- ***** Logo Start ***** -->
                    <a href="{{ route('index') }}" class="logo">
                        <h1>SD MUHAMMADIYAH 3</h1>
                    </a>
                    <!-- ***** Logo End ***** -->
                    <!-- ***** Serach Start ***** -->
                    {{-- <div class="search-input">
                      <form id="search" action="#">
                        <input type="text" placeholder="Type Something" id='searchText' name="searchKeyword" onkeypress="handle" />
                        <i class="fa fa-search"></i>
                      </form>
                    </div> --}}
                    <!-- ***** Serach Start ***** -->
                    <!-- ***** Menu Start ***** -->
                    <ul class="nav">
                        <li class="scroll-to-section"><a href="{{ route('index') }}" class="{{ request()->routeIs('index') ? 'active' : '' }}">Home</a></li>
                        <li class="dropdown"><a href="#"><span>Profil </span> <i class="bi bi-chevron-down"></i></a>
                            <ul>
                              <li><a href="#">Akreditas</a></li>
                              <li class="dropdown"><a href="#"><span>Deep Drop Down</span> <i class="bi bi-chevron-right"></i></a>
                                <ul>
                                  <li><a href="#">Deep Drop Down 1</a></li>
                                  <li><a href="#">Deep Drop Down 2</a></li>
                                  <li><a href="#">Deep Drop Down 3</a></li>
                                  <li><a href="#">Deep Drop Down 4</a></li>
                                  <li><a href="#">Deep Drop Down 5</a></li>
                                </ul>
                              </li>
                              <li><a href="#">Struktur Organisasi</a></li>
                              <li><a href="#">Fasilitas</a></li>
                              <li><a href="#">Visi & Misi</a></li>

                            </ul>
                        </li>
                        <li class="scroll-to-section"><a href="{{ route('pembayaran.index') }}" class="{{ Request::routeIs('pembayaran.index') ? 'active' : '' }}">Pembayaran</a></li>
                        <li class="scroll-to-section"><a href="{{ route('berita.index') }}" class="{{ Request::routeIs('berita.index') ? 'active' : '' }}">Berita</a></li>
                        <li class="scroll-to-section"><a href="{{ route('artikel.index') }}" class="{{ Request::routeIs('artikel.index') ? 'active' : '' }}">Artikel</a></li>
                        <li class="scroll-to-section"><a href="{{ route('kontak.index') }}" class="{{ Request::routeIs('kontak.index') ? 'active' : '' }}">Kontak</a></li>

                    </ul>
                    <a class='menu-trigger'>
                        <span>Menu</span>
                    </a>
                    <!-- ***** Menu End ***** -->
                </nav>
            </div>
        </div>
    </div>
</header>
