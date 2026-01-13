@extends('layouts.dashboard')
@section('title', 'Profile')
@push('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css"
        integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous" nonce="{{ csp_nonce() }}">
    <style type="text/css">
        .image {
            opacity: 1;
            display: block;
            transition: 0.5s ease;
            backface-visibility: hidden;
            max-width: 100%;
            height: auto;
        }

        .label {
            transition: 0.5s ease;
            opacity: 0;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            font-size: 18px;
            color: #fff;
            background: rgba(0, 0, 0, 0.6);
            padding: 10px 20px;
            border-radius: 5px;
        }

        .profile-edit {
            position: relative;
            display: inline-block;
        }

        .profile-edit:hover .image {
            opacity: 0.3;
        }

        .profile-edit:hover .label {
            opacity: 1;
        }

        .text {
            color: #515151;
            font-size: 30px;
            text-align: center;
        }

        /* Responsif untuk layar kecil */
        @media (max-width: 768px) {
            .text {
                font-size: 24px;
            }

            .label {
                font-size: 16px;
            }
        }
    </style>
@endpush

@section('content')
<section class="section profile">
    <div class="row">
        <div class="col-xl-3 col-lg-4">
            <div class="card">
                <div class="pt-4 card-body profile-card d-flex flex-column align-items-center">
                    @if (Auth::user()->avatar === 'default.jpg')
                    <img src="{{ asset('asset_dashboard_new/img/avatars/1.png') }}" alt="Profile" id=""
                        class="img-profile rounded-circle" style="width: 120px; height: 120px; object-fit: cover;">
                    @else
                    <a href="{{ asset('storage/img/profile/' . Auth::user()->avatar) }}" class="text-underline-none">
                        <img src="{{ asset('storage/img/profile/' . Auth::user()->avatar) }}"
                            class="img-profile rounded-circle" alt="Profile" id="profile" style="width: 120px; height: 120px; object-fit: cover;">
                    </a>
                    @endif
                    <h2 class="mt-3">{{ Auth::user()->name }}</h2>
                    @if (empty(Auth::user()->roles->first()->name))
                    <h3>User</h3>
                    @else
                    <h3>{{ Auth::user()->roles->first()->name }}</h3>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-xl-9 col-lg-8">
            <div class="card">
                <div class="pt-3 card-body">
                    {{-- tab option --}}
                    <ul class="mb-3 nav nav-pills" id="pills-tab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="pills-home-tab" data-toggle="pill" href="#profile-overview"
                                role="tab" aria-controls="profile-overview" aria-selected="true">Overview</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="pills-profile-tab" data-toggle="pill" href="#profile-edit"
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
                    <div class="pt-2 tab-content">
                        <div class="mt-3 tab-pane fade show active profile-overview" id="profile-overview">
                            <h5 class="card-title">Profile Details</h5>
                            <div class="mt-2 row">
                                <div class="col-md-3 col-lg-2 col-form-label"><strong>Nama</strong></div>
                                <div class="col-lg-10 col-md-9">: {{ Auth::user()->name }}</div>
                            </div>
                            <hr>
                            <div class="row">
                                @if (empty(Auth::user()->roles->first()->name))
                                    <div class="col-lg-2 col-md-3 col-form-label"><strong>Status</strong></div>
                                    <div class="col-lg-10 col-md-9">: User</div>
                                @else
                                    <div class="col-lg-2 col-md-3 col-form-label"><strong>Jabatan</strong></div>
                                    <div class="col-lg-10 col-md-9">: {{ Auth::user()->roles->first()->name }}</div>
                                @endif
                            </div>
                            @role('karyawan|admin')
                            <hr>
                            <div class="row">
                                <div class="col-lg-2 col-md-3 col-form-label"><strong>Jenis Kelamin</strong></div>
                                <div class="col-lg-10 col-md-9">: {{ $karyawan->sex }}</div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-lg-2 col-md-3 col-form-label"><strong>HP</strong></div>
                                <div class="col-lg-10 col-md-9">: (+62) {{ $karyawan->phone }}</div>
                            </div>
                            @endrole
                            <hr>
                            <div class="row">
                                <div class="col-lg-2 col-md-3 col-form-label"><strong>Email</strong></div>
                                <div class="col-lg-10 col-md-9">: {{ Auth::user()->email }}</div>
                            </div>
                        </div>

                        <div class="pt-3 tab-pane fade profile-edit" id="profile-edit">
                            <!-- Profile Edit Form -->
                            @if ($karyawan && Auth::id() == $karyawan->user_id)
                                <button class="float-right btn btn-primary" id="button_edit_profile">
                                    <i class="fas fa-gear"></i> Edit Data
                                </button>
                            @else
                                <button class="float-right btn btn-primary" id="button_edit_profile_user">
                                    <i class="fas fa-gear"></i> Edit Data
                                </button>
                            @endif
                            <div class="mb-3 row">
                                <label for="profileImage" class="col-md-3 col-lg-2 col-form-label">Profile
                                    Image</label>
                                <div class="col-md-9 col-lg-10">
                                    @if (Auth::user()->avatar === 'default.jpg')
                                        <img src="{{ asset('asset_dashboard_new/img/avatars/1.png') }}" alt="Profile"
                                            id="preview-image" class="mb-2 rounded-circle" style="width: 120px; height: 120px; object-fit: cover;">
                                    @else
                                        <img src="{{ asset('storage/img/profile/' . Auth::user()->avatar) }}"
                                            class="mb-2 rounded-circle" alt="Profile" id="preview-image" style="width: 120px; height: 120px; object-fit: cover;">
                                    @endif

                                    <div class="pt-2">
                                        <label for="profileImage" class="btn btn-primary btn-sm"
                                            title="Upload new profile image">
                                            <i class="fa fa-upload"></i> Upload Image
                                        </label>
                                        <input type="file" id="profileImage" name="profileImage" accept="image/*"
                                            style="display: none;">
                                        <label for="profile_delete" class="btn btn-danger btn-sm">
                                            <i class="fa fa-trash"></i> Delete Profile
                                        </label>
                                        <button class="d-none" id="profile_delete"></button>
                                    </div>
                                </div>
                            </div>
                            @if ($karyawan && Auth::id() == $karyawan->user_id)
                                <form action="{{ route('dashboard.pengaturan.profile.update', Auth::user()->id) }}"
                                    method="POST" enctype="multipart/form-data">
                                    @csrf
                                    @method('PUT')
                                    <div class="mb-3 row">
                                        <label for="fullName" class="col-md-3 col-lg-2 col-form-label">Nama</label>
                                        <div class="col-md-9 col-lg-10">
                                            <input name="name" type="text" class="form-control" id="fullName"
                                                value="{{ $karyawan->name }}" readonly>
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for="sex" class="col-md-3 col-lg-2 col-form-label">Jenis
                                            Kelamin</label>
                                        <div class="col-md-9 col-lg-10">
                                            <select name="sex" class="form-control" id="sex" disabled>
                                                @if (empty($karyawan->sex))
                                                    <option selected disabled>Pilih Jenis Kelamin</option>
                                                @else
                                                    <option value="{{ $karyawan->sex }}" selected>
                                                        {{ $karyawan->sex }}</option>
                                                @endif
                                                <option value="laki-laki">Laki Laki</option>
                                                <option value="perempuan">Perempuan</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="mb-3 row">
                                        <label for="company" class="col-md-3 col-lg-2 col-form-label">Phone</label>
                                        <div class="col-md-9 col-lg-10">
                                            <input name="phone" type="text" class="form-control" id="phone"
                                                value="{{ $karyawan->phone }}" readonly>
                                        </div>
                                    </div>

                                    <div class="mb-3 row">
                                        <label for="email" class="col-md-3 col-lg-2 col-form-label">Email</label>
                                        <div class="col-md-9 col-lg-10">
                                            <input name="email" type="text" class="form-control" id="email"
                                                value="{{ Auth::user()->email }}" readonly>
                                        </div>
                                    </div>
                                    <div class="text-center">
                                        <button type="submit" class="btn btn-primary" id="submit">Save
                                            Changes</button>
                                    </div>
                                </form><!-- End Profile Edit Form -->
                            @else
                                <form action="{{ route('dashboard.pengaturan.profile.update', Auth::user()->id) }}"
                                    method="POST" enctype="multipart/form-data">
                                    @csrf
                                    @method('PUT')
                                    <div class="mb-3 row">
                                        <label for="fullName" class="col-md-3 col-lg-2 col-form-label">Nama</label>
                                        <div class="col-md-9 col-lg-10">
                                            <input name="name" type="text" class="form-control" id="fullName"
                                                value="{{ Auth::user()->name }}" readonly>
                                        </div>
                                    </div>

                                    <div class="mb-3 row">
                                        <label for="email" class="col-md-3 col-lg-2 col-form-label">Email</label>
                                        <div class="col-md-9 col-lg-10">
                                            <input name="email" type="text" class="form-control"
                                                value="{{ Auth::user()->email }}" readonly>
                                        </div>
                                    </div>
                                    <div class="text-center">
                                        <button type="submit" class="btn btn-primary" id="submit">Save
                                            Changes</button>
                                    </div>
                                </form><!-- End Profile Edit Form -->
                            @endif

                        </div>
                        <div class="pt-3 tab-pane fade" id="profile-settings">
                            <!-- Settings Form -->
                            <form>
                                <div class="mb-3 row">
                                    <div class="form-group">

                                    </div>
                                    <label for="email" class="col-md-3 col-lg-2 col-form-label">Email</label>
                                    <div class="col-md-9 col-lg-10">
                                        <input name="email" type="text" class="form-control"
                                            value="{{ Auth::user()->email }}" readonly />
                                    </div>
                                </div>
                                <div class="text-center">
                                    <button type="submit" class="btn btn-primary">Save Changes</button>
                                </div>
                            </form><!-- End settings Form -->
                        </div>
                        <div class="pt-3 tab-pane fade" id="profile-change-password">
                            <!-- Change Password Form -->
                            <form method="POST" action="{{ route('user-password.update') }}">
                                @method('PUT')
                                @csrf
                                <div class="mb-3 row">
                                    <label for="currentPassword" class="col-md-3 col-lg-2 col-form-label">Current
                                        Password</label>
                                    <div class="col-md-9 col-lg-10">
                                        <input name="current_password" type="password" class="form-control"
                                            id="currentPassword">
                                    </div>
                                </div>

                                <div class="mb-3 row">
                                    <label for="newPassword" class="col-md-3 col-lg-2 col-form-label">New
                                        Password</label>
                                    <div class="col-md-9 col-lg-10">
                                        <input name="password" type="password" class="form-control"
                                            id="newPassword">
                                    </div>
                                </div>

                                <div class="mb-3 row">
                                    <label for="renewPassword" class="col-md-3 col-lg-2 col-form-label">Re-enter New
                                        Password</label>
                                    <div class="col-md-9 col-lg-10">
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
@endsection

@push('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous">
    </script>
    <script type="text/javascript">
        $(document).ready(function() {
            // Upload foto langsung tanpa crop
            $("#profileImage").on("change", function(e) {
                var files = e.target.files;
                if (files && files.length > 0) {
                    var file = files[0];

                    // Validasi tipe file
                    if (!file.type.match('image.*')) {
                        Swal.fire({
                            title: 'Error!',
                            text: 'Please select an image file',
                            icon: 'error',
                        });
                        return;
                    }

                    // Validasi ukuran file (max 2MB)
                    if (file.size > 2097152) {
                        Swal.fire({
                            title: 'Error!',
                            text: 'File size must be less than 2MB',
                            icon: 'error',
                        });
                        return;
                    }

                    var reader = new FileReader();
                    reader.onload = function(e) {
                        var base64data = e.target.result;

                        // Preview image
                        $("#preview-image").attr('src', base64data);

                        // Upload ke server
                        $.ajaxSetup({
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            }
                        });

                        $.ajax({
                            type: "POST",
                            dataType: "json",
                            url: "{{ route('dashboard.pangaturan.profile.upload_image') }}",
                            data: {
                                '_token': $('meta[name="_token"]').attr('content'),
                                'image': base64data
                            },
                            success: function(data) {
                                Swal.fire({
                                    title: 'Success!',
                                    text: data['success'],
                                    icon: 'success',
                                }).then(function() {
                                    location.reload();
                                });
                            },
                            error: function(xhr) {
                                Swal.fire({
                                    title: 'Error!',
                                    text: 'Failed to upload image',
                                    icon: 'error',
                                });
                            }
                        });
                    };
                    reader.readAsDataURL(file);
                }
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const button_edit_profile = document.querySelector('#button_edit_profile');
            const button_edit_profile_user = document.querySelector('#button_edit_profile_user');
            const inputElements = document.querySelectorAll("input");
            const selectOptions = document.getElementById('sex');
            const button_submit = document.querySelector('button[type="submit"]');

            // button submit disabled
            button_submit.disabled = true;

            if (button_edit_profile) {
                button_edit_profile.addEventListener('click', function() {
                    toggleEditMode(button_submit, selectOptions, inputElements, button_edit_profile);
                });
            }

            if (button_edit_profile_user) {
                button_edit_profile_user.addEventListener('click', function() {
                    toggleEditMode(button_submit, selectOptions, inputElements, button_edit_profile_user);
                });
            }

            // Function to toggle edit mode
            function toggleEditMode(submitButton, selectOptions, inputElements, editButton) {
                if (editButton.id === 'button_edit_profile') {
                    if (editButton.classList.contains('editing')) {
                        submitButton.disabled = false;
                        selectOptions.disabled = false;
                        inputElements.forEach(element => {
                            element.removeAttribute('readonly');
                        });
                        editButton.classList.remove('editing');
                    } else {
                        submitButton.disabled = true;
                        selectOptions.disabled = true;
                        inputElements.forEach(element => {
                            element.setAttribute('readonly', true);
                        });
                        editButton.classList.add('editing');
                    }
                } else if (editButton.id === 'button_edit_profile_user') {
                    if (editButton.classList.contains('editing')) {
                        submitButton.disabled = false;

                        inputElements.forEach(element => {
                            element.removeAttribute('readonly');
                        });
                        editButton.classList.remove('editing');
                    } else {
                        submitButton.disabled = true;
                        inputElements.forEach(element => {
                            element.setAttribute('readonly', true);
                        });
                        editButton.classList.add('editing');
                    }
                }
            }

            // delete profile button
            const deleteProfileButton = document.getElementById('profile_delete');
            deleteProfileButton.addEventListener('click', function() {
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You want to delete your profile picture?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch('{{ route('dashboard.pangaturan.profile.removAvatar') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                        })
                        .then(response => {
                            if (response.ok) {
                                return response.json();
                            } else {
                                throw new Error('Failed to delete profile image');
                            }
                        })
                        .then(data => {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: data.message,
                            }).then(() => {
                                location.reload();
                            });
                        })
                        .catch(error => {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Failed to delete profile image',
                            });
                        });
                    }
                });
            });
        });
    </script>
@endpush
