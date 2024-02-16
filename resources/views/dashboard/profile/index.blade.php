@extends('layouts.dashboard')
@section('title', 'Profile')
@push('css')
{{-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous"> --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.6/cropper.css" />
<style>
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
                    @if(Auth::user()->avatar != 'default.jpg')
                    <img src="{{ asset('storage/img/profile/'. Auth::user()->avatar) }}" alt="Profile" id="profile">
                    @elseif($karyawan->sex === 'laki-laki')
                    <img src="{{ asset('asset_dashboard/img/boy.png') }}" alt="Profile" id="">
                    @elseif($karyawan->sex === 'perempuan')
                    <img src="{{ asset('asset_dashboard/img/girl.png') }}" alt="Profile" id="" class=" w-25">
                    @elseif(Auth::user()->avatar == null)
                    <img src="{{ asset('asset_dashboard/img/default.png') }}" alt="Profile" id="" class=" w-25">
                    @endif
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
                                <div class="col-lg-3 col-md-4 col-form-label">Jabatan</div>
                                <div class="col-lg-9 col-md-8">: {{ Auth::user()->roles->first()->name }}</div>
                            </div>
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
                            <hr>
                            <div class="row">
                                <div class="col-lg-3 col-md-4 col-form-label">Email</div>
                                <div class="col-lg-9 col-md-8">: {{ Auth::user()->email }}</div>
                            </div>
                        </div>

                        <div class="tab-pane fade profile-edit pt-3" id="profile-edit" id="profile_edit">
                            <!-- Profile Edit Form -->
                            <button class="btn btn-primary float-right" id="button_edit_profile">
                                <i class="fas fa-gear"></i> Edit Data
                            </button>
                            <div class="row mb-3">
                                <label for="profileImage" class="col-md-4 col-lg-3 col-form-label">Profile
                                    Image</label>
                                <div class="col-md-8 col-lg-9">
                                    @if(Auth::user()->avatar != 'default.jpg')
                                    <img src="{{ asset('storage/img/profile/'. Auth::user()->avatar) }}" alt="Profile" id="profile">
                                    @else
                                    @if($karyawan->sex === 'laki-laki')
                                    <img src="{{ asset('asset_dashboard/img/boy.png') }}" alt="Profile" id="">
                                    @else
                                    <img src="{{ asset('asset_dashboard/img/girl.png') }}" alt="Profile" id="" class=" w-25">
                                    @endif
                                    @endif
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
                            <form action="{{ route('dashboard.pengaturan.profile.update', $karyawan->slug) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="slug" value="{{ $karyawan->slug }}">
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
                                        <input name="phone" type="text" class="form-control" id="phone"
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
                                    <button type="submit" class="btn btn-primary" id="submit">Save Changes</button>
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
        {{-- crop modal for photo --}}
        <div class="modal fade" id="modal" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header" id="modalLabel">
                        <h4 class="modal-title">Crop Foto</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="img-container">
                            <div class="row">
                                <div class="col-md-8">
                                    <img class="img" id="image-crop" src="{{ asset('storage/img/profile/original/profile.jpg') }}">
                                </div>
                                <div class="col-md-4">
                                    <div class="preview"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                        <button type="button"  class="btn btn-primary" id="crop">Simpan</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@push('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.js"></script>
{{-- <script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.3/dist/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script> --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/js/bootstrap.min.js"
    integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous">
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.6/cropper.js"></script>
<script>
    $(document).ready(function () {
        var $modal = $('#modal');
        var image = document.getElementById('image-crop');
        var cropper;

        $("body").on("change", "#profileImage", function (e) {
            var files = e.target.files;
            var done = function (url) {
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
                    reader.onload = function (e) {
                        done(reader.result);
                    };
                    reader.readAsDataURL(file);
                }
            }
        });
        $modal.on('shown.bs.modal', function () {
            cropper = new Cropper(image, {
                aspectRatio: 1,
                viewMode: 3,
                preview: '.preview'
            });
        }).on('hidden.bs.modal', function () {
            cropper.destroy();
            cropper = null;
        });
        $("#crop").click(function () {
            canvas = cropper.getCroppedCanvas({
                width: 160,
                height: 160,
            });
            canvas.toBlob(function (blob) {
                url = URL.createObjectURL(blob);
                var reader = new FileReader();
                reader.readAsDataURL(blob);
                reader.onloadend = function () {
                    var base64data = reader.result;
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
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
                        success: function (data) {
                             $modal.modal('hide');
                            swal({
                                    title: 'Success!',
                                    text: data['success'],
                                    icon: 'success',
                                })
                                .then(function () {
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
    document.addEventListener('DOMContentLoaded', function () {
        const button_edit_profile = document.querySelector('#button_edit_profile');
        const inputElements = document.querySelectorAll("input");
        const selectOptions = document.getElementById('sex');
        const button_submit = document.querySelector('button[type="submit"]');

        button_submit.disabled = true;

        button_edit_profile.addEventListener('click', function () {
            if (button_edit_profile.classList.contains('editing')) {
                button_submit.disabled = false;
                selectOptions.disabled = false;
                inputElements.forEach(element => {
                    element.removeAttribute('readonly');
                });
                button_edit_profile.classList.remove('editing');
            } else {
                button_submit.disabled = true;
                selectOptions.disabled = true;
                inputElements.forEach(element => {
                    element.setAttribute('readonly', false);
                });
                button_edit_profile.classList.add('editing');
            }
        });

        // delete profile button
        const deleteProfileButton = document.getElementById('profile_delete');
        const url = '{{ route("dashboard.pangaturan.profile.removAvatar") }}';
        deleteProfileButton.addEventListener('click', function () {
            fetch('{{ route("dashboard.pangaturan.profile.removAvatar") }}', {
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
