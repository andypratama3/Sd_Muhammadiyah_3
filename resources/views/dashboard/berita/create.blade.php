@extends('layouts.dashboard')
@section('title', 'Tambah Berita')
@section('content')
<div class="card mb-4">
    @include('layouts.flashmessage')
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">Tambah Berita</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('dashboard.news.berita.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label for="judul">Judul</label>
                <input type="text" class="form-control" name="judul" id="judul" value="{{ old('judul') }}" placeholder="Masukan Judul">
            </div>
            <div class="form-group">
                <label for="">Deskripsi</label>
                <input type="text" class="form-control" id="" name="desc" value="{{ old('desc') }}" placeholder="Deskripsi">
            </div>
            <div class="form-group">
                <label for="">Foto</label>
                <div class="custom-file">
                    <input type="file" class="form-control" id="foto" name="foto" accept="image/jpeg,image/png,application/pdf,image" value="{{ old('foto') }}" onchange="document.getElementById('output').src = window.URL.createObjectURL(this.files[0])">
                </div>
                <div class="mt-3 text-center">
                    <h6 class="">Poto yang di pilih</h6>
                    <img src="" id="output" alt="" style="width: 200px; height: 50%;">
                </div>
            </div>
            <a  href="{{ route('dashboard.news.berita.index') }}" class="btn btn-danger float-lg-start">Kembali</a>
            <button type="submit" class="btn btn-primary float-lg-right">Submit</button>
        </form>
    </div>
</div>
@endsection
