@extends('layouts.dashboard')
@section('title','Edit Karyawan')

@section('content')
<div class="row">
    <div class="col-lg-12">
        <!-- Form Basic -->
        <div class="card mb-4">

            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary text-center">Tambah Karyawan</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('dashboard.pengaturan.karyawan.update', $karyawan->slug) }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="slug" value="{{ $karyawan->slug }}">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Nama Karyawan <code>*</code></label>
                                <input type="text" class="form-control" id="name" name="name"
                                    placeholder="Masukkan Nama Karyawan" value="{{ $karyawan->name }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Jenis Kelamin<code>*</code></label>
                                <select name="sex" id="" class="form-control">
                                    <option value="{{ $karyawan->sex }}">{{ $karyawan->sex }}</option>
                                    <option value="Laki-Laki">Laki Laki</option>
                                    <option value="Perempuan">Perempuan</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Email Karyawan <code>*</code></label>
                                <input type="text" class="form-control" name="email" placeholder="Email Karyawan" value="{{ $karyawan->user->email }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Nomor Hp <code>*</code></label>
                                <input type="text" class="form-control" id="phone" name="phone"
                                    placeholder="Masukkan Email Karyawan" value="{{ $karyawan->phone }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Role Karyawan <code>*</code></label>
                                <select class="form-control  {{ $errors->has('role_id') ? 'is-invalid' : '' }} select2bs4" id="role_id" name="role_id" style="width: 100%;">
                                    @foreach ($roles as $role)
                                        <option value="{{ $role->id }}" @foreach ($karyawan->user->roles as $role_id) @if ($role->id == $role_id->id) selected @endif @endforeach >{{ $role->name }}</option>
                                    @endforeach
                                </select>
                                @if ($errors->has('role_id'))
                                    <div class="invalid-feedback">{{ $errors->first('role_id') }}</div>
                                @endif
                            </div>
                        </div>
                    </div>
            </div>
            <div class="card-footer">
                <button onclick="window.location.href='{{ route('dashboard.pengaturan.karyawan.index') }}'" type="button" class="btn btn-sm btn-danger" aria-hidden="true">Cancel</button>
                <button type="submit" class="btn btn-sm btn-primary  float-lg-right">Submit</button>
            </div>
            </form>
        </div>
        <!-- /.card -->
    </div>
    <!-- /.col-md-6 -->
</div>
<!-- /.row -->
@push('js')
<script src="{{ asset('asset_dashboard/vendor/select2/dist/js/select2.min.js') }}"></script>
{{-- <script>
$(function () {
    //Initialize Select2 Elements
    $('.select2bs4').select2({
        theme: 'bootstrap4'
    })
})
</script> --}}
@endpush
@endsection
