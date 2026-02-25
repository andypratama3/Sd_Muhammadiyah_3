 <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
     <div class="mb-3 app-brand demo d-flex justify-content-center">
         <a href="{{ route('dashboard') }}" class="app-brand-link">
             <span class="p-0 m-0 app-brand-logo demo w-100 d-flex justify-content-center align-items-center">
                 <img src="{{ asset('asset_dashboard/img/SD3_logo.png') }}" alt="" style="width: 25%;">
             </span>
         </a>

         <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
             <i class="align-middle bx bx-chevron-left bx-sm"></i>
         </a>
     </div>

     <div class="menu-inner-shadow"></div>

     <ul class="py-1 menu-inner">
         <!-- Dashboard -->
         <li class="menu-item {{ Request::routeIs('dashboard') ? 'active' : '' }}">
             <a href="{{ route('dashboard') }}" class="menu-link">
                 <i class="menu-icon tf-icons bx bx-home-circle"></i>
                 <div data-i18n="Dashboard">Dashboard</div>
             </a>
         </li>
         @if(Auth::user()->hasRole('user'))
         <li class="menu-item {{ Request::routeIs('dashboard.news.artikel.*') ? 'active' : '' }}">
            <a class="menu-link" href="{{ route('dashboard.news.artikel.index') }}">
                <i class="menu-icon tf-icons bx bx-book"></i>
                <div data-i18n="Artikel">Artikel</div>
            </a>
        </li>
        @endif
        <li class="menu-header small text-uppercase">
             <span class="menu-header-text">Absensi</span>
        </li>
        <li class="menu-item {{ Request::routeIs('dashboard.absensis.*') ? 'active' : '' }}">
             <a href="{{ route('dashboard.absensis.index') }}" class="menu-link">
                 <i class="menu-icon tf-icons bx bx-home-circle"></i>
                 <div data-i18n="Dashboard">Absensi</div>
             </a>
        </li>
        <li class="menu-item {{ Request::routeIs('dashboard.pengajuan_cuti.*') ? 'active' : '' }}">
             <a href="{{ route('dashboard.pengajuan_cuti.index') }}" class="menu-link">
                 <i class="menu-icon tf-icons bx bx-envelope"></i>
                 <div data-i18n="Dashboard">Pengajuan Cuti</div>
             </a>
         </li>
        <li class="menu-item {{ Request::routeIs('dashboard.rekap.absensi.*') ? 'active' : '' }}">
            <a href="{{ route('dashboard.rekap.absensi.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-calendar-check"></i>
                <div>Rekap Absensi</div>
            </a>
        </li>
        @role('admin|superadmin')
        <li class="menu-header small text-uppercase"><span class="menu-header-text">Pengaturan Absensi</span></li>
         <!-- Pengaturan -->
         <li class="menu-item {{ Request::routeIs('dashboard.pengaturan-absen.*') ? 'open' : '' }}">
            <a href="javascript:void(0)" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-cog"></i>
                <div data-i18n="Pengaturan">Pengaturan Absensi</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ Request::routeIs('dashboard.pengaturan.role.*') ? 'active' : ''  }}">
                    <a href="{{ route('dashboard.lokasi.absen.index') }}" class="menu-link">
                        <i class="menu-icon tf-icons bx bx-user"></i>
                        <div class="mx-3" data-i18n="Role">Lokasi Absen</div>
                    </a>
                </li>
                <li class="menu-item {{ Request::routeIs('dashboard.jam.absen.*') ? 'active' : ''  }}">
                    <a href="{{ route('dashboard.jam.absen.index') }}" class="menu-link">
                        <i class="menu-icon tf-icons bx bx-group"></i>
                        <div class="mx-3" data-i18n="Karyawan">Jam Absen</div>
                    </a>
                </li>
            </ul>
        </li>
        
         <li class="menu-header small text-uppercase">
             <span class="menu-header-text">News</span>
         </li>
         <li class="menu-item {{ Request::routeIs('dashboard.news.hero.*') ? 'active' : '' }}">
             <a class="menu-link" href="{{ route('dashboard.news.hero.index') }}">
                 <i class="menu-icon tf-icons bx bxs-image"></i>
                 <div data-i18n="Hero Banner">Gambar Depan</div>
             </a>
         </li>
         <li class="menu-item {{ Request::routeIs('dashboard.news.berita.*') ? 'active' : '' }}">
             <a class="menu-link" href="{{ route('dashboard.news.berita.index') }}">
                 <i class='menu-icon tf-icons bx bxs-news'></i>
                 <div data-i18n="Berita"> Berita</div>
             </a>
         </li>
         <li class="menu-item {{ Request::routeIs('dashboard.news.category.*') ? 'active' : '' }}">
             <a class="menu-link" href="{{ route('dashboard.news.category.index') }}">
                 <i class="menu-icon tf-icons bx bx-book-open"></i>
                 <div data-i18n="Kategori Artikel">Kategori Artikel</div>
             </a>
         </li>
         <li class="menu-item {{ Request::routeIs('dashboard.news.artikel.*') ? 'active' : '' }}">
             <a class="menu-link" href="{{ route('dashboard.news.artikel.index') }}">
                 <i class="menu-icon tf-icons bx bx-book"></i>
                 <div data-i18n="Artikel">Artikel</div>
             </a>
         </li>

         <!-- Components -->
         <li class="menu-header small text-uppercase"><span class="menu-header-text">Data Sekolah</span></li>

         <!-- Data Sekolah -->
         <li class="menu-item {{ Request::routeIs('dashboard.datasekolah.*') ? 'open' : '' }}">
             <a href="javascript:void(0)" class="menu-link menu-toggle">
                 <i class="menu-icon tf-icons bx bxs-city"></i>
                 <div data-i18n="Data Sekolah">Data Sekolah</div>
             </a>
             <ul class="menu-sub">
                 <li class="menu-item {{ Request::routeIs('dashboard.datasekolah.foto_sekolah.*') ? 'active' : '' }}">
                     <a href="{{ route('dashboard.datasekolah.foto_sekolah.index') }}" class="menu-link">
                         <i class="menu-icon tf-icons bx bxs-home"></i>
                         <div data-i18n="Sarana &  Prasarana">Foto Sekolah</div>
                     </a>
                 </li>
                 <li class="menu-item {{ Request::routeIs('dashboard.datasekolah.fasilitas.*') ? 'active' : '' }}">
                     <a href="{{ route('dashboard.datasekolah.fasilitas.index') }}" class="menu-link">
                         <i class="menu-icon tf-icons bx bxs-home"></i>
                         <div data-i18n="Sarana &  Prasarana">Sarana & Prasarana</div>
                     </a>
                 </li>
                 <li class="menu-item {{ Request::routeIs('dashboard.datasekolah.matapelajaran.*') ? 'active' : '' }}">
                     <a href="{{ route('dashboard.datasekolah.matapelajaran.index') }}" class="menu-link">
                        <i class="menu-icon tf-icons bx bxs-book"></i>
                         <div data-i18n="Mata Pelajaran">Mata Pelajaran</div>
                     </a>
                 </li>
                 <li class="menu-item {{ Request::routeIs('dashboard.datasekolah.guru.*') ? 'active' : '' }}">
                     <a href="{{ route('dashboard.datasekolah.guru.index') }}" class="menu-link">
                         <i class="menu-icon tf-icons bx bxs-user-badge"></i>
                         <div data-i18n="Guru">Guru</div>
                     </a>
                 </li>
                 <li class="menu-item {{ Request::routeIs('dashboard.datasekolah.struktur.tenaga.pendidikan.*') ? 'active' : '' }}">
                     <a href="{{ route('dashboard.datasekolah.struktur.tenaga.pendidikan.index') }}" class="menu-link">
                         <i class="menu-icon tf-icons bx bxs-user-account"></i>
                         <div data-i18n="Tenaga Pendidikan">Struktur Tenaga Pendidikan</div>
                     </a>
                 </li>
                 <li class="menu-item {{ Request::routeIs('dashboard.datasekolah.tenagapendidikan.*') ? 'active' : '' }}">
                     <a href="{{ route('dashboard.datasekolah.tenagapendidikan.index') }}" class="menu-link">
                         <i class="menu-icon tf-icons bx bxs-user-account"></i>
                         <div data-i18n="Tenaga Pendidikan">Tenaga Pendidikan</div>
                     </a>
                 </li>
                 <li class="menu-item {{ Request::routeIs('dashboard.datasekolah.ekstrakurikuler.*') ? 'active' : '' }}">
                     <a href="{{ route('dashboard.datasekolah.ekstrakurikuler.index') }}" class="menu-link">
                         <i class="menu-icon tf-icons bx bxs-business"></i>
                         <div data-i18n="Ekstrakurikuler">Ekstrakurikuler</div>
                     </a>
                 </li>
                 <li class="menu-item {{ Request::routeIs('dashboard.datasekolah.kelas.*') ? 'active' : '' }}">
                     <a href="{{ route('dashboard.datasekolah.kelas.index') }}" class="menu-link">
                         <i class="menu-icon tf-icons bx bxs-school"></i>
                         <div data-i18n="Kelas">Kelas</div>
                     </a>
                 </li>
                 <li class="menu-item {{ Request::routeIs('dashboard.datasekolah.jadwal.*') ? 'active' : '' }}">
                     <a href="{{ route('dashboard.datasekolah.jadwal.index') }}" class="menu-link">
                         <i class="menu-icon tf-icons bx bxs-calendar"></i>
                         <div data-i18n="Jadwal">Jadwal</div>
                     </a>
                 </li>
                 <li class="menu-item {{ Request::routeIs('dashboard.datasekolah.kategori.prestasi.*') ? 'active' : '' }}">
                     <a href="{{ route('dashboard.datasekolah.kategori.prestasi.index') }}" class="menu-link">
                         <i class="menu-icon tf-icons bx bx-cog"></i>
                         <div data-i18n="Prestasi">Kategori Prestasi</div>
                     </a>
                 </li>
                 <li class="menu-item {{ Request::routeIs('dashboard.datasekolah.prestasi.*') ? 'active' : '' }}">
                     <a href="{{ route('dashboard.datasekolah.prestasi.index') }}" class="menu-link">
                         <i class="menu-icon tf-icons bx bxs-trophy"></i>
                         <div data-i18n="Prestasi">Prestasi</div>
                     </a>
                 </li>
                 <li class="menu-item {{ Request::routeIs('dashboard.datasekolah.kategori.gallery.*') ? 'active' : '' }}">
                     <a href="{{ route('dashboard.datasekolah.kategori.gallery.index') }}" class="menu-link">
                         <i class="menu-icon tf-icons bx bxs-image"></i>
                         <div data-i18n="Gallery">Kategori Gallery</div>
                     </a>
                 </li>
                 <li class="menu-item {{ Request::routeIs('dashboard.datasekolah.gallery.*') ? 'active' : '' }}">
                     <a href="{{ route('dashboard.datasekolah.gallery.index') }}" class="menu-link">
                         <i class="menu-icon tf-icons bx bxs-image"></i>
                         <div data-i18n="Gallery">Gallery</div>
                     </a>
                 </li>
                 <li class="menu-item {{ Request::routeIs('dashboard.datasekolah.cooperation.*') ? 'active' : '' }}">
                     <a href="{{ route('dashboard.datasekolah.cooperation.index') }}" class="menu-link">
                         <i class="menu-icon tf-icons bx bxs-user-voice"></i>
                         <div data-i18n="Kerja Sama">Kerja Sama</div>
                     </a>
                 </li>
                 <li class="menu-item {{ Request::routeIs('dashboard.datasekolah.achivement.*') ? 'active' : '' }}">
                     <a href="{{ route('dashboard.datasekolah.achivement.index') }}" class="menu-link">
                         <i class="menu-icon tf-icons bx bxs-medal"></i>
                         <div data-i18n="Penghargaan">Pengargaan</div>
                     </a>
                 </li>
             </ul>
         </li>


         {{-- <li class="menu-item {{ Request::routeIs('dashboard.attendances.*') ? 'active' : '' }}">
             <a href="{{ route('dashboard.attendances.index') }}" class="menu-link">
                 <i class="menu-icon tf-icons bx bx-crown"></i>
                 <div data-i18n="Boxicons">Absensi Siswa</div>
             </a>
         </li> --}}
         <li class="menu-item {{ Request::routeIs('dashboard.spmb.*') ? 'active' : '' }}">
             <a href="{{ route('dashboard.spmb.index') }}" class="menu-link">
                 <i class="menu-icon tf-icons bx bx-crown"></i>
                 <div data-i18n="Boxicons">SPMB</div>
             </a>
         </li>
        @endrole

        @canany('role:admin|superadmin')
         <!-- Forms & Tables -->
         <li class="menu-header small text-uppercase"><span class="menu-header-text">Siswa &amp;
                 Pembayaran</span></li>
         <!-- Forms -->
         <li class="menu-item {{ Request::routeIs('dashboard.datamaster.*') ? 'open' : '' }}">
             <a href="javascript:void(0);" class="menu-link menu-toggle">
                 <i class="menu-icon tf-icons bx bx-detail"></i>
                 <div data-i18n="Form Elements"> Siswa</div>
             </a>
             <ul class="menu-sub">
                 <li class="menu-item {{ Request::routeIs('dashboard.datamaster.siswa.*') ? 'active' : ''  }}">
                     <a class="menu-link" href="{{ route('dashboard.datamaster.siswa.index') }}">
                         <i class="menu-icon tf-icons fas fa-solid fa-users"> </i>
                         <div class="mx-3" data-i18n="siswa"> Siswa</div>
                     </a>
                 </li>
                 <li class="menu-item {{ Request::routeIs('dashboard.datamaster.rapot.*') ? 'active' : ''  }}">
                     <a class="menu-link"
                         href="{{ route('dashboard.datamaster.rapot.index') }}">
                         <i class="menu-icon tf-icons fas fa-solid fa-user"></i>
                         <div class="mx-3" data-i18n="rapot Siswa">Rapot Siswa</div>
                     </a>
                 </li>

                 <li class="menu-item {{ Request::routeIs('dashboard.datamaster.nilai.*') ? 'active' : ''  }}">
                     <a class="menu-link"
                         href="{{ route('dashboard.datamaster.nilai.index') }}">
                         <i class="menu-icon tf-icons fas fa-solid fa-user"></i>
                         <div class="mx-3" data-i18n="Nilai Siswa">Nilai Siswa</div>
                     </a>
                 </li>
                 <li class="menu-item {{ Request::routeIs('dashboard.datamaster.judul.pembayaran.*') ? 'active' : ''  }}">
                     <a class="menu-link"
                         href="{{ route('dashboard.datamaster.judul.pembayaran.index') }}">
                         <i class="menu-icon tf-icons fas fa-solid fa-money-check"></i>
                         <div class="mx-3" data-i18n="Judul Pembayaran"> Kategori Pembayaran</div>
                     </a>
                 </li>
                 @can('view-pembayaran')
                 <li class="menu-item {{ Request::routeIs('dashboard.datamaster.charge.*') ? 'active' : ''  }}">
                     <a class="menu-link"
                         href="{{ route('dashboard.datamaster.charge.index') }}">
                         <i class="menu-icon tf-icons fas fa-solid fa-file-invoice"></i>
                         <div class="mx-3" data-i18n="Invoice">Charge</div>
                     </a>
                 </li>
                 @endcan
                 <li class="menu-item {{ Request::routeIs('dashboard.notifikasi.*') ? 'active' : ''  }}">
                     <a class="menu-link"
                         href="{{ route('dashboard.notifikasi.index') }}">
                         <i class="menu-icon tf-icons fas fa-solid fa-bell"></i>
                         <div class="mx-3" data-i18n="Invoice">Notifikasi</div>
                     </a>
                 </li>
             </ul>
         </li>
         @endcanany
         @canany('role: admin|superadmin')
         <li class="menu-header small text-uppercase"><span class="menu-header-text">Pengunjung</span></li>

         <li class="menu-item {{ Request::routeIs('dashboard.url.visitor.*') ? 'active' : '' }}">
            <a class="menu-link" href="{{ route('dashboard.url.visitor.index') }}">
                <i class="menu-icon tf-icons bx bx-link"></i>
                <div data-i18n="Halaman Pengunjung">Halaman Pengunjung</div>
            </a>
        </li>

        <li class="menu-header small text-uppercase"><span class="menu-header-text">Webhook</span></li>

         <li class="menu-item {{ Request::routeIs('dashboard.monitoring.whatsapp.*') ? 'active' : '' }}">
            <a class="menu-link" href="{{ route('dashboard.monitoring.whatsapp') }}">
                <i class="menu-icon tf-icons bx bx-desktop-alt"></i>
                <div data-i18n="Halaman Pengunjung">WhatsApp Webhook</div>
            </a>
        </li>
         <li class="menu-item {{ Request::routeIs('dashboard.monitoring.whatsapp-error.*') ? 'active' : '' }}">
            <a class="menu-link" href="{{ route('dashboard.monitoring.whatsapp-error') }}">
                <i class="menu-icon tf-icons bx bx-desktop-alt"></i>
                <div data-i18n="Halaman Pengunjung">WhatsApp Error</div>
            </a>
        </li>

         <li class="menu-header small text-uppercase"><span class="menu-header-text">Kritik</span></li>

         <li class="menu-item {{ Request::routeIs('dashboard.kritik.saran.*') ? 'active' : '' }}">
            <a class="menu-link" href="{{ route('dashboard.kritik.saran.index') }}">
                <i class="menu-icon tf-icons bx bxs-comment"></i>
                <div data-i18n="Hero Banner">Kritik Saran</div>
            </a>
        </li>

         <li class="menu-header small text-uppercase"><span class="menu-header-text">Pengaturan Akses</span></li>
         <!-- Pengaturan -->
         <li class="menu-item {{ Request::routeIs('dashboard.pengaturan.*') ? 'open' : '' }}">
            <a href="javascript:void(0)" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-cog"></i>
                <div data-i18n="Pengaturan">Pengaturan Akses</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ Request::routeIs('dashboard.pengaturan.role.*') ? 'active' : ''  }}">
                    <a href="{{ route('dashboard.pengaturan.role.index') }}" class="menu-link">
                        <i class="menu-icon tf-icons bx bx-user"></i>
                        <div class="mx-3" data-i18n="Role">Role</div>
                    </a>
                </li>
                <li class="menu-item {{ Request::routeIs('dashboard.pengaturan.karyawan.*') ? 'active' : ''  }}">
                    <a href="{{ route('dashboard.pengaturan.karyawan.index') }}" class="menu-link">
                        <i class="menu-icon tf-icons bx bx-group"></i>
                        <div class="mx-3" data-i18n="Karyawan">Karyawan</div>
                    </a>
                </li>
                @can('role: superadmin')
                <li class="menu-item {{ Request::routeIs('dashboard.pengaturan.user.*') ? 'active' : ''  }}">
                    <a href="{{ route('dashboard.pengaturan.user.index') }}" class="menu-link">
                        <i class="menu-icon tf-icons fa fa-users"></i>
                        <div class="mx-3" data-i18n="User">User</div>
                    </a>
                </li>
                @endcan
            </ul>
        </li>
        @endcan
     </ul>
 </aside>
