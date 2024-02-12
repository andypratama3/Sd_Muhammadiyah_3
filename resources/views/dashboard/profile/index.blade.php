@extends('layouts.dashboard')
@section('title', 'Profile')
@push('css')
{{-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous"> --}}

@endpush
@section('content')
<!-- Container Fluid-->
{{-- <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
        <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ Breadcrumbs::render('dashboard') }}">
{{ Breadcrumbs::render('dashboard') }}</a></li>
<li class="breadcrumb-item active" aria-current="page">Dashboard</li>
</ol>
</div> --}}

<section class="section profile">
    <div class="row">
        <div class="col-xl-4">

            <div class="card">
                <div class="card-body profile-card pt-4 d-flex flex-column align-items-center">
                    <img src="{{ asset('storage/img/siswa/Siswa_andy_20240204172926.jpg') }}" alt="Profile"
                        class="rounded-circle mb-4" style="height: 180px; width: 60%;" id="profile">
                    <h2>{{ Auth::user()->name }}</h2>
                    <h3>{{ Auth::user()->roles->first()->name }}</h3>
                    <div class="social-links mt-2">
                        <a href="#" class="twitter"><i class="bi bi-twitter"></i></a>
                        <a href="#" class="facebook"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="instagram"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="linkedin"><i class="bi bi-linkedin"></i></a>
                    </div>
                </div>
            </div>

        </div>

        <div class="col-xl-8">

            <div class="card">
                <div class="card-body pt-3">
                    <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link " id="pills-home-tab" data-toggle="pill" href="#profile-overview"
                                role="tab" aria-controls="profile-overview" aria-selected="true">Overview</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" id="pills-profile-tab" data-toggle="pill" href="#profile-edit"
                                role="tab" aria-controls="profile-edit" aria-selected="false">Profile Edit</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="pills-contact-tab" data-toggle="pill" href="#profile-settings"
                                role="tab" aria-controls="profile-settings" aria-selected="false">Settings</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="pills-contact-tab" data-toggle="pill"
                                href="#profile-change-password" role="tab" aria-controls="profile-change-password"
                                aria-selected="false">Change Password</a>
                        </li>
                    </ul>
                    {{-- custstom --}}

                    <div class="tab-content pt-2">
                        <div class="tab-pane fade  profile-overview mt-3" id="profile-overview">
                            <h5 class="card-title">Profile Details</h5>
                            <div class="row mt-2">
                                <div class="col-lg-3 col-md-4 label ">Nama</div>
                                <div class="col-lg-9 col-md-8">: {{ Auth::user()->name }}</div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-lg-3 col-md-4 label">Jabatan</div>
                                <div class="col-lg-9 col-md-8">: {{ Auth::user()->roles->first()->name }}</div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-lg-3 col-md-4 label">Jenis Kelamin</div>
                                <div class="col-lg-9 col-md-8">: {{ $karyawan->sex }}</div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-lg-3 col-md-4 label">HP</div>
                                <div class="col-lg-9 col-md-8">: (+62) {{ $karyawan->phone }}</div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-lg-3 col-md-4 label">Email</div>
                                <div class="col-lg-9 col-md-8">: {{ Auth::user()->email }}</div>
                            </div>

                        </div>

                        <div class="tab-pane fade profile-edit pt-3 show active" id="profile-edit" id="profile_edit">

                            <!-- Profile Edit Form -->
                            <button class="btn btn-primary float-right" id="button_edit_profile"><i
                                    class="fas fa-gear"></i> Edit Data</button>

                                <div class="row mb-3">
                                    <label for="profileImage" class="col-md-4 col-lg-3 col-form-label">Profile
                                        Image</label>
                                    <div class="col-md-8 col-lg-9">
                                        <img src="assets/img/profile-img.jpg" alt="Profile" id="profile">
                                        <div class="pt-2">
                                            <label for="profileImage" class="btn btn-primary btn-sm" title="Upload new profile image">
                                                <i class="fa fa-upload"></i> Upload Image
                                            </label>
                                            <input type="file" id="profileImage" name="profileImage" style="display: none;">
                                            <label for="profile_delete" class="btn btn-danger btn-sm">
                                                <i class="fa fa-trash"></i> Delete Profile
                                            </label>
                                            <button class="d-none" id="profile_delete"></button>
                                        </div>
                                    </div>
                                </div>
                                <form>
                                <div class="row mb-3">
                                    <form action="">
                                        <label for="fullName" class="col-md-4 col-lg-3 col-form-label">Nama</label>
                                        <div class="col-md-8 col-lg-9">
                                            <input name="name" type="text" class="form-control" id="fullName"
                                                value="{{ $karyawan->name }}" readonly>
                                        </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="sex" class="col-md-4 col-lg-3 col-form-label">Jenis Kelamin</label>
                                    <div class="col-md-8 col-lg-9">
                                        <select name="sex" class="form-control" id="sex" disabled>
                                            @if($karyawan->sex == null)
                                            <option selected disabled>Pilih Jenis Kelamin</option>
                                            @else
                                            <option value="{{ $karyawan->sex }}" selected>{{ $karyawan->sex }}</option>
                                            @endif
                                            <option value="laki-laki">Laki Laki</option>
                                            <option value="perempuan">Perempuan</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="company" class="col-md-4 col-lg-3 col-form-label">phone</label>
                                    <div class="col-md-8 col-lg-9">
                                        <input name="tel" type="text" class="form-control" id="phone"
                                            value="{{ $karyawan->phone }}" readonly>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="email" class="col-md-4 col-lg-3 col-form-label">Email</label>
                                    <div class="col-md-8 col-lg-9">
                                        <input name="email" type="text" class="form-control" id="email"
                                            value="{{ $karyawan->email }}" readonly>
                                    </div>
                                </div>
                            <div class="text-center">
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                            </div>
                        </form><!-- End Profile Edit Form -->

                    </div>

                    <div class="tab-pane fade pt-3" id="profile-settings">
                        <!-- Settings Form -->
                        <form>

                            <div class="row mb-3">
                                <label for="fullName" class="col-md-4 col-lg-3 col-form-label">Email
                                    Notifications</label>
                                <div class="col-md-8 col-lg-9">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="changesMade" checked>
                                        <label class="form-check-label" for="changesMade">
                                            Changes made to your account
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="newProducts" checked>
                                        <label class="form-check-label" for="newProducts">
                                            Information on new products and services
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="proOffers">
                                        <label class="form-check-label" for="proOffers">
                                            Marketing and promo offers
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="securityNotify" checked
                                            disabled>
                                        <label class="form-check-label" for="securityNotify">
                                            Security alerts
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="text-center">
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                            </div>
                        </form><!-- End settings Form -->
                    </div>
                    <div class="tab-pane fade pt-3" id="profile-change-password">
                        <!-- Change Password Form -->

                        <form method="POST" action="{{ route('user-password.update') }}">
                            @method('PUT')
                            @csrf
                            <div class="row mb-3">
                                <label for="currentPassword" class="col-md-4 col-lg-3 col-form-label">Current
                                    Password</label>
                                <div class="col-md-8 col-lg-9">
                                    <input name="current_password" type="password" class="form-control"
                                        id="currentPassword">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="newPassword" class="col-md-4 col-lg-3 col-form-label">New
                                    Password</label>
                                <div class="col-md-8 col-lg-9">
                                    <input name="password" type="password" class="form-control" id="newPassword">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="renewPassword" class="col-md-4 col-lg-3 col-form-label">Re-enter New
                                    Password</label>
                                <div class="col-md-8 col-lg-9">
                                    <input name="password_confirmation" type="password" class="form-control"
                                        id="password_confirmation">
                                </div>
                            </div>

                            <div class="text-center">
                                <button type="submit" class="btn btn-primary">Change Password</button>
                            </div>
                        </form><!-- End Change Password Form -->

                    </div>

                </div><!-- End Bordered Tabs -->

            </div>
        </div>

    </div>
    </div>
</section>
@push('js')
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"
    integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous">
</script>
{{-- <script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.3/dist/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script> --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/js/bootstrap.min.js"
    integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous">
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
    const button_edit_profile = document.querySelector('#button_edit_profile');
    const inputElements = document.querySelectorAll("input");
    const selectOptions = document.getElementById('sex');

    button_edit_profile.addEventListener('click', function () {
        if (button_edit_profile.classList.contains('editing')) {
            selectOptions.disabled = false;
            inputElements.forEach(element => {
                element.removeAttribute('readonly');
            });
            button_edit_profile.classList.remove('editing');
        } else {
            selectOptions.disabled = true;
            inputElements.forEach(element => {
                element.setAttribute('readonly', false);
            });
            button_edit_profile.classList.add('editing');
        }
    });

    // delete profile button
    profile = document.getElementById('profile').getAttribute('src');
    const deleteProfileButton = document.getElementById('profile_delete');
    const url = '{{ route("dashboard.pangaturan.profile.removAvatar") }}';
    deleteProfileButton.addEventListener('click', function () {
        fetch('your_delete_endpoint_url', {
            method: 'POST',
            data: {
                profile: profile,
            }
        })
        .then(response => {
            // Check if the response is successful
            if (response.ok) {
                // Assuming the profile image is deleted successfully, you can update the UI accordingly
                // For example, you can remove the image from the DOM or replace it with a default image
                document.getElementById('profile').setAttribute('src', 'path_to_default_image');
            } else {
                // Handle error case if needed
                console.error('Failed to delete profile image');
            }
        })
        .catch(error => {
            console.error('Error deleting profile image:', error);
        });
    });

    // upload file in js
    const file_upload = document.getElementById('profile')


});

</script>
@endpush
@endsection
