@extends('layouts.dashboard')
@section('title', 'Tambah guru')
@push('css')
<link href="{{ asset('asset_dashboard/vendor/select2/dist/css/select2.css') }}" rel="stylesheet" type="text/css">
<link href="{{ asset('asset_dashboard/vendor/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}" rel="stylesheet" type="text/css">
@endpush
@section('content')
<div class="mb-4 card">
    @include('layouts.flashmessage')
    <div class="flex-row py-3 card-header d-flex align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">Tambah guru</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('dashboard.datasekolah.guru.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mt-2 form-group">
                <label class="form-label" for="">Nama Guru</label>
                <input type="text" class="form-control @error('name') is-invalid @enderror" id="" name="name" placeholder="Nama Guru" value="{{ old('name') }}">
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mt-2 form-group">
                <label class="form-label" for="judul">Guru (opsional)</label>
                <select name="karyawan_id" id="" class="form-control select2 @error('karyawan_id') is-invalid @enderror">
                    <option selected disabled>Pilih Guru</option>
                    @foreach ($karyawans as $karyawan)
                        <option value="{{ $karyawan->id }}" {{ old('karyawan_id') == $karyawan->id ? 'selected' : '' }}>{{ $karyawan->name }}</option>
                    @endforeach
                </select>
                @error('karyawan_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mt-2 form-group">
                <label class="form-label" for="">Deskripsi</label>
                <input type="text" class="form-control @error('description') is-invalid @enderror" id="" name="description" placeholder="Deskripsi" value="{{ old('description') }}">
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mt-2 form-group">
                <label class="form-label" for="">Lulusan</label>
                <input type="text" class="form-control @error('lulusan') is-invalid @enderror" id="" name="lulusan" placeholder="lulusan" value="{{ old('lulusan') }}">
                @error('lulusan')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mt-2 form-group">
                <label class="form-label" for="">Pelajaran</label>
                <select name="pelajarans[]" multiple class="form-control select2 @error('pelajarans') is-invalid @enderror" aria-placeholder="Pilih Pelajaran">
                    <option disabled>Pilih Pelajaran</option>
                    @foreach ($pelajarans as $pelajaran)
                        <option value="{{ $pelajaran->id }}" {{ in_array($pelajaran->id, old('pelajarans', [])) ? 'selected' : '' }}>{{ $pelajaran->name }}</option>
                    @endforeach
                </select>
                @error('pelajarans')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mt-2 mb-2 form-group">
                <label class="form-label" for="">Foto</label>
                <div class="custom-file">
                    <input type="file" class="form-control @error('foto') is-invalid @enderror" id="foto" name="foto" accept="image/*" onchange="document.getElementById('output').src = window.URL.createObjectURL(this.files[0])">
                </div>
                @error('foto')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div class="mt-3 text-center">
                    <h6 class="">Foto yang di pilih</h6>
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
