@extends('layouts.dashboard')
@section('title', 'Profile')
@push('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css"
        integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous" nonce="{{ csp_nonce() }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.6/cropper.css"  nonce="{{ csp_nonce() }}"/>
    <style type="text/stylesheet">
        .image {
            opacity: 1;
            display: block;
            transition: .5s ease;
            backface-visibility: hidden;
        }

        .label {
            transition: .5s ease;
            opacity: 0;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            -ms-transform: translate(-50%, -50%);
            text-align: center;
        }

        .profile-edit:hover .image {
            opacity: 0.3;
        }

        .profile-edit:hover .label {
            opacity: 1;
        }

        .text {
            /* background-color: #04AA6D; */
            color: #515151;
            font-size: 30px;
            /* padding: 10px 50px; */
        }

        .img {
            display: block;
            max-width: 100%;
        }

        .cropper-container {
            width: 100%;
            height: 10%;
        }
        .cropper-container cropper-bg {
            width: 100% !important;
            height: 50% !important;
        }
        .cropper-container cropper-bg img {
            width: 50% !important;
            height: 10% !important;
        }
        .preview {
            overflow: hidden;
            width: 160px;
            height: 160px;
            margin: 10px;
            border: 1px solid red;
        }


    </style>
@endpush
@section('content')
<section class="section profile">
    <div class="row">
        <div class="col-xl-4">
            <div class="card">
                <div class="card-body profile-card pt-4 d-flex flex-column align-items-center">
                    @if (Auth::user()->avatar === 'default.jpg')
                        <img src="{{ asset('asset_dashboard_new/img/avatars/1.png') }}" alt="Profile" id=""
                            class="w-25 img-profile rounded-circle">
                    @else
                    <a href="{{ asset('storage/img/profile/' . Auth::user()->avatar) }}">
                        <img src="{{ asset('storage/img/profile/' . Auth::user()->avatar) }}"
                            class="img-profile rounded-circle" alt="Profile" id="profile">
                    </a>
                    @endif
                    <h2>{{ Auth::user()->name }}</h2>
                    @if (empty(Auth::user()->roles->first()->name))
                        <h3>User</h3>
                    @else
                        <h3>{{ Auth::user()->roles->first()->name }}</h3>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-xl-8">
            <div class="card">
                <div class="card-body pt-3">
                    {{-- tab option --}}
                    <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
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
                    <div class="tab-content pt-2">
                        <div class="tab-pane fade show active profile-overview mt-3" id="profile-overview">
                            <h5 class="card-title">Profile Details</h5>
                            <div class="row mt-2">
                                <div class="col-md-4 col-lg-3 col-form-label">Nama</div>
                                <div class="col-lg-9 col-md-8">: {{ Auth::user()->name }}</div>
                            </div>
                            <hr>
                            <div class="row">
                                @if (empty(Auth::user()->roles->first()->name))
                                    <div class="col-lg-3 col-md-4 col-form-label">Status</div>
                                    <div class="col-lg-9 col-md-8">: User</div>
                                @else
                                    <div class="col-lg-3 col-md-4 col-form-label">Jabatan</div>
                                    <div class="col-lg-9 col-md-8">: {{ Auth::user()->roles->first()->name }}</div>
                                @endif
                            </div>
                            @role('karyawan|admin')
                                <hr>
                                <div class="row">
                                    <div class="col-lg-3 col-md-4 col-form-label">Jenis Kelamin</div>
                                    <div class="col-lg-9 col-md-8">: {{ $karyawan->sex }}</div>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-lg-3 col-md-4 col-form-label">HP</div>
                                    <div class="col-lg-9 col-md-8">: (+62) {{ $karyawan->phone }}</div>
                                </div>
                            @endrole
                            <hr>
                            <div class="row">
                                <div class="col-lg-3 col-md-4 col-form-label">Email</div>
                                <div class="col-lg-9 col-md-8">: {{ Auth::user()->email }}</div>
                            </div>
                        </div>

                        <div class="tab-pane fade profile-edit pt-3" id="profile-edit">
                            <!-- Profile Edit Form -->
                            @if ($karyawan && Auth::id() == $karyawan->user_id)
                                <button class="btn btn-primary float-right" id="button_edit_profile">
                                    <i class="fas fa-gear"></i> Edit Data
                                </button>
                            @else
                                <button class="btn btn-primary float-right" id="button_edit_profile_user">
                                    <i class="fas fa-gear"></i> Edit Data
                                </button>
                            @endif
                            <div class="row mb-3">
                                <label for="profileImage" class="col-md-4 col-lg-3 col-form-label">Profile
                                    Image</label>
                                <div class="col-md-8 col-lg-9">
                                    @if (Auth::user()->avatar === 'default.jpg')
                                        <img src="{{ asset('asset_dashboard_new/img/avatars/1.png') }}" alt="Profile"
                                            id="" class=" w-25">
                                    @else
                                        <img src="{{ asset('storage/img/profile/' . Auth::user()->avatar) }}"
                                            class=" w-25" alt="Profile" id="profile">
                                    @endif

                                    <div class="pt-2">
                                        <label for="profileImage" class="btn btn-primary btn-sm"
                                            title="Upload new profile image">
                                            <i class="fa fa-upload"></i> Upload Image
                                        </label>
                                        <input type="file" id="profileImage" name="profileImage"
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
                                    <div class="row mb-3">
                                        <label for="fullName" class="col-md-4 col-lg-3 col-form-label">Nama</label>
                                        <div class="col-md-8 col-lg-9">
                                            <input name="name" type="text" class="form-control" id="fullName"
                                                value="{{ $karyawan->name }}" readonly>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label for="sex" class="col-md-4 col-lg-3 col-form-label">Jenis
                                            Kelamin</label>
                                        <div class="col-md-8 col-lg-9">
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

                                    <div class="row mb-3">
                                        <label for="company" class="col-md-4 col-lg-3 col-form-label">phone</label>
                                        <div class="col-md-8 col-lg-9">
                                            <input name="phone" type="text" class="form-control" id="phone"
                                                value="{{ $karyawan->phone }}" readonly>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <label for="email" class="col-md-4 col-lg-3 col-form-label">Email</label>
                                        <div class="col-md-8 col-lg-9">
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
                                    <div class="row mb-3">
                                        <label for="fullName" class="col-md-4 col-lg-3 col-form-label">Nama</label>
                                        <div class="col-md-8 col-lg-9">
                                            <input name="name" type="text" class="form-control" id="fullName"
                                                value="{{ Auth::user()->name }}" readonly>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <label for="email" class="col-md-4 col-lg-3 col-form-label">Email</label>
                                        <div class="col-md-8 col-lg-9">
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
                        <div class="tab-pane fade pt-3" id="profile-settings">
                            <!-- Settings Form -->
                            <form>
                                <div class="row mb-3">
                                    <div class="form-group">

                                    </div>
                                    <label for="email" class="col-md-4 col-lg-3 col-form-label">Email</label>
                                    <div class="col-md-8 col-lg-9">
                                        <input name="email" type="text" class="form-control"
                                            value="{{ Auth::user()->email }}" readonly>
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
                                        <input name="password" type="password" class="form-control"
                                            id="newPassword">
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
        {{-- crop modal for photo --}}
        <div class="modal fade show" id="modal" tabindex="-1" role="dialog" aria-labelledby="modalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header" id="modalLabel">
                        <h4 class="modal-title">Crop Foto</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body mx-3">
                        <div class="img-container">
                            <div class="row">
                                <img class="img" id="image-crop"
                                    src="{{ asset('storage/img/profile/' . Auth::user()->avatar) }}">
                                {{-- <div class="col-md-4"> --}}
                                    <div class="preview"></div>
                                {{-- </div> --}}
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                        <button type="button" class="btn btn-primary" id="crop">Simpan</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@push('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/js/bootstrap.min.js"
        integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous">
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.6/cropper.js" nonce="{{ csp_nonce() }}"></script>
    <script>
        $(document).ready(function() {
            // cropp foto
            var $modal = $('#modal');
            var image = document.getElementById('image-crop');
            var cropper;

            $("body").on("change", "#profileImage", function(e) {
                var files = e.target.files;
                var done = function(url) {
                    image.src = url;
                    $modal.modal('show');
                };
                var reader;
                var file;
                var url;
                if (files && files.length > 0) {
                    file = files[0];
                    if (URL) {
                        done(URL.createObjectURL(file));
                    } else if (FileReader) {
                        reader = new FileReader();
                        reader.onload = function(e) {
                            done(reader.result);
                        };
                        reader.readAsDataURL(file);
                    }
                }
            });
            $modal.on('shown.bs.modal', function() {
                cropper = new Cropper(image, {
                    aspectRatio: 1,
                    viewMode: 3,
                    preview: '.preview',
                });
            }).on('hidden.bs.modal', function() {
                cropper.destroy();
                cropper = null;
            });
            $("#crop").click(function() {
                canvas = cropper.getCroppedCanvas({
                    width: 160,
                    height: 160,
                });
                canvas.toBlob(function(blob) {
                    url = URL.createObjectURL(blob);
                    var reader = new FileReader();
                    reader.readAsDataURL(blob);
                    reader.onloadend = function() {
                        var base64data = reader.result;
                        $.ajaxSetup({
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                                    'content')
                            }
                        });

                        $.ajax({
                            type: "POST",
                            dataType: "json",
                            url: "{{ route('dashboard.pangaturan.profile.crop_image') }}",
                            data: {
                                '_token': $('meta[name="_token"]').attr('content'),
                                'image': base64data
                            },
                            success: function(data) {
                                $modal.modal('hide');
                                swal({
                                        title: 'Success!',
                                        text: data['success'],
                                        icon: 'success',
                                    })
                                    .then(function() {
                                        location.reload();
                                    });
                            }
                        });
                    }
                });
            })
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
            const url = '{{ route('dashboard.pangaturan.profile.removAvatar') }}';
            deleteProfileButton.addEventListener('click', function() {
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
                            console.error('Failed to delete profile image');
                        }
                    })
                    .then(data => {
                        swal({
                            icon: 'success',
                            title: 'Success',
                            text: data.message,
                        }).then(() => {
                            // Reload the page
                            location.reload();
                        });
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
