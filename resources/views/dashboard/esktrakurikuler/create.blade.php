@extends('layouts.dashboard')
@section('title', 'Tambah Esktrakurikuler')
@section('content')
<div class="mb-4 card">
    @include('layouts.flashmessage')
    <div class="flex-row py-3 card-header d-flex align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">Tambah Esktrakurikuler</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('dashboard.datasekolah.ekstrakurikuler.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mt-2 form-group">
                <label for="judul">Nama</label>
                <input type="text" class="form-control" name="name" id="name" placeholder="Masukan name" value="{{ old('name') }}">
            </div>
            <div class="mt-2 form-group">
                <label for="">Deskripsi</label>
                <input type="text" class="form-control" id="" name="desc" value="{{ old('desc') }}" placeholder="Deskripsi">
            </div>
            <div class="mt-2 form-group">
                <label for="">Kategori</label>
                <select name="kategori" id="kategori" class="form-control">
                    <option value="">Pilih Kategori</option>
                    <option value="seni">Seni</option>
                    <option value="olahraga">Olahraga</option>
                    <option value="sains">Sains</option>
                    <option value="islami">Islami</option>
                    <option value="lainnya">Lainnya</option>
                </select>
            </div>
            <div class="mt-2 form-group">
                <label for="jam">Jam</label>
                <input type="time" class="form-control" id="jam" name="jam" placeholder="Masukan jam" value="{{ old('jam') }}">
            </div>
            <div class="mt-2 form-group">
                <label for="guru">Guru</label>
                <input type="text" class="form-control" id="guru" name="guru" placeholder="Masukan guru" value="{{ old('guru') }}">
            </div>
            <div class="mt-2 form-group">
                <label for="kelas">Kelas</label>
                <input type="text" class="form-control" id="kelas" name="kelas" placeholder="Masukan kelas" value="{{ old('kelas') }}">
            </div>

            <div class="mt-2 mb-2 form-group">
                <label for="">Foto</label>
                <div class="custom-file">
                    <input type="file" class="form-control" id="foto" multiple name="foto[]">
                </div>
                <div class="mt-3 text-center">
                    <h6 class="">Poto yang di pilih</h6>
                    <img src="" id="output" alt="" style="width: 200px; height: 50%;">
                </div>
            </div>
            <a  href="{{ route('dashboard.datasekolah.ekstrakurikuler.index') }}" class="btn btn-danger btn-sm float-lg-start">Kembali</a>
            <button type="submit" class="btn btn-primary btn-sm float-lg-end">Submit</button>
        </form>
    </div>
</div>
@endsection
