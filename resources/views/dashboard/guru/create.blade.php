
@extends('layouts.dashboard')
@section('title', 'Tambah guru')
@push('css')
<link href="{{ asset('asset_dashboard/vendor/select2/dist/css/select2.css') }}" rel="stylesheet" type="text/css">
<link href="{{ asset('asset_dashboard/vendor/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}" rel="stylesheet" type="text/css">
@endpush
@section('content')
<div class="card mb-4">
    @include('layouts.flashmessage')
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">Tambah guru</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('dashboard.datasekolah.guru.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group mt-2">
                <label for="judul">Guru</label>
                <select name="karyawan_id" id="" class="form-control select2">
                    <option selected disabled>Pilih Guru</option>
                    @foreach ($karyawans as $karyawan)
                        <option value="{{ $karyawan->id }}">{{ $karyawan->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group mt-2">
                <label for="">Deskripsi</label>
                <input type="text" class="form-control" id="" name="description" placeholder="Deskripsi">
            </div>
            <div class="form-group mt-2">
                <label for="">Lulusan</label>
                <input type="text" class="form-control" id="" name="lulusan" placeholder="lulusan">
            </div>
            <div class="form-group mt-2">
                <label for="">Pelajaran</label>
                <select name="pelajarans[]" multiple class="form-control select2" aria-placeholder="Pilih Pelajaran">
                    <option disabled>Pilih Pelajaran</option>
                    @foreach ($pelajarans as $pelajaran)
                    <option value="{{ $pelajaran->id }}">{{ $pelajaran->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group mb-2 mt-2">
                <label for="">Foto</label>
                <div class="custom-file">
                    <input type="file" class="form-control" id="foto" name="foto" accept="image/*" onchange="document.getElementById('output').src = window.URL.createObjectURL(this.files[0])">
                </div>
                <div class="mt-3 text-center">
                    <h6 class="">Poto yang di pilih</h6>
                    <img src="" id="output" alt="" style="width: 200px; height: 50%;">
                </div>
            </div>
            <a href="{{ route('dashboard.datasekolah.guru.index') }}" class="btn btn-danger btn-sm float-lg-start">Kembali</a>
            <button type="submit" class="btn btn-primary btn-sm float-lg-end">Simpan</button>
        </form>
    </div>
</div>
@push('js')
<script src="{{ asset('asset_dashboard/vendor/select2/dist/js/select2.js') }}"></script>
<script>
    $(document).ready(function () {
        $('.select2').select2({
            theme: 'bootstrap4',
            placeholder: "Pilih Mata Pelajaran",
            allowClear: true,

        });
    });
</script>
@endpush
@endsection
