@push('css')
<style>
    /* ==========================
       ACTIVITY DROPDOWN FIX
    ========================== */
    #activity_list {
        min-width: 320px;
        max-width: 360px;
        top: 100% !important;   /* ⬅️ nempel ke icon */
        margin-top: 12px;
        right: 0;
        left: auto;
        transform: none !important;
        z-index: 1080;
    }

    #activity_items {
        max-height: 320px;
        overflow-y: auto;
    }

    #activity_items > * {
        word-break: break-word;
        hyphens: auto;
        padding: 10px 16px;
    }
    /* ==========================
       AVATAR FIX (FULL ASPECT)
    ========================== */
    .avatar {
        width: 40px;
        height: 40px;
    }

    .avatar img {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
        /* ⬅️ PENTING */
        object-position: center;
        display: block;
    }

    /* ==========================
       ICON CIRCLE
    ========================== */
    .icon-circle {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
    }

    .bg-success {
        background-color: #28a745 !important;
    }

    .bg-primary {
        background-color: #007bff !important;
    }

    .bg-danger {
        background-color: #dc3545 !important;
    }

    .bg-warning {
        background-color: #ffc107 !important;
    }

    /* ==========================
       DROPDOWN FIX
    ========================== */
    .dropdown-menu {
        z-index: 1050;
    }

    @media (min-width: 992px) {
        #activity_items>* {
            word-break: break-word;
            hyphens: auto;
            max-width: 14em;
        }
    }
</style>
@endpush
<nav class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme"
    id="layout-navbar">

    <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 d-xl-none">
        <a class="px-0 nav-item nav-link" href="javascript:void(0)">
            <i class="bx bx-menu bx-sm"></i>
        </a>
    </div>

    <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">

        <!-- TITLE -->
        <div class="navbar-nav align-items-center">
            <h6 class="pt-3 mb-0">SD MUHAMMADIYAH 3 SAMARINDA</h4>
        </div>

        <ul class="navbar-nav align-items-center ms-auto">

            <!-- ======================
                 NOTIFICATION
            ====================== -->
            {{-- <li class="nav-item dropdown me-2">
                <a class="nav-link" href="#" id="alertsDropdown">
                    <i class="fas fa-bell fa-fw"></i>
                    <span class="badge badge-danger badge-counter" id="activity_count"></span>
                </a>

                <div class="shadow dropdown-menu dropdown-menu-end" id="activity_list">
                    <h6 class="dropdown-header">Activity</h6>
                    <div id="activity_items"></div>
                    <a class="text-center dropdown-item small" href="{{ route('dashboard.notifikasi.index') }}">
                        Show All Activity
                    </a>
                </div>
            </li> --}}

            <!-- ======================
                 USER DROPDOWN
            ====================== -->
            <li class="nav-item dropdown-user dropdown">
                <a class="nav-link dropdown-toggle hide-arrow" href="#" data-bs-toggle="dropdown">
                    <div class="avatar avatar-online">
                        <img src="{{ Auth::user()->avatar && Auth::user()->avatar !== 'default.jpg'
                            ? asset('storage/img/profile/' . Auth::user()->avatar)
                            : asset('asset_dashboard_new/img/avatars/1.png') }}" alt="Profile" style="border-radius: 20px;">
                    </div>
                </a>

                <ul class="dropdown-menu dropdown-menu-end">

                    <li class="dropdown-item">
                        <div class="d-flex align-items-center">
                            <div class="me-3 avatar avatar-online">
                                <img src="{{ Auth::user()->avatar && Auth::user()->avatar !== 'default.jpg'
                                    ? asset('storage/img/profile/' . Auth::user()->avatar)
                                    : asset('asset_dashboard_new/img/avatars/1.png') }}" alt="Profile" style="border-radius: 20px;" >
                            </div>
                            <div>
                                <span class="fw-semibold d-block">{{ Auth::user()->name }}</span>
                                <small class="text-muted">{{ Auth::user()->roles->first()->name }}</small>
                            </div>
                        </div>
                    </li>

                    <li>
                        <div class="dropdown-divider"></div>
                    </li>

                    <li>
                        <a class="dropdown-item" href="{{ route('dashboard.pengaturan.profile.index') }}">
                            <i class="bx bx-user me-2"></i> My Profile
                        </a>
                    </li>

                    <li>
                        <a class="dropdown-item" href="{{ route('dashboard.pengaturan.profile.index') }}">
                            <i class="bx bx-cog me-2"></i> Settings
                        </a>
                    </li>

                    <li>
                        <div class="dropdown-divider"></div>
                    </li>

                    <li>
                        <a href="#" class="dropdown-item swal-logout">
                            <form id="logout-form" action="{{ route('logout') }}" method="POST">
                                @csrf
                            </form>
                            <i class="bx bx-power-off me-2"></i> Logout
                        </a>
                    </li>
                </ul>
            </li>

        </ul>
    </div>
</nav>
@push('js')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const toggle = document.getElementById('alertsDropdown');
        const menu = document.getElementById('activity_list');

        toggle.addEventListener('click', e => {
            e.preventDefault();
            e.stopPropagation();
            menu.classList.toggle('show');
        });

        document.addEventListener('click', e => {
            if (!menu.contains(e.target)) {
                menu.classList.remove('show');
            }
        });
    });
</script>
@endpush
