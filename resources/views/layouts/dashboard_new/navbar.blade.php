@push('css')

<style>
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

    @media (min-width: 1200px) {
        .navbar-expand-xl .navbar-nav .dropdown-menu {
            margin-top: 350px !important;
        }
    }

    @media (min-width: 992px) {
        .navbar-expand-lg .navbar-nav .dropdown-menu #activity_items>* {
            /* max word wrap max 10*/
            word-break: break-word;
            hyphens: auto;
            max-width: 10em;
        }
    }
</style>

@endpush
<nav class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme"
    id="layout-navbar">
    <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
        <a class="px-0 nav-item nav-link me-xl-4" href="javascript:void(0)">
            <i class="bx bx-menu bx-sm"></i>
        </a>
    </div>

    <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
        <!-- Search -->
        <div class="navbar-nav align-items-center">
            <div class="nav-item d-flex align-items-center">
                <h4 class="pt-3">SD MUHAMMADIYAH 3 SAMARINDA</h4>
            </div>

        </div>

        <!-- /Search -->
        <ul class="flex-row navbar-nav align-items-center ms-auto ">
            <li class="mx-1 mr-2 nav-item dropdown no-arrow float-end "
                style="list-style: none !important; background-color: transparent !important;">
                <a class="nav-link dropdown-toggle" style="z-index: 9999 !important;" href="#" id="alertsDropdown"
                    role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-bell fa-fw"></i>
                    <span class="badge badge-danger badge-counter" style="color: red !important;"
                        id="activity_count"></span>
                </a>
                <div class="shadow dropdown-list dropdown-menu animated--grow-in" spellcheck=""
                    aria-labelledby="alertsDropdown" id="activity_list">
                    <h6 class="dropdown-header">Activity</h6>
                    <div id="activity_items"></div>
                    <a class="text-center dropdown-item small text-black-500"
                        href="{{ route('dashboard.notifikasi.index') }}">Show All Activity</a>
                </div>
            </li>



            <!-- User -->
            <li class="nav-item navbar-dropdown dropdown-user dropdown">
                <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                    <div class="avatar avatar-online">
                        @if(Auth::user()->avatar === 'default.jpg')
                        <img src="{{ asset('asset_dashboard_new/img/avatars/1.png') }}" alt
                            class="h-auto w-px-40 rounded-circle" />
                        @else
                        <img src="{{ asset('storage/img/profile/'. Auth::user()->avatar) }}"
                            class="h-auto w-px-40 rounded-circle" alt="Profile" id="profile">
                        @endif
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <a class="dropdown-item" href="#">
                            <div class="d-flex">
                                <div class="flex-shrink-0 me-3">
                                    <div class="avatar avatar-online">
                                        @if(Auth::user()->avatar === 'default.jpg')
                                        <img src="{{ asset('asset_dashboard_new/img/avatars/1.png') }}" alt
                                            class="h-auto w-px-40 rounded-circle" />
                                        @elseif(Auth::user()->avatar == null)
                                        <img src="{{ asset('asset_dashboard_new/img/avatars/1.png') }}" alt
                                            class="h-auto w-px-40 rounded-circle" />
                                        @else
                                        <img src="{{ asset('storage/img/profile/'. Auth::user()->avatar) }}"
                                            class="h-auto w-px-40 rounded-circle" alt="Profile" id="profile">
                                        @endif
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <span class="fw-semibold d-block">{{ Auth::user()->name }}</span>
                                    <small class="text-muted">{{ Auth::user()->roles->first()->name }}</small>
                                </div>
                            </div>
                        </a>
                    </li>
                    <li>
                        <div class="dropdown-divider"></div>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('dashboard.pengaturan.profile.index') }}">
                            <i class="bx bx-user me-2"></i>
                            <span class="align-middle">My Profile</span>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('dashboard.pengaturan.profile.index') }}">
                            <i class="bx bx-cog me-2"></i>
                            <span class="align-middle">Settings</span>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="#">
                            <span class="align-middle d-flex align-items-center">
                                <i class="flex-shrink-0 bx bx-credit-card me-2"></i>
                                <span class="align-middle flex-grow-1">Billing</span>
                                <span class="flex-shrink-0 badge badge-center rounded-pill bg-danger w-px-20 h-px-20">
                                    4
                                </span>
                            </span>
                        </a>
                    </li>
                    <li>
                        <div class="dropdown-divider"></div>
                    </li>
                    <li>
                        <a href="#" class="dropdown-item swal-logout" title="Logout">
                            <form action="{{ route('logout') }}" id="logout-form" method="POST"
                                enctype="multipart/form-data">
                                @csrf
                            </form>
                            <i class="mr-2 fas fa-sign-out-alt"></i>
                            <span class="d-none d-sm-inline-block">Logout</span>
                        </a>
                    </li>
                </ul>
            </li>
        </ul>
    </div>
</nav>

@push('js')

<script type="text/javascript">
    document.addEventListener("DOMContentLoaded", function () {
        const dropdownToggle = document.getElementById("alertsDropdown");
        const dropdownMenu = document.querySelector(".dropdown-menu");

        dropdownToggle.addEventListener("click", function (event) {
            event.preventDefault();
            dropdownMenu.classList.toggle("show");
        });

        // Klik di luar dropdown untuk menutupnya
        document.addEventListener("click", function (event) {
            if (!dropdownToggle.contains(event.target) && !dropdownMenu.contains(event.target)) {
                dropdownMenu.classList.remove("show");
            }
        });
    });
</script>
@endpush
