<!-- TopBar -->
<nav class="navbar navbar-expand navbar-light bg-navbar topbar mb-4 static-top">
    <button id="sidebarToggleTop" class="btn btn-link rounded-circle mr-3">
        <i class="fa fa-bars"></i>
    </button>
    <ul class="navbar-nav ml-auto">
        @can('role: Admin-web')
        <li class="nav-item dropdown no-arrow mx-1">
            <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button"
                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-bell fa-fw"></i>
                <span class="badge badge-danger badge-counter" id="activity_count"></span>
            </a>
            <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in"
                aria-labelledby="alertsDropdown">
                <h6 class="dropdown-header">
                    Notifications
                </h6>
                <a class="dropdown-item d-flex align-items-center" href="#" id="activity_link">
                    <div class="mr-3">
                        <div class="icon-circle bg-primary">
                            <i class="fas fa-file-alt text-white" id="activity_icon"></i>
                        </div>
                    </div>
                    <div>
                        <div class="small text-gray-500" id="activity_date"></div>
                        <span class="font-weight-bold" id="activity_data">A new monthly report is ready to download!</span>
                    </div>
                </a>
                <a class="dropdown-item text-center small text-gray-500" href="{{ route('dashboard.datamaster.activity.index') }}">Show All Notifications</a>
            </div>
        </li>
        @endcan
        <div class="topbar-divider d-none d-sm-block"></div>
        <li class="nav-item dropdown no-arrow">
            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                @if(Auth::user()->avatar === 'default.jpg')
                <img src="{{ asset('asset_dashboard/img/default.jpg') }}" alt="Profile" id="" class="img-profile rounded-circle">
                @else
                <img src="{{ asset('storage/img/profile/'. Auth::user()->avatar) }}" class="img-profile rounded-circle" alt="Profile"
                    id="profile">
                @endif
                {{-- <img class="img-profile rounded-circle" src="{{ asset('assetimg/boy.png') }}" style="max-width: 60px"> --}}
                <span class="ml-2 d-none d-lg-inline text-white small">{{ Auth::user()->name }}</span>
            </a>
            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                aria-labelledby="userDropdown">
                <a class="dropdown-item" href="{{ route('dashboard.pengaturan.profile.index') }}">
                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                    Profile
                </a>
                <div class="dropdown-divider"></div>
                <a href="#" class="dropdown-item swal-logout" title="Logout">
                    <form action="{{ route('logout') }}" id="logout-form" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                    </form>
                    <i class="fas fa-sign-out-alt mr-2"></i> <span
                        class="d-none d-sm-inline-block">Logout</span>
                </a>
            </div>
        </li>
    </ul>
</nav>
<!-- Topbar -->



