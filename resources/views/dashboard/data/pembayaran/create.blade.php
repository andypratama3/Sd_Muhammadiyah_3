@extends('layouts.dashboard')
@section('title', 'Pembayaran')
@section('content')
@push('css')
<link href="{{ asset('asset_dashboard/vendor/select2/dist/css/select2.css') }}" rel="stylesheet" type="text/css">
<link href="{{ asset('asset_dashboard/vendor/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}" rel="stylesheet"
    type="text/css">
@endpush
<div class="card mb-4">
    @include('layouts.flashmessage')
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">Tambah Pembayaran</h6>
    </div>
    <hr>
    <div class="card-body">
        <form action="{{ route('dashboard.datamaster.siswa.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group row">
                        <label class="col-sm-3 text-dark" for="name">Judul Pembayaran</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control"  name="name" id="name" value="{{ old('name') }}"/>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group row">
                        <label class="col-sm-3 text-dark" for="tgl_lahir">Nama Siswa</label>
                        <div class="col-sm-9">
                            <select class="form-control select2" name="siswa_id" id="">
                                <option selected disabled>Pilih Siswa</option>
                                @foreach ($siswas as $siswa)
                                <option value="{{ $siswa->id }}">{{ $siswa->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group row">
                        <label class="col-sm-3 text-dark" for="kelas">Kelas</label>
                        <div class="col-sm-9">
                            <select name="kelas" id="kelas" class="select2 form-control" data-placholder="Pilih Kelas">
                                <option selected disabled>Pilih Kelas</option>
                                @foreach ($kelass as $kelas)
                                    <option value="{{ $kelas->id }}">{{ $kelas->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group row">
                        <label class="col-sm-3 text-dark" for="category_kelas">Kategori Kelas</label>
                        <div class="col-sm-9">
                            <select name="category_kelas" id="category_kelas" class="select2 form-control" data-placholder="Pilih Kategori Kelas">
                                <option selected disabled>Pilih Kategori Kelas</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group row">
                        <label class="col-sm-3 text-dark" for="category_kelas">Total Pembayaran</label>
                        <div class="col-sm-9">
                            <input type="text" name="total" class="form-control" id="total">
                        </div>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group row justify-content-end">
                        <a href="{{ route('dashboard.datamaster.siswa.index') }}" class="btn btn-danger float-lg-start mr-2">Kembali</a>
                        <button type="submit" class="btn btn-primary float-lg-right">Submit</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@push('js')
<script src="{{ asset('asset_dashboard/vendor/select2/dist/js/select2.js') }}"></script>
<script>
    $(document).ready(function () {
        $('.select2').select2({
            theme: 'bootstrap4'
        });
    });
</script>
@endpush
@endsection
