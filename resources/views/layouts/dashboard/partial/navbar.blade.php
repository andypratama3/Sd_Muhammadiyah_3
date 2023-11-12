<ul class="navbar-nav sidebar sidebar-light accordion" id="accordionSidebar">
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ route('dashboard') }}">
        <div class="sidebar-brand-icon">
            <img src="{{ asset('assets/img/SD3_logo.png') }}" alt="">
        </div>
        <div class="sidebar-brand-text mx-3">SD Muhammadiyah 3</div>
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
            data-toggle="collapse" data-target="#news" aria-expanded="false"
            aria-controls="news">
            <i class="fas fa-book"></i>
            <span>News</span>
        </a>
        <div id="news" class="collapse {{ Request::routeIs('dashboard.news.*') ? 'show' : ''  }} }}"
            aria-labelledby="headingBootstrap" data-parent="#accordionSidebar" style="">
            <div class="py-2 collapse-inner rounded">
                <a class="collapse-item {{ Request::routeIs('dashboard.news.berita.*') ? 'active' : ''  }}"
                href="{{ route('dashboard.news.berita.index') }}">Berita</a>
                <a class="collapse-item {{ Request::routeIs('dashboard.news.category.*') ? 'active' : ''  }}"
                href="{{ route('dashboard.news.category.index') }}">Kategori Artikel</a>
                <a class="collapse-item {{ Request::routeIs('dashboard.news.artikel.*') ? 'active' : ''  }}"
                href="{{ route('dashboard.news.artikel.index') }}">Artikel</a>
            </div>
        </div>
    </li>

    <li class="nav-item {{ Request::routeIs('dashboard.fasilitas.*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('dashboard.fasilitas.index') }}">
            <i class="fas fa-home"></i>
            <span>Fasilitas</span>
        </a>
    </li>
    <li class="nav-item {{ Request::routeIs('dashboard.guru.*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('dashboard.guru.index') }}">
            <i class="fas fa-user"></i>
            <span>Guru</span>
        </a>
    </li>
    <li class="nav-item {{ Request::routeIs('dashboard.ekstrakurikuler.*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('dashboard.ekstrakurikuler.index') }}">
            <i class="fas fa-user"></i>
            <span>ekstrakurikuler</span>
        </a>
    </li>
    <hr>
    <div class="sidebar-heading">
        Fitur
    </div>
    <li class="nav-item">
        <a class="nav-link {{ Request::routeIs('dashboard.news.*') ? '' : 'collapsed'  }}" href="#"
            data-toggle="collapse" data-target="#datamaster" aria-expanded="false"
            aria-controls="datamaster">
            <i class="fas fa-gear"></i>
            <span>Data Master</span>
        </a>
        <div id="datamaster" class="collapse {{ Request::routeIs('dashboard.datamaster.*') ? 'show' : ''  }} }}"
            aria-labelledby="headingBootstrap" data-parent="#accordionSidebar" style="">
            <div class="py-2 collapse-inner rounded">
                <a class="collapse-item {{ Request::routeIs('dashboard.datamaster.kelas.*') ? 'active' : ''  }}"
                href="{{ route('dashboard.datamaster.kelas.index') }}">Kelas</a>
                {{-- <a class="collapse-item {{ Request::routeIs('dashboard.news.category.*') ? 'active' : ''  }}"
                href="{{ route('dashboard.news.category.index') }}">Kategori Artikel</a>
                <a class="collapse-item {{ Request::routeIs('dashboard.news.artikel.*') ? 'active' : ''  }}"
                href="{{ route('dashboard.news.artikel.index') }}">Artikel</a> --}}
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
