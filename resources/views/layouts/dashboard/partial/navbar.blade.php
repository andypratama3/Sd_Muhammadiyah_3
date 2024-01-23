<ul class="navbar-nav sidebar sidebar-light accordion" id="accordionSidebar">
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ route('dashboard') }}">
        <div class="sidebar-brand-icon">
            <img src="{{ asset('assets/img/SD3_logo.png') }}" alt="">
        </div>
        <div class="sidebar-brand-text mx-2 font-weight-bold text-uppercase" style="font-size: 12px;">SD Muhammadiyah 3 Samarinda</div>
    </a>
    <hr class="sidebar-divider my-0">
    <li class="nav-item {{ Request::routeIs('dashboard.*') ? 'active' : '' }}">
        <a class="nav-link  {{ Request::routeIs('dashboard.*') ? 'active' : '' }}" href="{{ route('dashboard') }}">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Dashboard</span></a>
    </li>
    <hr class="sidebar-divider">
    <div class="sidebar-heading">
        Fitur
    </div>
    <li class="nav-item">
        <a class="nav-link {{ Request::routeIs('dashboard.news.*') ? '' : 'collapsed'  }}" href="#"
            data-toggle="collapse" data-target="#news" aria-expanded="false" aria-controls="news">
            <i class="fas fa-book"></i>
            <span>News</span>
        </a>
        <div id="news" class="collapse {{ Request::routeIs('dashboard.news.*') ? 'show' : ''  }} }}"
            aria-labelledby="headingBootstrap" data-parent="#accordionSidebar" style="">
            <div class="py-2 collapse-inner rounded">
                <a class="collapse-item {{ Request::routeIs('dashboard.news.berita.*') ? 'active' : ''  }}"
                    href="{{ route('dashboard.news.berita.index') }}">
                    <i class="fas fa-solid fa-newspaper"></i>
                    <span>Berita</span>
                </a>
                <a class="collapse-item {{ Request::routeIs('dashboard.news.category.*') ? 'active' : ''  }}"
                    href="{{ route('dashboard.news.category.index') }}">
                    <i class="fas fa-solid fa-book-open"></i>
                    <span>Kategori Artikel</span>
                </a>
                <a class="collapse-item {{ Request::routeIs('dashboard.news.artikel.*') ? 'active' : ''  }}"
                    href="{{ route('dashboard.news.artikel.index') }}">
                    <i class="fas fa-book"></i>
                    <span>Artikel</span>
                </a>
            </div>
        </div>
    </li>
    <hr class="sidebar-divider">
    <div class="sidebar-heading">
        Data Sekolah
    </div>
    <li class="nav-item">
        <a class="nav-link {{ Request::routeIs('dashboard.news.*') ? '' : 'collapsed'  }}" href="#"
            data-toggle="collapse" data-target="#data_sekolah" aria-expanded="false" aria-controls="data_sekolah">
            <i class="fas fa-solid fa-city"></i>
            <span>Data Sekolah</span>
        </a>
        <div id="data_sekolah" class="collapse {{ Request::routeIs('dashboard.datasekolah.*') ? 'show' : ''  }} }}"
            aria-labelledby="headingBootstrap" data-parent="#accordionSidebar" style="">
            <div class="py-2 collapse-inner rounded">
                <a class="collapse-item" href="{{ route('dashboard.datasekolah.fasilitas.index') }}">
                    <i class="fas fa-solid fa-warehouse"></i>
                    <span>Sarana &  Prasarana</span>
                </a>
                <a class="collapse-item {{ Request::routeIs('dashboard.datasekolah.matapelajaran.*') ? 'active' : ''  }}"
                    href="{{ route('dashboard.datasekolah.matapelajaran.index') }}">
                    <i class="fas fa-solid fa-book"></i>
                    <span>Mata Pelajaran</span>
                </a>
                <a class="collapse-item {{ Request::routeIs('dashboard.datasekolah.guru.*') ? 'active' : ''  }}" href="{{ route('dashboard.datasekolah.guru.index') }}">
                    <i class="fas fa-solid fa-users"></i>
                    <span>Guru</span>
                </a>
                <a class="collapse-item {{ Request::routeIs('dashboard.datasekolah.tenagapendidikan.*') ? 'active' : ''  }}" href="{{ route('dashboard.datasekolah.tenagapendidikan.index') }}">
                    <i class="fas fa-solid fa-user-tie"></i>
                    <span>Tenaga Pendidikan</span>
                </a>
                <a class="collapse-item {{ Request::routeIs('dashboard.datasekolah.ekstrakurikuler.*') ? 'active' : ''  }}" href="{{ route('dashboard.datasekolah.ekstrakurikuler.index') }}">
                    <i class="fas fa-user"></i>
                    <span>Ekstrakurikuler</span>
                </a>
                <a class="collapse-item {{ Request::routeIs('dashboard.datasekolah.kelas.*') ? 'active' : ''  }}"
                    href="{{ route('dashboard.datasekolah.kelas.index') }}">
                    <i class="fas fa-home"></i>
                    <span>Kelas</span>
                </a>
                <a class="collapse-item {{ Request::routeIs('dashboard.datasekolah.jadwal.*') ? 'active' : ''  }}"
                    href="{{ route('dashboard.datasekolah.jadwal.index') }}">
                    <i class="fas fa-solid fa-list"></i>
                    <span>Jadwal</span>
                </a>
                <a class="collapse-item {{ Request::routeIs('dashboard.datasekolah.prestasi.*') ? 'active' : ''  }}"
                    href="{{ route('dashboard.datasekolah.prestasi.index') }}">
                    <i class="fas fa-solid fa-trophy"></i>
                    <span>Prestasi</span>
                </a>
            </div>
        </div>
    </li>
    <hr class="sidebar-divider">
    <div class="sidebar-heading">
        Data Master
    </div>
    <li class="nav-item">
        <a class="nav-link {{ Request::routeIs('dashboard.news.*') ? '' : 'collapsed'  }}" href="#"
            data-toggle="collapse" data-target="#datamaster" aria-expanded="false" aria-controls="datamaster">
            <i class="fas fa-database"></i>
            <span>Data Master</span>
        </a>
        <div id="datamaster" class="collapse {{ Request::routeIs('dashboard.datamaster.*') ? 'show' : ''  }} }}"
            aria-labelledby="headingBootstrap" data-parent="#accordionSidebar">
            <div class="py-2 collapse-inner rounded">
                {{-- <a class="collapse-item {{ Request::routeIs('dashboard.datamaster.kelas.*') ? 'active' : ''  }}"
                    href="{{ route('dashboard.datamaster.kelas.index') }}">Kelas</a>
                <a class="collapse-item {{ Request::routeIs('dashboard.datamaster.jadwal.*') ? 'active' : ''  }}"
                    href="{{ route('dashboard.datamaster.jadwal.index') }}">Jadwal</a> --}}
                    <a class="collapse-item {{ Request::routeIs('dashboard.datamaster.siswa.*') ? 'active' : ''  }}"
                    href="{{ route('dashboard.datamaster.siswa.index') }}">
                    <i class="fas fa-solid fa-user"></i>
                    <span>siswa</span>
                    </a>
                    <a class="collapse-item {{ Request::routeIs('dashboard.datamaster.nilai.*') ? 'active' : ''  }}"
                    href="{{ route('dashboard.datamaster.nilai.index') }}">
                    <i class="fas fa-solid fa-user"></i>
                    <span>Nilai Siswa</span>
                    </a>
            </div>
        </div>
    </li>
    <hr class="sidebar-divider">
    <div class="sidebar-heading">
        Akses
    </div>
    <li class="nav-item">
        <a class="nav-link {{ Request::routeIs('dashboard.pengaturan.*') ? 'collapsed' : ''  }}" href="#"
            data-toggle="collapse" data-target="#collapseBootstrap" aria-expanded="false"
            aria-controls="collapseBootstrap">
            <i class="fa fa-user-alt"></i>
            <span>Pengguna</span>
        </a>
        <div id="collapseBootstrap" class="collapse {{ Request::routeIs('dashboard.pengaturan.*') ? 'show' : ''  }} }}"
            aria-labelledby="headingBootstrap" data-parent="#accordionSidebar" style="">
            <div class="py-2 collapse-inner rounded">
                @role('superadmin')
                <a class="collapse-item {{ Request::routeIs('dashboard.pengaturan.task.*') ? 'active' : ''  }}"
                    href="{{ route('dashboard.pengaturan.task.index') }}">Task</a>
                @endrole
                <a class="collapse-item {{ Request::routeIs('dashboard.pengaturan.role.*') ? 'active' : ''  }}"
                    href="{{ route('dashboard.pengaturan.role.index') }}">Role</a>
                <a class="collapse-item {{ Request::routeIs('dashboard.pengaturan.karyawan.*') ? 'active' : ''  }}"
                    href="{{ route('dashboard.pengaturan.karyawan.index') }}">karyawan</a>
                <a class="collapse-item {{ Request::routeIs('dashboard.pengaturan.user.*') ? 'active' : ''  }}"
                    href="{{ route('dashboard.pengaturan.user.index') }}">User</a>
            </div>
        </div>
    </li>
</ul>
