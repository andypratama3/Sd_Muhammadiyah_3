@extends('layouts.dashboard')
@section('title', 'Detail User')
@section('content')
@push('css')

@endpush
<div class="card mb-4">
    @include('layouts.flashmessage')
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-center">
        <h6 class="m-0 font-weight-bold text-primary">Detail User</h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-12 mb-3 text-center">
            <img src="{{ asset('asset_dashboard/img/default.jpg') }}" alt="Profile" id="" class="w-25 img-profile rounded-circle">

            {{-- @if(Auth::user()->avatar === 'default.jpg')
            <img src="{{ asset('asset_dashboard/img/default.jpg') }}" alt="Profile" id="" class="w-25 img-profile rounded-circle">
            @else
            <img src="{{ asset('storage/img/profile/'. Auth::user()->avatar) }}" class=" img-profile rounded-circle" alt="Profile"
                id="profile">
            @endif --}}
            <hr>
            </div>
            <div class="col-md-6">
                <div class="form-group row">
                    <label class="col-sm-3 text-dark" for="name">Nama</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" name="name" id="name" value="{{ $user->name }}" readonly />
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group row">
                    <label class="col-sm-3 text-dark" for="tmpt_lahir">Email</label>
                    <div class="col-sm-9">
                        <input type="email" class="form-control" name="email" id="email"
                            value="{{ $user->email }}" readonly />
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group row">
                    <label class="col-sm-3 text-dark" for="tgl_lahir">Password</label>
                    <div class="col-sm-9">
                        <input type="password" class="form-control" name="password" id="password"
                            value="{{ $user->password }}" readonly />
                    </div>
                </div>
            </div>

            <div class="col-md-12">
                <div class="form-group row ml-1">
                    <a href="{{ route('dashboard.pengaturan.user.index') }}"
                        class="btn btn-danger float-lg-start mr-2">Kembali</a>
                </div>
            </div>
        </div>
    </div>
</div>
@push('js')

@endpush
@endsection
